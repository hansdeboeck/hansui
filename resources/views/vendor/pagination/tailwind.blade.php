{{--
    De paginatie, in het Nederlands en in de huisstijl.

    Laravels eigen tailwind-weergave is hier vervangen en niet vertaald, om drie
    redenen die alle drie op elk lijstscherm zichtbaar waren:

     1. de teksten stonden in het Engels ("Showing 1 to 25 of 500 results"), en
        de vier losse sleutels waaruit die zin is opgebouwd laten geen
        Nederlandse woordvolgorde toe;
     2. `pagination.previous` heeft geen vertaling in lang/nl, dus de RAUWE
        SLEUTEL belandde in het aria-label -- een schermlezer las "pagination
        punt previous" voor;
     3. ze brengt een eigen palet mee (blauwe focusring, dark:-varianten) dat
        botst met het gray-900-designsysteem van OTA en POS.

    De knopklassen komen uit hansui.css, zodat deze weergave meebeweegt met de
    rest.
--}}
@if ($paginator->hasPages())
    <nav role="navigation" aria-label="{{ __('Paginering') }}"
         class="flex items-center justify-between gap-4 border-t border-gray-200 pt-3 text-sm">

        {{-- Op een smal scherm alleen vorige en volgende: twintig paginanummers
             naast elkaar is daar onbruikbaar. --}}
        <div class="flex flex-1 items-center justify-between gap-2 sm:hidden">
            @if ($paginator->onFirstPage())
                <span class="btn btn-sm btn-secondary cursor-not-allowed opacity-50">{{ __('Vorige') }}</span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="btn btn-sm btn-secondary">{{ __('Vorige') }}</a>
            @endif

            <span class="text-gray-500">
                {{ $paginator->currentPage() }} / {{ $paginator->lastPage() }}
            </span>

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="btn btn-sm btn-secondary">{{ __('Volgende') }}</a>
            @else
                <span class="btn btn-sm btn-secondary cursor-not-allowed opacity-50">{{ __('Volgende') }}</span>
            @endif
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
                @if ($paginator->onFirstPage())
                    <span aria-disabled="true" aria-label="{{ __('Vorige pagina') }}"
                          class="grid h-8 w-8 place-items-center rounded-lg text-gray-300">&lsaquo;</span>
                @else
                    <a href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="{{ __('Vorige pagina') }}"
                       class="grid h-8 w-8 place-items-center rounded-lg text-gray-600 transition hover:bg-gray-100 hover:text-gray-900">&lsaquo;</a>
                @endif

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
                                <a href="{{ $url }}" aria-label="{{ __('Naar pagina :page', ['page' => $page]) }}"
                                   class="grid h-8 min-w-8 place-items-center rounded-lg px-2 text-gray-600 transition hover:bg-gray-100 hover:text-gray-900">{{ $page }}</a>
                            @endif
                        @endforeach
                    @endif
                @endforeach

                @if ($paginator->hasMorePages())
                    <a href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="{{ __('Volgende pagina') }}"
                       class="grid h-8 w-8 place-items-center rounded-lg text-gray-600 transition hover:bg-gray-100 hover:text-gray-900">&rsaquo;</a>
                @else
                    <span aria-disabled="true" aria-label="{{ __('Volgende pagina') }}"
                          class="grid h-8 w-8 place-items-center rounded-lg text-gray-300">&rsaquo;</span>
                @endif
            </div>
        </div>
    </nav>
@endif
