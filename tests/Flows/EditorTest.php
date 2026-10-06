<?php

declare(strict_types=1);

namespace HansDeBoeck\HansUi\Tests\Flows;

use DOMDocument;
use DOMXPath;
use HansDeBoeck\HansUi\Flows\Builder;
use HansDeBoeck\HansUi\Flows\Graph;
use HansDeBoeck\HansUi\Flows\Messages;
use HansDeBoeck\HansUi\Flows\Node;
use HansDeBoeck\HansUi\Flows\NodeType;
use HansDeBoeck\HansUi\Tests\TestCase;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ViewErrorBag;
use Illuminate\View\DynamicComponent;
use PHPUnit\Framework\Attributes\Test;
use ReflectionProperty;

/**
 * <x-flow-editor> zet de flow in een verborgen veld van het formulier dat de
 * applicatie noemt, geeft de soorten, de zinnen en de issues van de server
 * mee, leent de iconen van de stappen bij de applicatie, en toont na een
 * validatiefout wat de gebruiker had.
 */
final class EditorTest extends TestCase
{
    use MaaktFlows;

    protected function setUp(): void
    {
        parent::setUp();

        // De sleutels zijn het Nederlands; lang/en.json zou ze anders vertalen.
        app()->setLocale('nl');
        view()->share('errors', new ViewErrorBag);
    }

    private static function graaf(): Graph
    {
        $flow = new Builder(self::soorten());
        $start = $flow->add('trigger', ['event' => 'deal.won']);
        $flow->then($start, 'task', ['title' => 'Bellen <snel>']);

        return $flow->graph();
    }

    private function teken(string $attributen = '', string $slot = '', ?Graph $graaf = null): string
    {
        return Blade::render('<x-flow-editor :graph="$graph" '.$attributen.'>'.$slot.'</x-flow-editor>', [
            'graph' => $graaf ?? self::graaf(),
            'issues' => ['n1' => ['Het team bestaat niet meer.']],
        ]);
    }

    private function xpath(string $html): DOMXPath
    {
        $document = new DOMDocument;
        @$document->loadHTML('<?xml encoding="utf-8"?>'.$html);

        return new DOMXPath($document);
    }

    private function wortel(DOMXPath $xpath): ?\DOMElement
    {
        $wortel = $xpath->query('//div[@data-flow-editor]')->item(0);

        return $wortel instanceof \DOMElement ? $wortel : null;
    }

    #[Test]
    public function tekent_de_editor_met_de_flow_in_een_verborgen_veld(): void
    {
        $html = $this->teken('name="flow" form="automatisatie" :issues="$issues" class="mt-4"', '<template data-flow-form="task"><input name="title" required></template>');

        foreach (['class="flow mt-4"', 'data-flow-editor', 'name="flow"', 'form="automatisatie"', 'data-flow-input', '<template data-flow-form="task"><input name="title" required></template>', 'data-flow-chrome="warning"', 'data-flow-add'] as $stuk) {
            $this->assertStringContainsString($stuk, $html);
        }

        $xpath = $this->xpath($html);
        $wortel = $this->wortel($xpath);
        $waarde = $xpath->query('//input[@data-flow-input]')->item(0)?->getAttribute('value');
        $soorten = json_decode((string) $wortel?->getAttribute('data-flow-types'), true);
        $zinnen = json_decode((string) $wortel?->getAttribute('data-flow-strings'), true);
        $issues = json_decode((string) $wortel?->getAttribute('data-flow-issues'), true);

        $this->assertSame('Bellen <snel>', Graph::fromJson((string) $waarde, self::soorten())->node('n1')?->get('title'));
        $this->assertSame(['trigger', 'condition', 'wait', 'task', 'stop'], array_column($soorten, 'key'));
        $this->assertSame([['key' => 'yes', 'label' => 'Ja'], ['key' => 'no', 'label' => 'Nee']], $soorten[1]['outputs']);
        $this->assertSame('Nog in te vullen: :veld', $zinnen['required']);
        $this->assertSame(['n1' => ['Het team bestaat niet meer.']], $issues);
    }

