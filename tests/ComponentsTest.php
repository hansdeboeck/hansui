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
            'badge' => ['<x-badge variant="danger" :solid="true">Fataal</x-badge><x-badge>sentry</x-badge>'],
            'code-block' => ['<x-code-block code="npm install voorbeeld"/>'],
            'key-value' => ['<x-key-value title="Kopregels" :data="[1, [2, 3], null, true]"/>'],
            'table' => ['<x-table><x-slot:head><th class="th">Naam</th></x-slot:head><tr><td class="td">Nina</td></tr></x-table>'],
            'th-sort' => ['<table><tr><x-th-sort by="naam">Naam</x-th-sort></tr></table>'],
            'filter-bar' => ['<x-filter-bar :labels="[\'q\' => \'Zoekterm\']"><input name="q"></x-filter-bar>'],
            'empty' => ['<x-empty title="Niets gevonden" :filtered="true"/>'],
            'result' => ['<x-result :result="$resultaat"/>'],
            'icon' => ['<x-icon name="leaf"/><x-icon name="leaf" size="h-3.5 w-3.5" class="text-gray-500"/>'],
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
            'alert' => ['<x-alert variant="warning" title="Let op" :dismissible="true">Dit kan niet terug.</x-alert><x-alert>Los.</x-alert>'],
            'delta' => ['<x-delta :change="12.5"/><x-delta :change="-3" :invert="true"/><x-delta/>'],
            'chart.line' => ['<x-chart.line :series="[[\'label\' => \'Volgers\', \'color\' => \'var(--series-1)\', \'points\' => [\'2026-01-01\' => 10, \'2026-01-02\' => 14]]]" :from-zero="false"/><x-chart.line/>'],
            'chart.columns' => ['<x-chart.columns :data="[\'2026-01-01\' => 3, \'2026-01-02\' => 0]"/><x-chart.columns :data="[\'Beeld\' => 4]" :dates="false"/>'],
            'chart.bars' => ['<x-chart.bars :rows="[[\'label\' => \'Instagram\', \'value\' => 4.2, \'hint\' => \'12 berichten\']]" unit="%" :decimals="1"/>'],
            'chart.heatmap' => ['<x-chart.heatmap :cells="[1 => [9 => [\'value\' => 3.1, \'tip\' => \'2 berichten\'], 10 => [\'value\' => 1, \'weak\' => true]]]" less="rustig" more="druk"/>'],
            'cropper' => ['<x-cropper src="/foto.jpg" alt="Etalage" :ratios="[\'free\', \'1:1\', \'4:5\' => \'Instagram 4:5\']" ratio="4:5" name="uitsnede"/><x-cropper/>'],
            'lightbox' => ['<x-lightbox id="voorbeeld" group="fotos" label="Voorbeeld"><p>Paneel</p><x-slot:actions>knop</x-slot:actions></x-lightbox><x-lightbox id="kaal"/><x-lightbox id="leeg" :aside="true"/>'],
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
        // Ook een map dieper: chart/line.blade.php is <x-chart.line>.
        $map = $this->pakket('resources/views/components/');
        $bestanden = array_map(
            static fn (string $pad): string => str_replace('/', '.', substr($pad, strlen($map), -strlen('.blade.php'))),
            array_merge(glob($map.'*.blade.php') ?: [], glob($map.'*/*.blade.php') ?: []),
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
    public function een_verschil_kleurt_naar_wat_beter_is(): void
    {
        // Minder is soms beter (een reactietijd): dan kantelt de kleur, niet
        // het teken. Het teken blijft zeggen wat er gebeurde.
        $this->assertStringContainsString('text-emerald-700', Blade::render('<x-delta :change="-4" :invert="true"/>'));
        $this->assertStringContainsString('-4,0%', Blade::render('<x-delta :change="-4" :invert="true"/>'));
        $this->assertStringContainsString('text-red-700', Blade::render('<x-delta :change="-4"/>'));
        $this->assertSame('', trim(Blade::render('<x-delta/>')));
    }

    #[Test]
    public function een_kerncijfer_toont_zijn_verschil(): void
    {
        $html = Blade::render('<x-stat label="Weergaven" value="4.210" :change="18"/>');

        $this->assertStringContainsString('+18%', $html);
    }

    #[Test]
    public function elke_grafiek_heeft_een_tabel_of_tooltips(): void
    {
        // Kleur alleen is geen identiteit: wie de reeksen niet uit elkaar
        // houdt, leest de tabel eronder of de tooltip.
        $lijn = Blade::render('<x-chart.line :series="[[\'label\' => \'A\', \'color\' => \'var(--series-1)\', \'points\' => [\'2026-01-01\' => 1]]]"/>');

        $this->assertStringContainsString('<details class="chart-table', $lijn);
        $this->assertStringContainsString('data-tip=', $lijn);
    }

    #[Test]
    public function een_lijn_met_meer_reeksen_schaalt_op_de_hoogste(): void
    {
        // Twee reeksen op dezelfde datums: de schaal moet de hoogste volgen,
        // niet de laatste. Zo stond het eerst niet.
        $html = Blade::render('<x-chart.line :series="$reeksen"/>', ['reeksen' => [
            ['label' => 'Hoog', 'color' => 'var(--series-1)', 'points' => ['2026-01-01' => 900, '2026-01-02' => 950]],
            ['label' => 'Laag', 'color' => 'var(--series-2)', 'points' => ['2026-01-01' => 10, '2026-01-02' => 12]],
        ]]);

        $this->assertStringContainsString('>1.000</text>', $html);
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

    #[Test]
    public function de_bijsnijder_levert_vier_velden_en_de_gekozen_verhouding(): void
    {
        /*
        | De velden en de knoppen horen bij de dichtstbijzijnde bijsnijder, en
        | de knop met aria-pressed is de verhouding waarmee hansui.js begint.
        | Staat die er niet, dan begint het kader vrij en klopt de eerste
        | uitsnede niet met wat de knoppen beloven.
        */
        $html = Blade::render('<x-cropper src="/foto.jpg" :ratios="[\'free\', \'1:1\', \'4:5\' => \'Instagram 4:5\']" ratio="4:5" name="uitsnede"/>');

        foreach (['x', 'y', 'width', 'height'] as $veld) {
            $this->assertStringContainsString('name="uitsnede['.$veld.']" data-crop-'.$veld, $html);
        }

        $this->assertSame(1, substr_count($html, 'aria-pressed="true"'));
        $this->assertMatchesRegularExpression('/data-crop-ratio="4:5"\s+aria-pressed="true">Instagram 4:5</', $html);
        $this->assertStringContainsString('data-crop-ratio="free"', $html);
        $this->assertMatchesRegularExpression('/data-crop="[^"]+"/', $html, 'Het kader heeft een label nodig voor een schermlezer.');
    }
}
