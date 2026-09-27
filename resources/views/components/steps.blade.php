{{-- Horizontal progress tracker. $steps: labels; $current: index of the step in progress (count($steps) = all done); $failed marks it red. --}}
@props(['steps', 'current', 'failed' => false])
<ol class="flex w-full items-start">
    @foreach ($steps as $i => $label)
        @php
            $done = $i < $current;
            $active = $i === $current;
        @endphp
        <li class="relative flex flex-1 flex-col items-center text-center">
            @if (! $loop->first)
                <span class="absolute top-3.5 right-1/2 left-[-50%] h-0.5 transition-colors duration-500 {{ $i <= $current ? 'bg-brand-600' : 'bg-slate-200' }}"></span>
            @endif
            <span @class([
                'relative z-[1] grid size-7 place-items-center rounded-full text-xs font-semibold ring-4 ring-white transition-all duration-500',
                'bg-brand-700 text-white' => $done,
                'scale-110 bg-gold-500 text-brand-950' => $active && ! $failed,
                'bg-rose-500 text-white' => $active && $failed,
                'bg-slate-200 text-slate-500' => ! $done && ! $active,
            ])>
                @if ($done)<x-icon name="check" class="size-3.5" />@else{{ $i + 1 }}@endif
            </span>
            <span class="mt-2 hidden px-1 text-[11px] leading-tight font-medium sm:block {{ $i === $current ? 'text-slate-900' : 'text-slate-500' }}">{{ $label }}</span>
        </li>
    @endforeach
</ol>
