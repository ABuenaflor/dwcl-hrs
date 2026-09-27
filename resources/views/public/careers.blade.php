<x-layouts.public title="Vacancies">
    <section class="border-b border-slate-100 bg-gradient-to-b from-brand-50/60 to-white">
        <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
            <h1 class="text-3xl font-semibold tracking-tight text-slate-900">Job vacancies</h1>
            <p class="mt-2 text-slate-500">Qualifications for vacant positions inside Divine Word College of Legazpi.</p>

            <form method="GET" class="mt-8 grid gap-3 rounded-2xl border border-slate-200 bg-white p-3 shadow-(--shadow-card) md:grid-cols-12" data-no-loading>
                <div class="relative md:col-span-4">
                    <x-icon name="search" class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-slate-400" />
                    <input type="search" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Search job title or keyword" class="form-control border-0 pl-9 shadow-none focus:ring-0">
                </div>
                <select name="campus" class="form-control md:col-span-2" onchange="this.form.requestSubmit()">
                    <option value="">All campuses</option>
                    @foreach ($campuses as $c)<option value="{{ $c->id }}" @selected(($filters['campus'] ?? '') == $c->id)>{{ $c->name }}</option>@endforeach
                </select>
                <select name="department" class="form-control md:col-span-3" onchange="this.form.requestSubmit()">
                    <option value="">All departments</option>
                    @foreach ($departments as $d)<option value="{{ $d->id }}" @selected(($filters['department'] ?? '') == $d->id)>{{ $d->name }}</option>@endforeach
                </select>
                <select name="type" class="form-control md:col-span-2" onchange="this.form.requestSubmit()">
                    <option value="">Any schedule</option>
                    @foreach (App\Enums\EmploymentType::cases() as $t)<option value="{{ $t->value }}" @selected(($filters['type'] ?? '') === $t->value)>{{ $t->label() }}</option>@endforeach
                </select>
                <button class="btn btn-primary md:col-span-1"><x-icon name="search" class="size-4" /><span class="md:sr-only">Search</span></button>
            </form>

            <div class="mt-4 flex flex-wrap gap-2">
                @php $cat = $filters['category'] ?? ''; @endphp
                <a href="{{ request()->fullUrlWithQuery(['category' => null, 'page' => null]) }}" @class(['chip', 'is-active' => $cat === ''])>All roles</a>
                @foreach (App\Enums\JobCategory::cases() as $c)
                    <a href="{{ request()->fullUrlWithQuery(['category' => $c->value, 'page' => null]) }}" @class(['chip', 'is-active' => $cat === $c->value])>{{ $c->label() }}</a>
                @endforeach
            </div>
        </div>
    </section>

    <section class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
        <p class="mb-6 text-sm text-slate-500">{{ $postings->total() }} {{ Str::plural('vacancy', $postings->total()) }} found</p>
        @if ($postings->isEmpty())
            <div class="card"><x-empty icon="search" title="No vacancies match your filters" message="Try removing a filter or searching a different keyword.">
                <a href="{{ route('careers.index') }}" class="btn btn-secondary">Clear filters</a>
            </x-empty></div>
        @else
            <div class="reveal grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($postings as $posting)
                    @include('public._posting-card')
                @endforeach
            </div>
            <div class="mt-10">{{ $postings->links() }}</div>
        @endif
    </section>
</x-layouts.public>
