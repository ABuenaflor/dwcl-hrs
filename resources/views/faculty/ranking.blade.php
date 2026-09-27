<x-layouts.app :title="'Ranking '.$ranking->cycle">
    <x-page-header :title="$editable ? 'Self-rating' : 'My ranking'" :subtitle="'Cycle '.$ranking->cycle" :back="route('faculty.dashboard')">
        @if ($ranking->status === App\Enums\RankingStatus::Certified)
            <a href="{{ route('faculty.rankings.certificate', $ranking) }}" class="btn btn-gold"><x-icon name="award" class="size-4" /> Certificate of rank</a>
        @endif
    </x-page-header>

    <div class="space-y-6">
        @include('rankings._summary')

        @if ($ranking->status === App\Enums\RankingStatus::Returned && ($last = $ranking->reviews->first()))
            <div class="card flex gap-4 border-rose-200 bg-rose-50/60 p-5">
                <span class="grid size-10 shrink-0 place-items-center rounded-xl bg-rose-100 text-rose-600"><x-icon name="undo" /></span>
                <div class="text-sm">
                    <p class="font-semibold text-slate-900">Returned by {{ $last->user?->name }} for revision</p>
                    <p class="mt-1 text-slate-700">{{ $last->remarks }}</p>
                </div>
            </div>
        @endif

        @if ($editable)
            <form method="POST" action="{{ route('faculty.rankings.update', $ranking) }}" enctype="multipart/form-data" class="space-y-6">
                @csrf @method('PUT')
                <div class="flex flex-wrap items-center gap-x-6 gap-y-2 text-xs text-slate-500">
                    <span><strong class="text-slate-700">SR</strong> Self-rating</span>
                    <span><strong class="text-slate-700">DRC</strong> Department Ranking Committee</span>
                    <span><strong class="text-slate-700">{{ $ranking->rubric->level->councilName() }}</strong> {{ $ranking->rubric->level === App\Enums\Level::BasicEd ? 'Basic Ed Rank & Tenure Committee' : 'College Rank & Tenure Council' }}</span>
                    <span class="flex items-center gap-1"><x-icon name="upload" class="size-3.5" /> Attach evidence (PDF/JPG/PNG, 5 MB)</span>
                </div>

                @include('rankings._table', ['edit' => 'sr_points', 'evidence' => 'upload'])

                <div class="card sticky bottom-4 z-10 flex flex-col gap-3 p-4 shadow-(--shadow-lift) sm:flex-row sm:items-center sm:justify-between">
                    <p class="text-sm text-slate-500">Save as often as you like. Once submitted, the form locks until a committee returns it.</p>
                    <div class="flex gap-3">
                        <button class="btn btn-secondary" name="submit" value="0"><span class="btn-label">Save draft</span></button>
                        <button type="button" class="btn btn-primary" x-data x-on:click="$dispatch('open-modal', 'submit-ranking')"><x-icon name="send" class="size-4" /> Submit to DRC</button>
                    </div>
                </div>

                <x-modal name="submit-ranking" title="Submit your self-rating?">
                    <p class="text-sm text-slate-600">Your scores and evidence go to the Department Ranking Committee. You won't be able to edit them unless the committee returns the form.</p>
                    <div class="mt-6 flex justify-end gap-3">
                        <button type="button" class="btn btn-secondary" x-on:click="$dispatch('close-modal')">Keep editing</button>
                        <button class="btn btn-primary" name="submit" value="1"><span class="btn-label">Save &amp; submit</span></button>
                    </div>
                </x-modal>
            </form>
        @else
            @include('rankings._table', ['edit' => null, 'evidence' => 'view'])
        @endif

        @include('rankings._history')
    </div>
</x-layouts.app>
