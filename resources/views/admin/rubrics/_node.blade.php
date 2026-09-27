@php $fmt = fn ($v) => $v === null ? '' : rtrim(rtrim(number_format($v, 2, '.', ''), '0'), '.'); @endphp
<tr @class(['bg-slate-50/70' => $depth === 0])>
    <td style="padding-left: {{ 1 + $depth * 1.25 }}rem">
        <p @class(['text-slate-900', 'font-semibold' => $depth === 0])>@if ($node->code)<span class="mr-1 text-slate-400">{{ $node->code }}</span>@endif{{ $node->title }}</p>
        @if ($node->guide)<p class="text-xs text-slate-500">{{ $node->guide }}</p>@endif
    </td>
    <td class="text-right text-slate-600 tabular-nums">{{ $fmt($tertiary ? $node->credit_in_field : $node->weight_percent) }}</td>
    <td class="text-right text-slate-600 tabular-nums">{{ $fmt($tertiary ? $node->credit_related : $node->credit_points) }}</td>
    <td class="text-right font-medium text-slate-700 tabular-nums">{{ $fmt($node->max_points) }}</td>
    <td class="text-center">@if ($node->is_scorable)<x-icon name="check" class="inline size-4 text-emerald-600" />@endif</td>
    <td class="text-right whitespace-nowrap">
        <button type="button" class="btn btn-ghost btn-sm" title="Add sub-criterion" x-data x-on:click="$dispatch('edit-item', { parent_id: '{{ $node->id }}', is_scorable: true })"><x-icon name="plus" class="size-4" /></button>
        <button type="button" class="btn btn-ghost btn-sm" title="Edit" x-data
                x-on:click="$dispatch('edit-item', @js($node->only(['id', 'code', 'title', 'guide', 'weight_percent', 'credit_points', 'credit_in_field', 'credit_related', 'max_points', 'is_scorable']) + ['parent_id' => (string) $node->parent_id]))"><x-icon name="pencil" class="size-4" /></button>
        <form method="POST" action="{{ route('admin.rubrics.items.destroy', $node) }}" class="inline" x-data x-on:submit="if (! confirm('Remove “{{ addslashes($node->title) }}”{{ $node->children->isNotEmpty() ? ' and its sub-criteria' : '' }}?')) $event.preventDefault()">
            @csrf @method('DELETE')
            <button class="btn btn-ghost btn-sm text-slate-400 hover:text-rose-600" title="Delete"><x-icon name="trash" class="size-4" /></button>
        </form>
    </td>
</tr>
@foreach ($node->children as $child)
    @include('admin.rubrics._node', ['node' => $child, 'depth' => $depth + 1])
@endforeach
