@props(['items' => []])

{{--
    Een rij pillen om tussen samenhangende schermen te springen.

    Niet hetzelfde als de hoofdnavigatie: die zegt waar je in de applicatie
    bent, deze zegt waar je binnen EEN onderwerp bent -- jobs, rangen,
    specialisaties, diensten. In streek stond die rij twee keer met de hand
    uitgeschreven, en de twee waren al uit elkaar gelopen.

    De actieve pil hangt aan `aria-current` en niet aan een klasse, net als
    <x-nav-link>: dat is wat een schermlezer voorleest, en .tab haakt op
    datzelfde attribuut in.

    Elk item is `['label' => ..., 'href' => ..., 'active' => bool]`, met
    optioneel `count` voor een aantal achter het woord. De SLOT komt ervoor, voor
    wat bij de rij hoort maar geen pil is -- een terugkeerlink, een kopje.
--}}

<nav {{ $attributes->merge(['class' => 'mb-5 flex flex-wrap items-center gap-2']) }}>
    {{ $slot }}

    @foreach ($items as $item)
        <a href="{{ $item['href'] }}" class="tab"
           @if ($item['active'] ?? false) aria-current="page" @endif>
            {{ $item['label'] }}

            @isset($item['count'])
                <span class="opacity-60">{{ $item['count'] }}</span>
            @endisset
        </a>
    @endforeach
</nav>
