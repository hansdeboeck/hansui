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
|
| TAKKEN UIT DE CONFIG (`branches`): een splitsing op de waarde van een veld
| heeft een uitgang per gekozen waarde, en die staan in haar config, niet in
| de soort. `branches: 'values'` maakt van elke waarde in config['values']
| een uitgang, voor de vaste (zoals `other`). Hoogstens MAX_BRANCHES, zodat
| een stap met al haar uitgangen nog in een rij past; een waarde die geen
| geldige naam voor een uitgang is, telt niet. Wie de uitgangen van een stap
| wil kennen, vraagt ze dus met haar config: outputsFor().
*/
final class NodeType implements JsonSerializable
{
    public const TONES = ['neutral', 'info', 'ok', 'warn', 'danger', 'brand'];

    /** Hoeveel uitgangen een stap hoogstens uit haar config haalt (zie `branches`). */
    public const MAX_BRANCHES = 4;

    private const PORT = '/^[a-z][a-z0-9_-]{0,19}$/';

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
        public readonly ?string $branches = null,
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

        if ($branches !== null && ! preg_match('/^[a-z][a-z0-9_]{0,39}$/', $branches)) {
            throw new InvalidArgumentException(sprintf('"%s" is not a valid config key for the branches of node type "%s".', $branches, $key));
        }

        $this->outputs = self::named($outputs, '');
        $this->required = self::named($required, null);

        foreach (array_keys($this->outputs) as $port) {
            if (! preg_match(self::PORT, $port)) {
                throw new InvalidArgumentException(sprintf('"%s" is not a valid output of node type "%s".', $port, $key));
            }
        }
    }

    /**
     * De uitgangen van een stap van deze soort: eerst die uit haar config
     * (branches), dan de vaste. Een uitgang uit de config heeft geen label;
     * de editor leest het uit het formulier.
     *
     * @param  array<string, mixed>  $config
     * @return array<string, string>
     */
    public function outputsFor(array $config = []): array
    {
        if ($this->branches === null) {
            return $this->outputs;
        }

        $branches = [];

        foreach ((array) ($config[$this->branches] ?? []) as $value) {
            if (count($branches) < self::MAX_BRANCHES && is_string($value) && preg_match(self::PORT, $value) && ! isset($this->outputs[$value])) {
                $branches[$value] = '';
            }
        }

        return $branches + $this->outputs;
    }

    /** @param  array<string, mixed>  $config  de config van de stap, voor een soort met takken */
    public function hasOutput(string $port, array $config = []): bool
    {
        return array_key_exists($port, $this->outputsFor($config));
    }

    /**
     * De eerste uitgang: waar een nieuwe stap vanzelf aan hangt.
     *
     * @param  array<string, mixed>  $config
     */
    public function firstOutput(array $config = []): ?string
    {
        return array_key_first($this->outputsFor($config));
    }

    /** @param  array<string, mixed>  $config */
    public function isEnd(array $config = []): bool
    {
        return $this->outputsFor($config) === [];
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
            'branches' => $this->branches,
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
