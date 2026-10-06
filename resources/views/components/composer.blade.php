@props([
    'action',
    'method' => 'POST',
    // Het tekstvak: zijn naam in het formulier, en zijn id voor een label of een teller.
    'name' => 'body',
    'id' => 'composer',
    'label' => null,
    'placeholder' => null,
    'value' => null,
    // Onthoudt wat je typte onder deze sleutel tot het vertrokken is, zie data-draft.
    'draft' => null,
    // Wat {sleutel} in een ingevoegde tekst wordt, zie data-insert.
    'values' => [],
    'rows' => 2,
    'required' => true,
    'maxlength' => null,
])

{{--
    Een antwoordvak onder een gesprek.

    EEN VAK: de tekst, wat eronder meegaat en de knoppen in dezelfde rand,
    die oplicht zodra je typt. Het vak groeit mee met wat je schrijft,
    onthoudt het tot het vertrokken is, en verstuurt met Ctrl + Enter.

        <x-composer :action="route('antwoord', $gesprek)" :draft="'gesprek.'.$gesprek->id"
                    :placeholder="__('Schrijf een antwoord…')" :values="['naam' => $voornaam]">
            <x-slot:head>…antwoorden of notitie, en waar het naartoe gaat…</x-slot:head>
            <x-slot:below><p class="composer-sign">…handtekening…</p></x-slot:below>
            <x-slot:tools>…standaardantwoorden, een teller…</x-slot:tools>
            <x-slot:actions><button class="btn btn-primary btn-sm">Versturen</button></x-slot:actions>
        </x-composer>

    ANTWOORDEN OF NOTITIE is een paar radioknoppen in `head`, in een
    `.segmented`: `data-composer-note` op die van de notitie kleurt het vak,
    `data-composer-placeholder` zet een andere voorzettekst. Wat per stand
    anders is (de knop, de handtekening), toont `data-show-when`.

    Na een validatiefout staat de tekst er weer: `old()` vult het vak.
--}}

@php
    $method = strtoupper($method);
@endphp

<form method="{{ $method === 'GET' ? 'GET' : 'POST' }}" action="{{ $action }}"
      {{ $attributes->class(['composer']) }} data-composer-form
      @if ($values) data-composer-values="{{ json_encode($values, JSON_UNESCAPED_UNICODE) }}" @endif>
    @if ($method !== 'GET')
        @csrf
    @endif
    @if (! in_array($method, ['GET', 'POST'], true))
        @method($method)
    @endif

    @isset($head)
        <div {{ $head->attributes->class(['composer-head']) }}>{{ $head }}</div>
    @endisset

    <div class="composer-box">
        <label for="{{ $id }}" class="sr-only">{{ $label ?? __('Bericht') }}</label>
        <textarea id="{{ $id }}" name="{{ $name }}" rows="{{ $rows }}" class="composer-input" data-autogrow
                  @if ($draft) data-draft="{{ $draft }}" @endif
                  @if ($required) required @endif
                  @if ($maxlength) maxlength="{{ $maxlength }}" @endif
                  @if ($placeholder) placeholder="{{ $placeholder }}" @endif>{{ $value ?? old($name) }}</textarea>

        {{ $below ?? '' }}

        <div class="composer-bar">
            {{ $tools ?? '' }}

            <div class="composer-actions">{{ $actions ?? '' }}</div>
        </div>
    </div>
</form>
