@props(['type' => 'bar', 'data', 'label' => null, 'height' => 'h-64', 'horizontal' => false])
<div class="relative {{ $height }}">
    @if (array_sum($data['data'] ?? []) === 0)
        <div class="absolute inset-0 grid place-items-center text-sm text-slate-400">No data yet</div>
    @else
        <canvas data-chart="{{ json_encode(['type' => $type, 'labels' => $data['labels'], 'data' => $data['data'], 'label' => $label]) }}" @if ($horizontal) data-horizontal @endif role="img" aria-label="{{ $label ?? 'Chart' }}"></canvas>
    @endif
</div>
