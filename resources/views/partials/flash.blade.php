{{--
    Inline banners, geen toasts: een melding die vanzelf verdwijnt is een
    melding die iemand mist. Zelfde vorm als OTA en POS.

    De opmaak staat in .alert en niet meer in losse utilities. Dat was hier geen
    smaakkwestie: `text-emerald-800` en `border-emerald-200` stonden niet in het
    @theme-blok van hansui.css, dus die twee bleven Tailwinds eigen kleur houden
    terwijl de achtergrond wel meekantelde -- in donkere modus donkergroen op
    bijna-zwart, contrast 1,4:1.
--}}
@if (session('status') || session('success'))
    <div data-flash class="alert alert-success mb-6">
        <div class="alert-body">{{ session('status') ?? session('success') }}</div>
        <button type="button" data-dismiss class="alert-dismiss" aria-label="{{ __('Sluiten') }}">&times;</button>
    </div>
@endif

@if (session('error'))
    <div data-flash class="alert alert-danger mb-6">
        <div class="alert-body">{{ session('error') }}</div>
        <button type="button" data-dismiss class="alert-dismiss" aria-label="{{ __('Sluiten') }}">&times;</button>
    </div>
@endif

@if ($errors->any())
    <div data-flash class="alert alert-danger mb-6">
        <div class="alert-body">
            <p class="mb-1 font-medium">{{ __('Controleer de invoer:') }}</p>
            <ul class="list-inside list-disc space-y-0.5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        <button type="button" data-dismiss class="alert-dismiss" aria-label="{{ __('Sluiten') }}">&times;</button>
    </div>
@endif
