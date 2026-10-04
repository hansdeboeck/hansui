<?php

declare(strict_types=1);

namespace HansDeBoeck\HansUi\Flows;

use InvalidArgumentException;
use JsonSerializable;

/*
| Een soort stap: wat de editor toont (naam, icoon, kleur, uitleg) en wat de
| graaf mag (welke uitgangen, of het de start is, hoe vaak ze mag voorkomen).
|
| UITGANGEN ZIJN NAMEN, geen nummers: een voorwaarde heeft `yes` en `no`, een
| gewone stap `out`. Een verbinding onthoudt de naam, zodat een uitgang die
| erbij komt (een derde tak) geen bestaande verbinding verschuift. Een stap
| zonder uitgangen is een einde.
|
| DE KLEUR IS EEN ROL (neutral, info, ok, warn, danger, brand) en geen kleur:
| ze ligt op de tokens van HansUI en kantelt mee met het thema.
|
| WAT VERPLICHT IS, staat hier met een naam voor de melding: `['title' =>
| 'Titel']`. De editor leest hetzelfde uit het formulier (`required`); de
| server controleert het opnieuw, want wat in een browser gebeurt, is een
| voorstel.
*/
final class NodeType implements JsonSerializable
{
    public const TONES = ['neutral', 'info', 'ok', 'warn', 'danger', 'brand'];

    /** @var array<string, string> uitgang => label ('' voor de enige uitgang zonder naam) */
    public readonly array $outputs;

    /** @var array<string, string> sleutel in de config => naam voor de melding */
    public readonly array $required;

    /**
     * @param  array<string, string>|list<string>  $outputs
     * @param  array<string, string>|list<string>  $required
     * @param  array<string, mixed>  $defaults  de config van een nieuwe stap
     */
    public function __construct(
        public readonly string $key,
        public readonly string $label,
        public readonly string $icon = 'bolt',
        public readonly string $tone = 'neutral',
        array $outputs = ['out' => ''],
        public readonly bool $start = false,
        public readonly string $group = '',
        public readonly string $hint = '',
        array $required = [],
        public readonly ?int $max = null,
        public readonly array $defaults = [],
    ) {
        if (! preg_match('/^[a-z][a-z0-9_.-]{0,39}$/', $key)) {
            throw new InvalidArgumentException(sprintf('"%s" is not a valid node type key: use lower case letters, digits, "_", "." or "-".', $key));
        }

        if (! in_array($tone, self::TONES, true)) {
            throw new InvalidArgumentException(sprintf('"%s" is not a tone; use one of %s.', $tone, implode(', ', self::TONES)));
        }

        if ($max !== null && $max < 1) {
            throw new InvalidArgumentException(sprintf('The maximum of node type "%s" must be at least 1.', $key));
        }

        $this->outputs = self::named($outputs, '');
        $this->required = self::named($required, null);

        foreach (array_keys($this->outputs) as $port) {
            if (! preg_match('/^[a-z][a-z0-9_-]{0,19}$/', $port)) {
                throw new InvalidArgumentException(sprintf('"%s" is not a valid output of node type "%s".', $port, $key));
            }
        }
    }

    public function hasOutput(string $port): bool
    {
        return array_key_exists($port, $this->outputs);
    }

    /** De eerste uitgang: waar een nieuwe stap vanzelf aan hangt. */
    public function firstOutput(): ?string
    {
        return array_key_first($this->outputs);
    }

    public function isEnd(): bool
    {
        return $this->outputs === [];
    }

    /** @return array<string, mixed> wat de editor van deze soort kent */
    public function toArray(): array
    {
        return [
            'key' => $this->key,
            'label' => $this->label,
            'icon' => $this->icon,
            'tone' => $this->tone,
            'outputs' => array_map(fn (string $port, string $label) => ['key' => $port, 'label' => $label], array_keys($this->outputs), $this->outputs),
            'start' => $this->start,
            'group' => $this->group,
            'hint' => $this->hint,
            'max' => $this->start ? 1 : $this->max,
            'defaults' => (object) $this->defaults,
        ];
    }

    /** @return array<string, mixed> */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * Een lijst van namen, of namen met een label: `['yes', 'no']` en
     * `['yes' => 'Ja', 'no' => 'Nee']` zijn allebei goed.
     *
     * @param  array<int|string, string>  $items
     * @return array<string, string>
     */
    private static function named(array $items, ?string $label): array
    {
        $named = [];

        foreach ($items as $key => $value) {
            if (is_int($key)) {
                $named[$value] = $label ?? $value;
            } else {
                $named[$key] = $value;
            }
        }

        return $named;
    }
}
