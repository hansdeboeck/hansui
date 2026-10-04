<?php

declare(strict_types=1);

namespace HansDeBoeck\HansUi\Tests\Flows;

use HansDeBoeck\HansUi\Flows\Edge;
use HansDeBoeck\HansUi\Flows\Graph;
use HansDeBoeck\HansUi\Flows\InvalidGraph;
use HansDeBoeck\HansUi\Flows\Layout;
use HansDeBoeck\HansUi\Flows\Node;
use HansDeBoeck\HansUi\Flows\NodeType;
use HansDeBoeck\HansUi\Flows\Sticky;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

/**
 * Een geldige flow wordt gelezen zoals hij geschreven is, en terug
 * geschreven zonder verlies; wat niet klopt (een soort die niet bestaat, een
 * lus, twee starts, een uitgang die twee keer verbonden is) komt er niet in;
 * wat nog niet af is, zijn issues en geen fouten; en elke wijziging geeft een
 * nieuwe graaf die opnieuw gecontroleerd is.
 */
final class GraphTest extends ZonderLaravel
{
    private static function takken(): Graph
    {
        return Graph::fromArray(self::flow(
            [['start', 'trigger', ['event' => 'deal.won']], ['c', 'condition'], ['a', 'task', ['title' => 'Bellen']], ['b', 'wait'], ['m', 'task', ['title' => 'Mailen']]],
            [['start', 'out', 'c'], ['c', 'yes', 'a'], ['c', 'no', 'b'], ['a', 'out', 'm'], ['b', 'out', 'm']],
        ), self::soorten());
    }

    /**
     * @param  array<int, Node>  $nodes
     * @return array<int, string>
     */
    private static function ids(array $nodes): array
    {
        return array_map(fn (Node $node) => $node->id, $nodes);
    }

    #[Test]
    public function leest_een_flow_en_volgt_de_verbindingen(): void
    {
        $graph = self::takken();

        $this->assertCount(5, $graph);
        $this->assertSame('start', $graph->start()?->id);
        $this->assertSame('c', $graph->next('start')?->id);
        $this->assertSame('a', $graph->next('c', 'yes')?->id);
        $this->assertSame('b', $graph->next('c', 'no')?->id);
        $this->assertSame('a', $graph->next('c')?->id, 'Zonder uitgang de eerste van de soort.');
        $this->assertNull($graph->target('m', 'out'));
        $this->assertNull($graph->next('m'));
        $this->assertSame(['yes', 'no'], array_map(fn (Edge $edge) => $edge->port, $graph->outgoing('c')));
        $this->assertSame(['a', 'b'], array_map(fn (Edge $edge) => $edge->from, $graph->incoming('m')));
        $this->assertSame('Bellen', $graph->node('a')?->get('title'));
        $this->assertSame('Voorwaarde', $graph->typeOf('c')?->label);
        $this->assertSame(['a', 'm'], self::ids($graph->ofType('task')));
    }

    #[Test]
    public function leest_terug_wat_toarray_schreef_ook_zonder_json(): void
    {
        $this->assertEquals(self::takken()->toArray(), Graph::fromArray(self::takken()->toArray(), self::soorten())->toArray());
    }

    #[Test]
    public function schrijft_terug_wat_het_las(): void
    {
        $graph = self::takken()->withPositions(['start' => [0, 0], 'c' => [300, 0]]);
        $again = Graph::fromJson($graph->toJson(), self::soorten());

        $this->assertEquals($graph->toArray(), $again->toArray());
        $this->assertSame(1, $graph->toArray()['version']);
        $this->assertSame(300, $graph->node('c')?->x);
        $this->assertNull($graph->node('a')?->x);

        // Een lege config is een object in json, zodat de editor er een object van maakt.
        $this->assertStringContainsString('"id":"c","type":"condition","config":{}', $graph->toJson());
    }

    #[Test]
    public function volgt_eerst_de_eerste_uitgang_helemaal_af(): void
    {
        $graph = self::takken();

        $this->assertSame(['start', 'c', 'a', 'm', 'b'], $graph->reachable());
        $this->assertSame(['start', 'c', 'a', 'm', 'b'], self::ids($graph->ordered()));
    }

