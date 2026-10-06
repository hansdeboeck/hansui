@props([
    // [weekdag 1-7 => [uur 0-23 => ['value' => float|null, 'tip' => string, 'weak' => bool]]]
    'cells' => [],
    'max' => null,
    'less' => null,
    'more' => null,
])

{{--
    Een week in vakjes: zeven dagen bij vierentwintig uur. Voor "wanneer werkt
    het", "wanneer is het druk", "wanneer komen de meldingen binnen".

    EEN TINT, van licht naar donker (sequentieel): donkerder is meer. Leeg
    (`value` null) is de lichtste stap en niet wit: "niets gemeten" is iets
    anders dan "niet getekend". Een vakje met `weak` staat lichter, voor een
    waarde die op te weinig rust (een enkel bericht is een anekdote); zeg dat
    er dan ook bij in de `tip`.

    De `tip` schrijft de applicatie zelf, want alleen zij weet wat een waarde
    betekent. De dag en het uur zet dit component ervoor.
--}}

@php
    $dagen = [1 => __('ma'), 2 => __('di'), 3 => __('wo'), 4 => __('do'), 5 => __('vr'), 6 => __('za'), 7 => __('zo')];
    $max ??= collect($cells)->flatten(1)->pluck('value')->filter(fn ($v) => $v !== null)->max();
    $max = max(1e-9, (float) $max);
@endphp

<div {{ $attributes->class(['chart overflow-x-auto']) }}>
    <div class="grid min-w-[560px] grid-cols-[2rem_repeat(24,minmax(0,1fr))] gap-[2px] text-[0.625rem] text-gray-400">
        <span></span>
        @foreach (range(0, 23) as $uur)
            <span class="text-center">{{ $uur % 3 === 0 ? $uur : '' }}</span>
        @endforeach

        @foreach ($dagen as $dag => $naam)
            <span class="self-center">{{ $naam }}</span>
            @foreach (range(0, 23) as $uur)
                @php
                    $cel = $cells[$dag][$uur] ?? [];
                    $waarde = $cel['value'] ?? null;
                    $stap = $waarde === null ? 0 : max(1, min(6, (int) ceil($waarde / $max * 6)));
                    $tip = $naam.' '.sprintf('%02d:00', $uur).(isset($cel['tip']) ? "\n".$cel['tip'] : '');
                @endphp
                <span class="heat-cell" data-tip="{{ $tip }}"
                      style="background: var(--seq-{{ $stap }});{{ ! empty($cel['weak']) ? ' opacity: .55' : '' }}"></span>
            @endforeach
        @endforeach
    </div>

    <div class="mt-3 flex items-center gap-2 text-[0.6875rem] text-gray-500">
        <span>{{ $less ?? __('minder') }}</span>
        @foreach (range(1, 6) as $stap)
            <span class="h-2.5 w-5 rounded-sm" style="background: var(--seq-{{ $stap }})"></span>
        @endforeach
        <span>{{ $more ?? __('meer') }}</span>
    </div>
</div>
