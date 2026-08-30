@props([
    'name',
    'label' => null,
    'help' => null,
    'required' => false,
    'error' => null,
])

@php
    /*
    | Een formulierveld: label, invoer, hulptekst en de foutmelding.
    |
    | Die vier stonden overal los uit elkaar geschreven, en de @error-regel
    | ontbrak in de meeste formulieren.
    |
    | Dat is geen bug: `partials/flash.blade.php` zet elke validatiefout in een
    | balk bovenaan, en die partial staat in de layout. De gebruiker ZIET de
    | fout dus. Maar op een formulier van dertig velden is "Controleer de
    | invoer: de begindatum is verplicht" bovenaan iets anders dan diezelfde zin
    | onder het juiste veld -- de balk zegt WAT er mis is, deze component zegt
    | WAAR.
    |
    | De twee sluiten elkaar niet uit en horen allebei te blijven staan: de balk
    | vangt ook de fouten die bij geen enkel veld horen.
    |
    | De INVOER zelf komt uit de slot. Deze component verzint geen <input>: een
    | select, een textarea en een groep radioknoppen horen allemaal in dezelfde
    | omlijsting, en een component die dat probeert te dekken, dekt het slecht.
    |
    | DAARMEE IS DE HELFT VAN DE KOPPELING AAN DE AANROEPER. Hier stond dat de
    | fout met aria-describedby terugwijst en dat het veld aria-invalid krijgt.
    | Dat kan niet: de slot wordt in de scope van de aanroeper uitgevoerd, dus
    | deze component kan er geen attributen in zetten. Wat ze wel doet is de
    | ids VOORSPELBAAR maken -- {name}-help en {name}-error -- zodat de
    | aanroeper ze kan aanwijzen:
    |
    |     <x-field name="email" :label="__('E-mail')" :help="__('Werkadres.')">
    |         <input id="email" name="email" class="input"
    |                aria-describedby="email-error email-help"
    |                @error('email') aria-invalid="true" @enderror>
    |     </x-field>
    |
    | Een id dat naar niets wijst is onschadelijk, dus beide mogen er altijd
    | staan. Het label wijst met for= naar id="{name}", dus dat id moet er zijn.
    */
    $errorKey = str_replace(['[]', '[', ']'], ['', '.', ''], $name);

    /*
    | $errors bestaat alleen binnen de web-middleware, die hem deelt vanuit de
    | sessie. In een mailtemplate, een PDF of een Blade::render() is hij er niet,
    | en dan valt een component die hem klakkeloos aanspreekt om met "Undefined
    | variable". Vandaar de controle: geen foutenzak betekent geen fout.
    */
    $bag = isset($errors) ? $errors : null;
    $message = $error ?? ($bag?->has($errorKey) ? $bag->first($errorKey) : null);
@endphp

<div {{ $attributes->class(['min-w-0']) }}>
    @if ($label)
        <label class="label" for="{{ $name }}">
            {{ $label }}
            @if ($required)
                <span aria-hidden="true" class="text-red-600">*</span>
                <span class="sr-only">{{ __('verplicht') }}</span>
            @endif
        </label>
    @endif

    {{ $slot }}

    @if ($help && ! $message)
        <p class="help" id="{{ $name }}-help">{{ $help }}</p>
    @endif

    @if ($message)
        <p class="mt-1 text-xs text-red-600" id="{{ $name }}-error">{{ $message }}</p>
    @endif
</div>