    /** @return array<string, array{array<mixed>, string}> */
    public static function ongeldig(): array
    {
        return [
            'een soort die niet bestaat' => [self::flow([['start', 'trigger'], ['x', 'robot']]), 'unknown type "robot"'],
            'een id twee keer' => [self::flow([['start', 'trigger'], ['a', 'task'], ['a', 'wait']]), 'used twice'],
            'een id met een spatie' => [self::flow([['start', 'trigger'], ['een stap', 'task']]), 'no valid id'],
            'geen start' => [self::flow([['a', 'task']]), 'exactly one start; this one has 0'],
            'twee starts' => [self::flow([['start', 'trigger'], ['again', 'trigger']]), 'exactly one start; this one has 2'],
            'meer dan toegelaten' => [self::flow([['start', 'trigger'], ['s1', 'stop'], ['s2', 'stop'], ['s3', 'stop']]), 'at most 2 node(s) of type "stop"'],
            'een verbinding naar niets' => [self::flow([['start', 'trigger']], [['start', 'out', 'ghost']]), 'leads to node "ghost"'],
            'een verbinding van niets' => [self::flow([['start', 'trigger']], [['ghost', 'out', 'start']]), 'starts at node "ghost"'],
            'een uitgang die er niet is' => [self::flow([['start', 'trigger'], ['a', 'task']], [['start', 'maybe', 'a']]), 'has no output "maybe"'],
            'een uitgang twee keer' => [self::flow([['start', 'trigger'], ['a', 'task'], ['b', 'task']], [['start', 'out', 'a'], ['start', 'out', 'b']]), 'connected twice'],
            'iets naar de start' => [self::flow([['start', 'trigger'], ['a', 'task']], [['start', 'out', 'a'], ['a', 'out', 'start']]), 'starts the flow'],
            'naar zichzelf' => [self::flow([['start', 'trigger'], ['a', 'task']], [['a', 'out', 'a']]), 'connected to itself'],
            'een lus' => [self::flow([['start', 'trigger'], ['a', 'task'], ['b', 'wait']], [['start', 'out', 'a'], ['a', 'out', 'b'], ['b', 'out', 'a']]), 'loops'],
            'een einde met een uitgang' => [self::flow([['start', 'trigger'], ['s', 'stop'], ['a', 'task']], [['s', 'out', 'a']]), 'has no output "out"'],
            'een config die een lijst is' => [self::flow([['start', 'trigger', ['a', 'b']]]), 'must be an object'],
            'een andere versie' => [['version' => 2, 'nodes' => []], 'Version 2'],
            'geen lijst van stappen' => [['nodes' => ['a' => []]], 'must be a list'],
        ];
    }

    /** @param  array<mixed>  $data */
    #[Test]
    #[DataProvider('ongeldig')]
    public function weigert_wat_niet_klopt(array $data, string $reden): void
    {
        $this->assertGooit(InvalidGraph::class, $reden, fn () => Graph::fromArray($data, self::soorten()));
    }

    #[Test]
    public function weigert_een_positie_die_geen_getal_is_en_houdt_ze_binnen_de_perken(): void
    {
        $data = self::flow([['start', 'trigger']]);
        $data['nodes'][0]['x'] = '12';

        $this->assertGooit(InvalidGraph::class, 'not a number', fn () => Graph::fromArray($data, self::soorten()));

        $data['nodes'][0]['x'] = 99_999_999.4;
        $data['nodes'][0]['y'] = -12.6;
        $start = Graph::fromArray($data, self::soorten())->node('start');

        $this->assertSame(1_000_000, $start?->x);
        $this->assertSame(-13, $start?->y);
    }

    #[Test]
    public function weigert_te_veel_stappen_en_te_veel_json(): void
    {
        $nodes = [['start', 'trigger']];

        for ($i = 1; $i <= Graph::MAX_NODES; $i++) {
            $nodes[] = ['n'.$i, 'task'];
        }

        $this->assertGooit(InvalidGraph::class, 'at most 200 nodes', fn () => Graph::fromArray(self::flow($nodes), self::soorten()));
        $this->assertGooit(InvalidGraph::class, 'larger than', fn () => Graph::fromJson(str_repeat('x', Graph::MAX_BYTES + 1), self::soorten()));
        $this->assertGooit(InvalidGraph::class, 'not valid JSON', fn () => Graph::fromJson('{"nodes": [', self::soorten()));
        $this->assertGooit(InvalidGraph::class, 'empty', fn () => Graph::fromJson('', self::soorten()));
        $this->assertGooit(InvalidGraph::class, 'not a JSON object', fn () => Graph::fromJson('"tekst"', self::soorten()));
    }

