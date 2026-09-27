<x-layouts.public title="Careers">
    {{-- Hero --}}
    <section class="relative overflow-hidden bg-brand-900">
        <img src="{{ asset('images/campus.png') }}" alt="" class="absolute inset-0 size-full object-cover opacity-25 mix-blend-luminosity">
        <div class="absolute inset-0 bg-gradient-to-r from-brand-950 via-brand-900/90 to-brand-800/40"></div>
        <div class="relative mx-auto grid max-w-7xl gap-12 px-4 py-20 sm:px-6 lg:grid-cols-5 lg:px-8 lg:py-28">
            <div class="reveal lg:col-span-3">
                <span class="inline-flex items-center gap-2 rounded-full border border-gold-400/30 bg-gold-400/10 px-3 py-1 text-xs font-semibold tracking-wide text-gold-300">
                    <x-icon name="sparkles" class="size-3.5" /> Now hiring · {{ $openCount }} open {{ Str::plural('position', $openCount) }}
                </span>
                <h1 class="mt-6 text-4xl leading-[1.1] font-semibold tracking-tight text-white sm:text-5xl">
                    Build your career at <span class="text-gold-400">Divine Word College</span> of Legazpi.
                </h1>
                <p class="mt-6 max-w-xl text-lg text-brand-100/80">Browse academic and non-academic vacancies, apply online in minutes, and follow your application from screening to offer.</p>
                <div class="mt-10 flex flex-wrap gap-3">
                    <a href="{{ route('careers.index') }}" class="btn btn-gold px-6 py-3">Browse vacancies <x-icon name="arrow-right" class="size-4" /></a>
                    @guest
                        <a href="{{ route('register') }}" class="btn border border-white/20 bg-white/5 px-6 py-3 text-white hover:bg-white/10">Create an account</a>
                    @endguest
                </div>
            </div>
            <div class="reveal hidden grid-cols-2 gap-4 self-end lg:col-span-2 lg:grid">
                @foreach ([[$openCount, 'Open positions', 'briefcase'], [$departmentCount, 'Offices & schools', 'building'], [$campusCount, 'Campuses', 'map-pin'], ['SAW', 'Fair, weighted ranking', 'scale']] as [$value, $label, $icon])
                    <div class="rounded-2xl border border-white/10 bg-white/5 p-5 backdrop-blur">
                        <x-icon :name="$icon" class="size-5 text-gold-400" />
                        <p class="mt-3 text-2xl font-semibold text-white">{{ $value }}</p>
                        <p class="text-sm text-brand-200">{{ $label }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Latest vacancies --}}
    <section class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-sm font-semibold tracking-wider text-gold-600 uppercase">Vacancies</p>
                <h2 class="mt-2 text-3xl font-semibold tracking-tight text-slate-900">Latest openings</h2>
            </div>
            <a href="{{ route('careers.index') }}" class="btn btn-secondary">See all vacancies <x-icon name="arrow-right" class="size-4" /></a>
        </div>
        @if ($postings->isEmpty())
            <div class="card mt-10"><x-empty icon="briefcase" title="No open vacancies right now" message="Please check back soon." /></div>
        @else
            <div class="reveal mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($postings as $posting)
                    @include('public._posting-card')
                @endforeach
            </div>
        @endif
    </section>

    {{-- How it works --}}
    <section class="border-t border-slate-100 bg-slate-50">
        <div class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8">
            <h2 class="text-center text-3xl font-semibold tracking-tight text-slate-900">How hiring works</h2>
            <p class="mx-auto mt-3 max-w-2xl text-center text-slate-500">Every applicant for a vacancy is scored on the same weighted criteria, so shortlists are consistent and explainable.</p>
            <div class="reveal mt-12 grid gap-6 md:grid-cols-4">
                @foreach ([
                    ['user', 'Create a profile', 'Enter your personal data sheet and education once — reuse it for every application.'],
                    ['upload', 'Apply online', 'Upload your résumé, transcript, license and certificates as PDF.'],
                    ['scale', 'Weighted screening', 'Experience, education, trainings, skills and interview are combined with Simple Additive Weighting.'],
                    ['check-circle', 'Interview & offer', 'Track each stage and accept or decline an offer right in the portal.'],
                ] as $i => [$icon, $heading, $text])
                    <div class="card p-6">
                        <div class="flex items-center gap-3">
                            <span class="grid size-10 place-items-center rounded-xl bg-brand-700 text-white"><x-icon :name="$icon" /></span>
                            <span class="text-xs font-semibold text-slate-400">STEP {{ $i + 1 }}</span>
                        </div>
                        <h3 class="mt-4 font-semibold text-slate-900">{{ $heading }}</h3>
                        <p class="mt-2 text-sm text-slate-500">{{ $text }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
</x-layouts.public>
