<x-layouts.app :title="$ranking->user->name.' — ranking'">
    @php $user = $ranking->user; @endphp
    <x-page-header :title="$user->full_name" :subtitle="($user->designation ?? 'Faculty').' · '.$user->department?->name" :back="route('admin.rankings.index')">
        @if ($ranking->status === App\Enums\RankingStatus::Certified)
            <a href="{{ route('admin.rankings.certificate', $ranking) }}" class="btn btn-gold"><x-icon name="award" class="size-4" /> Certificate of rank</a>
        @endif
    </x-page-header>

    <div class="space-y-6">
        @include('rankings._summary')

        @if ($canAct)
            <form method="POST" action="{{ route('admin.rankings.update', $ranking) }}" class="space-y-6">
                @csrf @method('PUT')

                @if ($column)
                    <div class="card flex gap-4 border-gold-300 bg-gold-50/60 p-5">
                        <span class="grid size-10 shrink-0 place-items-center rounded-xl bg-gold-100 text-gold-700"><x-icon name="pencil" /></span>
                        <p class="text-sm text-slate-700">Enter the <strong>{{ $column === 'drc_points' ? 'DRC' : $ranking->rubric->level->councilName() }}</strong> points for each criterion after checking the attached evidence. Subtotals and caps update as you type.</p>
                    </div>
                @endif

                @include('rankings._table', ['edit' => $column, 'evidence' => 'view'])

                <div class="card sticky bottom-4 z-10 p-4 shadow-(--shadow-lift)">
                    <div class="flex flex-col gap-3 lg:flex-row lg:items-end">
                        <div class="flex-1">
                            <label for="remarks" class="form-label">Remarks <span class="font-normal text-slate-400">(recorded in the review history)</span></label>
                            <input id="remarks" name="remarks" value="{{ old('remarks') }}" class="form-control" placeholder="Optional note for the next reviewer">
                        </div>
                        <div class="flex flex-wrap gap-2">
                            @if ($ranking->status->canBeReturned())
                                <button type="button" class="btn btn-secondary text-rose-600" x-data x-on:click="$dispatch('open-modal', 'return')"><x-icon name="undo" class="size-4" /> Return</button>
                            @endif
                            @if ($column)
                                <button class="btn btn-secondary" name="forward" value="0"><span class="btn-label">Save scores</span></button>
                            @endif
                            <button class="btn btn-primary" name="forward" value="1"><x-icon name="send" class="size-4" /><span class="btn-label">{{ $ranking->status->forwardLabel() }}</span></button>
                        </div>
                    </div>
                </div>
            </form>

            <x-modal name="return" title="Return to faculty for revision">
                <form method="POST" action="{{ route('admin.rankings.return', $ranking) }}" class="space-y-4">@csrf
                    <p class="text-sm text-slate-600">The self-rating unlocks so {{ $user->first_name }} can correct scores or attach missing evidence, then resubmit.</p>
                    <x-field name="remarks" label="What needs to change?" type="textarea" rows="4" required placeholder="e.g. Please attach the certificate for the national seminar claimed under 1.2.2." />
                    <div class="flex justify-end gap-3">
                        <button type="button" class="btn btn-secondary" x-on:click="$dispatch('close-modal')">Cancel</button>
                        <button class="btn btn-danger"><span class="btn-label">Return ranking</span></button>
                    </div>
                </form>
            </x-modal>
        @else
            @if ($ranking->status->actor())
                <div class="card flex items-center gap-3 p-4 text-sm text-slate-600">
                    <x-icon name="clock" class="size-5 text-slate-400" /> Waiting on the {{ $ranking->status->actor()->label() }}. You can view but not change this ranking.
                </div>
            @endif
            @include('rankings._table', ['edit' => null, 'evidence' => 'view'])
        @endif

        @include('rankings._history')
    </div>
</x-layouts.app>