    #[Test]
    public function weigert_een_config_met_iets_dat_geen_tekst_getal_of_lijst_is(): void
    {
        $diep = ['a' => ['b' => ['c' => ['d' => ['e' => ['f' => 1]]]]]];

        $this->assertGooit(InvalidGraph::class, 'nested too deeply', fn () => Graph::fromArray(self::flow([['start', 'trigger', $diep]]), self::soorten()));
        $this->assertGooit(InvalidGraph::class, 'longer than 60', fn () => Graph::fromArray(self::flow([['start', 'trigger', [str_repeat('k', 61) => 1]]]), self::soorten()));
    }

    #[Test]
    public function kent_issues_een_start_zonder_vervolg_een_losse_stap_een_leeg_verplicht_veld(): void
    {
        $alleen = Graph::starting(self::soorten(), 'trigger', ['event' => 'deal.won']);

        $this->assertSame(['start' => ['Nog geen volgende stap.']], $alleen->issuesByNode());

        $graph = Graph::fromArray(self::flow(
            [['start', 'trigger'], ['a', 'task', ['title' => '  ']], ['loose', 'wait']],
            [['start', 'out', 'a']],
        ), self::soorten());

        $this->assertSame([
            'start' => ['Nog in te vullen: Gebeurtenis'],
            'a' => ['Nog in te vullen: Titel'],
            'loose' => ['Deze stap hangt aan geen enkele andere stap.'],
        ], $graph->issuesByNode());
        $this->assertFalse($graph->isComplete());

        // De eigen controles van de applicatie komen erbij.
        $extra = fn (Node $node) => $node->type === 'wait' ? ['Te lang.'] : [];

        $this->assertSame(['Deze stap hangt aan geen enkele andere stap.', 'Te lang.'], $graph->issuesByNode($extra)['loose']);
        $this->assertTrue(self::takken()->isComplete());
    }

    #[Test]
    public function sluit_een_reeks_weer_aan_als_er_een_stap_uit_gaat(): void
    {
        $graph = Graph::fromArray(self::flow(
            [['start', 'trigger'], ['a', 'task'], ['b', 'wait'], ['c', 'task']],
            [['start', 'out', 'a'], ['a', 'out', 'b'], ['b', 'out', 'c']],
        ), self::soorten());

        $zonder = $graph->withoutNode('b');

        $this->assertFalse($zonder->has('b'));
        $this->assertSame('c', $zonder->target('a', 'out'));
        $this->assertTrue($graph->has('b'), 'De oude graaf blijft wat hij was.');
        $this->assertNull($graph->withoutNode('b', bridge: false)->target('a', 'out'));
        $this->assertSame($graph, $graph->withoutNode('ghost'));

        // Een voorwaarde met twee takken: haar eerste uitgang sluit aan, de andere tak hangt los.
        $takken = self::takken()->withoutNode('c');

        $this->assertSame('a', $takken->target('start', 'out'));
        $this->assertArrayHasKey('b', $takken->issuesByNode());
    }

    #[Test]
    public function verbindt_ontkoppelt_voegt_toe_en_hernoemt(): void
    {
        $graph = self::takken();

        $this->assertSame('m', $graph->connect('c', 'yes', 'm')->target('c', 'yes'), 'Wat er hing, laat los.');
        $this->assertNull($graph->disconnect('c', 'no')->target('c', 'no'));
        $this->assertGooit(InvalidGraph::class, 'loops', fn () => $graph->connect('m', 'out', 'c'));

        $erbij = $graph->withNode(new Node('n9', 'task', ['title' => 'Nieuw']))->connect('m', 'out', 'n9');

        $this->assertSame('Nieuw', $erbij->next('m')?->get('title'));

        $hernoemd = $erbij->renamed(['n9' => '12', 'a' => '7']);

        $this->assertTrue($hernoemd->has('12'));
        $this->assertSame('12', $hernoemd->target('m', 'out'));
        $this->assertSame('7', $hernoemd->target('c', 'yes'));
        $this->assertGooit(InvalidGraph::class, 'same id', fn () => $erbij->renamed(['n9' => 'a']));
    }

