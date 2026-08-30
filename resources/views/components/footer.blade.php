@props(['owner' => 'deboeck.dev', 'dark' => false])

{{--
    De voettekst.

    Stond in vijf applicaties, in drie varianten -- dezelfde regel, telkens net
    anders opgemaakt. Dat is precies het soort bestand waarvan niemand merkt dat
    het uiteenloopt, tot je ze naast elkaar legt.

    De ZIN mag anders, de opmaak niet. Wat in de slot staat vervangt de tekst;
    zonder slot staat er de standaardregel. Zonder die uitweg moest een
    applicatie met een eigen voettekst -- streek zet er een naam onder -- de
    hele component overslaan, en dan loopt precies dit bestand opnieuw uiteen.
--}}

<footer {{ $attributes->class([
    'px-4 py-6 text-center text-xs',
    'on-dark opacity-60' => $dark,
    'text-gray-500' => ! $dark,
]) }}>
    @if (trim($slot) !== '')
        {{ $slot }}
    @else
        &copy; {{ date('Y') }} {{ $owner }}. {{ __('Alle rechten voorbehouden.') }}
    @endif
</footer>
