<?php

declare(strict_types=1);

namespace HansDeBoeck\HansUi;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

/**
 * HansUI.
 *
 * De gedeelde interface van de Laravel-applicaties op deboeck.dev. Ze bestond
 * al -- als een kopie van resources/css/app.css die van project naar project
 * meeverhuisde. Dat ging goed tot er iets ontbrak: shippingtail voegde een
 * .badge-warning toe, growtail vond diezelfde klasse onafhankelijk opnieuw uit,
 * en ota en pos hebben hem nog altijd niet. Dezelfde leemte, drie keer anders
 * gedicht.
 *
 * Wat dit package mogelijk maakte is de tokenlaag: zolang de kleuren als
 * Tailwind-utilities in de klassen zaten (bg-gray-900), kon een project alleen
 * afwijken door te forken. Met --surface, --ink en --brand kan een app afwijken
 * zonder de klassen aan te raken, en dat is het verschil tussen een kopie en
 * een afhankelijkheid.
 *
 * DE COMPONENTEN WORDEN ZONDER PREFIX GEREGISTREERD.
 *
 * Dus <x-page> en niet <x-hansui::page>. Reden: de applicaties die hierop
 * overstappen hebben de korte vorm al in tientallen schermen staan, en een
 * prefix zou van een `composer require` een zoek-en-vervang maken. De prijs is
 * dat het package namen claimt in de gedeelde ruimte -- vandaar dat de lijst
 * hieronder kort en saai is, en dat een app hem kan overschrijven door een
 * gelijknamig bestand in resources/views/components te zetten.
 */
class HansUiServiceProvider extends ServiceProvider
{
    /**
     * De componenten die dit package levert.
     *
     * Expliciet opgesomd en niet "alles in de map": zo staat er in de code welke
     * namen HansUI claimt, en levert een nieuw bestand in het package niet
     * stilzwijgend een botsing op in een applicatie die die naam zelf al
     * gebruikt.
     *
     * @var array<int, string>
     */
    public const COMPONENTS = [
        'page',
        'table',
        'th-sort',
        'filter-bar',
        'empty',
        'result',
        'icon',
        'field',
        'money',
        'detail-list',
        'detail-row',
        'nav-link',
        'footer',
        'nav-dropdown',
        'nav-mega-group',
        'nav-mega-link',
    ];

    public function boot(): void
    {
        $this->loadViewsFrom($this->path('resources/views'), 'hansui');

        /*
        | Zonder prefix, en daarnaast ook mét.
        |
        | De eerste registratie geeft <x-page>. De tweede geeft <x-hansui::page>
        | als ontsnapping voor een applicatie die de korte naam zelf al gebruikt
        | en toch bij de onze wil kunnen.
        */
        Blade::anonymousComponentPath($this->path('resources/views/components'));
        Blade::anonymousComponentPath($this->path('resources/views/components'), 'hansui');

        $this->publishes([
            $this->path('lang/nl') => lang_path('nl'),
        ], 'hansui-lang');

        $this->publishes([
            $this->path('resources/views/errors') => resource_path('views/errors'),
        ], 'hansui-errors');

        $this->publishes([
            $this->path('resources/views/components') => resource_path('views/components'),
            $this->path('resources/views/partials') => resource_path('views/partials'),
        ], 'hansui-views');

        $this->publishes([
            $this->path('resources/views/vendor') => resource_path('views/vendor'),
        ], 'hansui-pagination');
    }

    /**
     * Een pad binnen dit package.
     *
     * Via __DIR__ en niet via base_path(): een package weet waar het zelf staat
     * en mag daarvoor niet leunen op waar de applicatie staat.
     */
    private function path(string $relative): string
    {
        return __DIR__.'/../'.$relative;
    }
}
