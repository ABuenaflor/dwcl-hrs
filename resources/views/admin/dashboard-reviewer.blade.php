<x-layouts.app title="Review queue">
    <x-page-header :title="'Welcome, '.auth()->user()->first_name" :subtitle="auth()->user()->role->label().' — faculty rankings that need your review.'" />

    <div class="grid gap-6 lg:grid-cols-3">
        <section class="card overflow-hidden lg:col-span-2">
            <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
                <h2 class="text-sm font-semibold text-slate-900">Awaiting your action <span class="ml-1 rounded-full bg-slate-100 px-2 py-0.5 text-xs text-slate-600">{{ $awaiting->count() }}</span></h2>
                <a href="{{ route('admin.rankings.index') }}" class="text-xs font-medium text-brand-700 hover:underline">All rankings</a>
            </div>
            @include('admin._ranking-queue')
        </section>
        <aside class="card p-6">
            <h2 class="text-sm font-semibold text-slate-900">Your part in the process</h2>
            <ol class="mt-4 space-y-4 text-sm">
                @foreach ([['drc', 'DRC', 'Checks each claim against the evidence and records the DRC score.'], ['crtc', 'CRTC / BERTC', 'Reviews the DRC assessment and records the final score.'], ['vp', 'VPAA / VPBE', 'Endorses the ranking to the President.'], ['admin', 'HRDO', "Records the President's approval and issues the certificate."]] as [$role, $who, $what])
                    <li @class(['rounded-xl p-3', 'bg-gold-50 ring-1 ring-gold-300' => auth()->user()->role->value === $role, 'bg-slate-50' => auth()->user()->role->value !== $role])>
                        <p class="font-semibold text-slate-900">{{ $who }}</p>
                        <p class="text-slate-600">{{ $what }}</p>
                    </li>
                @endforeach
            </ol>
        </aside>
    </div>
</x-layouts.app>
