<x-layouts.print title="Certificate of rank">
    @php $user = $ranking->user; @endphp
    <div class="relative overflow-hidden rounded-2xl bg-white p-3 shadow-(--shadow-lift) print:shadow-none">
        <div class="relative rounded-xl border-[3px] border-double border-gold-500 px-8 py-14 text-center sm:px-16">
            <div class="pointer-events-none absolute inset-0 grid place-items-center opacity-[0.04]">
                <svg viewBox="0 0 24 24" class="size-[28rem] text-brand-900" fill="none" stroke="currentColor" stroke-width="1"><path d="M22 10 12 5 2 10l10 5 10-5Z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>
            </div>

            <p class="text-sm font-semibold tracking-[0.3em] text-brand-800 uppercase">Divine Word College of Legazpi</p>
            <p class="text-xs text-slate-500">Legazpi City, Albay · Society of the Divine Word</p>

            <h1 class="mt-10 font-serif text-4xl text-brand-900 sm:text-5xl">Certificate of Rank</h1>
            <p class="mt-8 text-slate-600">This certifies that</p>
            <p class="mt-3 font-serif text-3xl text-slate-900">{{ $user->full_name }}</p>
            <div class="mx-auto mt-2 h-px w-72 bg-gold-400"></div>
            <p class="mt-6 text-slate-600">of the {{ $user->department?->name }}, having been evaluated by the ranking committees under the<br class="hidden sm:inline"> {{ $ranking->rubric->name }} for the {{ $ranking->cycle }} cycle, is hereby conferred the academic rank of</p>
            <p class="mt-6 inline-block rounded-xl bg-brand-900 px-8 py-3 text-2xl font-semibold tracking-wide text-gold-300">{{ $ranking->recommendedRank?->name ?? '—' }}</p>
            <p class="mt-6 text-sm text-slate-500">with a total of <strong class="text-slate-800">{{ number_format($ranking->effectiveTotal(), 2) }} points</strong>, effective {{ $ranking->certified_at->format('F j, Y') }}.</p>

            <div class="mt-16 grid gap-10 text-sm sm:grid-cols-3">
                @foreach (['President', 'Vice President for Academic Affairs', 'Vice President for Finance'] as $signatory)
                    <div><div class="mx-auto h-px w-44 bg-slate-400"></div><p class="mt-2 font-medium text-slate-700">{{ $signatory }}</p></div>
                @endforeach
            </div>
            <p class="mt-12 text-xs text-slate-400">Certificate No. {{ $ranking->certificate_no }} · Prepared by the Human Resources and Development Office</p>
        </div>
    </div>
</x-layouts.print>
