{{-- Signed-in shell: sidebar navigation by role, top bar with notifications and account menu. --}}
@props(['title' => null])
@use('App\Enums\Role')
@php
    $user = auth()->user();
    $role = $user->role;

    $nav = match (true) {
        $role === Role::Applicant => [
            ['Dashboard', 'applicant.dashboard', 'dashboard', 'applicant.dashboard'],
            ['Browse Vacancies', 'careers.index', 'briefcase', 'careers.*'],
            ['My Profile', 'applicant.profile.edit', 'user', 'applicant.profile.*'],
        ],
        $role === Role::Faculty => [
            ['My Ranking', 'faculty.dashboard', 'award', 'faculty.*'],
        ],
        $role === Role::Admin => [
            ['Dashboard', 'admin.dashboard', 'dashboard', 'admin.dashboard'],
            ['Vacancies', 'admin.postings.index', 'briefcase', 'admin.postings.*'],
            ['Applicants', 'admin.applications.index', 'users', 'admin.applications.*'],
            ['Faculty Ranking', 'admin.rankings.index', 'award', 'admin.rankings.*'],
            ['Reports', 'admin.reports.index', 'chart', 'admin.reports.*'],
        ],
        default => [
            ['Review Queue', 'admin.dashboard', 'list', 'admin.dashboard'],
            ['Faculty Ranking', 'admin.rankings.index', 'award', 'admin.rankings.*'],
        ],
    };

    $adminNav = $role === Role::Admin ? [
        ['User Accounts', 'admin.users.index', 'shield', 'admin.users.*', \App\Models\User::where('status', 'pending')->count()],
        ['Rubrics & Ranks', 'admin.rubrics.index', 'scale', 'admin.rubrics.*', 0],
        ['Campuses & Depts', 'admin.setup.index', 'building', 'admin.setup.*', 0],
    ] : [];

    $unread = $user->unreadNotifications()->latest()->take(6)->get();
    $unreadCount = $user->unreadNotifications()->count();
