<?php

declare(strict_types=1);

namespace HansDeBoeck\HansUi\Tests;

use PHPUnit\Framework\Attributes\Test;

/**
 * Dat de gedragslaag en wat erover beweerd wordt, hetzelfde zeggen.
 *
 * Twee fouten die hier echt in zaten, en die allebei van dezelfde soort waren:
 * de opmaak verhuisde mee en het gedrag niet. <x-nav-dropdown> kwam
 * byte-identiek uit shippingtail met een paneel dat nooit openging, en de
 * themaknop schreef naar `growtail.theme` terwijl het script in de <head>
 * `hansui.theme` las -- de keuze kwam dus bij elke paginalading niet terug.
 */
final class BehaviourTest extends TestCase
{
    /**
     * De gedragslaag: hansui.js, en de flow-editor die een applicatie apart
     * opneemt (resources/js/flows.js en wat die laadt).
     *
     * @return array<int, string>
     */
    private function scripts(): array
    {
        $flows = glob($this->pakket('resources/js/flows/*.js')) ?: [];
        sort($flows);

        return [$this->pakket('resources/js/hansui.js'), $this->pakket('resources/js/flows.js'), ...$flows];
    }

    private function js(): string
    {
        return implode("\n", array_map(static fn (string $pad): string => (string) file_get_contents($pad), $this->scripts()));
    }

    private function css(): string
    {
        return (string) file_get_contents($this->pakket('resources/css/hansui.css'))
            .(string) file_get_contents($this->pakket('resources/css/flows.css'));
    }

    /**
     * Wat de gedragslaag oppakt: de scripts en de stijlen, en wat een script
     * via `dataset` leest onder de naam die in de view staat.
     *
     * Zonder die laatste stap telde `root.dataset.flowTypes` niet als iets dat
     * naar data-flow-types luistert, terwijl het precies dat doet. De editor
     * kent twee vormen: `x.dataset.flowTypes`, en `'flowAdd' in data` met
     * `data` een dataset.
     */
    private function achterkant(): string
    {
        preg_match_all("/\\bdataset\\.([a-z][a-zA-Z0-9]*)|'([a-z][a-zA-Z0-9]*)' in data\\b/", $this->js(), $m);

        $gelezen = array_map(
            static fn (string $naam): string => 'data-'.strtolower((string) preg_replace('/[A-Z]/', '-$0', $naam)),
            array_unique(array_filter([...$m[1], ...$m[2]])),
        );

        return $this->js().$this->css().' '.implode(' ', $gelezen);
    }

    private function readme(): string
    {
        return (string) file_get_contents($this->pakket('README.md'));
    }

    /**
     * De data-attributen in een bestand, zonder wat er in commentaar staat.
     *
     * @return array<int, string>
     */
    private function haken(string $inhoud): array
    {
        $zonderCommentaar = (string) preg_replace(
            ['/\{\{--.*?--\}\}/s', '/<!--.*?-->/s', '/\/\*.*?\*\//s'],
            '',
            $inhoud,
        );

        preg_match_all('/\bdata-([a-z][a-z0-9-]*)/', $zonderCommentaar, $m);

        return array_values(array_unique($m[0]));
    }

    #[Test]
    public function elke_haak_in_een_view_wordt_ergens_opgepakt(): void
    {
        /*
        | DIT IS DE TEST DIE <x-nav-dropdown> HAD MOETEN VANGEN.
        |
        | Een data-attribuut in een view is een belofte: hier gebeurt iets. Zit
        | er niets achter -- geen listener in hansui.js, geen regel in
        | hansui.css -- dan is het een dood attribuut, en dat merk je pas als
        | een gebruiker op iets duwt dat niet opengaat.
        */
        $achterkant = $this->achterkant();
        $dood = [];

        foreach ($this->bladeBestanden() as $view) {
            foreach ($this->haken((string) file_get_contents($view)) as $haak) {
                if (! str_contains($achterkant, $haak)) {
                    $dood[] = basename($view).': '.$haak;
                }
            }
        }

        $this->assertSame([], $dood, 'Deze haken staan in een view, maar niets luistert erop.');
    }

    #[Test]
    public function elke_haak_uit_de_javascript_staat_in_de_readme(): void
    {
        // De gedragslaag is de helft van dit package en stond nergens
        // beschreven: je kon hem alleen vinden door hansui.js open te doen.
        $readme = $this->readme();
        $ongedocumenteerd = [];

        preg_match_all('/\[(data-[a-z0-9-]+)[\]=]/', $this->js(), $m);

        foreach (array_unique($m[1]) as $haak) {
            if (! str_contains($readme, '`'.$haak.'`')) {
                $ongedocumenteerd[] = $haak;
            }
        }

        $this->assertSame([], $ongedocumenteerd, 'Deze haken werken wel, maar staan niet in de README.');
    }

    #[Test]
    public function elke_haak_uit_de_readme_bestaat_ook(): void
    {
        // En de andere richting, want een tabel die iets belooft dat er niet
        // is, kost meer dan een tabel die zwijgt.
        $achterkant = $this->achterkant();

        preg_match_all('/`(data-[a-z0-9-]+)`/', $this->readme(), $m);

        $verzonnen = array_values(array_filter(
            array_unique($m[1]),
            static fn (string $haak): bool => ! str_contains($achterkant, $haak),
        ));

        $this->assertSame([], $verzonnen, 'Deze haken staan in de README, maar bestaan niet.');
        $this->assertNotEmpty($m[1], 'De README beschrijft de gedragslaag niet meer.');
    }

    #[Test]
    public function de_javascript_en_de_readme_gebruiken_dezelfde_opslagsleutels(): void
    {
        /*
        | Het thema wordt AL in de <head> gezet, voor de eerste verf; hansui.js
        | schrijft alleen wat er bij een klik verandert. Die twee moeten dus
        | wel dezelfde sleutel gebruiken, en dat deden ze niet.
        */
        preg_match_all("/localStorage\.\w+\('([^']+)'/", $this->js(), $geschreven);
        preg_match_all("/localStorage\.\w+\('([^']+)'/", $this->readme(), $gelezen);

        $inJs = array_unique($geschreven[1]);
        $inReadme = array_unique($gelezen[1]);

        sort($inJs);
        sort($inReadme);

        $this->assertNotEmpty($inJs);
        $this->assertSame($inReadme, $inJs, 'Het script in de <head> leest andere sleutels dan hansui.js schrijft.');
    }

    #[Test]
    public function er_staat_nergens_nog_een_sleutel_van_een_enkele_applicatie(): void
    {
        $bestanden = [...$this->scripts(), $this->pakket('resources/css/hansui.css'), $this->pakket('resources/css/flows.css'), $this->pakket('README.md')];

        foreach ($bestanden as $bestand) {
            foreach (['growtail.', 'projecttail.', 'socialtail.'] as $sleutel) {
                $this->assertStringNotContainsString(
                    $sleutel,
                    (string) file_get_contents($bestand),
                    basename($bestand).' draagt nog een sleutel van een van de applicaties.',
                );
            }
        }
    }
}