    #[Test]
    public function leent_de_iconen_van_de_stappen_bij_de_applicatie(): void
    {
        // Standaard <x-icon>: die van de applicatie als ze er een heeft, anders die van HansUI.
        $this->assertMatchesRegularExpression('/<template data-flow-icon="task"><svg[^>]*class="flow-icon"/', $this->teken());

        // Of een andere component, voor een applicatie met andere namen. DynamicComponent onthoudt
        // zijn compiler in een statische variabele, met de Blade van de eerste testapplicatie: een
        // pad dat deze test registreert, ziet hij pas als die vergeten is.
        (new ReflectionProperty(DynamicComponent::class, 'compiler'))->setValue(null, null);
        Blade::anonymousComponentPath(__DIR__.'/views', 'proef');

        $this->assertStringContainsString('data-icon="check"', $this->teken('icon="proef::icoon"'));
    }

    #[Test]
    public function geeft_notities_en_cijfers_per_stap_mee(): void
    {
        $html = Blade::render('<x-flow-editor :graph="$graph" :notes="$notes" :badges="$badges"/>', [
            'graph' => self::graaf(),
            'notes' => ['n1' => ['Geheim: abc']],
            'badges' => ['n1' => '12 wachten hier'],
        ]);

        $this->assertStringContainsString('data-flow-notes="{&quot;n1&quot;:[&quot;Geheim: abc&quot;]}"', $html);
        $this->assertStringContainsString('data-flow-badges="{&quot;n1&quot;:&quot;12 wachten hier&quot;}"', $html);
    }

    #[Test]
    public function geeft_een_leeg_object_zonder_issues_en_geen_form_zonder_formulier(): void
    {
        $html = $this->teken();
        $veld = $this->xpath($html)->query('//input[@data-flow-input]')->item(0);

        $this->assertStringContainsString('data-flow-issues="{}"', $html);
        $this->assertInstanceOf(\DOMElement::class, $veld);
        $this->assertFalse($veld->hasAttribute('form'));
    }

    #[Test]
    public function toont_de_flow_alleen_om_te_bekijken(): void
    {
        // Geen veld dat verstuurd wordt, geen knoppen om te wijzigen; kijken en zoomen wel.
        $html = $this->teken('name="flow" form="automatisatie" :readonly="true"');
        $xpath = $this->xpath($html);
        $veld = $xpath->query('//input[@data-flow-input]')->item(0);

        $this->assertTrue($this->wortel($xpath)?->hasAttribute('data-flow-readonly'));
        $this->assertInstanceOf(\DOMElement::class, $veld);
        $this->assertFalse($veld->hasAttribute('name'));
        $this->assertFalse($veld->hasAttribute('form'));
        $this->assertTrue(Graph::fromJson($veld->getAttribute('value'), self::soorten())->has('n1'));

        foreach (['data-flow-add', 'data-flow-undo', 'data-flow-arrange'] as $knop) {
            $this->assertStringNotContainsString($knop, $html);
        }

        $this->assertStringContainsString('data-flow-issues-button', $html);
        $this->assertStringContainsString('data-flow-zoom-in', $html);
        $this->assertStringNotContainsString('data-flow-readonly', $this->teken());
    }

    #[Test]
    public function loopt_tot_onderaan_het_venster_als_de_pagina_dat_vraagt(): void
    {
        $this->assertStringContainsString('data-flow-fill', $this->teken(':fill="true"'));
        $this->assertStringNotContainsString('data-flow-fill', $this->teken());
    }

