@props(['result'])

{{--
    De uitkomst van een handeling op meerdere medewerkers.

    Dit ging tot nu toe door de flash-balk, en dat werkte tot er iets misging:
    "3 van 10 gelukt. Niet gelukt: Helena Antoine: geen maat bekend; Nina
    Bodart: onvoldoende voorraad; ..." wordt een zin van vierhonderd tekens die
    niemand uitleest.

    Nu een lijst met een naam per regel. Wat MISLUKT staat bovenaan, want dat is
    waar iemand nog iets mee moet.
--}}

<div {{ $attributes->merge(['class' => 'card overflow-hidden']) }}>
    <div class="flex items-center gap-2.5 px-4 py-3">
        <span @class(['badge', 'badge-danger' => $result->hasFailures(), 'badge-success' => ! $result->hasFailures()])>
            {{ $result->doneCount() }}
        </span>
        <span class="text-sm font-medium text-gray-900">
            {{ trans_choice('{0}Niets uitgevoerd|{1}Een gelukt|[2,*]:count gelukt', $result->doneCount(), ['count' => $result->doneCount()]) }}
        </span>
    </div>

    @if ($result->hasFailures())
        <ul class="divide-y divide-gray-200 border-t border-gray-200">
            @foreach ($result->failures() as $name => $reason)
                <li class="flex flex-wrap items-baseline gap-x-2 px-4 py-2 text-sm">
                    <span class="font-medium text-gray-900">{{ $name }}</span>
                    <span class="text-gray-500">{{ $reason }}</span>
                </li>
            @endforeach
        </ul>
    @endif
</div>
