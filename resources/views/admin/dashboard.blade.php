<x-layouts.app title="Dashboard">
    <x-page-header title="HRDO dashboard" :subtitle="now()->format('l, F j, Y')">
        <a href="{{ route('admin.postings.create') }}" class="btn btn-primary"><x-icon name="plus" class="size-4" /> Post a vacancy</a>
    </x-page-header>

    <div class="reveal grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-stat label="Open vacancies" :value="$stats['open_postings']" icon="briefcase" :href="route('admin.postings.index', ['status' => 'open'])" />
        <x-stat label="Applications this month" :value="$stats['applications_month']" icon="file" tone="gold" :href="route('admin.applications.index', ['sort' => 'latest'])" />
        <x-stat label="In the pipeline" :value="$stats['in_pipeline']" icon="users" tone="success" hint="Shortlisted, interview or offered" />
        <x-stat label="Pending accounts" :value="$stats['pending_accounts']" icon="shield" tone="warning" :href="route('admin.users.index', ['status' => 'pending'])" />
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-3">
        <section class="card p-6 lg:col-span-2">
            <h2 class="text-sm font-semibold text-slate-900">Applications — last 6 months</h2>
            <div class="mt-4"><x-chart type="line" :data="$charts['trend']" /></div>
        </section>
        <section class="card p-6">
            <h2 class="text-sm font-semibold text-slate-900">By status</h2>
            <div class="mt-4"><x-chart type="doughnut" :data="$charts['status']" /></div>
        </section>
        <section class="card p-6 lg:col-span-2">
            <h2 class="text-sm font-semibold text-slate-900">By department</h2>
            <div class="mt-4"><x-chart :data="$charts['department']" horizontal /></div>
        </section>
        <section class="card p-6">
            <h2 class="text-sm font-semibold text-slate-900">By campus</h2>
            <div class="mt-4"><x-chart type="doughnut" :data="$charts['campus']" /></div>
        </section>
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-2">
        <section class="card overflow-hidden">
            <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
                <h2 class="text-sm font-semibold text-slate-900">Recent applications</h2>
                <a href="{{ route('admin.applications.index', ['sort' => 'latest']) }}" class="text-xs font-medium text-brand-700 hover:underline">View all</a>
            </div>
            @forelse ($recent as $app)
                <a href="{{ route('admin.applications.show', $app) }}" class="flex items-center gap-3 border-b border-slate-100 px-6 py-3 transition last:border-0 hover:bg-slate-50">
                    <x-avatar :user="$app->user" size="size-9" />
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-medium text-slate-900">{{ $app->user->name }}</p>
                        <p class="truncate text-xs text-slate-500">{{ $app->jobPosting->title }} · {{ $app->created_at->diffForHumans() }}</p>
                    </div>
                    @if ($app->saw_rank)<span class="text-xs font-semibold text-slate-500 tabular-nums">#{{ $app->saw_rank }}</span>@endif
                    <x-badge :tone="$app->status->tone()">{{ $app->status->label() }}</x-badge>
                </a>
            @empty
                <x-empty icon="file" title="No applications yet" />
            @endforelse
        </section>

        <section class="card overflow-hidden">
            <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
                <h2 class="text-sm font-semibold text-slate-900">Faculty rankings awaiting action</h2>
                <a href="{{ route('admin.rankings.index') }}" class="text-xs font-medium text-brand-700 hover:underline">View all</a>
            </div>
            @include('admin._ranking-queue')
        </section>
    </div>
</x-layouts.app>
