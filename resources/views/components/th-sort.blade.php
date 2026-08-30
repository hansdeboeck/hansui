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

<th {{ $attributes->class(['th', 'th-num' => $align === 'right']) }}
    aria-sort="{{ $active ? ($direction === 'asc' ? 'ascending' : 'descending') : 'none' }}">
    <a href="{{ request()->fullUrlWithQuery(['sort' => $by, 'dir' => $next, 'page' => null]) }}"
       @class(['inline-flex items-center gap-1 transition hover:opacity-70', 'text-gray-900' => $active])>
        {{ $slot }}
        <span @class(['text-[0.6rem] leading-none', 'opacity-[.35]' => ! $active])>
            {{ $active && $direction === 'desc' ? '▼' : '▲' }}
        </span>
    </a>
</th>
