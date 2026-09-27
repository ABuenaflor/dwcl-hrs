{{-- Educational background, one collapsible row per level. Expects $user with educations loaded. --}}
@php $byLevel = $user->educations->keyBy(fn ($e) => $e->level->value); @endphp
<div class="space-y-3">
    @foreach (App\Enums\EducationLevel::cases() as $level)
        @php
            $e = $byLevel->get($level->value);
            $key = "education.{$level->value}";
            $filled = $e || old("{$key}.school") || $level === App\Enums\EducationLevel::Secondary;
        @endphp
        <div class="rounded-xl border border-slate-200" x-data="{ open: {{ $filled ? 'true' : 'false' }} }">
            <button type="button" class="flex w-full items-center justify-between px-4 py-3 text-left" x-on:click="open = ! open">
                <span class="flex items-center gap-3">
                    <span class="grid size-8 place-items-center rounded-lg bg-brand-50 text-brand-700"><x-icon name="graduation" class="size-4" /></span>
                    <span>
                        <span class="block text-sm font-semibold text-slate-900">{{ $level->label() }}@if ($level === App\Enums\EducationLevel::Secondary)<span class="text-rose-500"> *</span>@endif</span>
                        <span class="block text-xs text-slate-500">{{ $e?->school ?? 'Not added' }}</span>
                    </span>
                </span>
                <x-icon name="chevron-down" class="size-4 text-slate-400 transition-transform duration-300" ::class="open && 'rotate-180'" />
            </button>
            <div x-show="open" x-collapse>
                <div class="grid gap-4 border-t border-slate-100 p-4 sm:grid-cols-6">
                    <x-field class="sm:col-span-3" name="education[{{ $level->value }}][school]" label="School" :value="$e?->school" />
                    <x-field class="sm:col-span-3" name="education[{{ $level->value }}][course]" label="{{ in_array($level->value, ['elementary', 'secondary']) ? 'Track / strand' : 'Degree / course' }}" :value="$e?->course" />
                    <x-field class="sm:col-span-2" name="education[{{ $level->value }}][inclusive_dates]" label="Inclusive dates" :value="$e?->inclusive_dates" placeholder="2015 – 2019" />
                    <x-field class="sm:col-span-2" name="education[{{ $level->value }}][year_graduated]" label="Year graduated" type="number" :value="$e?->year_graduated" />
                    <x-field class="sm:col-span-2" name="education[{{ $level->value }}][honors]" label="Honors received" :value="$e?->honors" />
                </div>
            </div>
        </div>
    @endforeach
</div>
