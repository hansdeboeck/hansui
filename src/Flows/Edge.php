<?php

declare(strict_types=1);

namespace HansDeBoeck\HansUi\Flows;

use JsonSerializable;

/*
| Een verbinding: van een uitgang van een stap naar een andere stap.
|
| EEN UITGANG HEEFT HOOGSTENS EEN VERBINDING. Wat na een stap komt, is zo
| altijd een stap, of niets (en dan eindigt de flow daar); een run staat
| nooit op twee plaatsen tegelijk. Wie twee dingen na elkaar wil, zet ze na
| elkaar. Een stap kan wel van meerdere kanten bereikt worden: twee takken
| komen weer samen.
*/
final class Edge implements JsonSerializable
{
    public function __construct(
        public readonly string $from,
        public readonly string $port,
        public readonly string $to,
    ) {}

    /** @return array{from: string, port: string, to: string} */
    public function toArray(): array
    {
        return ['from' => $this->from, 'port' => $this->port, 'to' => $this->to];
    }

    /** @return array{from: string, port: string, to: string} */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
