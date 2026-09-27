{{-- Open with: $dispatch('open-modal', 'name') --}}
@props(['name', 'title', 'maxWidth' => 'max-w-lg'])
<div x-data="{ open: false }"
     x-on:open-modal.window="if ($event.detail === '{{ $name }}') open = true"
     x-on:close-modal.window="open = false"
     x-on:keydown.escape.window="open = false"
     x-show="open" x-cloak
     class="fixed inset-0 z-50 flex items-end justify-center p-4 sm:items-center" role="dialog" aria-modal="true">
    <div x-show="open" x-transition.opacity.duration.200ms class="absolute inset-0 bg-brand-950/40 backdrop-blur-sm" x-on:click="open = false"></div>
    <div x-show="open"
         x-transition:enter="transition duration-300 ease-(--ease-out-soft)" x-transition:enter-start="opacity-0 translate-y-4 scale-95" x-transition:enter-end="opacity-100 translate-y-0 scale-100"
         x-transition:leave="transition duration-150 ease-in" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
         class="card relative max-h-[90vh] w-full overflow-y-auto {{ $maxWidth }} p-6">
        <div class="mb-4 flex items-start justify-between gap-4">
            <h2 class="text-lg font-semibold text-slate-900">{{ $title }}</h2>
            <button type="button" class="btn-ghost -m-1.5 rounded-lg p-1.5" x-on:click="open = false" aria-label="Close"><x-icon name="x" class="size-5" /></button>
        </div>
        {{ $slot }}
    </div>
</div>
