<?php

declare(strict_types=1);

namespace HansDeBoeck\HansUi\Flows;

use JsonSerializable;

/*
| Een notitie op het canvas: tekst bij de flow, voor wie ze later leest
| ("hier wachten we op de boekhouding"), zonder dat ze iets doet.
|
| GEEN STAP. Een notitie hangt nergens aan, heeft geen soort en telt niet
| mee voor de wandeling of de controles: ze staat in de json naast de stappen
| (`stickies`), en een applicatie die haar stappen als rijen bewaart, bewaart
| ze apart.
*/
final class Sticky implements JsonSerializable
{
    /** Hoe lang de tekst van een notitie hoogstens is, in tekens. */
    public const MAX_TEXT = 1000;

    public function __construct(
        public readonly string $id,
        public readonly string $text,
        public readonly ?int $x = null,
        public readonly ?int $y = null,
    ) {}

    /** @return array{id: string, text: string, x: ?int, y: ?int} */
    public function toArray(): array
    {
        return ['id' => $this->id, 'text' => $this->text, 'x' => $this->x, 'y' => $this->y];
    }

    /** @return array<string, mixed> */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
