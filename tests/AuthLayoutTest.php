<?php

declare(strict_types=1);

namespace HansDeBoeck\HansUi\Tests;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\MessageBag;
use Illuminate\Support\ViewErrorBag;
use PHPUnit\Framework\Attributes\Test;

/**
 * De aanmeldpagina.
 *
 * Ze wordt ge-@extend en niet aangeroepen zoals een component, dus elke test
 * hieronder tekent een klein aanmeldscherm en kijkt wat de layout ervan maakt.
 * De FOTO is wat hier bewaakt wordt: dat ze rechts staat wanneer ze er is, en
 * dat de pagina heel blijft wanneer ze er niet is -- de tweede vorm is de
 * makkelijkste om stil kapot te maken, want geen enkel scherm van dit package
 * gebruikt hem.
 */
final class AuthLayoutTest extends TestCase
{
    /**
     * Een aanmeldscherm zoals een applicatie het schrijft.
     *
     * De twee vormen die zo'n scherm gebruikt, en ze zijn niet inwisselbaar:
     * een sectie MET waarde wordt door Blade ontsnapt -- prima voor een pad of
     * een titel, fataal voor opmaak -- en een sectie als BLOK niet.
     *
     * Het blok staat op eigen regels, en ook dat moet: Blade leest
     * `@endsection` achter een woordteken niet als directive, en dan blijft de
     * sectie open staan.
     *
     * @param  array<string, string>  $waarden
     * @param  array<string, string>  $blokken
     */
    private function scherm(array $waarden = [], array $blokken = []): string
    {
        $sjabloon = "@extends('hansui::layouts.auth')\n";

        foreach ($waarden as $naam => $waarde) {
            $sjabloon .= "@section('{$naam}', '{$waarde}')\n";
        }

        foreach ($blokken as $naam => $inhoud) {
            $sjabloon .= "@section('{$naam}')\n{$inhoud}\n@endsection\n";
        }

        return $sjabloon;
    }

    #[Test]
    public function de_foto_staat_rechts_van_het_formulier(): void
    {
        $html = Blade::render($this->scherm(
            ['image' => '/beeld/aanmelden.webp'],
            ['form' => '<form id="aanmelden"><input id="email"></form>'],
        ));

        $this->assertStringContainsString('src="/beeld/aanmelden.webp"', $html);

        // RECHTS, en niet alleen "ergens op de pagina". De twee kolommen staan
        // naast elkaar in een flexrij, dus de volgorde in de opmaak IS de
        // volgorde op het scherm -- en een foto die voor het formulier staat,
        // staat links.
        $this->assertLessThan(
            strpos($html, '<img'),
            strpos($html, 'id="aanmelden"'),
            'De foto staat voor het formulier en dus links.',
        );

        // En niet op een telefoon: daar is een halve kolom foto een halve
        // kolom minder formulier.
        $this->assertStringContainsString('hidden lg:block', $html);
    }

    #[Test]
    public function zonder_foto_blijft_de_pagina_heel(): void
    {
        $html = Blade::render($this->scherm(blokken: ['form' => '<form id="aanmelden"></form>']));

        $this->assertStringContainsString('id="aanmelden"', $html);
        $this->assertStringNotContainsString('<img', $html);

        // Geen lege kolom die op de helft van het scherm blijft staan.
        $this->assertStringNotContainsString('lg:w-1/2', $html);
    }

    #[Test]
    public function een_foto_die_iets_zegt_krijgt_haar_beschrijving_mee(): void
    {
        // Standaard is de foto versiering en blijft alt leeg; dat is geen
        // vergeten attribuut maar de bewering dat er niets te missen valt.
        $kaal = Blade::render($this->scherm(['image' => '/beeld/aanmelden.webp']));
        $this->assertStringContainsString('alt=""', $kaal);

        $html = Blade::render($this->scherm([
            'image' => '/beeld/aanmelden.webp',
            'image-alt' => 'Een hand op een aanraakscherm met een kaart erop.',
        ]));

        $this->assertStringContainsString('alt="Een hand op een aanraakscherm met een kaart erop."', $html);
    }

    #[Test]
    public function de_pagina_tekent_zonder_gebouwd_stijlblad(): void
    {
        /*
        | Zelfde reden als op de foutpagina: zonder de controle op het manifest
        | gooit @vite een exception, en dan is er van de aanmelding niets meer
        | over. In deze testapplicatie is er geen build, dus dit is het geval
        | waar het om gaat.
        */
        $this->assertFileDoesNotExist(public_path('build/manifest.json'));

        $html = Blade::render($this->scherm(blokken: ['form' => '<form id="aanmelden"></form>']));

        $this->assertStringContainsString('surface-dark', $html);
        $this->assertStringNotContainsString('<link rel="stylesheet"', $html);
    }

    #[Test]
    public function een_applicatie_met_andere_vite_ingangen_kan_ze_zelf_zetten(): void
    {
        /*
        | De standaard is `resources/css/app.css` en `resources/js/app.js`,
        | want dat zijn de namen waarmee Laravel begint. Wie andere ingangen
        | heeft, krijgt anders een @vite die op een onbekende naam valt -- en
        | dan is er van het aanmeldscherm niets meer over. Vandaar de uitweg.
        */
        $html = Blade::render($this->scherm(blokken: ['assets' => '<link rel="stylesheet" href="/eigen.css">']));

        $this->assertStringContainsString('href="/eigen.css"', $html);
    }

    #[Test]
    public function de_melding_over_de_inloggegevens_komt_in_de_kaart(): void
    {
        /*
        | "De inloggegevens kloppen niet" is de melding die dit scherm het
        | vaakst te tonen heeft, en die staat in de flash-partial. Hij hoort in
        | de layout en niet in elk aanmeldscherm apart -- dit is de test die dat
        | waar houdt.
        */
        $zak = new ViewErrorBag;
        $zak->put('default', new MessageBag(['email' => [trans('auth.failed')]]));

        $html = Blade::render($this->scherm(blokken: ['form' => '<form></form>']), ['errors' => $zak]);

        $this->assertStringContainsString('alert-danger', $html);
        $this->assertStringContainsString(trans('auth.failed'), $html);
    }

    #[Test]
    public function zonder_foutenzak_valt_de_pagina_niet_om(): void
    {
        /*
        | $errors komt uit de web-middleware. Buiten een verzoek bestaat hij
        | niet, en de flash-partial spreekt hem klakkeloos aan -- vandaar de
        | isset() in de layout. Zonder die controle staat hier "Undefined
        | variable $errors" in plaats van een aanmeldscherm.
        */
        $html = Blade::render($this->scherm(blokken: ['form' => '<form id="aanmelden"></form>']));

        $this->assertStringContainsString('id="aanmelden"', $html);
        $this->assertStringNotContainsString('alert', $html);
    }

    #[Test]
    public function de_kop_staat_er_ook_zonder_dat_de_applicatie_hem_zet(): void
    {
        // Een pagina zonder <h1> is een pagina zonder begin voor wie hem
        // beluistert. De standaard is de Nederlandse sleutel, dus een
        // applicatie op `en` hoort hier Engels te krijgen.
        app()->setLocale('nl');
        $this->assertStringContainsString('<h1 class="text-lg font-semibold text-gray-900">Aanmelden</h1>', Blade::render($this->scherm()));

        app()->setLocale('en');
        $this->assertStringContainsString('>Sign in</h1>', Blade::render($this->scherm()));
    }
}
