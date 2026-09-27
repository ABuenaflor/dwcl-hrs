@php
    $score = $scores->get($node->id);
    $isGroup = $node->children->isNotEmpty();
    $fmt = fn ($v) => $v === null ? '' : rtrim(rtrim(number_format($v, 2, '.', ''), '0'), '.');
@endphp
<tr data-node="{{ $node->id }}" data-parent="{{ $node->parent_id }}" data-max="{{ $node->max_points }}"
    data-sr="{{ $fmt($score?->sr_points) }}" data-drc="{{ $fmt($score?->drc_points) }}"
    @class(['bg-slate-50/70' => $depth === 0])>
    <td style="padding-left: {{ 1 + $depth * 1.25 }}rem">
        <p @class(['text-slate-900', 'font-semibold' => $depth === 0, 'font-medium' => $isGroup && $depth > 0])>
            @if ($node->code)<span class="mr-1 text-slate-400 tabular-nums">{{ $node->code }}</span>@endif{{ $node->title }}
        </p>
        @if ($node->guide)<p class="mt-0.5 text-xs text-slate-500">{{ $node->guide }}</p>@endif
    </td>
    <td class="text-right text-slate-500 tabular-nums">{{ $fmt($tertiary ? $node->credit_in_field : $node->weight_percent) }}</td>
    <td class="text-right text-slate-500 tabular-nums">{{ $fmt($tertiary ? $node->credit_related : $node->credit_points) }}</td>
    <td class="text-right text-slate-400 tabular-nums">{{ $fmt($node->max_points) }}</td>

    @foreach ($columns as $col => $label)
        <td @class(['text-right tabular-nums', 'bg-gold-50/50' => $edit === $col])>
            @if ($edit === $col && $node->is_scorable)
                <input type="number" name="points[{{ $node->id }}]" data-points step="0.01" min="0" @if ($node->max_points !== null) max="{{ $node->max_points }}" @endif
                       value="{{ old("points.{$node->id}", $fmt($score?->{$col})) }}" x-on:input="recalc()"
                       class="form-control w-20 px-2 py-1.5 text-right @error("points.{$node->id}") is-invalid @enderror" aria-label="{{ $label }} points for {{ $node->title }}">
            @elseif ($edit === $col)
                <span data-subtotal class="font-semibold text-slate-900">0.00</span>
            @elseif ($isGroup)
                <span class="font-semibold text-slate-700">{{ ($subtotals[$col][$node->id] ?? 0) > 0 ? number_format($subtotals[$col][$node->id], 2) : '—' }}</span>
            @else
                <span class="text-slate-700">{{ $fmt($score?->{$col}) ?: '—' }}</span>
            @endif
        </td>
    @endforeach

    @if ($evidence)
        <td>
            @if ($node->is_scorable)
                <div class="flex items-center gap-2">
                    @if ($score?->evidence_path)
                        <a href="{{ route('evidence.show', $score) }}" target="_blank" class="inline-flex max-w-28 items-center gap-1 truncate text-xs font-medium text-brand-700 hover:underline" title="{{ $score->evidence_name }}">
                            <x-icon name="file" class="size-3.5 shrink-0" /> <span class="truncate">{{ $score->evidence_name }}</span>
                        </a>
                    @elseif ($evidence === 'view')
                        <span class="text-xs text-slate-400">None</span>
                    @endif
                    @if ($evidence === 'upload')
                        <label class="cursor-pointer rounded-md p-1 text-slate-400 transition hover:bg-brand-50 hover:text-brand-700" title="{{ $score?->evidence_path ? 'Replace' : 'Attach' }} evidence" x-data="{ picked: false }">
                            <x-icon name="upload" class="size-4" ::class="picked && 'text-emerald-600'" />
                            <input type="file" name="evidence[{{ $node->id }}]" accept=".pdf,.jpg,.jpeg,.png" class="sr-only" x-on:change="picked = $event.target.files.length > 0">
                        </label>
                    @endif
                </div>
            @endif
        </td>
    @endif
</tr>
@foreach ($node->children as $child)
    @include('rankings._row', ['node' => $child, 'depth' => $depth + 1])
@endforeach
