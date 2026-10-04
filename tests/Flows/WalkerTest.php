<?php

declare(strict_types=1);

namespace HansDeBoeck\HansUi\Tests\Flows;

use DateTimeImmutable;
use HansDeBoeck\HansUi\Flows\Builder;
use HansDeBoeck\HansUi\Flows\Graph;
use HansDeBoeck\HansUi\Flows\Node;
use HansDeBoeck\HansUi\Flows\Step;
use HansDeBoeck\HansUi\Flows\Walk;
use HansDeBoeck\HansUi\Flows\Walker;
use PHPUnit\Framework\Attributes\Test;
use RuntimeException;

/**
 * De walker volgt de uitgang die een stap kiest, stopt bij een wachtstap met
 * een bladwijzer (waar, tot wanneer, langs welke uitgang), gaat daar later
 * verder, en zegt wanneer iets mislukte of te lang duurde.
 */
final class WalkerTest extends ZonderLaravel
{
    private static function campagne(): Graph
    {
        $flow = new Builder(self::soorten());
        $start = $flow->add('trigger', ['event' => 'quote.sent']);
        $wait = $flow->then($start, 'wait', ['amount' => 3]);
        $check = $flow->then($wait, 'condition', ['field' => 'accepted']);
        $flow->then($check, 'task', ['title' => 'Bedanken'], port: 'yes');
        $flow->then($check, 'task', ['title' => 'Nabellen'], port: 'no');

        return $flow->graph();
    }

    #[Test]
    public function wandelt_tot_een_wachtstap_en_gaat_daarna_verder_langs_de_gekozen_uitgang(): void
    {
        $graph = self::campagne();
        $gedaan = [];
        $tot = new DateTimeImmutable('2026-10-07 09:00');
        $aanvaard = null;

        $handler = function (Node $node) use (&$gedaan, $tot, &$aanvaard): Step {
            $gedaan[] = $node->id;

            return match ($node->type) {
                'wait' => Step::wait($tot, state: ['reden' => 'drie dagen']),
                'condition' => Step::next($aanvaard ? 'yes' : 'no'),
                default => Step::next(),
            };
        };

        $walk = (new Walker($graph))->resume('start', 'out', $handler);

        $this->assertTrue($walk->waiting());
        $this->assertSame('n1', $walk->node);
        $this->assertEquals($tot, $walk->until);
        $this->assertNull($walk->port);
        $this->assertSame(['reden' => 'drie dagen'], $walk->state);
        $this->assertSame(['n1'], $walk->visited);

        $aanvaard = false;
        $later = (new Walker($graph))->resume($walk->node, $walk->port, $handler);

        $this->assertTrue($later->finished());
        $this->assertSame(['n2', 'n4'], $later->visited);
        $this->assertSame('n4', $later->node);
        $this->assertSame(['n1', 'n2', 'n4'], $gedaan);
    }

    #[Test]
    public function stopt_mislukt_of_kent_de_stap_niet_meer(): void
    {
        $walker = new Walker(self::campagne());

        $gestopt = $walker->from('n2', fn () => Step::stop());
        $mislukt = $walker->from('n2', fn () => Step::fail('Geen klant.'));
        $verkeerd = $walker->from('n2', fn () => Step::next('maybe'));
        $weg = $walker->from('ghost', fn () => Step::next());

        $this->assertTrue($gestopt->finished());
        $this->assertSame(['n2'], $gestopt->visited);
        $this->assertTrue($mislukt->failed());
        $this->assertSame('Geen klant.', $mislukt->reason);
        $this->assertSame('n2', $mislukt->node);
        $this->assertTrue($verkeerd->failed());
        $this->assertStringContainsString('no output "maybe"', (string) $verkeerd->reason);
        $this->assertSame(Walk::FAILED, $weg->status);
        $this->assertSame('Deze stap bestaat niet meer.', $weg->reason);
        $this->assertTrue($walker->resume('ghost', null, fn () => Step::next())->failed());
        $this->assertTrue($walker->resume('n3', null, fn () => Step::next())->finished(), 'Na de laatste stap is de flow af.');
    }

    #[Test]
    public function houdt_op_na_te_veel_stappen(): void
    {
        $walk = (new Walker(self::campagne(), maxSteps: 2))->from('n1', fn () => Step::next());

        $this->assertSame(Walk::LIMIT, $walk->status);
        $this->assertTrue($walk->failed());
        $this->assertSame(['n1', 'n2'], $walk->visited);
        $this->assertSame('n3', $walk->node);
    }

    #[Test]
    public function laat_een_fout_van_de_applicatie_door(): void
    {
        $this->assertGooit(RuntimeException::class, 'Kapot', fn () => (new Walker(self::campagne()))->from('n1', fn () => throw new RuntimeException('Kapot')));
    }

    #[Test]
    public function wacht_op_iets_zonder_tijdstip(): void
    {
        $walk = (new Walker(self::campagne()))->from('n1', fn () => Step::wait(port: 'out', state: ['task_id' => 12]));

        $this->assertNull($walk->until);
        $this->assertSame('out', $walk->port);
        $this->assertSame(['task_id' => 12], $walk->state);
    }
}
