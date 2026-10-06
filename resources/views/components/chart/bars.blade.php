@props([
    // [['label' => ..., 'value' => 12, 'hint' => '…', 'key' => html voor het label (optioneel)], ...]
    'rows' => [],
    'color' => 'var(--series-1)',
    'unit' => '',
    'decimals' => 0,
])

{{--
    Liggende balken, voor een vergelijking tussen namen die te lang zijn voor
    een kolom ("Bakkerij Janssens · Instagram", "Afdeling Oost-Vlaanderen"). Html en geen svg: de namen
    lopen dan netjes af, en de waarde staat aan het uiteinde van de balk.
--}}

@php
    $rows = collect($rows);
    $max = max(1e-9, (float) $rows->max('value'));
@endphp

<div {{ $attributes->class(['space-y-2.5']) }}>
    @foreach ($rows as $row)
        <div class="grid grid-cols-[minmax(0,11rem)_1fr_auto] items-center gap-3 text-sm"
             data-tip="{{ $row['label'] }}&#10;{{ number_format($row['value'], $decimals, ',', '.') }}{{ $unit }}{{ isset($row['hint']) ? "\n".$row['hint'] : '' }}">
            <span class="flex min-w-0 items-center gap-2 truncate text-gray-700">{!! $row['key'] ?? '' !!}<span class="truncate">{{ $row['label'] }}</span></span>
            <span class="h-3.5 rounded-r bg-transparent">
                <span class="block h-full rounded-r" style="width: {{ max(1, $row['value'] / $max * 100) }}%; background: {{ $row['color'] ?? $color }}"></span>
            </span>
            <span class="text-right text-xs tabular-nums text-gray-600">{{ number_format($row['value'], $decimals, ',', '.') }}{{ $unit }}</span>
        </div>
    @endforeach
</div>
