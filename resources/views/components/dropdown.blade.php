@props(['align' => 'right', 'width' => 'w-56'])
<div x-data="{ open: false }" x-on:click.outside="open = false" x-on:keydown.escape="open = false" class="relative">
    <div x-on:click="open = ! open">{{ $trigger }}</div>
    <div x-show="open" x-cloak
         x-transition:enter="transition duration-200 ease-(--ease-out-soft)" x-transition:enter-start="opacity-0 -translate-y-1 scale-95"
         x-transition:leave="transition duration-100" x-transition:leave-end="opacity-0 scale-95"
         class="card absolute z-40 mt-2 {{ $width }} {{ $align === 'right' ? 'right-0 origin-top-right' : 'left-0 origin-top-left' }} overflow-hidden p-1.5">
        {{ $slot }}
    </div>
</div>
