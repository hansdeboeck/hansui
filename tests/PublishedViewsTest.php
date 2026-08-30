<?php

declare(strict_types=1);

namespace HansDeBoeck\HansUi\Tests;

use Illuminate\Support\MessageBag;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\ViewErrorBag;
use PHPUnit\Framework\Attributes\Test;

/**
 * De views die niet in COMPONENTS staan: de flash-partial en de foutpagina.
 *
 * Ze worden gepubliceerd in plaats van aangeroepen, dus geen enkel scherm van
 * dit package raakt ze aan -- en dat is precies waarom ze een test nodig hebben.
 */
final class PublishedViewsTest extends TestCase
{
    #[Test]
    public function de_flash_partial_tekent_alle_drie_de_soorten(): void
    {
        session()->flash('success', 'Drie medewerkers bijgewerkt.');
        session()->flash('error', 'Er ging iets mis.');

        $zak = new ViewErrorBag;
        $zak->put('default', new MessageBag(['naam' => ['Vul dit veld in.']]));

        $html = view('hansui::partials.flash', ['errors' => $zak])->render();

        $this->assertStringContainsString('alert-success', $html);
        $this->assertStringContainsString('Drie medewerkers bijgewerkt.', $html);
        $this->assertStringContainsString('alert-danger', $html);
        $this->assertStringContainsString('Vul dit veld in.', $html);

        // De losse tinten waarop dit stukliep in donkere modus horen weg te
        // blijven: emerald-800 en red-800 stonden niet in het @theme-blok.
        $this->assertStringNotContainsString('emerald-', $html);
        $this->assertStringNotContainsString('red-', $html);
    }

    #[Test]
    public function de_foutpagina_tekent_zonder_gebouwd_stijlblad(): void
    {
        /*
        | Zonder de controle op het manifest gooit @vite hier een exception, en
        | dan geeft de foutpagina zelf een fout -- precies wat haar eigen
        | commentaar zegt te vermijden. In deze testapplicatie is er geen
        | build, dus dit is het geval waar het om gaat.
        */
        $this->assertFileDoesNotExist(public_path('build/manifest.json'));

        // De taal vastgezet, want de bewering hieronder gaat over de PAGINA en
        // niet over de taal waarin de testapplicatie toevallig staat.
        app()->setLocale('nl');

        $html = view('hansui::errors.layout')->render();

        $this->assertStringContainsString('surface-dark', $html);
        $this->assertStringContainsString('Terug naar het begin', $html);
        $this->assertStringNotContainsString('<link rel="stylesheet"', $html);
    }

    #[Test]
    public function de_foutpagina_zet_geen_merk_van_een_enkele_applicatie_vast(): void
    {
        config(['app.name' => 'Shippingtail']);

        $html = view('hansui::errors.layout')->render();

        $this->assertStringContainsString('Shippingtail', $html);
        $this->assertStringContainsString('>S<', str_replace([' ', "\n"], ['', ''], $html));
    }

    #[Test]
    public function de_eigen_strings_zijn_ook_in_het_engels_te_krijgen(): void
    {
        /*
        | De sleutels van dit package ZIJN het Nederlands. Een applicatie die op
        | `en` draait kreeg er dus Nederlands uit, en dat merk je pas in een
        | scherm -- niet in een ontbrekende vertaling, want er ontbreekt niets.
        |
        | lang/en.json wordt daarom GELADEN en niet gepubliceerd. Deze test is
        | wat dat waar houdt: valt de registratie weg, dan staat er hieronder
        | weer Nederlands.
        */
        app()->setLocale('nl');
        $this->assertStringContainsString('Terug naar het begin', view('hansui::errors.layout')->render());

        app()->setLocale('en');
        $this->assertStringContainsString('Back to the start', view('hansui::errors.layout')->render());
    }

    #[Test]
    public function elke_publiceertag_wijst_naar_iets_dat_bestaat(): void
    {
        $tags = ['hansui-lang', 'hansui-errors', 'hansui-views', 'hansui-pagination'];

        foreach ($tags as $tag) {
            $paden = ServiceProvider::pathsToPublish(null, $tag);

            $this->assertNotEmpty($paden, "De tag {$tag} publiceert niets.");

            foreach (array_keys($paden) as $bron) {
                $this->assertDirectoryExists($bron, "De tag {$tag} wijst naar een map die niet bestaat.");
            }
        }
    }
}
