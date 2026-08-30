<?php

declare(strict_types=1);

namespace HansDeBoeck\HansUi\Tests;

use HansDeBoeck\HansUi\HansUiServiceProvider;
use HansDeBoeck\HansUi\Tests\Fakes\VoorbeeldResultaat;
use Illuminate\Support\Facades\Blade;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

/**
 * Dat elke component die dit package claimt, ook tekent.
 *
 * Deze test bestaat om een fout die er echt in zat: <x-nav-dropdown> kwam
 * byte-identiek uit shippingtail over, maar de listener waar hij op leunde
 * bleef achter. Het paneel stond op `hidden` en ging nooit open, en niets in
 * het package merkte dat op.
 */
final class ComponentsTest extends TestCase
{
    /**
     * Voor elke component: hoe hij er minimaal uitziet.
     *
     * Met de VERPLICHTE props erin, want een component die zonder valt, valt
     * ook in een scherm. De sleutels van deze lijst worden hieronder tegen
     * HansUiServiceProvider::COMPONENTS gelegd, dus een nieuwe component zonder
     * regel hier maakt de test rood.
     *
     * @return array<string, array{string}>
     */
    public static function componenten(): array
    {
        return [
            'page' => ['<x-page title="Medewerkers" subtitle="Alle actieve" back="/terug"/>'],
            'card' => ['<x-card class="p-4">Inhoud</x-card>'],
            'button' => ['<x-button variant="danger" :small="true">Verwijderen</x-button><x-button as="a" href="/x">Terug</x-button>'],
            'badge' => ['<x-badge variant="danger" :solid="true">Fataal</x-badge><x-badge :plain="true">sentry</x-badge>'],
            'code-block' => ['<x-code-block code="npm install voorbeeld"/>'],
            'key-value' => ['<x-key-value title="Kopregels" :data="[1, [2, 3], null, true]"/>'],
            'table' => ['<x-table><x-slot:head><th class="th">Naam</th></x-slot:head><tr><td class="td">Nina</td></tr></x-table>'],
            'th-sort' => ['<table><tr><x-th-sort by="naam">Naam</x-th-sort></tr></table>'],
            'filter-bar' => ['<x-filter-bar :labels="[\'q\' => \'Zoekterm\']"><input name="q"></x-filter-bar>'],
            'empty' => ['<x-empty title="Niets gevonden" :filtered="true"/>'],
            'result' => ['<x-result :result="$resultaat"/>'],
            'icon' => ['<x-icon name="leaf"/>'],
            'field' => ['<x-field name="email" label="E-mail" help="Werkadres." :required="true"><input id="email"></x-field>'],
            'money' => ['<x-money :cents="123456"/><x-money/>'],
            'detail-list' => ['<x-detail-list :stacked="true"><x-detail-row label="Naam">Nina</x-detail-row></x-detail-list>'],
            'detail-row' => ['<dl><x-detail-row label="Naam">Nina</x-detail-row></dl>'],
            'nav-link' => ['<x-nav-link href="/x" :active="true">Overzicht</x-nav-link>'],
            'footer' => ['<x-footer/><x-footer :dark="true"/><x-footer>Eigen regel</x-footer>'],
            'nav-dropdown' => ['<x-nav-dropdown label="Beheer" :wide="true" align="right">links</x-nav-dropdown>'],
            'nav-mega-group' => ['<x-nav-mega-group label="Voorraad">links</x-nav-mega-group>'],
            'nav-mega-link' => ['<x-nav-mega-link href="/x" label="Zendingen" desc="Alles onderweg" :active="true"/>'],
            'section' => ['<x-section title="Verdeling" subtitle="Per afdeling" :padding="false"><x-slot:actions>knop</x-slot:actions> Inhoud <x-slot:footer>voet</x-slot:footer></x-section>'],
            'stat' => ['<x-stat label="Leden" value="128" icon="people" tone="brand" hint="deze week" href="/leden"/><x-stat label="Saldo"><x-money :cents="1250"/></x-stat>'],
            'modal' => ['<x-modal id="rang" title="Nieuwe rang" size="lg" :open="true"><div class="modal-body">veld</div></x-modal>'],
            'avatar' => ['<x-avatar name="Nina Bodart" size="lg"/><x-avatar name="Nina" src="/n.jpg"/>'],
            'tabs' => ['<x-tabs :items="[[\'label\' => \'Jobs\', \'href\' => \'/jobs\', \'active\' => true, \'count\' => 4]]">terug</x-tabs>'],
            'choice' => ['<x-choice name="job" value="politie" label="Politie" icon="shield" hint="Rang 3" :checked="true"/>'],
            'alert' => ['<x-alert variant="warning" title="Let op" :dismissible="true">Dit kan niet terug.</x-alert>'],
        ];
    }

