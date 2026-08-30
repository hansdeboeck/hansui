{{-- Inline banners, geen toasts: een melding die vanzelf verdwijnt is een
     melding die iemand mist. Zelfde vorm als OTA en POS. --}}
@if (session('status') || session('success'))
    <div data-flash class="mb-6 flex items-start justify-between gap-3 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
        <span>{{ session('status') ?? session('success') }}</span>
        <button type="button" data-dismiss class="text-lg leading-none text-emerald-500 hover:text-emerald-800" aria-label="{{ __('Sluiten') }}">&times;</button>
    </div>
@endif

@if (session('error'))
    <div data-flash class="mb-6 flex items-start justify-between gap-3 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
        <span>{{ session('error') }}</span>
        <button type="button" data-dismiss class="text-lg leading-none text-red-500 hover:text-red-800" aria-label="{{ __('Sluiten') }}">&times;</button>
    </div>
@endif

@if ($errors->any())
    <div data-flash class="mb-6 flex items-start justify-between gap-3 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
        <div>
            <p class="mb-1 font-medium">{{ __('Controleer de invoer:') }}</p>
            <ul class="list-inside list-disc space-y-0.5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        <button type="button" data-dismiss class="text-lg leading-none text-red-500 hover:text-red-800" aria-label="{{ __('Sluiten') }}">&times;</button>
    </div>
@endif
