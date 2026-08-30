@php($afzender = $afzender ?? config('app.name'))

{{--
    Gedeelde opmaak voor uitgaande e-mail.

    Verwacht: $titel en de inhoud in $slot. Optioneel $knopUrl + $knopLabel voor
    een knop, $voet voor de kleine regel eronder, en $afzender voor de naam
    bovenaan (default: de app-naam).

    Bewust ZONDER @props: dit wordt met `@component('hansui::mail.layout', [...])`
    gebruikt vanuit een Mailable-view, en @props verwacht het $attributes-object
    dat alleen een <x-component> meebrengt.

    ALLES INLINE, en dat is geen slordigheid. Gmail stript een <style>-blok uit de
    <head>, dus een klassenlaag komt er niet doorheen. Om diezelfde reden staan
    hier geen tokens uit hansui.css: die bestaan in een mailclient niet, en een
    var() die nergens op uitkomt levert zwart op zwart.

    Geen tabellen voor de opbouw. Dat was ooit nodig; de clients die het vroegen
    zijn hier het publiek niet, en een tabelloze opbouw leest een schermlezer wél
    voor als lopende tekst.
--}}
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $titel }}</title>
</head>
<body style="margin:0;padding:0;background-color:#f3f4f6;">
    <div style="max-width:560px;margin:0 auto;padding:24px 16px;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;font-size:15px;line-height:1.6;color:#111827;">
        <div style="padding:0 0 16px;font-size:14px;font-weight:600;color:#6b7280;">{{ $afzender }}</div>

        <div style="background-color:#ffffff;border:1px solid #e5e7eb;border-radius:12px;padding:28px;">
            <h1 style="margin:0 0 16px;font-size:18px;font-weight:600;color:#111827;">{{ $titel }}</h1>

            {{ $slot }}

            @isset($knopUrl)
                <div style="margin:26px 0 6px;">
                    <a href="{{ $knopUrl }}" style="display:inline-block;background-color:#111827;color:#ffffff;text-decoration:none;padding:11px 20px;border-radius:8px;font-size:14px;font-weight:500;">{{ $knopLabel }}</a>
                </div>
                <p style="margin:16px 0 0;font-size:12px;color:#6b7280;word-break:break-all;">
                    {{ __('Werkt de knop niet? Plak dan deze link in je browser:') }}<br>{{ $knopUrl }}
                </p>
            @endisset
        </div>

        <div style="padding:16px 4px 0;font-size:12px;color:#6b7280;">
            {{ $voet ?? __('Deze e-mail is automatisch verstuurd door :afzender.', ['afzender' => $afzender]) }}
        </div>
    </div>
</body>
</html>
