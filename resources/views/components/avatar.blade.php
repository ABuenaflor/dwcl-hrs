@props(['user', 'size' => 'size-9'])
<span {{ $attributes->merge(['class' => "grid {$size} shrink-0 place-items-center rounded-full bg-gradient-to-br from-brand-600 to-brand-800 text-xs font-semibold text-white ring-2 ring-white"]) }}>{{ $user->initials }}</span>