    #[Test]
    public function begint_een_nieuwe_flow_met_alleen_de_start(): void
    {
        $graph = Graph::starting(self::soorten(), 'trigger', ['event' => 'quote.sent']);

        $this->assertSame([['id' => 'start', 'type' => 'trigger', 'config' => ['event' => 'quote.sent'], 'x' => null, 'y' => null]], $graph->toArray()['nodes']);
    }

    #[Test]
    public function schikt_alles_of_alleen_wat_nog_geen_plaats_had(): void
    {
        $graph = self::takken()->withPositions(['c' => [5, 5]]);

        $this->assertSame([300, 0], [$graph->arranged()->node('c')?->x, $graph->arranged()->node('c')?->y]);
        $this->assertSame([5, 5], [$graph->arranged(all: false)->node('c')?->x, $graph->arranged(all: false)->node('c')?->y]);
        $this->assertSame([600, 150], [$graph->arranged(all: false)->node('b')?->x, $graph->arranged(all: false)->node('b')?->y]);
    }

    #[Test]
    public function een_splitsing_heeft_de_uitgangen_uit_haar_config(): void
    {
        $soorten = self::soorten()->add(new NodeType('split', 'Splitsen', icon: 'route', outputs: ['other' => 'Anders'], branches: 'values'));
        $graph = Graph::fromArray(self::flow(
            [['start', 'trigger', ['event' => 'ticket.created']], ['s', 'split', ['field' => 'priority', 'values' => ['high', 'urgent']]], ['a', 'task', ['title' => 'Bellen']], ['b', 'task', ['title' => 'Mailen']], ['c', 'wait']],
            [['start', 'out', 's'], ['s', 'high', 'a'], ['s', 'urgent', 'b'], ['s', 'other', 'c']],
        ), $soorten);

        $this->assertSame(['high' => '', 'urgent' => '', 'other' => 'Anders'], $graph->outputsOf('s'));
        $this->assertSame(['high', 'urgent', 'other'], array_map(fn (Edge $edge) => $edge->port, $graph->outgoing('s')));
        $this->assertSame('a', $graph->next('s')?->id, 'Zonder uitgang de eerste tak.');
        $this->assertSame(['start', 's', 'a', 'b', 'c'], $graph->reachable());
        $this->assertSame([[0, 0], [300, 0], [600, 0], [600, 150], [600, 300]], array_values(Layout::positions($graph)));

        // Een tak die niet gekozen is, heeft geen uitgang; een tak die wegvalt, verliest zijn verbinding.
        $this->assertGooit(InvalidGraph::class, 'has no output "low"', fn () => $graph->connect('s', 'low', 'c'));

        $minder = $graph->withNode(new Node('s', 'split', ['field' => 'priority', 'values' => ['high']]));

        $this->assertNull($minder->target('s', 'urgent'));
        $this->assertSame('a', $minder->target('s', 'high'));
        $this->assertSame('c', $minder->target('s', 'other'));
        $this->assertArrayHasKey('b', $minder->issuesByNode(), 'Wat aan de tak hing, hangt nu los.');

        // Een stap weghalen sluit aan langs de eerste tak.
        $this->assertSame('a', $graph->withoutNode('s')->target('start', 'out'));
    }

