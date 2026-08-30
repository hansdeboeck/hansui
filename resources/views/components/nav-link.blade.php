@props(['active' => false])

{{--
    Een link in een navigatiebalk of zijbalk.

    Dit bestand stond als BYTE-IDENTIEKE kopie in vier applicaties -- growtail,
    shippingtail, deboeckdev2 en pos -- met dezelfde md5. Vier keer hetzelfde,
    vier plaatsen om het te vergeten.

    De actieve staat hangt aan `aria-current` en niet aan een klasse. Dat is wat
    een schermlezer voorleest, en `.nav-item` in HansUI haakt op datzelfde
    attribuut in: onmogelijk om het een zonder het ander te hebben.
--}}

<a {{ $attributes->merge(['class' => 'nav-item']) }}
   @if ($active) aria-current="page" @endif>
    {{ $slot }}
</a>
