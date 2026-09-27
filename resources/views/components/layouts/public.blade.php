{{-- Public careers site. --}}
@props(['title' => null])
<!DOCTYPE html>
<html lang="en">
<head>
    @include('partials.head', ['title' => $title])
</head>
<body class="flex min-h-screen flex-col bg-white" x-data="{ menu: false }">
    <div class="nav-progress"></div>

    <header class="vt-site-header sticky top-0 z-30 border-b border-slate-200/70 bg-white/85 backdrop-blur-md">
        <div class="mx-auto flex h-16 max-w-7xl items-center gap-6 px-4 sm:px-6 lg:px-8">
            <a href="{{ route('home') }}"><x-logo /></a>
            <nav class="hidden flex-1 items-center gap-1 md:flex">
                <a href="{{ route('home') }}" @class(['rounded-lg px-3 py-2 text-sm font-medium transition hover:bg-slate-100', 'text-brand-800' => request()->routeIs('home'), 'text-slate-600' => ! request()->routeIs('home')])>Home</a>
                <a href="{{ route('careers.index') }}" @class(['rounded-lg px-3 py-2 text-sm font-medium transition hover:bg-slate-100', 'text-brand-800' => request()->routeIs('careers.*'), 'text-slate-600' => ! request()->routeIs('careers.*')])>Vacancies</a>
            </nav>
            <div class="ml-auto hidden items-center gap-2 md:flex">
                @auth
                    <a href="{{ auth()->user()->homeRoute() }}" class="btn btn-primary">Go to dashboard <x-icon name="arrow-right" class="size-4" /></a>
                @else
                    <a href="{{ route('login') }}" class="btn btn-ghost">Sign in</a>
                    <a href="{{ route('register') }}" class="btn btn-primary">Create account</a>
                @endauth
            </div>
            <button class="btn-ghost ml-auto rounded-lg p-2 md:hidden" x-on:click="menu = ! menu" aria-label="Menu"><x-icon name="menu" /></button>
        </div>
        <div x-show="menu" x-collapse x-cloak class="border-t border-slate-100 md:hidden">
            <div class="space-y-1 px-4 py-3">
                <a href="{{ route('home') }}" class="block rounded-lg px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Home</a>
                <a href="{{ route('careers.index') }}" class="block rounded-lg px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Vacancies</a>
                @auth
                    <a href="{{ auth()->user()->homeRoute() }}" class="block rounded-lg px-3 py-2 text-sm font-medium text-brand-700 hover:bg-slate-50">Dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="block rounded-lg px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Sign in</a>
                    <a href="{{ route('register') }}" class="block rounded-lg px-3 py-2 text-sm font-medium text-brand-700 hover:bg-slate-50">Create account</a>
                @endauth
            </div>
        </div>
    </header>

    <main class="vt-main flex-1">
        {{ $slot }}
    </main>

    <footer class="border-t border-slate-200 bg-slate-50">
        <div class="mx-auto flex max-w-7xl flex-col gap-4 px-4 py-8 text-sm text-slate-500 sm:flex-row sm:items-center sm:justify-between sm:px-6 lg:px-8">
            <div>
                <p class="font-medium text-slate-700">Divine Word College of Legazpi</p>
                <p>Human Resources and Development Office · Rizal St., Legazpi City, Albay</p>
            </div>
            <div class="flex gap-4">
                <a href="{{ route('careers.index') }}" class="hover:text-brand-700">Vacancies</a>
                <a href="{{ route('register.employee') }}" class="hover:text-brand-700">Employee access</a>
            </div>
        </div>
    </footer>

    <x-toasts />
</body>
</html>
