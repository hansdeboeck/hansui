<?php

declare(strict_types=1);

namespace HansDeBoeck\HansUi\Tests\Flows;

use HansDeBoeck\HansUi\Flows\NodeType;
use HansDeBoeck\HansUi\Flows\NodeTypes;
use Throwable;

/**
 * De flows van de tests, kort geschreven.
 *
 * tests/fixtures/flows-layout.json is van de PHP-tests en van
 * tests/js/flows-graph.test.js samen: daar staat wat Layout en graph.js
 * allebei moeten uitkomen.
 */
trait MaaktFlows
{
    /** De soorten van de tests: een start, een voorwaarde, wachten, een taak en een einde. */
    protected static function soorten(): NodeTypes
    {
        return new NodeTypes([
            new NodeType('trigger', 'Als dit gebeurt', icon: 'bolt', tone: 'warn', start: true, required: ['event' => 'Gebeurtenis']),
            new NodeType('condition', 'Voorwaarde', icon: 'filter', tone: 'info', outputs: ['yes' => 'Ja', 'no' => 'Nee'], group: 'Logica'),
            new NodeType('wait', 'Wachten', icon: 'clock', group: 'Logica', defaults: ['amount' => 1, 'unit' => 'days']),
            new NodeType('task', 'Taak maken', icon: 'check', tone: 'ok', group: 'Acties', required: ['title' => 'Titel']),
            new NodeType('stop', 'Stoppen', icon: 'stop', outputs: [], group: 'Logica', max: 2),
        ]);
    }

    /**
     * @param  list<array{0: string, 1: string, 2?: array<mixed>}>  $stappen  id, soort, config
     * @param  list<array{0: string, 1: string, 2: string}>  $verbindingen  van, uitgang, naar
     * @return array<string, mixed>
     */
    protected static function flow(array $stappen, array $verbindingen = []): array
    {
        return [
            'version' => 1,
            'nodes' => array_map(fn (array $stap) => ['id' => $stap[0], 'type' => $stap[1], 'config' => $stap[2] ?? []], $stappen),
            'edges' => array_map(fn (array $verbinding) => ['from' => $verbinding[0], 'port' => $verbinding[1], 'to' => $verbinding[2]], $verbindingen),
        ];
    }

    /** @return array<string, mixed> */
    protected static function fixture(string $naam): array
    {
        return json_decode((string) file_get_contents(dirname(__DIR__).'/fixtures/'.$naam), true, flags: JSON_THROW_ON_ERROR);
    }

    /**
     * Dat $doe faalt met een $klasse waarvan de reden $reden bevat.
     *
     * @param  class-string<Throwable>  $klasse
     */
    protected function assertGooit(string $klasse, string $reden, callable $doe): void
    {
        try {
            $doe();
        } catch (Throwable $fout) {
            $this->assertInstanceOf($klasse, $fout);
            $this->assertStringContainsString($reden, $fout->getMessage());

            return;
        }

        $this->fail("Verwacht: {$klasse} met \"{$reden}\", en er ging niets mis.");
    }
}
