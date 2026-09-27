@props(['light' => false])
<span {{ $attributes->merge(['class' => 'flex items-center gap-2.5']) }}>
    <span class="grid size-9 place-items-center rounded-xl bg-gradient-to-br from-gold-400 to-gold-600 shadow-sm">
        <svg viewBox="0 0 24 24" class="size-5 text-brand-950" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M22 10 12 5 2 10l10 5 10-5Z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/>
        </svg>
    </span>
    <span class="leading-tight">
        <span class="block text-sm font-bold tracking-tight {{ $light ? 'text-white' : 'text-brand-900' }}">DWCL HRDO</span>
        <span class="block text-[11px] font-medium {{ $light ? 'text-brand-200' : 'text-slate-500' }}">Hiring &amp; Faculty Ranking</span>
    </span>
</span>
