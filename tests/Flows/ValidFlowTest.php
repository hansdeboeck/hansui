<?php

declare(strict_types=1);

namespace HansDeBoeck\HansUi\Tests\Flows;

use HansDeBoeck\HansUi\Flows\Builder;
use HansDeBoeck\HansUi\Flows\Node;
use HansDeBoeck\HansUi\Flows\ValidFlow;
use HansDeBoeck\HansUi\Tests\TestCase;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

/**
 * Een ongeldige flow is altijd een fout, met een zin voor de gebruiker en
 * niet de technische reden; een flow die aan moet, mag niets meer open
 * hebben, ook niet wat de applicatie zelf controleert.
 */
final class ValidFlowTest extends TestCase
{
    use MaaktFlows;

    protected function setUp(): void
    {
        parent::setUp();

        app()->setLocale('nl');
    }

    private static function flowJson(?string $titel = 'Bellen'): string
    {
        $flow = new Builder(self::soorten());
        $flow->then($flow->add('trigger', ['event' => 'deal.won']), 'task', ['title' => $titel]);

        return $flow->graph()->toJson();
    }

    /** @return array<int, string> */
    private function fouten(mixed $waarde, ValidFlow $regel): array
    {
        return Validator::make(['flow' => $waarde], ['flow' => [$regel]])->errors()->get('flow');
    }

    #[Test]
    public function laat_een_geldige_flow_door_als_json_of_als_array(): void
    {
        $this->assertSame([], $this->fouten(self::flowJson(), new ValidFlow(self::soorten())));
        $this->assertSame([], $this->fouten(json_decode(self::flowJson(), true), new ValidFlow(self::soorten())));
    }

    /** @return array<string, array{mixed}> */
    public static function onleesbaar(): array
    {
        return [
            'kapotte json' => ['{"nodes": ['],
            'een getal' => [12],
            'een lus' => [json_encode(self::flow([['start', 'trigger'], ['a', 'task'], ['b', 'task']], [['start', 'out', 'a'], ['a', 'out', 'b'], ['b', 'out', 'a']]))],
        ];
    }

    #[Test]
    #[DataProvider('onleesbaar')]
    public function weigert_wat_niet_gelezen_kan_worden_met_een_zin_voor_de_gebruiker(mixed $waarde): void
    {
        $this->assertSame(
            ['De flow kon niet gelezen worden. Laad de pagina opnieuw en probeer het nog eens.'],
            $this->fouten($waarde, new ValidFlow(self::soorten())),
        );
    }

    #[Test]
    public function vraagt_een_volledige_flow_als_ze_aan_moet(): void
    {
        $this->assertSame([], $this->fouten(self::flowJson(''), new ValidFlow(self::soorten())));
        $this->assertSame(
            ['Een stap vraagt nog aandacht: vul ze aan, of bewaar de flow uitgeschakeld.'],
            $this->fouten(self::flowJson(''), new ValidFlow(self::soorten(), complete: true)),
        );

        $extra = fn (Node $node) => $node->type === 'trigger' ? ['Die gebeurtenis bestaat niet meer.'] : [];

        $this->assertSame(
            ['2 stappen vragen nog aandacht: vul ze aan, of bewaar de flow uitgeschakeld.'],
            $this->fouten(self::flowJson(''), new ValidFlow(self::soorten(), complete: true, extra: $extra)),
        );
    }
}
