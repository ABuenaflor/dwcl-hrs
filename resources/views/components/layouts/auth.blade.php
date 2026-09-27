{{-- Split screen: brand panel with the campus illustration + form. --}}
@props(['title' => null, 'heading', 'subheading' => null, 'wide' => false])
<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    @include('partials.head', ['title' => $title])
</head>
<body class="h-full bg-white">
    <div class="nav-progress"></div>
    <div class="flex min-h-full">
        <div class="relative hidden w-[44%] overflow-hidden bg-brand-900 lg:block">
            <img src="{{ asset('images/campus.png') }}" alt="" class="absolute inset-0 size-full object-cover opacity-35 mix-blend-luminosity">
            <div class="absolute inset-0 bg-gradient-to-t from-brand-950 via-brand-900/70 to-brand-800/40"></div>
            <div class="relative flex h-full flex-col justify-between p-10">
                <a href="{{ route('home') }}"><x-logo light /></a>
                <div class="max-w-md">
                    <p class="text-sm font-semibold tracking-wider text-gold-300 uppercase">Duc in Altum</p>
                    <h2 class="mt-3 text-3xl leading-tight font-semibold text-white">Fair, transparent hiring and faculty ranking for the DWCL community.</h2>
                    <p class="mt-4 text-brand-100/80">Applicants are ranked with Simple Additive Weighting; faculty self-ratings move through every committee in one place — with evidence attached.</p>
                </div>
                <p class="text-xs text-brand-200/60">© {{ date('Y') }} Divine Word College of Legazpi · HRDO</p>
            </div>
        </div>

        <main class="vt-main flex flex-1 flex-col justify-center px-4 py-12 sm:px-6 lg:px-16">
            <div class="mx-auto w-full {{ $wide ? 'max-w-2xl' : 'max-w-sm' }}">
                <a href="{{ route('home') }}" class="lg:hidden"><x-logo /></a>
                <h1 class="mt-8 text-2xl font-semibold tracking-tight text-slate-900 lg:mt-0">{{ $heading }}</h1>
                @if ($subheading)<p class="mt-2 text-sm text-slate-500">{{ $subheading }}</p>@endif
                <div class="mt-8">{{ $slot }}</div>
            </div>
        </main>
    </div>
    <x-toasts />
</body>
</html>
