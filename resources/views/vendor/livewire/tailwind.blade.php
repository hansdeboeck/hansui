{{--
    Dezelfde paginatie als resources/views/vendor/pagination/tailwind.blade.php,
    maar voor Livewire.

    Twee weergaven en niet een: een Livewire-component moet pagineren met
    wire:click, want een gewone link herlaadt de pagina en gooit de
    componenttoestand weg -- op de matrix is dat de gekozen groep, de eenheid en
    de zoekterm. Verandert de ene weergave, verander dan de andere mee.

    Livewires eigen weergave scrolt na het pagineren met een x-on:click-snippet
    naar boven. Die is hier weggelaten: het project schrijft zelf geen
    Alpine-directives, de tabelkop is sticky, en vijfentwintig rijen passen op
    een scherm.
--}}
@if ($paginator->hasPages())
    <nav role="navigation" aria-label="{{ __('Paginering') }}"
         class="flex items-center justify-between gap-4 border-t border-gray-200 pt-3 text-sm">

        {{-- Op een smal scherm alleen vorige en volgende. --}}
        <div class="flex flex-1 items-center justify-between gap-2 sm:hidden">
            <button type="button" wire:click="previousPage('{{ $paginator->getPageName() }}')"
                    wire:loading.attr="disabled" @disabled($paginator->onFirstPage())
                    class="btn btn-sm btn-secondary disabled:cursor-not-allowed disabled:opacity-50">{{ __('Vorige') }}</button>

            <span class="text-gray-500">{{ $paginator->currentPage() }} / {{ $paginator->lastPage() }}</span>

            <button type="button" wire:click="nextPage('{{ $paginator->getPageName() }}')"
                    wire:loading.attr="disabled" @disabled(! $paginator->hasMorePages())
                    class="btn btn-sm btn-secondary disabled:cursor-not-allowed disabled:opacity-50">{{ __('Volgende') }}</button>
        </div>

        <div class="hidden flex-1 items-center justify-between gap-4 sm:flex">
            <p class="text-gray-500">
                {{ __('Toont') }}
                <span class="font-medium text-gray-900">{{ $paginator->firstItem() }}</span>
                {{ __('tot') }}
                <span class="font-medium text-gray-900">{{ $paginator->lastItem() }}</span>
                {{ __('van') }}
                <span class="font-medium text-gray-900">{{ $paginator->total() }}</span>
            </p>

            <div class="flex items-center gap-1">
                <button type="button" wire:click="previousPage('{{ $paginator->getPageName() }}')"
                        wire:loading.attr="disabled" @disabled($paginator->onFirstPage())
                        aria-label="{{ __('Vorige pagina') }}"
                        class="grid h-8 w-8 place-items-center rounded-lg text-gray-600 transition hover:bg-gray-100 hover:text-gray-900 disabled:text-gray-300 disabled:hover:bg-transparent">&lsaquo;</button>

                @foreach ($elements as $element)
                    @if (is_string($element))
                        <span aria-hidden="true" class="grid h-8 w-8 place-items-center text-gray-400">{{ $element }}</span>
                    @endif

                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <span aria-current="page"
                                      class="grid h-8 min-w-8 place-items-center rounded-lg bg-gray-900 px-2 font-medium text-white">{{ $page }}</span>
                            @else
                                <button type="button" wire:click="gotoPage({{ $page }}, '{{ $paginator->getPageName() }}')"
                                        aria-label="{{ __('Naar pagina :page', ['page' => $page]) }}"
                                        class="grid h-8 min-w-8 place-items-center rounded-lg px-2 text-gray-600 transition hover:bg-gray-100 hover:text-gray-900">{{ $page }}</button>
                            @endif
                        @endforeach
                    @endif
                @endforeach

                <button type="button" wire:click="nextPage('{{ $paginator->getPageName() }}')"
                        wire:loading.attr="disabled" @disabled(! $paginator->hasMorePages())
                        aria-label="{{ __('Volgende pagina') }}"
                        class="grid h-8 w-8 place-items-center rounded-lg text-gray-600 transition hover:bg-gray-100 hover:text-gray-900 disabled:text-gray-300 disabled:hover:bg-transparent">&rsaquo;</button>
            </div>
        </div>
    </nav>
@endif
