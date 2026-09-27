@props(['label', 'value', 'icon' => 'chart', 'tone' => 'brand', 'hint' => null, 'href' => null])
@php
    $tones = [
        'brand' => 'bg-brand-50 text-brand-700',
        'gold' => 'bg-gold-50 text-gold-700',
        'success' => 'bg-emerald-50 text-emerald-700',
        'warning' => 'bg-amber-50 text-amber-700',
    ];
    $tag = $href ? 'a' : 'div';
@endphp
<{{ $tag }} @if ($href) href="{{ $href }}" @endif {{ $attributes->merge(['class' => 'card flex items-start gap-4 p-5'.($href ? ' card-hover' : '')]) }}>
    <div class="grid size-11 shrink-0 place-items-center rounded-xl {{ $tones[$tone] ?? $tones['brand'] }}">
        <x-icon :name="$icon" class="size-5" />
    </div>
    <div class="min-w-0">
        <p class="text-sm font-medium text-slate-500">{{ $label }}</p>
        <p class="mt-1 text-2xl font-semibold tracking-tight text-slate-900 tabular-nums">{{ $value }}</p>
        @if ($hint)<p class="mt-0.5 text-xs text-slate-500">{{ $hint }}</p>@endif
    </div>
</{{ $tag }}>
