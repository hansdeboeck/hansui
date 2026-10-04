<?php

declare(strict_types=1);

namespace HansDeBoeck\HansUi\Flows;

use JsonSerializable;

/*
| Een stap in een flow: een id, een soort, wat erin ingevuld is (config) en
| waar ze op het canvas staat.
|
| HET ID IS EEN NAAM, GEEN TELLER. Een applicatie die haar stappen als rijen
| bewaart, gebruikt het id van de rij ("12"); een stap die net in de editor
| gemaakt werd, heeft er een van de editor ("n4") tot ze bewaard is. Daarom
| is het een string, en vergelijkt niemand twee ids als getallen.
|
| EEN POSITIE MAG ONTBREKEN (null): een flow die uit code komt, heeft er
| geen, en Layout zet ze dan zelf.
*/
final class Node implements JsonSerializable
{
    /** @param  array<string, mixed>  $config */
    public function __construct(
        public readonly string $id,
        public readonly string $type,
        public readonly array $config = [],
        public readonly ?int $x = null,
        public readonly ?int $y = null,
    ) {}

    /** Een waarde uit de config, met een punt voor wat dieper zit: `get('wait.amount')`. */
    public function get(string $key, mixed $default = null): mixed
    {
        if (array_key_exists($key, $this->config)) {
            return $this->config[$key];
        }

        $value = $this->config;

        foreach (explode('.', $key) as $part) {
            if (! is_array($value) || ! array_key_exists($part, $value)) {
                return $default;
            }

            $value = $value[$part];
        }

        return $value;
    }

    /** Of een waarde ingevuld is: niet null, geen lege tekst, geen lege lijst. */
    public function filled(string $key): bool
    {
        $value = $this->get($key);

        return ! ($value === null || (is_string($value) && trim($value) === '') || $value === []);
    }

    public function hasPosition(): bool
    {
        return $this->x !== null && $this->y !== null;
    }

    /** @param  array<string, mixed>  $config */
    public function withConfig(array $config): self
    {
        return new self($this->id, $this->type, $config, $this->x, $this->y);
    }

    public function at(?int $x, ?int $y): self
    {
        return new self($this->id, $this->type, $this->config, $x, $y);
    }

    /** @return array{id: string, type: string, config: array<string, mixed>|object, x: ?int, y: ?int} */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            // Een lege config is een object in json ({}), geen lijst ([]): de editor leest ze als object.
            'config' => $this->config === [] ? (object) [] : $this->config,
            'x' => $this->x,
            'y' => $this->y,
        ];
    }

    /** @return array<string, mixed> */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
