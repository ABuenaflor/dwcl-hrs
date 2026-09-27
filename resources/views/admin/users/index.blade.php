<x-layouts.app title="User accounts">
    <x-page-header title="User accounts" subtitle="Approve employee requests, create committee accounts, and enable or disable access.">
        <a href="{{ route('admin.users.create') }}" class="btn btn-primary"><x-icon name="plus" class="size-4" /> New account</a>
    </x-page-header>

    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.users.index') }}" @class(['chip', 'is-active' => ! $status && ! $role])>Everyone</a>
            <a href="{{ route('admin.users.index', ['status' => 'pending']) }}" @class(['chip', 'is-active' => $status === App\Enums\AccountStatus::Pending])>
                Pending approval @if ($pendingCount)<span class="rounded-full bg-gold-500 px-1.5 text-[10px] font-bold text-brand-950">{{ $pendingCount }}</span>@endif
            </a>
            @foreach (App\Enums\Role::cases() as $r)
                <a href="{{ route('admin.users.index', ['role' => $r->value]) }}" @class(['chip', 'is-active' => $role === $r])>{{ $r->label() }}</a>
            @endforeach
        </div>
        <form method="GET" class="relative" data-no-loading>
            @foreach (request()->only(['role', 'status']) as $k => $v)<input type="hidden" name="{{ $k }}" value="{{ $v }}">@endforeach
            <x-icon name="search" class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-slate-400" />
            <input type="search" name="q" value="{{ request('q') }}" placeholder="Name or email" class="form-control w-64 pl-9">
        </form>
    </div>

    <div class="card overflow-hidden">
        @if ($users->isEmpty())
            <x-empty icon="users" title="No accounts found" />
        @else
            <div class="overflow-x-auto">
                <table class="table-clean">
                    <thead><tr><th>Name</th><th>Role</th><th>Department</th><th>Last sign-in</th><th>Status</th><th class="text-right">Actions</th></tr></thead>
                    <tbody>
                        @foreach ($users as $u)
                            <tr>
                                <td>
                                    <div class="flex items-center gap-3">
                                        <x-avatar :user="$u" size="size-8" />
                                        <span><span class="block font-medium text-slate-900">{{ $u->name }}</span><span class="block text-xs text-slate-500">{{ $u->email }}</span></span>
                                    </div>
                                </td>
                                <td class="text-slate-600">{{ $u->role->label() }}</td>
                                <td class="text-slate-600">{{ $u->department?->name ?? '—' }}</td>
                                <td class="text-slate-500">{{ $u->last_login_at?->diffForHumans() ?? 'Never' }}</td>
                                <td><x-badge :tone="$u->status->tone()">{{ $u->status->label() }}</x-badge></td>
                                <td class="text-right whitespace-nowrap">
                                    @unless ($u->is(auth()->user()))
                                        @if ($u->status === App\Enums\AccountStatus::Pending)
                                            <form method="POST" action="{{ route('admin.users.status', $u) }}" class="inline">@csrf @method('PATCH')
                                                <input type="hidden" name="status" value="active">
                                                <button class="btn btn-success btn-sm"><x-icon name="check" class="size-3.5" /><span class="btn-label">Approve</span></button>
                                            </form>
                                        @endif
                                        <form method="POST" action="{{ route('admin.users.status', $u) }}" class="inline">@csrf @method('PATCH')
                                            <input type="hidden" name="status" value="{{ $u->status === App\Enums\AccountStatus::Disabled ? 'active' : 'disabled' }}">
                                            <button class="btn btn-ghost btn-sm {{ $u->status === App\Enums\AccountStatus::Disabled ? 'text-emerald-700' : 'text-rose-600' }}">
                                                <span class="btn-label">{{ $u->status === App\Enums\AccountStatus::Disabled ? 'Enable' : ($u->status === App\Enums\AccountStatus::Pending ? 'Reject' : 'Disable') }}</span>
                                            </button>
                                        </form>
                                    @endunless
                                    <a href="{{ route('admin.users.edit', $u) }}" class="btn btn-ghost btn-sm" title="Edit"><x-icon name="pencil" class="size-4" /></a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
    <div class="mt-6">{{ $users->links() }}</div>
</x-layouts.app>