    #[Test]
    #[DataProvider('componenten')]
    public function elke_component_tekent_zonder_te_vallen(string $sjabloon): void
    {
        $html = Blade::render($sjabloon, ['resultaat' => new VoorbeeldResultaat]);

        $this->assertNotSame('', trim($html));
    }

    #[Test]
    #[DataProvider('componenten')]
    public function elke_component_is_ook_met_de_prefix_bereikbaar(string $sjabloon): void
    {
        // <x-hansui::page> naast <x-page>, voor een applicatie die de korte
        // naam zelf al gebruikt. Intern gebruikt het package altijd de prefix.
        $html = Blade::render(
            preg_replace('/<(\/?)x-(?!hansui::|slot)/', '<$1x-hansui::', $sjabloon),
            ['resultaat' => new VoorbeeldResultaat],
        );

        $this->assertNotSame('', trim($html));
    }

    #[Test]
    public function de_inventaris_dekt_de_map_en_omgekeerd(): void
    {
        /*
        | Dit is wat COMPONENTS waar maakt.
        |
        | De registratie is anonymousComponentPath() en die neemt de hele map,
        | dus de constante filtert niets. Ze is een inventaris van de namen die
        | HansUI in de gedeelde ruimte claimt -- en een inventaris die niemand
        | controleert, is een lijst die uit de pas loopt.
        */
        $bestanden = array_map(
            static fn (string $pad): string => basename($pad, '.blade.php'),
            glob($this->pakket('resources/views/components/*.blade.php')) ?: [],
        );

        sort($bestanden);

        $geclaimd = HansUiServiceProvider::COMPONENTS;
        sort($geclaimd);

        $gedekt = array_keys(self::componenten());
        sort($gedekt);

        $this->assertSame($bestanden, $geclaimd, 'COMPONENTS en de componentmap lopen uit elkaar.');
        $this->assertSame($geclaimd, $gedekt, 'Er is een component zonder regel in deze test.');
    }

    #[Test]
    public function een_onbekende_icoonnaam_levert_een_leeg_vierkant_op(): void
    {
        // Zoals het commentaar in <x-icon> belooft: geen stille verdwijning,
        // maar iets dat je meteen ziet staan.
        $html = Blade::render('<x-icon name="bestaat-niet"/>');

        $this->assertStringContainsString('<svg', $html);
        $this->assertStringNotContainsString('<path', $html);
    }

    #[Test]
    public function de_kopieerknop_van_een_codeblok_wijst_naar_het_blok(): void
    {
        /*
        | De knop staat NAAST het blok en kopieert het via data-copy-target.
        |
        | Die twee horen bij elkaar en worden op twee plaatsen geschreven, dus
        | ze kunnen uit elkaar lopen -- en dan doet de knop niets en zegt hij
        | daar niets over. Precies de stille vorm die dit package elders al een
        | keer gekost heeft.
        */
        $html = Blade::render('<x-code-block code="npm install voorbeeld"/>');

        $this->assertSame(1, preg_match('/<pre id="([^"]+)"/', $html, $m));
        $this->assertStringContainsString('data-copy-target="#'.$m[1].'"', $html);
    }

    #[Test]
    public function money_zet_een_bedrag_in_belgische_notatie(): void
    {
        $this->assertStringContainsString('€&nbsp;1.234,56', Blade::render('<x-money :cents="123456"/>'));
        $this->assertStringContainsString('-€&nbsp;12,50', Blade::render('<x-money :cents="-1250"/>'));
        $this->assertStringContainsString('—', Blade::render('<x-money/>'));
    }
}
