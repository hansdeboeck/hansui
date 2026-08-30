{{--
    De opmaak voor elke foutpagina.

    Bewust zonder layouts/app: die verwacht een aangemelde gebruiker en een
    organisatie in context, en juist bij een fout is een van beide er vaak niet.
    Een foutpagina die zelf stukloopt geeft de kale pagina van het framework
    terug, en dat is precies wat hier vermeden wordt.

    Om dezelfde reden staat er geen route() in maar url('/'): op een centraal
    domein bestaat de tenantroute niet, en op een tenanthost wijst / naar het
    dashboard.
--}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') &middot; {{ config('app.name') }}</title>
    @vite(['resources/css/app.css'])
</head>
<body class="surface-dark relative flex min-h-screen items-center justify-center px-4 antialiased">
    <div class="w-full max-w-md">
        <div class="mb-6 flex items-center justify-center gap-2.5">
            <span class="on-dark grid h-10 w-10 place-items-center rounded-lg" style="background: rgb(255 255 255 / .1)">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 21v-8.25M12 12.75a4.5 4.5 0 0 0-4.5-4.5H6v1.5a4.5 4.5 0 0 0 4.5 4.5h1.5Zm0 0a4.5 4.5 0 0 1 4.5-4.5H18v.75a4.5 4.5 0 0 1-4.5 4.5H12Z"/>
                </svg>
            </span>
            <span class="on-dark text-lg font-semibold">{{ config('app.name') }}</span>
        </div>

        <div class="card p-8 text-center">
            <p class="text-4xl font-semibold tracking-tight text-gray-300">@yield('code')</p>
            <h1 class="mt-3 text-lg font-semibold text-gray-900">@yield('title')</h1>
            <p class="mt-2 text-sm text-gray-500">@yield('message')</p>

            <div class="mt-6 flex flex-col gap-2">
                @hasSection('action')
                    @yield('action')
                @else
                    <a href="{{ url('/') }}" class="btn btn-primary w-full">{{ __('Terug naar het begin') }}</a>
                @endif
            </div>
        </div>

        <p class="mt-6 text-center text-xs" style="color: rgb(255 255 255 / .4)">
            &copy; {{ date('Y') }} deboeck.dev. {{ __('Alle rechten voorbehouden.') }}
        </p>
    </div>
</body>
</html>