    #[Test]
    public function zet_een_balk_bovenaan_met_volledig_scherm_en_meldingen_alleen_als_er_zijn(): void
    {
        $met = $this->teken(':flush="true"', '<x-slot:header><a href="/terug">Terug</a></x-slot:header><x-slot:notices><p>Opgeslagen.</p></x-slot:notices>');
        $xpath = $this->xpath($met);
        $wortel = $this->wortel($xpath);

        $this->assertTrue($wortel?->hasAttribute('data-flow-header'));
        $this->assertTrue($wortel?->hasAttribute('data-flow-flush'));
        $this->assertSame(1, $xpath->query('//div[contains(@class, "flow-header-main")]/a[@href="/terug"]')->length);
        $this->assertSame(1, $xpath->query('//div[contains(@class, "flow-header")]//button[@data-flow-fullscreen][@aria-pressed="false"]')->length);
        $this->assertSame('Opgeslagen.', $xpath->query('//div[contains(@class, "flow-header-notices")]/p')->item(0)?->textContent);
        $this->assertSame(1, $xpath->query('//div[@data-flow-stage]//div[@data-flow-canvas]')->length);

        $leeg = $this->teken('', '<x-slot:header><span>Naam</span></x-slot:header><x-slot:notices> </x-slot:notices>');

        $this->assertStringNotContainsString('flow-header-notices', $leeg);
        $this->assertStringNotContainsString('data-flow-view', $leeg, 'Zonder overzicht is er niets te kiezen.');

        foreach (['data-flow-header', 'class="flow-header"', 'data-flow-flush'] as $stuk) {
            $this->assertStringNotContainsString($stuk, $this->teken());
        }
    }

    #[Test]
    public function zet_wat_onder_de_editor_stond_in_een_overzicht_ernaast(): void
    {
        $html = $this->teken('', '<x-slot:overview :label="\'Uitvoeringen\'" hash="uitvoeringen" class="space-y-4"><p>Drie keer gelopen.</p></x-slot:overview>');
        $xpath = $this->xpath($html);
        $overzicht = $xpath->query('//div[@data-flow-overview]')->item(0);
        $knoppen = $xpath->query('//div[contains(@class, "flow-header")]//button[@data-flow-view]');

        // Een balk, ook zonder slot header: de keuze moet ergens staan.
        $this->assertTrue($this->wortel($xpath)?->hasAttribute('data-flow-header'));
        $this->assertSame(2, $knoppen->length);
        $this->assertSame(['flow', 'true', 'Flow'], [$knoppen->item(0)?->getAttribute('data-flow-view'), $knoppen->item(0)?->getAttribute('aria-pressed'), trim((string) $knoppen->item(0)?->textContent)]);
        $this->assertSame(['overview', 'false', 'Uitvoeringen'], [$knoppen->item(1)?->getAttribute('data-flow-view'), $knoppen->item(1)?->getAttribute('aria-pressed'), trim((string) $knoppen->item(1)?->textContent)]);

        // Het overzicht staat naast het werkvlak, verborgen tot iemand het kiest.
        $this->assertInstanceOf(\DOMElement::class, $overzicht);
        $this->assertTrue($overzicht->hasAttribute('hidden'));
        $this->assertSame('uitvoeringen', $overzicht->getAttribute('data-flow-overview'));
        $this->assertSame('flow-overview space-y-4', $overzicht->getAttribute('class'));
        $this->assertFalse($overzicht->hasAttribute('label'));
        $this->assertSame('Drie keer gelopen.', trim($overzicht->textContent));
        $this->assertSame($overzicht->getAttribute('id'), $knoppen->item(1)?->getAttribute('aria-controls'));
        $this->assertSame(0, $xpath->query('//div[@data-flow-stage]//div[@data-flow-overview]')->length);

        // Zonder naam en zonder hash: Overzicht, op #overzicht.
        $kaal = $this->xpath($this->teken('', '<x-slot:overview><p>Cijfers</p></x-slot:overview>'));

        $this->assertSame('Overzicht', trim((string) $kaal->query('//button[@data-flow-view="overview"]')->item(0)?->textContent));
        $this->assertSame('overzicht', $kaal->query('//div[@data-flow-overview]')->item(0)?->getAttribute('data-flow-overview'));
    }

