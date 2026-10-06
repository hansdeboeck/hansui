@props([
    // ['2026-01-01' => 12, ...] of ['label' => waarde]
    'data' => [],
    'height' => 160,
    'color' => 'var(--series-1)',
    'unit' => '',
    'dates' => true,
    'caption' => null,
])

{{--
    Kolommen, een per dag of per categorie. Een reeks, dus een kleur en geen
    legende. Kolommen van hoogstens 24 pixels met een afgeronde top en een
    vierkante voet; wat er van de strook overblijft, is lucht.
--}}

@php
    $data = collect($data);
    $n = max(1, $data->count());
    $w = 720; $h = (int) $height; $padL = 44; $padR = 8; $padT = 8; $padB = 22;
    $plotW = $w - $padL - $padR; $plotH = $h - $padT - $padB;
    $scale = \HansDeBoeck\HansUi\Chart::scale((float) $data->max());
    $slot = $plotW / $n;
    $barW = min(24, max(2, $slot - 2));
    $labelEvery = max(1, (int) ceil($n / 8));
    $fmt = fn ($v) => number_format($v, floor($v) != $v ? 1 : 0, ',', '.').$unit;
    $name = fn ($k) => $dates ? \Illuminate\Support\Carbon::parse($k)->translatedFormat('D j M') : $k;
@endphp

<figure {{ $attributes->class(['chart']) }}>
    <svg viewBox="0 0 {{ $w }} {{ $h }}" role="img" aria-label="{{ $caption }}">
        @foreach ($scale['ticks'] as $tick)
            @php $ty = $padT + $plotH - $tick / $scale['max'] * $plotH; @endphp
            <line x1="{{ $padL }}" x2="{{ $w - $padR }}" y1="{{ $ty }}" y2="{{ $ty }}" class="{{ $loop->first ? 'chart-axis' : 'chart-grid' }}"/>
            <text x="{{ $padL - 8 }}" y="{{ $ty + 4 }}" text-anchor="end" class="chart-tick">{{ \HansDeBoeck\HansUi\Chart::tick($tick) }}</text>
        @endforeach

        @foreach ($data as $key => $value)
            @php
                $i = $loop->index;
                $bh = $value > 0 ? max(2, $value / $scale['max'] * $plotH) : 0;
                $bx = $padL + $i * $slot + ($slot - $barW) / 2;
                $by = $padT + $plotH - $bh;
                $r = min(4, $barW / 2, $bh);
            @endphp
            @if ($bh > 0)
                <path class="chart-bar" style="fill: {{ $color }}"
                      d="M{{ $bx }} {{ $padT + $plotH }} V{{ $by + $r }} Q{{ $bx }} {{ $by }} {{ $bx + $r }} {{ $by }} H{{ $bx + $barW - $r }} Q{{ $bx + $barW }} {{ $by }} {{ $bx + $barW }} {{ $by + $r }} V{{ $padT + $plotH }} Z"/>
            @endif
            <rect x="{{ $padL + $i * $slot }}" y="{{ $padT }}" width="{{ $slot }}" height="{{ $plotH }}" class="chart-hit"
                  data-tip="{{ $name($key) }}&#10;{{ $fmt($value) }}"/>
            @if ($i % $labelEvery === 0)
                <text x="{{ $padL + $i * $slot + $slot / 2 }}" y="{{ $h - 5 }}" text-anchor="middle" class="chart-tick">
                    {{ $dates ? \Illuminate\Support\Carbon::parse($key)->format('d/m') : \Illuminate\Support\Str::limit($key, 8, '') }}
                </text>
            @endif
        @endforeach
    </svg>

    <details class="chart-table mt-2">
        <summary>{{ __('Als tabel') }}</summary>
        <div class="mt-2 max-h-64 overflow-auto">
            <table class="w-full text-xs">
                <tbody>
                    @foreach ($data as $key => $value)
                        <tr><td class="td">{{ $name($key) }}</td><td class="td th-num">{{ $fmt($value) }}</td></tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </details>
</figure>
