@props(['owner' => 'deboeck.dev', 'dark' => false])

{{--
    De voettekst.

    Stond in vijf applicaties, in drie varianten -- dezelfde regel, telkens net
    anders opgemaakt. Dat is precies het soort bestand waarvan niemand merkt dat
    het uiteenloopt, tot je ze naast elkaar legt.
--}}

<footer {{ $attributes->class([
    'px-4 py-6 text-center text-xs',
    'on-dark opacity-60' => $dark,
    'text-gray-500' => ! $dark,
]) }}>
    &copy; {{ date('Y') }} {{ $owner }}. {{ __('Alle rechten voorbehouden.') }}
</footer>
