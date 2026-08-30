@props(['by', 'align' => 'left'])

@php
    /*
    | Een sorteerbare kolomkop.
    |
    | Sorteren via de QUERY-STRING en niet via component-toestand: de URL blijft
    | dan deelbaar, en dat is wat HR met een lijst doet -- hem doorsturen. Klikken
    | op de actieve kolom draait de richting om.
    */
    $current = request('sort');
    $direction = request('dir') === 'desc' ? 'desc' : 'asc';
    $active = $current === $by;
    $next = $active && $direction === 'asc' ? 'desc' : 'asc';
@endphp

<th class="th" @if ($align === 'right') style="text-align: right" @endif
    aria-sort="{{ $active ? ($direction === 'asc' ? 'ascending' : 'descending') : 'none' }}">
    <a href="{{ request()->fullUrlWithQuery(['sort' => $by, 'dir' => $next, 'page' => null]) }}"
       class="inline-flex items-center gap-1 transition hover:opacity-70"
       @if ($active) style="color: var(--ink)" @endif>
        {{ $slot }}
        <span class="text-[0.6rem] leading-none" @unless ($active) style="opacity: .35" @endunless>
            {{ $active && $direction === 'desc' ? '▼' : '▲' }}
        </span>
    </a>
</th>
