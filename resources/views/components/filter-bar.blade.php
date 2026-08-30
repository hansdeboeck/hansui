@props([
    'labels' => [],
    'display' => [],
    'action' => null,
])

@php
    /*
    | Welke filters staan er AAN?
    |
    | Dit is het gat dat het scherm nu heeft: je zet een filter, het formulier
    | klapt weg, en daarna staat er een lijst met te weinig rijen zonder dat
    | iets vertelt waarom. Elk actief filter krijgt hier een chip met een kruisje
    | dat alleen dat ene filter wist.
    |
    | `page` hoort er nooit bij -- dat is paginering en geen filter -- en een
    | filter dat leeg is, staat niet aan.
    */
    $query = collect(request()->query())
        ->except(['page'])
        ->filter(fn ($value, $key) => isset($labels[$key]) && trim((string) $value) !== '');

    $chip = function (string $key, mixed $value) use ($display) {
        $map = $display[$key] ?? null;

        return $map[$value] ?? $map[(int) $value] ?? (string) $value;
    };
@endphp

<div {{ $attributes->merge(['class' => 'mb-4 flex flex-col gap-3']) }}>
    <form method="GET" action="{{ $action }}" class="card grid gap-4 p-4 sm:grid-cols-4">
        {{ $slot }}
    </form>

    @if ($query->isNotEmpty())
        <div class="flex flex-wrap items-center gap-2">
            @foreach ($query as $key => $value)
                <span class="chip">
                    <span>{{ $labels[$key] }}</span>
                    <b>{{ $chip($key, $value) }}</b>

                    {{-- Dezelfde URL, dit ene filter eruit. Een link en geen
                         formulier: hij hoort deelbaar en terug-knop-bestendig te
                         zijn, net als de filters zelf. --}}
                    <a href="{{ request()->fullUrlWithoutQuery([$key, 'page']) }}"
                       class="grid h-4 w-4 place-items-center rounded-full transition"
                       style="color: var(--ink-faint)"
                       aria-label="{{ __('Filter :naam wissen', ['naam' => $labels[$key]]) }}">&times;</a>
                </span>
            @endforeach

            @if ($query->count() > 1)
                <a href="{{ request()->fullUrlWithoutQuery(array_merge(array_keys($labels), ['page'])) }}"
                   class="text-xs underline-offset-2 hover:underline" style="color: var(--ink-faint)">
                    {{ __('Alles wissen') }}
                </a>
            @endif
        </div>
    @endif
</div>
