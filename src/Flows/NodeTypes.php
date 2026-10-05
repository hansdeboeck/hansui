<?php

declare(strict_types=1);

namespace HansDeBoeck\HansUi\Flows;

use ArrayIterator;
use Countable;
use InvalidArgumentException;
use IteratorAggregate;
use JsonSerializable;
use Traversable;

/*
| De soorten stappen die een flow kent, in de volgorde van het palet.
|
| Een applicatie maakt dit een keer per aanvraag (de labels zijn vertaald),
| en geeft het aan de graaf, de editor en de walker. Een soort die hier niet
| staat, bestaat voor de graaf niet: een flow met zo'n stap is ongeldig.
*/
final class NodeTypes implements Countable, IteratorAggregate, JsonSerializable
{
    /** @var array<string, NodeType> */
    private array $types = [];

    /** @param  iterable<NodeType>  $types */
    public function __construct(iterable $types = [])
    {
        foreach ($types as $type) {
            $this->add($type);
        }
    }

    public function add(NodeType $type): self
    {
        if (isset($this->types[$type->key])) {
            throw new InvalidArgumentException(sprintf('Node type "%s" is already registered.', $type->key));
        }

        $this->types[$type->key] = $type;

        return $this;
    }

    public function has(string $key): bool
    {
        return isset($this->types[$key]);
    }

    public function get(string $key): ?NodeType
    {
        return $this->types[$key] ?? null;
    }

    public function require(string $key): NodeType
    {
        return $this->types[$key] ?? throw new InvalidArgumentException(sprintf('Unknown node type "%s".', $key));
    }

    /** @return array<string, NodeType> */
    public function all(): array
    {
        return $this->types;
    }

    /** @return list<NodeType> de soorten waar een flow mee begint */
    public function starts(): array
    {
        return array_values(array_filter($this->types, fn (NodeType $type) => $type->start));
    }

    public function count(): int
    {
        return count($this->types);
    }

    /** @return Traversable<string, NodeType> */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->types);
    }

    /** @return list<array<string, mixed>> */
    public function toArray(): array
    {
        return array_values(array_map(fn (NodeType $type) => $type->toArray(), $this->types));
    }

    /** @return list<array<string, mixed>> */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
