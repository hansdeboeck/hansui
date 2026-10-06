@props([
    // in: van de ander. out: van jou. auto: automatisch, van jou. failed: van
    // jou, maar niet vertrokken. note: een notitie die alleen je team ziet.
    'type' => 'in',
    // bubble voor een chat, card voor een mail.
    'layout' => 'bubble',
    'name' => null,
    // card: het adres achter de naam, en naar wie het ging.
    'address' => null,
    'to' => null,
    'time' => null,
    'zone' => null,
    // De tekst. Regeleinden blijven staan, een lange link breekt af.
    'body' => null,
    // Het woord bij auto en note; anders "Automatisch antwoord" en "interne notitie".
    'label' => null,
    // failed: waarom het niet vertrok.
    'error' => null,
    // De kleur van de avatar van de ander, zie x-avatar.
    'tint' => true,
])

{{--
    Een bericht in een gesprek.

    VIJF SOORTEN, en elk ziet er anders uit omdat ze iets anders betekenen:
    wat binnenkwam (links), wat jij schreef (rechts, in de merkkleur), een
    automatisch antwoord (rechts, met een bliksem, want wie "we hebben je
    bericht" kreeg, heeft nog geen antwoord), een notitie (midden, geel: die
    zag de ander nooit) en wat niet vertrok (rood, met de reden).

    TWEE VORMEN. Een chat is een ballon; een mail is een kaart met een kop,
    want een mail heeft een afzender, een adres en een onderwerp dat ertoe
    doet.

        <x-message type="in" :name="$r->naam" :time="$r->created_at" :body="$r->tekst">
            <x-slot:avatar><x-avatar :name="$r->naam" :src="$r->foto"/></x-slot:avatar>
            …bijlagen…
        </x-message>

    De slot komt na de tekst (bijlagen), `top` ervoor (sterren bij een review),
    en `retry` naast de reden van een bericht dat niet vertrok. Zonder `avatar`
    tekent de component er zelf een uit `name`.
--}}

@php
    $type = in_array($type, ['in', 'out', 'auto', 'failed', 'note'], true) ? $type : 'in';
    $ours = in_array($type, ['out', 'auto', 'failed'], true);
    $label ??= match ($type) {
        'auto' => __('Automatisch antwoord'),
        'note' => __('interne notitie'),
        default => null,
    };
    $classes = [
        $ours ? 'message-out' : 'message-in',
        'message-auto' => $type === 'auto',
        'message-failed' => $type === 'failed',
    ];
@endphp

@if ($type === 'note')
    <div {{ $attributes->class(['message-note']) }}>
        <p class="message-note-head">
            <x-hansui::icon name="note" size="h-3.5 w-3.5"/>
            @if (filled($name))
                <span class="font-semibold">{{ $name }}</span>
                <span aria-hidden="true">&middot;</span>
            @endif
            <span>{{ $label }}</span>
            @if ($time)
                <span aria-hidden="true">&middot;</span>
                <x-hansui::ago :time="$time" :zone="$zone"/>
            @endif
        </p>

        {{ $top ?? '' }}

        @if (filled($body))
            <p class="message-text">{{ $body }}</p>
        @endif

        {{ $slot }}
    </div>

@elseif ($layout === 'card')
    <article {{ $attributes->class(['message-card', ...$classes]) }}>
        <header class="message-card-head">
            @isset($avatar)
                {{ $avatar }}
            @elseif ($type === 'auto')
                <span class="message-bot"><x-hansui::icon name="bolt"/></span>
            @else
                <x-hansui::avatar :name="$name" size="sm" :tint="$ours ? null : $tint"/>
            @endisset

            <div class="min-w-0 flex-1 text-sm">
                <p class="truncate">
                    <span class="font-semibold text-gray-900">{{ $name }}</span>
                    @if (filled($address))
                        <span class="text-gray-500">&lt;{{ $address }}&gt;</span>
                    @endif
                </p>

                @if (filled($to) || $type === 'auto')
                    <p class="truncate text-xs text-gray-500">
                        @if (filled($to))
                            {{ __('aan :adres', ['adres' => $to]) }}
                        @endif
                        @if ($type === 'auto')
                            @if (filled($to)) &middot; @endif
                            <span class="font-medium text-[var(--brand)]">{{ $label }}</span>
                        @endif
                    </p>
                @endif
            </div>

            @if ($time)
                <x-hansui::ago :time="$time" :zone="$zone" class="flex-none text-xs text-gray-500"/>
            @endif
        </header>

        <div class="message-card-body">
            {{ $top ?? '' }}

            @if (filled($body))
                <p class="message-text">{{ $body }}</p>
            @endif

            {{ $slot }}
        </div>

        @if ($type === 'failed')
            <div class="message-card-error">
                <x-hansui::icon name="warning" size="h-3.5 w-3.5"/>
                <span class="min-w-0 flex-1">{{ __('Niet verstuurd:') }} {{ $error }}</span>
                {{ $retry ?? '' }}
            </div>
        @endif
    </article>

@else
    <div {{ $attributes->class(['message', ...$classes]) }}>
        <span class="message-avatar">
            @isset($avatar)
                {{ $avatar }}
            @elseif ($type === 'auto')
                <span class="message-bot"><x-hansui::icon name="bolt"/></span>
            @else
                <x-hansui::avatar :name="$name" size="sm" :tint="$ours ? null : $tint"/>
            @endisset
        </span>

        <div class="message-main">
            <div class="message-bubble">
                {{ $top ?? '' }}

                @if ($type === 'auto')
                    <p class="message-label"><x-hansui::icon name="bolt" size="h-3 w-3"/> {{ $label }}</p>
                @endif

                @if (filled($body))
                    <p class="message-text">{{ $body }}</p>
                @endif

                {{ $slot }}
            </div>

            @if ($type === 'failed')
                <p class="message-error">
                    <span>{{ __('Niet verstuurd:') }} {{ $error }}</span>
                    {{ $retry ?? '' }}
                </p>
            @endif

            <p class="message-foot">
                {{ $name }}
                @if ($time)
                    @if (filled($name)) &middot; @endif
                    <x-hansui::ago :time="$time" :zone="$zone"/>
                @endif
            </p>
        </div>
    </div>
@endif
