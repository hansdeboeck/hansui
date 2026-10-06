@props([
    'id' => 'sneltoetsen',
    'title' => null,
    // ['J' => 'Volgende', 'Ctrl ↵' => 'Versturen']
    'keys' => [],
])

{{--
    Het overzicht van de sneltoetsen van een scherm, in een venster.

    Open het met een knop die zelf ook een sneltoets is:

        <button type="button" data-modal-open="#sneltoetsen" data-shortcut="?">?</button>
        <x-shortcuts :keys="['J' => __('Volgende'), 'K' => __('Vorige'), '/' => __('Zoeken')]"/>

    Een toets met Ctrl erin wordt op een Mac ⌘ (data-kbd-mod): hansui.js
    luistert daar toch al naar allebei. Wat er in de slot staat, komt onder de
    lijst.
--}}

<x-hansui::modal :id="$id" :title="$title ?? __('Sneltoetsen')" size="sm" {{ $attributes }}>
    <div class="modal-body">
        <dl class="space-y-2 text-sm">
            @foreach ($keys as $key => $label)
                <div class="flex items-center justify-between gap-4">
                    <dt class="text-gray-700">{{ $label }}</dt>
                    <dd class="flex-none"><kbd class="kbd" @if (str_contains((string) $key, 'Ctrl')) data-kbd-mod @endif>{{ $key }}</kbd></dd>
                </div>
            @endforeach
        </dl>

        {{ $slot }}
    </div>
</x-hansui::modal>
