{{-- Label + control + hint/error. Use type="select" / type="textarea", or pass a custom control in the slot. --}}
@props(['name', 'label' => null, 'type' => 'text', 'value' => null, 'hint' => null, 'required' => false, 'options' => null, 'placeholder' => null])
@php
    $key = trim(str_replace(['[', ']'], ['.', ''], $name), '.');
    $id = 'f_'.str_replace('.', '_', $key);
    $current = old($key, $value);
    $current = $current instanceof \BackedEnum ? $current->value : $current;
    $current = $current instanceof \DateTimeInterface ? $current->format($type === 'datetime-local' ? 'Y-m-d\TH:i' : 'Y-m-d') : $current;
    $invalid = $errors->has($key);
@endphp
<div {{ $attributes->only('class') }}>
    @if ($label)
        <label for="{{ $id }}" class="form-label">{{ $label }}@if ($required)<span class="text-rose-500"> *</span>@endif</label>
    @endif

    @if ($slot->isNotEmpty())
        {{ $slot }}
    @elseif ($type === 'select')
        <select id="{{ $id }}" name="{{ $name }}" @required($required) {{ $attributes->except('class')->merge(['class' => 'form-control'.($invalid ? ' is-invalid' : '')]) }}>
            @if ($placeholder !== false)<option value="">{{ $placeholder ?? 'Select…' }}</option>@endif
            @foreach ($options ?? [] as $optValue => $optLabel)
                <option value="{{ $optValue }}" @selected((string) $current === (string) $optValue)>{{ $optLabel }}</option>
            @endforeach
        </select>
    @elseif ($type === 'textarea')
        <textarea id="{{ $id }}" name="{{ $name }}" rows="4" placeholder="{{ $placeholder }}" @required($required) {{ $attributes->except('class')->merge(['class' => 'form-control'.($invalid ? ' is-invalid' : '')]) }}>{{ $current }}</textarea>
    @else
        <input id="{{ $id }}" name="{{ $name }}" type="{{ $type }}" value="{{ $type === 'password' ? '' : $current }}" placeholder="{{ $placeholder }}" @required($required) {{ $attributes->except('class')->merge(['class' => 'form-control'.($invalid ? ' is-invalid' : '')]) }}>
    @endif

    @error($key)
        <p class="mt-1.5 flex items-center gap-1 text-xs text-rose-600"><x-icon name="alert" class="size-3.5" />{{ $message }}</p>
    @else
        @if ($hint)<p class="mt-1.5 text-xs text-slate-500">{{ $hint }}</p>@endif
    @enderror
</div>
