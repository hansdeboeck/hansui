<?php

declare(strict_types=1);

namespace HansDeBoeck\HansUi\Tests\Flows;

use Closure;
use HansDeBoeck\HansUi\Flows\NodeType;
use HansDeBoeck\HansUi\Flows\NodeTypes;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

/**
 * Een soort zegt wat de editor nodig heeft (uitgangen met hun label, de
 * start, de kleur als rol), en een fout in een soort valt op bij het maken,
 * niet pas in de browser.
 */
final class NodeTypeTest extends ZonderLaravel
{
    #[Test]
    public function kent_uitgangen_met_of_zonder_label(): void
    {
        $condition = new NodeType('condition', 'Voorwaarde', outputs: ['yes' => 'Ja', 'no' => 'Nee']);
        $split = new NodeType('split', 'Splitsen', outputs: ['a', 'b']);
        $end = new NodeType('stop', 'Stoppen', outputs: []);

        $this->assertSame(['yes' => 'Ja', 'no' => 'Nee'], $condition->outputs);
        $this->assertSame('yes', $condition->firstOutput());
        $this->assertTrue($condition->hasOutput('no'));
        $this->assertFalse($condition->hasOutput('out'));
        $this->assertSame(['a' => '', 'b' => ''], $split->outputs);
        $this->assertTrue($end->isEnd());
        $this->assertNull($end->firstOutput());
        $this->assertSame(['out' => ''], (new NodeType('task', 'Taak'))->outputs);
    }

    #[Test]
    public function kent_verplichte_velden_met_of_zonder_naam(): void
    {
        $this->assertSame(['title' => 'Titel', 'to' => 'to'], (new NodeType('task', 'Taak', required: ['title' => 'Titel', 'to']))->required);
    }

    #[Test]
    public function zegt_de_editor_wat_hij_moet_weten(): void
    {
        $type = new NodeType('trigger', 'Als dit gebeurt', icon: 'bolt', tone: 'warn', start: true, group: 'Start', hint: 'Waar het begint.', defaults: ['event' => 'deal.won']);

        $this->assertSame([
            'key' => 'trigger',
            'label' => 'Als dit gebeurt',
            'icon' => 'bolt',
            'tone' => 'warn',
            'outputs' => [['key' => 'out', 'label' => '']],
            'start' => true,
            'group' => 'Start',
            'hint' => 'Waar het begint.',
            'max' => 1,
            'defaults' => ['event' => 'deal.won'],
            'branches' => null,
        ], json_decode((string) json_encode($type), true));

        // Zonder standaard is het een leeg object, geen lijst.
        $this->assertSame('{}', json_encode((new NodeType('task', 'Taak'))->toArray()['defaults']));
    }

    #[Test]
    public function haalt_takken_uit_de_config_voor_de_vaste_uitgangen(): void
    {
        $split = new NodeType('split', 'Splitsen', outputs: ['other' => 'Anders'], branches: 'values');

        $this->assertSame(['high' => '', 'urgent' => '', 'other' => 'Anders'], $split->outputsFor(['values' => ['high', 'urgent']]));
        $this->assertSame(['other' => 'Anders'], $split->outputsFor([]), 'Zonder keuze blijft alleen de vaste uitgang.');
        $this->assertSame('high', $split->firstOutput(['values' => ['high']]));
        $this->assertSame('other', $split->firstOutput());
        $this->assertTrue($split->hasOutput('urgent', ['values' => ['urgent']]));
        $this->assertFalse($split->hasOutput('urgent'));
        $this->assertSame('values', $split->toArray()['branches']);

        // Wat geen naam voor een uitgang is, een dubbele, de vaste of een vijfde tak telt niet.
        $this->assertSame(
            ['a' => '', 'b-2' => '', 'c' => '', 'd' => '', 'other' => 'Anders'],
            $split->outputsFor(['values' => ['a', '12', 'Hoog', 'a', 'other', ['x'], 'b-2', 'c', 'd', 'e']]),
        );

        $end = new NodeType('route', 'Route', outputs: [], branches: 'values');

        $this->assertTrue($end->isEnd());
        $this->assertFalse($end->isEnd(['values' => ['a']]));
    }

    /** @return array<string, array{Closure, string}> */
    public static function fouten(): array
    {
        return [
            'een sleutel met een hoofdletter' => [fn () => new NodeType('Task', 'Taak'), 'not a valid node type key'],
            'een kleur in plaats van een rol' => [fn () => new NodeType('task', 'Taak', tone: 'purple'), 'not a tone'],
            'een uitgang met een spatie' => [fn () => new NodeType('task', 'Taak', outputs: ['ja nee' => '']), 'not a valid output'],
            'een maximum van nul' => [fn () => new NodeType('task', 'Taak', max: 0), 'at least 1'],
            'takken met een ongeldige sleutel' => [fn () => new NodeType('split', 'Splitsen', branches: 'De waarden'), 'not a valid config key'],
        ];
    }

    #[Test]
    #[DataProvider('fouten')]
    public function weigert_een_soort_die_niet_klopt(Closure $maak, string $reden): void
    {
        $this->assertGooit(InvalidArgumentException::class, $reden, $maak);
    }

    #[Test]
    public function houdt_de_soorten_bij_in_volgorde(): void
    {
        $types = new NodeTypes([new NodeType('trigger', 'Start', start: true), new NodeType('task', 'Taak')]);

        $this->assertCount(2, $types);
        $this->assertSame(['trigger', 'task'], array_keys($types->all()));
        $this->assertSame('trigger', $types->starts()[0]->key);
        $this->assertNull($types->get('robot'));
        $this->assertTrue($types->has('task'));
        $this->assertSame(['trigger', 'task'], array_column($types->toArray(), 'key'));
        $this->assertGooit(InvalidArgumentException::class, 'already registered', fn () => $types->add(new NodeType('task', 'Nog een taak')));
        $this->assertGooit(InvalidArgumentException::class, 'Unknown node type', fn () => $types->require('robot'));
    }
}
