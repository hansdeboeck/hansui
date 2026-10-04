<?php

declare(strict_types=1);

namespace HansDeBoeck\HansUi\Tests\Flows;

use HansDeBoeck\HansUi\Flows\Graph;
use HansDeBoeck\HansUi\Flows\Layout;
use HansDeBoeck\HansUi\Flows\Node;
use HansDeBoeck\HansUi\Flows\NodeType;
use HansDeBoeck\HansUi\Flows\NodeTypes;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

/**
 * Layout schikt zoals tests/fixtures/flows-layout.json zegt, en dat is wat
 * resources/js/flows/graph.js ook doet (tests/js/flows-graph.test.js leest
 * hetzelfde bestand). Wie het ene aanpast, ziet het andere falen.
 */
final class LayoutTest extends ZonderLaravel
{
    /** @return iterable<string, array{array<mixed>, array<string, mixed>}> */
    public static function schikkingen(): iterable
    {
        $fixture = self::fixture('flows-layout.json');

        foreach ($fixture['cases'] as $geval) {
            yield $geval['name'] => [$fixture['types'], $geval];
        }
    }

    /**
     * @param  array<mixed>  $soorten
     * @param  array<string, mixed>  $geval
     */
    #[Test]
    #[DataProvider('schikkingen')]
    public function schikt_zoals_de_browser(array $soorten, array $geval): void
    {
        $register = new NodeTypes(array_map(fn (array $soort) => new NodeType($soort['key'], $soort['key'], outputs: $soort['outputs'], start: $soort['start']), $soorten));
        $graph = Graph::fromArray(self::flow($geval['nodes'], $geval['edges']), $register);

        $this->assertSame($geval['positions'], Layout::positions($graph));
        $this->assertSame($geval['reachable'], $graph->reachable());
        $this->assertSame($geval['ordered'], array_map(fn (Node $node) => $node->id, $graph->ordered()));
    }

    #[Test]
    public function geeft_een_lege_flow_geen_plaatsen(): void
    {
        $soorten = new NodeTypes([new NodeType('task', 'Taak')]);

        $this->assertSame([], Layout::positions(Graph::fromArray(['nodes' => []], $soorten)));
    }
}
