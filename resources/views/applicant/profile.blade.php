<x-layouts.app title="My profile">
    <x-page-header title="My profile" subtitle="Saved once, reused for every application you submit." />

    <form method="POST" action="{{ route('applicant.profile.update') }}" class="grid gap-6 lg:grid-cols-3">
        @csrf @method('PUT')
        <div class="space-y-6 lg:col-span-2">
            <section class="card p-6">
                <h2 class="mb-6 text-base font-semibold text-slate-900">Personal information</h2>
                @include('applicant._personal')
            </section>
            <section class="card p-6">
                <h2 class="mb-6 text-base font-semibold text-slate-900">Educational background</h2>
                @include('applicant._education')
            </section>
        </div>
        <aside>
            <div class="card sticky top-24 p-6">
                <x-avatar :user="$user" size="size-14" class="text-base" />
                <p class="mt-4 font-semibold text-slate-900">{{ $user->full_name }}</p>
                <p class="text-sm text-slate-500">{{ $user->email }}</p>
                <button class="btn btn-primary mt-6 w-full"><span class="btn-label">Save profile</span></button>
            </div>
        </aside>
    </form>
</x-layouts.app>
