<?php

declare(strict_types=1);

namespace HansDeBoeck\HansUi\Tests\Flows;

use HansDeBoeck\HansUi\Flows\Builder;
use HansDeBoeck\HansUi\Flows\InvalidGraph;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;

/**
 * Een flow in code: na graph() gecontroleerd en geschikt, de start heet
 * "start", de rest n1, n2, ... tenzij de applicatie zelf ids geeft.
 */
final class BuilderTest extends ZonderLaravel
{
    #[Test]
    public function bouwt_een_flow_met_takken(): void
    {
        $flow = new Builder(self::soorten());
        $start = $flow->add('trigger', ['event' => 'deal.won']);
        $check = $flow->then($start, 'condition', ['field' => 'value']);
        $yes = $flow->then($check, 'task', ['title' => 'Bellen'], port: 'yes');
        $no = $flow->then($check, 'wait', port: 'no');
        $graph = $flow->graph();

        $this->assertSame(['start', 'n1', 'n2', 'n3'], [$start, $check, $yes, $no]);
        $this->assertSame('n3', $graph->target('n1', 'no'));
        $this->assertSame(['amount' => 1, 'unit' => 'days'], $graph->node('n3')?->config, 'De standaard van de soort.');
        $this->assertSame(600, $graph->node('n3')?->x);
        $this->assertSame(150, $graph->node('n3')?->y);
        $this->assertTrue($graph->isComplete());
    }

    #[Test]
    public function neemt_eigen_ids_en_geeft_er_geen_twee_keer(): void
    {
        $flow = new Builder(self::soorten(), prefix: 'stap');
        $flow->add('trigger', id: 'begin');
        $flow->add('task', ['title' => 'A'], id: 'stap1');

        $this->assertSame('stap2', $flow->add('task', ['title' => 'B']));
        $this->assertGooit(InvalidArgumentException::class, 'already a node "begin"', fn () => $flow->add('wait', id: 'begin'));
        $this->assertGooit(InvalidArgumentException::class, 'no node "ghost"', fn () => $flow->connect('ghost', 'begin'));
        $this->assertNull($flow->graph(arrange: false)->node('stap2')?->x);
    }

    #[Test]
    public function laat_geen_ongeldige_flow_door(): void
    {
        $flow = new Builder(self::soorten());
        $flow->add('task', ['title' => 'Zonder start']);

        $this->assertGooit(InvalidGraph::class, 'exactly one start', fn () => $flow->graph());
    }
}