    #[Test]
    public function heeft_notities_een_balk_voor_de_weg_en_proefdraaien_als_de_applicatie_een_url_geeft(): void
    {
        $html = $this->teken('test="/automatisaties/7/proef"', '<x-slot:example><select name="deal_id"><option value="12">Zonnepanelen</option></select></x-slot:example>');
        $xpath = $this->xpath($html);
        $formulier = $xpath->query('//form[@data-flow-test-panel]')->item(0);

        $this->assertSame(1, $xpath->query('//div[@data-flow-world]/div[@data-flow-stickies]')->length);
        $this->assertSame(1, $xpath->query('//button[@data-flow-sticky-add]')->length);
        $this->assertSame(1, $xpath->query('//div[@data-flow-trace-bar][@hidden]//button[@data-flow-trace-close]')->length);

        // Proefdraaien: een knop, en een formulier met het token en de velden van de applicatie.
        $this->assertInstanceOf(\DOMElement::class, $formulier);
        $this->assertSame('/automatisaties/7/proef', $formulier->getAttribute('action'));
        $this->assertTrue($formulier->hasAttribute('hidden'));
        $this->assertSame(1, $xpath->query('//form[@data-flow-test-panel]//input[@name="_token"]')->length);
        $this->assertSame(1, $xpath->query('//form[@data-flow-test-panel]//select[@name="deal_id"]')->length);
        $this->assertSame(1, $xpath->query('//button[@data-flow-test-open][@aria-controls="'.$formulier->getAttribute('id').'"]')->length);

        // Zonder url geen knop; wie alleen kijkt, zet geen notities.
        $this->assertStringNotContainsString('data-flow-test-open', $this->teken());
        $this->assertStringNotContainsString('data-flow-sticky-add', $this->teken(':readonly="true"'));
        $this->assertStringContainsString('data-flow-stickies', $this->teken(':readonly="true"'));
    }

    #[Test]
    public function geeft_takken_en_notities_mee_aan_de_editor(): void
    {
        $soorten = self::soorten()->add(new NodeType('split', 'Splitsen', icon: 'route', outputs: ['other' => 'Anders'], branches: 'values'));
        $graaf = Graph::fromArray(self::flow([['start', 'trigger', ['event' => 'x']], ['s', 'split', ['values' => ['high']]]], [['start', 'out', 's']]) + ['stickies' => [['id' => 's1', 'text' => 'Eerst bellen', 'x' => 0, 'y' => 200]]], $soorten);
        $xpath = $this->xpath($this->teken('', '', $graaf));
        $wortel = $this->wortel($xpath);
        $soort = collect(json_decode((string) $wortel?->getAttribute('data-flow-types'), true))->firstWhere('key', 'split');
        $waarde = (string) $xpath->query('//input[@data-flow-input]')->item(0)?->getAttribute('value');

        $this->assertSame('values', $soort['branches']);
        $this->assertSame('Eerst bellen', json_decode($waarde, true)['stickies'][0]['text']);
    }

    #[Test]
    public function toont_na_een_validatiefout_wat_de_gebruiker_had_als_het_een_geldige_flow_is(): void
    {
        $mijn = self::graaf()->withNode(new Node('n1', 'task', ['title' => 'Mijn versie']));

        $sessie = app('session.store');
        request()->setLaravelSession($sessie);
        $sessie->flashInput(['flow' => $mijn->toJson()]);

        $this->assertStringContainsString('Mijn versie', $this->teken());

        $sessie->flashInput(['flow' => '{"nodes": "kapot"}']);

        $this->assertStringContainsString('Bellen &lt;snel&gt;', $this->teken());
    }

    #[Test]
    public function vertaalt_de_zinnen_van_de_editor_en_van_de_graaf(): void
    {
        // Engels komt mee met HansUI (lang/en.json); een applicatie vult de rest aan.
        app()->setLocale('en');

        $html = $this->teken();
        $issues = Graph::starting(self::soorten(), 'trigger', ['event' => 'deal.won'])->issuesByNode();

        $this->assertStringContainsString('Add step', $html);
        $this->assertSame(['start' => ['No next step yet.']], $issues);
        $this->assertSame('Still to fill in: Title', Messages::get('Nog in te vullen: :veld', ['veld' => 'Title']));

        app('translator')->addLines(['*.Stap toevoegen' => 'Ajouter une étape'], 'fr');
        app()->setLocale('fr');

        $this->assertStringContainsString('Ajouter une étape', $this->teken());
    }
}
