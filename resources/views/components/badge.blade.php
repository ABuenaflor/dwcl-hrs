@props(['tone' => 'neutral', 'dot' => true])
@php
    $tones = [
        'neutral' => 'bg-slate-100 text-slate-600 ring-slate-500/15',
        'info' => 'bg-sky-50 text-sky-700 ring-sky-600/15',
        'brand' => 'bg-brand-50 text-brand-700 ring-brand-600/20',
        'success' => 'bg-emerald-50 text-emerald-700 ring-emerald-600/20',
        'warning' => 'bg-amber-50 text-amber-800 ring-amber-600/20',
        'danger' => 'bg-rose-50 text-rose-700 ring-rose-600/20',
        'gold' => 'bg-gold-50 text-gold-700 ring-gold-600/25',
    ];
@endphp
<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-medium ring-1 ring-inset whitespace-nowrap '.($tones[$tone] ?? $tones['neutral'])]) }}>
    @if ($dot)<span class="size-1.5 rounded-full bg-current opacity-70"></span>@endif
    {{ $slot }}
</span>
