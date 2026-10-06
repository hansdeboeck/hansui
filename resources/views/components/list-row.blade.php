@props([
    'href',
    'title' => null,
    // Het item dat open staat: aria-current op de link, en bij het laden in beeld.
    'active' => false,
    // Vet: wat op jou wacht. Niet hetzelfde als "open".
    'strong' => false,
    // Het aantal berichten erin; vanaf twee staat het naast de titel.
    'count' => null,
    // De waarde van een vinkje voor data-bulk; zonder waarde geen vinkje.
    'check' => null,
    'checkLabel' => null,
    // Een sneltoets die deze rij opent, zie data-shortcut.
    'shortcut' => null,
])

{{--
    Een rij in een lijst om af te werken: een gesprek, een ticket, een
    bestelling. Wie, waarover, hoe lang al en wat ermee gebeurt, zonder te
    klikken.

        <x-list-row :href="route('tickets.show', $t)" :active="$t->is($open)" :strong="$t->wachtOpOns()"
                    :count="$t->berichten_count" :check="$t->id" :check-label="__('Kiezen')">
            <x-slot:avatar><x-avatar :name="$t->klant" :tint="$t->email"/></x-slot:avatar>
            <x-slot:title>{{ $t->klant }}</x-slot:title>
            <x-slot:time><x-ago :time="$t->updated_at"/></x-slot:time>
            <x-slot:subject>{{ $t->onderwerp }}</x-slot:subject>
            {{ $t->laatsteZin() }}
            <x-slot:meta>…labels, wie het oppakt…</x-slot:meta>
        </x-list-row>

    DE HELE RIJ IS EEN LINK: de titel spant over de rij (.list-row-link). Het
    vinkje ligt over de avatar en verschijnt zodra je erover gaat of er iets
    gekozen is; met Ctrl of Shift erbij kies je rijen in plaats van ze te
    openen. Zet de rijen dus in een omhulling met `data-bulk`.

    De slots `subject` en `meta` vallen weg als ze leeg zijn, zodat een lijst
    zonder onderwerpen geen lege regel per rij heeft.
--}}

@php
    $count = (int) $count;
@endphp

<div {{ $attributes->class(['list-row', 'list-row-strong' => $strong]) }}
     @if ($check !== null) data-bulk-row @endif
     @if ($active) data-scroll-here="center" @endif>

    @if (isset($avatar) || $check !== null)
        <span class="list-row-avatar">
            {{ $avatar ?? '' }}

            @if ($check !== null)
                <label class="list-row-check">
                    <input type="checkbox" value="{{ $check }}" data-bulk-item aria-label="{{ $checkLabel ?? __('Kiezen') }}">
                </label>
            @endif
        </span>
    @endif

    <span class="list-row-main">
        <span class="list-row-top">
            <a href="{{ $href }}" class="list-row-link"
               @if ($active) aria-current="true" @endif
               @if ($shortcut) data-shortcut="{{ $shortcut }}" @endif>{{ $title }}</a>

            @if ($count > 1)
                <span class="list-row-count" aria-label="{{ trans_choice('{1}Een bericht|[2,*]:count berichten', $count) }}">{{ $count }}</span>
            @endif

            @isset($time)
                <span {{ $time->attributes->class(['list-row-time']) }}>{{ $time }}</span>
            @endisset
        </span>

        @if (isset($subject) && $subject->isNotEmpty())
            <span {{ $subject->attributes->class(['list-row-subject']) }}>{{ $subject }}</span>
        @endif

        @if ($slot->isNotEmpty())
            <span class="list-row-preview">{{ $slot }}</span>
        @endif

        @if (isset($meta) && $meta->isNotEmpty())
            <span {{ $meta->attributes->class(['list-row-meta']) }}>{{ $meta }}</span>
        @endif
    </span>
</div>
