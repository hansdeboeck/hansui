<?php

declare(strict_types=1);

namespace HansDeBoeck\HansUi\Flows;

use Countable;
use JsonException;
use JsonSerializable;
use stdClass;

/*
| Een flow: stappen (Node) en verbindingen (Edge), zoals de editor ze tekent
| en een applicatie ze bewaart, als een json-object:
|
|   {"version": 1,
|    "nodes": [{"id": "start", "type": "trigger", "config": {...}, "x": 0, "y": 0}, ...],
|    "edges": [{"from": "start", "port": "out", "to": "n2"}, ...]}
|
| WAT ONGELDIG IS, KOMT ER NIET IN. Een graaf bestaat alleen als hij klopt:
| bekende soorten, unieke ids, verbindingen tussen stappen die er zijn en uit
| een uitgang die de soort heeft, een uitgang met hoogstens een verbinding,
| precies een start (waar niets naartoe gaat), en geen lus. Een lus zou een
| run eindeloos laten draaien, en een wachtstap in een lus elke dag een mail.
| Wat niet klopt, is een InvalidGraph; elke wijziging hieronder geeft een
| nieuwe graaf terug en controleert opnieuw.
|
| WAT NOG NIET AF IS, MAG ER WEL IN: een titel die leeg is, een stap die
| nergens aan hangt. Dat zijn issues(): de editor toont ze op de stap, en een
| applicatie zet een flow met issues niet aan.
*/
final class Graph implements Countable, JsonSerializable
{
    public const VERSION = 1;

    /** Hoeveel stappen een flow hoogstens heeft: genoeg voor elk proces dat iemand nog overziet. */
    public const MAX_NODES = 200;

    /** Hoe groot de json hoogstens is, met alle teksten erin. */
    public const MAX_BYTES = 262_144;

    private const ID = '/^[A-Za-z0-9_-]{1,40}$/';

    private const COORDINATE = 1_000_000;

    private ?string $start = null;

    /**
     * @param  array<string, Node>  $nodes
     * @param  array<string, Edge>  $edges  per "van\0uitgang"
     */
    private function __construct(
        private readonly NodeTypes $types,
        private readonly array $nodes,
        private readonly array $edges,
    ) {
        foreach ($nodes as $node) {
            if ($types->require($node->type)->start) {
                $this->start = $node->id;
                break;
            }
        }
    }

    /** Een nieuwe flow: alleen de start, met wat erin ingevuld is. */
    public static function starting(NodeTypes $types, string $type, array $config = [], string $id = 'start'): self
    {
        return self::fromArray(['nodes' => [['id' => $id, 'type' => $type, 'config' => $config + $types->require($type)->defaults]]], $types);
    }

