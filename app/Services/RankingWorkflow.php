<?php

namespace App\Services;

use App\Enums\AccountStatus;
use App\Enums\RankingStatus;
use App\Enums\Role;
use App\Models\AcademicRank;
use App\Models\FacultyRanking;
use App\Models\User;
use App\Notifications\Alert;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use InvalidArgumentException;

/**
 * Moves a faculty ranking through the committees, keeps its totals and
 * recommended rank current, logs each hand-off and notifies whoever acts next.
 */
class RankingWorkflow
{
    public function __construct(private RubricCalculator $calculator) {}

    public function canAct(User $user, FacultyRanking $ranking): bool
    {
        $actor = $ranking->status->actor();

        return $actor !== null && ($user->isAdmin() || $user->role === $actor);
    }

    public function recalculate(FacultyRanking $ranking): void
    {
        $tree = $ranking->rubric->tree();
        $scores = $ranking->scores()->get();

        $total = fn (string $column) => $scores->whereNotNull($column)->isEmpty()
            ? null
            : $this->calculator->calculate($tree, $scores->pluck($column, 'rubric_item_id')->all())['total'];

        $ranking->sr_total = $total('sr_points') ?? 0;
        $ranking->drc_total = $total('drc_points');
        $ranking->final_total = $total('final_points');
        $ranking->recommended_rank_id = AcademicRank::forPoints($ranking->rubric->level, $ranking->effectiveTotal())?->id;
        $ranking->save();
    }

    public function submit(FacultyRanking $ranking, User $by): void
    {
        if (! $ranking->status->isEditableByFaculty()) {
            throw new InvalidArgumentException('This ranking has already been submitted.');
        }

        $this->transition($ranking, $by, RankingStatus::Submitted, null, ['submitted_at' => now()]);

        $this->notifyRole(Role::Drc, 'Self-rating awaiting review',
            "{$ranking->user->name} submitted a self-rating for cycle {$ranking->cycle}.", $ranking);
    }

    public function forward(FacultyRanking $ranking, User $by, ?string $remarks = null): void
    {
        $next = $ranking->status->next();

        if ($next === null || ! $this->canAct($by, $ranking)) {
            throw new InvalidArgumentException('You cannot forward this ranking at its current stage.');
        }

        $extra = match ($next) {
            RankingStatus::Approved => ['approved_at' => now()],
            RankingStatus::Certified => ['certified_at' => now(), 'certificate_no' => $this->certificateNo($ranking)],
            default => [],
        };

        $this->transition($ranking, $by, $next, $remarks, $extra);

        if ($next === RankingStatus::Certified && $ranking->recommended_rank_id) {
            $ranking->user->update(['academic_rank_id' => $ranking->recommended_rank_id]);
        }

        if ($actor = $next->actor()) {
            $this->notifyRole($actor, 'Faculty ranking awaiting action',
                "{$ranking->user->name}'s ranking is now {$next->label()}.", $ranking);
        }

        $ranking->user->notify(new Alert(
            'Ranking update',
            "Your faculty ranking for {$ranking->cycle} moved to: {$next->label()}.",
            route('faculty.rankings.show', $ranking),
            $next === RankingStatus::Certified ? 'success' : 'info',
        ));
    }

    public function return(FacultyRanking $ranking, User $by, string $remarks): void
    {
        if (! $ranking->status->canBeReturned() || ! $this->canAct($by, $ranking)) {
            throw new InvalidArgumentException('You cannot return this ranking at its current stage.');
        }

        $this->transition($ranking, $by, RankingStatus::Returned, $remarks);

        $ranking->user->notify(new Alert(
            'Ranking returned for revision',
            $remarks,
            route('faculty.rankings.show', $ranking),
            'danger',
        ));
    }

    private function transition(FacultyRanking $ranking, User $by, RankingStatus $to, ?string $remarks, array $extra = []): void
    {
        DB::transaction(function () use ($ranking, $by, $to, $remarks, $extra) {
            $ranking->reviews()->create([
                'user_id' => $by->id,
                'from_status' => $ranking->status,
                'to_status' => $to,
                'remarks' => $remarks,
            ]);

            $ranking->fill(['status' => $to] + $extra)->save();
        });
    }

    private function notifyRole(Role $role, string $title, string $message, FacultyRanking $ranking): void
    {
        $users = User::role($role)->where('status', AccountStatus::Active)->get();

        Notification::send($users, new Alert($title, $message, route('admin.rankings.show', $ranking), 'warning'));
    }

    private function certificateNo(FacultyRanking $ranking): string
    {
        $prefix = $ranking->rubric->level->value === 'basic_ed' ? 'BE' : 'CO';

        return sprintf('DWCL-%s-%s-%05d', $prefix, now()->format('Y'), $ranking->id);
    }
}
