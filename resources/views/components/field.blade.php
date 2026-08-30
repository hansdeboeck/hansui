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
    | Die vier stonden in vierentwintig views los uit elkaar geschreven, en de
    | @error-regel ontbrak er in de helft -- een validatiefout die nergens
    | verschijnt, is een formulier dat niets doet en niets zegt.
    |
    | Hier hangen ze aan elkaar: het label wijst naar het veld, de fout wijst
    | terug met aria-describedby, en het veld krijgt aria-invalid. Dat is niet
    | alleen netter, het is het verschil tussen een schermlezer die de fout
    | voorleest en een die hem overslaat.
    |
    | De INVOER zelf komt uit de slot. Deze component verzint geen <input>: een
    | select, een textarea en een groep radioknoppen horen allemaal in dezelfde
    | omlijsting, en een component die dat probeert te dekken, dekt het slecht.
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
                <span aria-hidden="true" style="color: var(--danger)">*</span>
                <span class="sr-only">{{ __('verplicht') }}</span>
            @endif
        </label>
    @endif

    {{ $slot }}

    @if ($help && ! $message)
        <p class="help" id="{{ $name }}-help">{{ $help }}</p>
    @endif

    @if ($message)
        <p class="mt-1 text-xs" id="{{ $name }}-error" style="color: var(--danger)">{{ $message }}</p>
    @endif
</div>