    #[Test]
    public function bewaart_notities_op_het_canvas_naast_de_stappen(): void
    {
        $data = self::flow([['start', 'trigger', ['event' => 'deal.won']], ['a', 'task', ['title' => 'Bellen']]], [['start', 'out', 'a']]);
        $data['stickies'] = [['id' => 's1', 'text' => "Eerst bellen,\ndan mailen.", 'x' => 12.4, 'y' => 200]];
        $graph = Graph::fromArray($data, self::soorten());

        $this->assertSame([['id' => 's1', 'text' => "Eerst bellen,\ndan mailen.", 'x' => 12, 'y' => 200]], array_map(fn (Sticky $sticky) => $sticky->toArray(), $graph->stickies()));
        $this->assertSame($graph->toArray(), Graph::fromJson($graph->toJson(), self::soorten())->toArray());

        // Ze gaan mee met elke wijziging, en wegen niet mee in wat nog niet af is.
        $this->assertCount(1, $graph->withNode(new Node('b', 'wait'))->connect('a', 'out', 'b')->stickies());
        $this->assertCount(1, $graph->withoutNode('a')->stickies());
        $this->assertCount(1, $graph->arranged()->stickies());
        $this->assertCount(1, $graph->renamed(['a' => '7'])->stickies());
        $this->assertSame([], $graph->issuesByNode());

        // Een flow zonder notities schrijft er geen: wat al bewaard is, verandert niet.
        $zonder = $graph->withStickies([]);

        $this->assertArrayNotHasKey('stickies', $zonder->toArray());
        $this->assertSame('Nieuw', $zonder->withStickies([new Sticky('s2', 'Nieuw', 0, 0)])->stickies()[0]->text);
    }

    /** @return array<string, array{array<mixed>, string}> */
    public static function ongeldigeNotities(): array
    {
        $flow = fn (array $stickies) => ['nodes' => [['id' => 'start', 'type' => 'trigger']], 'stickies' => $stickies];

        return [
            'geen lijst' => [$flow(['s1' => ['id' => 's1']]), '"stickies" of a flow must be a list'],
            'geen id' => [$flow([['text' => 'x']]), 'Sticky 0 has no valid id'],
            'een id twee keer' => [$flow([['id' => 's1'], ['id' => 's1']]), 'used twice'],
            'te lang' => [$flow([['id' => 's1', 'text' => str_repeat('a', 1001)]]), 'longer than 1000'],
            'geen tekst' => [$flow([['id' => 's1', 'text' => ['a']]]), 'is not text'],
            'te veel' => [$flow(array_map(fn (int $i) => ['id' => 's'.$i], range(1, 51))), 'at most 50 stickies'],
        ];
    }

    /** @param  array<mixed>  $data */
    #[Test]
    #[DataProvider('ongeldigeNotities')]
    public function weigert_notities_die_niet_kloppen(array $data, string $reden): void
    {
        $this->assertGooit(InvalidGraph::class, $reden, fn () => Graph::fromArray($data, self::soorten()));
    }

    #[Test]
    public function rekent_met_ids_die_getallen_zijn_zoals_de_rijen_van_een_databank(): void
    {
        // Als sleutel van een array wordt "12" in PHP het getal 12: wat terugkomt, moet toch een id zijn.
        $graph = Graph::fromArray(self::flow(
            [['start', 'trigger', ['event' => 'deal.won']], ['12', 'condition'], ['7', 'task', ['title' => 'Bellen']], ['30', 'wait'], ['9', 'task']],
            [['start', 'out', '12'], ['12', 'yes', '7'], ['12', 'no', '30'], ['7', 'out', '9'], ['30', 'out', '9']],
        ), self::soorten());
        $los = $graph->withNode(new Node('41', 'task', ['title' => 'Los']));

        $this->assertSame(['start', '12', '7', '9', '30'], $graph->reachable());
        $this->assertSame(['start', '12', '7', '9', '30', '41'], self::ids($los->ordered()));
        $this->assertArrayHasKey('41', $los->issuesByNode());
        $this->assertTrue($los->arranged()->node('41')?->hasPosition());
        $this->assertSame(900, $graph->arranged()->node('9')?->x);
        $this->assertSame(10, $graph->withPositions(['12' => [10, 20]])->node('12')?->x);
        $this->assertSame('7', $graph->withoutNode('12')->next('start')?->id);

        $this->assertGooit(InvalidGraph::class, '', fn () => Graph::fromArray(self::flow(
            [['start', 'trigger', ['event' => 'deal.won']], ['1', 'task'], ['2', 'wait']],
            [['start', 'out', '1'], ['1', 'out', '2'], ['2', 'out', '1']],
        ), self::soorten()));
    }
}
