@props(['change' => null, 'invert' => false])

{{--
    Het verschil met de vorige periode, als klein label: "+12%" of "-3,4%".

    Groen voor beter en rood voor slechter, en bij `invert` andersom, voor een
    cijfer waar minder beter is (een reactietijd, een aantal klachten). De
    richting staat er ook als pijl en als teken, zodat het niet op kleur alleen
    leunt.

    Geen verschil te berekenen (de vorige periode was nul), dan staat er niets:
    "+∞%" zegt niemand iets.
--}}

@if ($change !== null)
    @php $beter = $invert ? $change <= 0 : $change >= 0; @endphp
    <span {{ $attributes->class([
        'inline-flex items-center gap-0.5 rounded-full px-1.5 py-0.5 text-[0.6875rem] font-medium',
        'bg-emerald-50 text-emerald-700' => $beter,
        'bg-red-50 text-red-700' => ! $beter,
    ]) }} title="{{ __('ten opzichte van de vorige periode') }}">
        <x-hansui::icon :name="$change >= 0 ? 'trend-up' : 'trend-down'" size="h-3 w-3"/>
        {{ $change > 0 ? '+' : '' }}{{ number_format($change, abs($change) < 10 ? 1 : 0, ',', '.') }}%
    </span>
@endif
