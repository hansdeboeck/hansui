<?php

declare(strict_types=1);

namespace HansDeBoeck\HansUi\Flows;

use InvalidArgumentException;

/*
| Een flow maken in code: voor een migratie die oude automatisaties omzet,
| een flow die een nieuwe werkruimte meekrijgt, of een test.
|
|   $flow = new Builder($types);
|   $start = $flow->add('trigger', ['event' => 'deal.won']);
|   $check = $flow->then($start, 'condition', ['field' => 'value', 'operator' => '>', 'value' => '5000']);
|   $flow->then($check, 'task', ['title' => 'Bellen'], port: 'yes');
|   $graph = $flow->graph();   // gecontroleerd en geschikt
|
| De start heet vanzelf "start", de rest n1, n2, ...: een applicatie die
| eigen ids heeft, geeft ze mee.
*/
final class Builder
{
    /** @var array<string, array{id: string, type: string, config: array<string, mixed>}> */
    private array $nodes = [];

    /** @var array<string, array{from: string, port: string, to: string}> */
    private array $edges = [];

    private int $counter = 0;

    public function __construct(
        private readonly NodeTypes $types,
        private readonly string $prefix = 'n',
    ) {}

    /** @param  array<string, mixed>  $config */
    public function add(string $type, array $config = [], ?string $id = null): string
    {
        $kind = $this->types->require($type);

        if ($id === null) {
            $id = $kind->start && ! isset($this->nodes['start']) ? 'start' : $this->freeId();
        }

        if (isset($this->nodes[$id])) {
            throw new InvalidArgumentException(sprintf('There is already a node "%s".', $id));
        }

        $this->nodes[$id] = ['id' => $id, 'type' => $type, 'config' => $config + $kind->defaults];

        return $id;
    }

    /** Een uitgang verbinden; zonder uitgang de eerste van de soort. */
    public function connect(string $from, string $to, ?string $port = null): self
    {
        $type = $this->types->require($this->nodes[$from]['type'] ?? throw new InvalidArgumentException(sprintf('There is no node "%s".', $from)));
        $port ??= $type->firstOutput($this->nodes[$from]['config']) ?? throw new InvalidArgumentException(sprintf('Node "%s" has no outputs.', $from));

        $this->edges[$from."\0".$port] = ['from' => $from, 'port' => $port, 'to' => $to];

        return $this;
    }

    /**
     * Een stap toevoegen na een andere, aan haar (eerste) uitgang.
     *
     * @param  array<string, mixed>  $config
     */
    public function then(string $after, string $type, array $config = [], ?string $port = null, ?string $id = null): string
    {
        $id = $this->add($type, $config, $id);
        $this->connect($after, $id, $port);

        return $id;
    }

    /** De flow, gecontroleerd (InvalidGraph als er iets niet klopt) en geschikt. */
    public function graph(bool $arrange = true): Graph
    {
        $graph = Graph::fromArray(['nodes' => array_values($this->nodes), 'edges' => array_values($this->edges)], $this->types);

        return $arrange ? $graph->arranged() : $graph;
    }

    private function freeId(): string
    {
        do {
            $id = $this->prefix.++$this->counter;
        } while (isset($this->nodes[$id]));

        return $id;
    }
}