    public static function fromJson(?string $json, NodeTypes $types): self
    {
        if ($json === null || trim($json) === '') {
            throw new InvalidGraph('The flow is empty.');
        }

        if (strlen($json) > self::MAX_BYTES) {
            throw new InvalidGraph(sprintf('The flow is larger than %d bytes.', self::MAX_BYTES));
        }

        try {
            $data = json_decode($json, true, 32, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new InvalidGraph('The flow is not valid JSON: '.$e->getMessage(), previous: $e);
        }

        if (! is_array($data)) {
            throw new InvalidGraph('The flow is not a JSON object.');
        }

        return self::fromArray($data, $types);
    }

    /** @param  array<string, mixed>  $data */
    public static function fromArray(array $data, NodeTypes $types): self
    {
        $version = $data['version'] ?? self::VERSION;

        if ($version !== self::VERSION) {
            throw new InvalidGraph(sprintf('Version %s of the flow format is not supported.', json_encode($version)));
        }

        $nodes = [];

        foreach (self::listOf($data['nodes'] ?? null, 'nodes') as $index => $raw) {
            $node = self::readNode($raw, $index, $types);

            if (isset($nodes[$node->id])) {
                throw new InvalidGraph(sprintf('Node id "%s" is used twice.', $node->id));
            }

            $nodes[$node->id] = $node;
        }

        if (count($nodes) > self::MAX_NODES) {
            throw new InvalidGraph(sprintf('A flow has at most %d nodes; this one has %d.', self::MAX_NODES, count($nodes)));
        }

        $edges = [];

        foreach (self::listOf($data['edges'] ?? [], 'edges') as $index => $raw) {
            $edge = self::readEdge($raw, $index, $nodes, $types);
            $key = $edge->from."\0".$edge->port;

            if (isset($edges[$key])) {
                throw new InvalidGraph(sprintf('Output "%s" of node "%s" is connected twice.', $edge->port, $edge->from));
            }

            $edges[$key] = $edge;
        }

        $graph = new self($types, $nodes, $edges);
        $graph->assertCounts();
        $graph->assertAcyclic();

        return $graph;
    }

    // ---- Lezen ---------------------------------------------------------------

    public function types(): NodeTypes
    {
        return $this->types;
    }

    /** @return array<string, Node> per id, in de volgorde waarin ze toegevoegd werden */
    public function nodes(): array
    {
        return $this->nodes;
    }

    /** @return list<Edge> */
    public function edges(): array
    {
        return array_values($this->edges);
    }

    public function count(): int
    {
        return count($this->nodes);
    }

    public function has(string $id): bool
    {
        return isset($this->nodes[$id]);
    }

    public function node(string $id): ?Node
    {
        return $this->nodes[$id] ?? null;
    }

    public function typeOf(string $id): ?NodeType
    {
        return isset($this->nodes[$id]) ? $this->types->get($this->nodes[$id]->type) : null;
    }

    /** De stap waar de flow begint; null alleen als de soorten geen start kennen. */
    public function start(): ?Node
    {
        return $this->start === null ? null : $this->nodes[$this->start];
    }

    /** Waar een uitgang naartoe gaat (het id), of null als ze nergens heen gaat. */
    public function target(string $id, string $port): ?string
    {
        return ($this->edges[$id."\0".$port] ?? null)?->to;
    }

    /** De stap na een uitgang; zonder uitgang de eerste van de soort. */
    public function next(string $id, ?string $port = null): ?Node
    {
        $port ??= $this->typeOf($id)?->firstOutput();

        if ($port === null) {
            return null;
        }

        $target = $this->target($id, $port);

        return $target === null ? null : $this->nodes[$target];
    }

    /** @return list<Edge> de verbindingen uit een stap, in de volgorde van de uitgangen van de soort */
    public function outgoing(string $id): array
    {
        $type = $this->typeOf($id);

        if ($type === null) {
            return [];
        }

        $edges = [];

        foreach (array_keys($type->outputs) as $port) {
            if (isset($this->edges[$id."\0".$port])) {
                $edges[] = $this->edges[$id."\0".$port];
            }
        }

        return $edges;
    }

    /** @return list<Edge> de verbindingen naar een stap */
    public function incoming(string $id): array
    {
        return array_values(array_filter($this->edges, fn (Edge $edge) => $edge->to === $id));
    }

    /** @return list<Node> de stappen van een of meer soorten, in volgorde (ordered()) */
    public function ofType(string ...$types): array
    {
        return array_values(array_filter($this->ordered(), fn (Node $node) => in_array($node->type, $types, true)));
    }

    /**
     * De ids die je vanaf de start bereikt, in de volgorde van een wandeling:
     * eerst de eerste uitgang helemaal af, dan de volgende.
     *
     * @return list<string>
     */
    public function reachable(): array
    {
        if ($this->start === null) {
            return [];
        }

        $order = [];
        $this->walk($this->start, $order);

        // Een id als "12" wordt als sleutel een getal; wat terugkomt, is altijd een id.
        return array_map('strval', array_keys($order));
    }

    /**
     * Alle stappen in een volgorde die een mens leest: wat vanaf de start
     * bereikt wordt, zoals reachable(), en daarna wat nergens aan hangt.
     *
     * @return list<Node>
     */
    public function ordered(): array
    {
        $order = [];

        if ($this->start !== null) {
            $this->walk($this->start, $order);
        }

        foreach ($this->nodes as $node) {
            if (! isset($order[$node->id]) && $this->incoming($node->id) === []) {
                $this->walk($node->id, $order);
            }
        }

        return array_map(fn (int|string $id) => $this->nodes[$id], array_keys($order));
    }

    // ---- Wat nog niet af is --------------------------------------------------

    /**
     * Wat nog moet gebeuren voor de flow aan kan: verplichte velden die leeg
     * zijn, een stap die nergens aan hangt, een start zonder vervolg. Met
     * $extra voegt een applicatie haar eigen controles toe (een team dat niet
     * meer bestaat): een functie die per stap een lijst zinnen teruggeeft.
     *
     * @param  (callable(Node, self): iterable<string>)|null  $extra
     * @return list<Issue>
     */
    public function issues(?callable $extra = null): array
    {
        $reachable = array_flip($this->reachable());
        $issues = [];

        foreach ($this->ordered() as $node) {
            $type = $this->types->require($node->type);

            if ($node->id === $this->start && $this->outgoing($node->id) === [] && ! $type->isEnd()) {
                $issues[] = new Issue($node->id, Messages::get('Nog geen volgende stap.'));
            }

            if ($this->start !== null && ! isset($reachable[$node->id])) {
                $issues[] = new Issue($node->id, Messages::get('Deze stap hangt aan geen enkele andere stap.'));
            }

            foreach ($type->required as $key => $label) {
                if (! $node->filled($key)) {
                    $issues[] = new Issue($node->id, Messages::get('Nog in te vullen: :veld', ['veld' => $label]));
                }
            }

            if ($extra !== null) {
                foreach ($extra($node, $this) as $message) {
                    $issues[] = new Issue($node->id, $message);
                }
            }
        }

        return $issues;
    }

    /** @return array<string, list<string>> de issues per stap, zoals de editor ze toont */
    public function issuesByNode(?callable $extra = null): array
    {
        $byNode = [];

        foreach ($this->issues($extra) as $issue) {
            $byNode[$issue->node][] = $issue->message;
        }

        return $byNode;
    }

    public function isComplete(?callable $extra = null): bool
    {
        return $this->issues($extra) === [];
    }

    // ---- Wijzigen (elke keer een nieuwe graaf, opnieuw gecontroleerd) ----------

    /** Een stap toevoegen, of een met hetzelfde id vervangen. */
    public function withNode(Node $node): self
    {
        $nodes = $this->nodes;
        $nodes[$node->id] = $node;

        return $this->rebuild($nodes, $this->edges);
    }

    /**
     * Een stap weghalen, met haar verbindingen. Hing ze tussen twee stappen,
     * met een verbinding erin en een op haar eerste uitgang, dan sluiten die
     * twee weer aan: wie een stap uit een reeks haalt, breekt de reeks niet.
     */
    public function withoutNode(string $id, bool $bridge = true): self
    {
        if (! isset($this->nodes[$id])) {
            return $this;
        }

        $incoming = $this->incoming($id);
        $first = $this->typeOf($id)?->firstOutput();
        $after = $first === null ? null : $this->target($id, $first);

        $nodes = $this->nodes;
        unset($nodes[$id]);

        $edges = array_filter($this->edges, fn (Edge $edge) => $edge->from !== $id && $edge->to !== $id);

        if ($bridge && count($incoming) === 1 && $after !== null) {
            $edges[$incoming[0]->from."\0".$incoming[0]->port] = new Edge($incoming[0]->from, $incoming[0]->port, $after);
        }

        return $this->rebuild($nodes, $edges);
    }

    /** Een uitgang verbinden; wat er al aan hing, laat los. */
    public function connect(string $from, string $port, string $to): self
    {
        $edges = $this->edges;
        $edges[$from."\0".$port] = new Edge($from, $port, $to);

        return $this->rebuild($this->nodes, $edges);
    }

    public function disconnect(string $from, string $port): self
    {
        $edges = $this->edges;
        unset($edges[$from."\0".$port]);

        return $this->rebuild($this->nodes, $edges);
    }

    /**
     * Ids vervangen: een applicatie die haar stappen als rijen bewaart, geeft
     * een stap uit de editor ("n4") na het bewaren het id van haar rij ("12").
     *
     * @param  array<string, string>  $ids  oud => nieuw
     */
    public function renamed(array $ids): self
    {
        $name = fn (string $id) => $ids[$id] ?? $id;
        $nodes = [];

        foreach ($this->nodes as $node) {
            $nodes[$name($node->id)] = new Node($name($node->id), $node->type, $node->config, $node->x, $node->y);
        }

        $edges = [];

        foreach ($this->edges as $edge) {
            $edges[$name($edge->from)."\0".$edge->port] = new Edge($name($edge->from), $edge->port, $name($edge->to));
        }

        if (count($nodes) !== count($this->nodes)) {
            throw new InvalidGraph('Renaming would give two nodes the same id.');
        }

        return $this->rebuild($nodes, $edges);
    }

    /** @param  array<string, array{0: int, 1: int}>  $positions  id => [x, y] */
    public function withPositions(array $positions): self
    {
        $nodes = [];

        foreach ($this->nodes as $id => $node) {
            $nodes[$id] = isset($positions[$id]) ? $node->at((int) $positions[$id][0], (int) $positions[$id][1]) : $node;
        }

        return new self($this->types, $nodes, $this->edges);
    }

    /** Netjes geschikt, van links naar rechts (Layout); met $all false alleen wat nog geen plaats had. */
    public function arranged(bool $all = true): self
    {
        $positions = Layout::positions($this);

        if (! $all) {
            $positions = array_filter($positions, fn (array $position, int|string $id) => ! $this->nodes[$id]->hasPosition(), ARRAY_FILTER_USE_BOTH);
        }

        return $this->withPositions($positions);
    }

    // ---- Schrijven -----------------------------------------------------------

    /** @return array{version: int, nodes: list<array<string, mixed>>, edges: list<array{from: string, port: string, to: string}>} */
    public function toArray(): array
    {
        return [
            'version' => self::VERSION,
            'nodes' => array_values(array_map(fn (Node $node) => $node->toArray(), $this->nodes)),
            'edges' => array_values(array_map(fn (Edge $edge) => $edge->toArray(), $this->edges)),
        ];
    }

    public function toJson(int $flags = 0): string
    {
        return json_encode($this->toArray(), $flags | JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /** @return array<string, mixed> */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    // ---- Binnenin --------------------------------------------------------------

    /**
     * @param  array<string, Node>  $nodes
     * @param  array<string, Edge>  $edges
     */
    private function rebuild(array $nodes, array $edges): self
    {
        return self::fromArray([
            'nodes' => array_values(array_map(fn (Node $node) => ['id' => $node->id, 'type' => $node->type, 'config' => $node->config, 'x' => $node->x, 'y' => $node->y], $nodes)),
            'edges' => array_values(array_map(fn (Edge $edge) => $edge->toArray(), $edges)),
        ], $this->types);
    }

    /** @param  array<string, true>  $order */
    private function walk(string $id, array &$order): void
    {
        $stack = [$id];

        // Met een stapel en niet recursief: 200 stappen achter elkaar is geen probleem, maar ook geen reden.
        while ($stack !== []) {
            $current = array_pop($stack);

            if (isset($order[$current])) {
                continue;
            }

            $order[$current] = true;

            foreach (array_reverse($this->outgoing($current)) as $edge) {
                if (! isset($order[$edge->to])) {
                    $stack[] = $edge->to;
                }
            }
        }
    }

    private function assertCounts(): void
    {
        $counts = array_count_values(array_map(fn (Node $node) => $node->type, $this->nodes));
        $starts = 0;

        foreach ($counts as $key => $count) {
            $type = $this->types->require((string) $key);
            $starts += $type->start ? $count : 0;

            if ($type->max !== null && $count > $type->max) {
                throw new InvalidGraph(sprintf('A flow has at most %d node(s) of type "%s"; this one has %d.', $type->max, $key, $count));
            }
        }

        if ($this->types->starts() !== [] && $starts !== 1) {
            throw new InvalidGraph(sprintf('A flow has exactly one start; this one has %d.', $starts));
        }
    }

    private function assertAcyclic(): void
    {
        $state = [];

        foreach ($this->nodes as $node) {
            $root = $node->id;

            if (isset($state[$root])) {
                continue;
            }

            // 1 = op het pad, 2 = klaar. Een verbinding naar iets op het pad is een lus.
            $stack = [[$root, 0]];
            $state[$root] = 1;

            while ($stack !== []) {
                [$id, $index] = $stack[count($stack) - 1];
                $outgoing = $this->outgoing($id);

                if ($index >= count($outgoing)) {
                    $state[$id] = 2;
                    array_pop($stack);

                    continue;
                }

                $stack[count($stack) - 1][1]++;
                $to = $outgoing[$index]->to;

                if (($state[$to] ?? 0) === 1) {
                    throw new InvalidGraph(sprintf('The flow loops: node "%s" leads back to node "%s".', $id, $to));
                }

                if (! isset($state[$to])) {
                    $state[$to] = 1;
                    $stack[] = [$to, 0];
                }
            }
        }
    }

    /** @return array<int, mixed> */
    private static function listOf(mixed $value, string $what): array
    {
        if (! is_array($value) || ! array_is_list($value)) {
            throw new InvalidGraph(sprintf('The "%s" of a flow must be a list.', $what));
        }

        return $value;
    }

    private static function readNode(mixed $raw, int $index, NodeTypes $types): Node
    {
        if (! is_array($raw)) {
            throw new InvalidGraph(sprintf('Node %d is not an object.', $index));
        }

        $id = $raw['id'] ?? null;

        if (! is_string($id) || ! preg_match(self::ID, $id)) {
            throw new InvalidGraph(sprintf('Node %d has no valid id: use 1 to 40 letters, digits, "_" or "-".', $index));
        }

        $type = $raw['type'] ?? null;

        if (! is_string($type) || ! $types->has($type)) {
            throw new InvalidGraph(sprintf('Node "%s" has an unknown type %s.', $id, json_encode($type)));
        }

        $config = $raw['config'] ?? [];

        // toArray() schrijft een lege config als object, zodat json er {} van maakt.
        if ($config instanceof stdClass && get_object_vars($config) === []) {
            $config = [];
        }

        if (! is_array($config) || ($config !== [] && array_is_list($config))) {
            throw new InvalidGraph(sprintf('The config of node "%s" must be an object.', $id));
        }

        self::assertConfig($config, $id);

        return new Node($id, $type, $config, self::coordinate($raw['x'] ?? null, $id), self::coordinate($raw['y'] ?? null, $id));
    }

    /** @param  array<string, Node>  $nodes */
    private static function readEdge(mixed $raw, int $index, array $nodes, NodeTypes $types): Edge
    {
        if (! is_array($raw)) {
            throw new InvalidGraph(sprintf('Edge %d is not an object.', $index));
        }

        $from = $raw['from'] ?? null;
        $port = $raw['port'] ?? null;
        $to = $raw['to'] ?? null;

        if (! is_string($from) || ! is_string($port) || ! is_string($to)) {
            throw new InvalidGraph(sprintf('Edge %d needs a "from", a "port" and a "to".', $index));
        }

        if (! isset($nodes[$from])) {
            throw new InvalidGraph(sprintf('Edge %d starts at node "%s", which does not exist.', $index, $from));
        }

        if (! isset($nodes[$to])) {
            throw new InvalidGraph(sprintf('Edge %d leads to node "%s", which does not exist.', $index, $to));
        }

        if (! $types->require($nodes[$from]->type)->hasOutput($port)) {
            throw new InvalidGraph(sprintf('Node "%s" has no output "%s".', $from, $port));
        }

        if ($types->require($nodes[$to]->type)->start) {
            throw new InvalidGraph(sprintf('Node "%s" starts the flow; nothing leads to it.', $to));
        }

        if ($from === $to) {
            throw new InvalidGraph(sprintf('Node "%s" is connected to itself.', $from));
        }

        return new Edge($from, $port, $to);
    }

    /** @param  array<mixed>  $config */
    private static function assertConfig(array $config, string $id, int $depth = 0): void
    {
        if ($depth > 4) {
            throw new InvalidGraph(sprintf('The config of node "%s" is nested too deeply.', $id));
        }

        foreach ($config as $key => $value) {
            if (is_string($key) && strlen($key) > 60) {
                throw new InvalidGraph(sprintf('The config of node "%s" has a key longer than 60 characters.', $id));
            }

            if (is_array($value)) {
                self::assertConfig($value, $id, $depth + 1);
            } elseif ($value !== null && ! is_scalar($value)) {
                throw new InvalidGraph(sprintf('The config of node "%s" holds something that is not text, a number or a list.', $id));
            }
        }
    }

    private static function coordinate(mixed $value, string $id): ?int
    {
        if ($value === null) {
            return null;
        }

        if (! is_int($value) && ! is_float($value)) {
            throw new InvalidGraph(sprintf('The position of node "%s" is not a number.', $id));
        }

        return (int) round(max(-self::COORDINATE, min(self::COORDINATE, $value)));
    }
}
