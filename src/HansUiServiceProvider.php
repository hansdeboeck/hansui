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
 *
 * DAT LAATSTE HEEFT EEN KEERZIJDE, en die geldt binnen dit package zelf: een
 * component van HansUI die <x-icon> schrijft, krijgt de icoon van de APPLICATIE
 * zodra die de naam overschrijft -- en die kent onze namen niet. Intern
 * verwijzen componenten daarom altijd met de prefix: <x-hansui::icon>.
 */
class HansUiServiceProvider extends ServiceProvider
{
    /**
     * De componenten die dit package levert.
     *
     * Hier stond dat ze "expliciet opgesomd" zijn en niet "alles in de map".
     * Dat was niet waar: `anonymousComponentPath()` hieronder neemt de hele map,
     * en deze lijst werd nergens gebruikt.
     *
     * Nu is ze wel waar, maar andersom dan het klonk. De lijst is geen filter
     * maar een INVENTARIS: hier staat welke namen HansUI in de gedeelde ruimte
     * claimt, en ComponentsTest valt om zodra de map en deze lijst uit elkaar
     * lopen -- in beide richtingen. Een nieuw bestand in het package levert dus
     * geen stilzwijgende botsing meer op, maar een rode test.
     *
     * Per component registreren zou de lijst afdwingen zonder test, maar dan
     * moet elk van deze drieëndertig een klasse krijgen in plaats van een
     * Blade-bestand. Dat is de omhaal niet waard voor dezelfde bewaking.
     *
     * @var array<int, string>
     */
    public const COMPONENTS = [
        'page',
        'card',
        'button',
        'badge',
        'code-block',
        'key-value',
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
        'section',
        'stat',
        'modal',
        'avatar',
        'tabs',
        'choice',
        'alert',
        'delta',
        'chart.line',
        'chart.columns',
        'chart.bars',
        'chart.heatmap',
    ];

    public function boot(): void
    {
        $this->loadViewsFrom($this->path('resources/views'), 'hansui');

        /*
        | De eigen strings van dit package, GELADEN en niet gepubliceerd -- het
        | omgekeerde van de frameworkvertalingen hieronder, en met reden.
        |
        | FileLoader::loadJsonPaths() zet de paden van de packages VOOR die van
        | de applicatie en merget met array_merge, waar het laatste wint. Een
        | applicatie overschrijft een sleutel dus gewoon door hem in haar eigen
        | lang/{locale}.json te zetten. Bij loadPaths(), voor de PHP-bestanden,
        | ligt dat andersom -- vandaar dat die gepubliceerd worden.
        |
        | Alleen en. De sleutels ZIJN het Nederlands, dus een nl.json zou elke
        | regel op zichzelf laten wijzen.
        */
        $this->loadJsonTranslationsFrom($this->path('lang'));

        /*
        | Zonder prefix, en daarnaast ook mét.
        |
        | De eerste registratie geeft <x-page>. De tweede geeft <x-hansui::page>
        | als ontsnapping voor een applicatie die de korte naam zelf al gebruikt
        | en toch bij de onze wil kunnen.
        */
        Blade::anonymousComponentPath($this->path('resources/views/components'));
        Blade::anonymousComponentPath($this->path('resources/views/components'), 'hansui');

        /*
        | De doelpaden via $this->app en niet via lang_path()/resource_path().
        |
        | Die twee helpers wonen in illuminate/foundation -- het framework zelf --
        | en dit package vraagt alleen illuminate/support en illuminate/view. In
        | een applicatie bestaan ze altijd, dus het viel niet op, maar dan staat
        | er in composer.json een afhankelijkheid minder dan de code gebruikt.
        | De methodes hieronder staan in ContractsFoundationApplication, en
        | die komt met illuminate/support mee.
        */
        $this->publishes([
            $this->path('lang/nl') => $this->app->langPath('nl'),
        ], 'hansui-lang');

        $this->publishes([
            $this->path('resources/views/errors') => $this->app->resourcePath('views/errors'),
        ], 'hansui-errors');

        $this->publishes([
            $this->path('resources/views/components') => $this->app->resourcePath('views/components'),
            $this->path('resources/views/partials') => $this->app->resourcePath('views/partials'),
        ], 'hansui-views');

        $this->publishes([
            $this->path('resources/views/vendor') => $this->app->resourcePath('views/vendor'),
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