@endphp
<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    @include('partials.head', ['title' => $title])
</head>
<body class="h-full" x-data="{ nav: false }">
    <div class="nav-progress"></div>

    {{-- Mobile backdrop --}}
    <div x-show="nav" x-cloak x-transition.opacity class="fixed inset-0 z-30 bg-brand-950/50 backdrop-blur-sm lg:hidden" x-on:click="nav = false"></div>

    {{-- Sidebar --}}
    <aside class="vt-sidebar fixed inset-y-0 left-0 z-40 flex w-68 -translate-x-full flex-col bg-brand-900 bg-[radial-gradient(120%_60%_at_0%_0%,rgba(90,127,197,0.25),transparent)] transition-transform duration-300 ease-(--ease-out-soft) lg:translate-x-0"
           :class="nav && 'translate-x-0'">
        <div class="flex h-16 items-center justify-between px-5">
            <a href="{{ $user->homeRoute() }}"><x-logo light /></a>
            <button class="rounded-lg p-1.5 text-brand-200 hover:bg-white/10 lg:hidden" x-on:click="nav = false" aria-label="Close menu"><x-icon name="x" /></button>
        </div>

        <nav class="flex-1 space-y-1 overflow-y-auto px-3 py-4">
            @foreach ($nav as [$label, $route, $icon, $pattern])
                <a href="{{ route($route) }}" @class(['nav-item', 'is-active' => request()->routeIs($pattern)])>
                    <x-icon :name="$icon" class="size-[18px]" /> {{ $label }}
                </a>
            @endforeach

            @if ($adminNav)
                <p class="px-3 pt-6 pb-2 text-[11px] font-semibold tracking-wider text-brand-300/70 uppercase">Administration</p>
                @foreach ($adminNav as [$label, $route, $icon, $pattern, $count])
                    <a href="{{ route($route) }}" @class(['nav-item', 'is-active' => request()->routeIs($pattern)])>
                        <x-icon :name="$icon" class="size-[18px]" /> <span class="flex-1">{{ $label }}</span>
                        @if ($count)<span class="rounded-full bg-gold-500 px-2 py-0.5 text-[11px] font-semibold text-brand-950">{{ $count }}</span>@endif
                    </a>
                @endforeach
            @endif
        </nav>

        <div class="border-t border-white/10 p-4">
            <div class="flex items-center gap-3">
                <x-avatar :user="$user" class="ring-brand-800" />
                <div class="min-w-0 flex-1">
                    <p class="truncate text-sm font-medium text-white">{{ $user->name }}</p>
                    <p class="truncate text-xs text-brand-200/80">{{ $role->label() }}</p>
                </div>
            </div>
        </div>
    </aside>

    <div class="lg:pl-68">
        {{-- Top bar --}}
        <header class="vt-topbar sticky top-0 z-20 flex h-16 items-center gap-3 border-b border-slate-200/80 bg-white/85 px-4 backdrop-blur-md sm:px-6">
            <button class="btn-ghost -ml-1 rounded-lg p-2 lg:hidden" x-on:click="nav = true" aria-label="Open menu"><x-icon name="menu" /></button>
            <div class="flex-1"></div>

            {{-- Notifications --}}
            <x-dropdown width="w-80 sm:w-96">
                <x-slot:trigger>
                    <button class="relative rounded-lg p-2 text-slate-500 transition hover:bg-slate-100 hover:text-slate-800" aria-label="Notifications">
                        <x-icon name="bell" />
                        @if ($unreadCount)
                            <span class="absolute top-1 right-1 grid min-w-4 place-items-center rounded-full bg-rose-500 px-1 text-[10px] font-bold text-white ring-2 ring-white">{{ $unreadCount > 9 ? '9+' : $unreadCount }}</span>
                        @endif
                    </button>
                </x-slot:trigger>
                <div class="flex items-center justify-between px-3 py-2">
                    <p class="text-sm font-semibold text-slate-900">Notifications</p>
                    @if ($unreadCount)
                        <form method="POST" action="{{ route('notifications.read-all') }}" data-no-loading>@csrf
                            <button class="text-xs font-medium text-brand-700 hover:underline">Mark all read</button>
                        </form>
                    @endif
                </div>
                <div class="max-h-96 overflow-y-auto">
                    @forelse ($unread as $n)
                        <a href="{{ route('notifications.open', $n->id) }}" class="flex gap-3 rounded-lg px-3 py-2.5 transition hover:bg-slate-50">
                            <span @class(['mt-1.5 size-2 shrink-0 rounded-full', 'bg-emerald-500' => ($n->data['tone'] ?? '') === 'success', 'bg-amber-500' => ($n->data['tone'] ?? '') === 'warning', 'bg-rose-500' => ($n->data['tone'] ?? '') === 'danger', 'bg-brand-500' => ! in_array($n->data['tone'] ?? '', ['success', 'warning', 'danger'])])></span>
                            <span class="min-w-0">
                                <span class="block text-sm font-medium text-slate-900">{{ $n->data['title'] }}</span>
                                <span class="line-clamp-2 block text-xs text-slate-500">{{ $n->data['message'] }}</span>
                                <span class="mt-0.5 block text-[11px] text-slate-400">{{ $n->created_at->diffForHumans() }}</span>
                            </span>
                        </a>
                    @empty
                        <p class="px-3 py-8 text-center text-sm text-slate-500">You're all caught up.</p>
                    @endforelse
                </div>
                <a href="{{ route('notifications.index') }}" class="mt-1 block rounded-lg border-t border-slate-100 px-3 py-2 text-center text-xs font-medium text-brand-700 hover:bg-slate-50">View all</a>
            </x-dropdown>

            {{-- Account --}}
            <x-dropdown>
                <x-slot:trigger>
                    <button class="flex items-center gap-2 rounded-lg p-1 pr-2 transition hover:bg-slate-100">
                        <x-avatar :user="$user" size="size-8" />
                        <span class="hidden text-sm font-medium text-slate-700 sm:block">{{ $user->first_name }}</span>
                        <x-icon name="chevron-down" class="size-4 text-slate-400" />
                    </button>
                </x-slot:trigger>
                <div class="border-b border-slate-100 px-3 py-2">
                    <p class="text-sm font-medium text-slate-900">{{ $user->name }}</p>
                    <p class="truncate text-xs text-slate-500">{{ $user->email }}</p>
                </div>
                <a href="{{ route('password.edit') }}" class="mt-1 flex items-center gap-2 rounded-lg px-3 py-2 text-sm text-slate-700 hover:bg-slate-50"><x-icon name="key" class="size-4" /> Change password</a>
                <a href="{{ route('home') }}" class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm text-slate-700 hover:bg-slate-50"><x-icon name="external" class="size-4" /> Public careers site</a>
                <form method="POST" action="{{ route('logout') }}">@csrf
                    <button class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-sm text-rose-600 hover:bg-rose-50"><x-icon name="logout" class="size-4" /> Sign out</button>
                </form>
            </x-dropdown>
        </header>

        <main class="vt-main mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            {{ $slot }}
        </main>
    </div>

    <x-toasts />
    @stack('scripts')
</body>
</html>
