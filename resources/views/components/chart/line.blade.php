@props([
    // [['label' => ..., 'color' => 'var(--series-1)', 'points' => ['2026-01-01' => 12, ...]], ...]
    'series' => [],
    'height' => 220,
    'unit' => '',
    'area' => null,
    'caption' => null,
    // Uit voor een stand die ver van nul ligt (volgers, een voorraad): een groei
    // van 5.000 naar 5.200 is anders een vlakke lijn.
    'fromZero' => true,
])

{{--
    Een lijngrafiek over de tijd, op de server getekend.

    EEN AS. Twee maten met een andere schaal krijgen twee grafieken, geen
    tweede as: een tweede as laat de lezer een verband zien dat de maker koos
    door de schalen te kiezen.

    Een reeks krijgt een lichte vlakvulling en geen legende (de titel zegt wat
    het is); twee of meer krijgen een legende, en geen vulling. Over elke dag
    ligt een onzichtbare strook die bij hover de waarden van die dag toont, en
    onder de grafiek staat dezelfde reeks als tabel, voor wie liever leest.
--}}

@php
    $series = collect($series)->filter(fn ($s) => ! empty($s['points']))->values();
    $dates = $series->flatMap(fn ($s) => array_keys($s['points']))->unique()->sort()->values();
    $n = max(1, $dates->count());
    $w = 720; $h = (int) $height; $padL = 44; $padR = 12; $padT = 10; $padB = 24;
    $plotW = $w - $padL - $padR; $plotH = $h - $padT - $padB;
    $values = $series->flatMap(fn ($s) => array_filter($s['points'], fn ($v) => $v !== null));
    $rawMin = (float) ($values->min() ?? 0);
    $rawMax = (float) ($values->max() ?? 0);
    $min = min(0, $rawMin);

    if (! $fromZero && $rawMin > 0) {
        $step = \HansDeBoeck\HansUi\Chart::scale(max(1, $rawMax - $rawMin))['ticks'][1] ?? 1;
        $min = floor($rawMin / $step) * $step;
    }

    $scale = \HansDeBoeck\HansUi\Chart::scale($rawMax - $min);
    $top = $scale['max'] + $min;
    $x = fn (int $i) => $padL + ($n === 1 ? $plotW / 2 : $i * $plotW / ($n - 1));
    $y = fn ($v) => $padT + $plotH - (($v - $min) / max(1e-9, $top - $min)) * $plotH;
    $single = $series->count() === 1;
    $area ??= $single;
    $labelEvery = max(1, (int) ceil($n / 7));
    $fmt = fn ($v) => $v === null ? '–' : number_format($v, floor($v) != $v ? 1 : 0, ',', '.').$unit;
    $id = 'lc'.substr(md5(json_encode($series)), 0, 6);
@endphp

<figure {{ $attributes->class(['chart']) }}>
    @if ($series->count() > 1)
        <figcaption class="chart-legend mb-3">
            @foreach ($series as $s)
                <span><span class="chart-key" style="background: {{ $s['color'] }}"></span>{{ $s['label'] }}</span>
            @endforeach
        </figcaption>
    @endif

    @if ($dates->isEmpty())
        <p class="py-10 text-center text-sm text-gray-400">{{ __('Nog geen cijfers in deze periode.') }}</p>
    @else
        <svg viewBox="0 0 {{ $w }} {{ $h }}" role="img" aria-label="{{ $caption ?? $series->pluck('label')->implode(', ') }}">
            @foreach ($scale['ticks'] as $tick)
                @php $ty = $y($tick + $min); @endphp
                <line x1="{{ $padL }}" x2="{{ $w - $padR }}" y1="{{ $ty }}" y2="{{ $ty }}" class="{{ $loop->first ? 'chart-axis' : 'chart-grid' }}"/>
                <text x="{{ $padL - 8 }}" y="{{ $ty + 4 }}" text-anchor="end" class="chart-tick">{{ \HansDeBoeck\HansUi\Chart::tick($tick + $min) }}</text>
            @endforeach

            @foreach ($dates as $i => $date)
                @if ($i % $labelEvery === 0 || $i === $n - 1)
                    <text x="{{ $x($i) }}" y="{{ $h - 6 }}" text-anchor="middle" class="chart-tick">{{ \Illuminate\Support\Carbon::parse($date)->format('d/m') }}</text>
                @endif
            @endforeach

            @foreach ($series as $s)
                @php
                    $pts = [];
                    foreach ($dates as $i => $date) {
                        if (($s['points'][$date] ?? null) !== null) { $pts[] = [$x($i), $y($s['points'][$date])]; }
                    }
                    $d = collect($pts)->map(fn ($p, $k) => ($k ? 'L' : 'M').round($p[0], 1).' '.round($p[1], 1))->implode(' ');
                @endphp
                @if ($area && count($pts) > 1)
                    <path d="{{ $d }} L{{ round(end($pts)[0], 1) }} {{ $padT + $plotH }} L{{ round($pts[0][0], 1) }} {{ $padT + $plotH }} Z"
                          style="fill: {{ $s['color'] }}; opacity: 0.1"/>
                @endif
                <path d="{{ $d }}" class="chart-line" style="stroke: {{ $s['color'] }}"/>
                @if ($pts)
                    <circle cx="{{ end($pts)[0] }}" cy="{{ end($pts)[1] }}" r="4" class="chart-dot" style="fill: {{ $s['color'] }}"/>
                @endif
            @endforeach

            @foreach ($dates as $i => $date)
                @php
                    $bandW = $plotW / max(1, $n - 1);
                    $tip = \Illuminate\Support\Carbon::parse($date)->translatedFormat('D j M')."\n"
                        .$series->map(fn ($s) => ($single ? '' : $s['label'].': ').$fmt($s['points'][$date] ?? null))->implode("\n");
                @endphp
                <rect x="{{ max($padL, $x($i) - $bandW / 2) }}" y="{{ $padT }}" width="{{ $n === 1 ? $plotW : $bandW }}" height="{{ $plotH }}"
                      class="chart-hit" data-tip="{{ $tip }}" tabindex="-1"/>
                <line x1="{{ $x($i) }}" x2="{{ $x($i) }}" y1="{{ $padT }}" y2="{{ $padT + $plotH }}" class="chart-cross"/>
            @endforeach
        </svg>

        <details class="chart-table mt-2">
            <summary>{{ __('Als tabel') }}</summary>
            <div class="mt-2 max-h-64 overflow-auto">
                <table class="w-full text-xs">
                    <thead><tr><th class="th">{{ __('Datum') }}</th>
                        @foreach ($series as $s)<th class="th th-num">{{ $s['label'] }}</th>@endforeach</tr></thead>
                    <tbody>
                        @foreach ($dates as $date)
                            <tr><td class="td">{{ \Illuminate\Support\Carbon::parse($date)->format('d/m/Y') }}</td>
                                @foreach ($series as $s)<td class="td th-num">{{ $fmt($s['points'][$date] ?? null) }}</td>@endforeach</tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </details>
    @endif
</figure>
