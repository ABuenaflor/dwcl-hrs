<x-layouts.app title="Dashboard">
    <x-page-header :title="'Hello, '.auth()->user()->first_name" subtitle="Track your applications and discover new openings.">
        <a href="{{ route('careers.index') }}" class="btn btn-primary"><x-icon name="search" class="size-4" /> Browse vacancies</a>
    </x-page-header>

    @unless ($hasProfile)
        <div class="card mb-6 flex flex-col gap-4 border-gold-300 bg-gold-50/60 p-5 sm:flex-row sm:items-center">
            <span class="grid size-10 shrink-0 place-items-center rounded-xl bg-gold-100 text-gold-700"><x-icon name="user" /></span>
            <div class="flex-1">
                <p class="font-semibold text-slate-900">Complete your profile</p>
                <p class="text-sm text-slate-600">Fill it in once and every application is pre-filled.</p>
            </div>
            <a href="{{ route('applicant.profile.edit') }}" class="btn btn-gold">Complete profile</a>
        </div>
    @endunless

    <div class="grid gap-6 lg:grid-cols-3">
        <section class="lg:col-span-2">
            <h2 class="mb-3 text-sm font-semibold text-slate-900">My applications</h2>
            @forelse ($applications as $app)
                @php
                    $flow = ['Submitted', 'Shortlisted', 'Interview', 'Offer', 'Hired'];
                    $at = match ($app->status) {
                        App\Enums\ApplicationStatus::Submitted => 0, App\Enums\ApplicationStatus::Shortlisted => 1,
                        App\Enums\ApplicationStatus::Interview => 2, App\Enums\ApplicationStatus::Offered => 3, default => 5,
                    };
                @endphp
                <a href="{{ route('applicant.applications.show', $app) }}" class="card card-hover mb-4 block p-5">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <p class="font-semibold text-slate-900">{{ $app->jobPosting->title }}</p>
                            <p class="text-sm text-slate-500">{{ $app->jobPosting->department->name }} · Applied {{ $app->created_at->format('M j, Y') }}</p>
                        </div>
                        <x-badge :tone="$app->status->tone()">{{ $app->status->label() }}</x-badge>
                    </div>
                    @unless (in_array($app->status, [App\Enums\ApplicationStatus::Withdrawn, App\Enums\ApplicationStatus::Rejected, App\Enums\ApplicationStatus::Declined]))
                        <div class="mt-5"><x-steps :steps="$flow" :current="$at" /></div>
                    @endunless
                    @if ($app->status === App\Enums\ApplicationStatus::Interview && $app->interview_at)
                        <p class="mt-4 flex items-center gap-2 rounded-lg bg-brand-50 px-3 py-2 text-sm text-brand-800"><x-icon name="calendar" class="size-4" /> Interview on {{ $app->interview_at->format('l, M j \a\t g:i A') }}</p>
                    @elseif ($app->status === App\Enums\ApplicationStatus::Offered)
                        <p class="mt-4 flex items-center gap-2 rounded-lg bg-gold-50 px-3 py-2 text-sm text-gold-700"><x-icon name="sparkles" class="size-4" /> You have a job offer — open to respond.</p>
                    @endif
                </a>
            @empty
                <div class="card"><x-empty icon="briefcase" title="No applications yet" message="Find a vacancy that fits you and apply in a few minutes.">
                    <a href="{{ route('careers.index') }}" class="btn btn-primary">Browse vacancies</a>
                </x-empty></div>
            @endforelse
        </section>

        <aside>
            <h2 class="mb-3 text-sm font-semibold text-slate-900">Open positions you may like</h2>
            <div class="space-y-3">
                @forelse ($recommended as $posting)
                    <a href="{{ route('careers.show', $posting) }}" class="card card-hover group block p-4">
                        <p class="font-medium text-slate-900 group-hover:text-brand-700">{{ $posting->title }}</p>
                        <p class="mt-0.5 text-xs text-slate-500">{{ $posting->department->name }} · {{ $posting->employment_type->label() }}</p>
                    </a>
                @empty
                    <p class="text-sm text-slate-500">You've applied to every open position.</p>
                @endforelse
            </div>
        </aside>
    </div>
</x-layouts.app>
