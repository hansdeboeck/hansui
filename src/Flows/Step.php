<?php

declare(strict_types=1);

namespace HansDeBoeck\HansUi\Flows;

use DateTimeImmutable;
use DateTimeInterface;

/*
| Wat een stap zegt als ze uitgevoerd is (zie Walker): verder langs een
| uitgang, wachten, stoppen of mislukken.
|
| WACHTEN is stoppen met een bladwijzer: de walker geeft terug waar het
| wacht, tot wanneer, en langs welke uitgang het daarna verdergaat. De
| applicatie bewaart dat bij haar run en roept later resume(). Wachten zonder
| tijdstip is wachten op iets (een taak die af moet); $state is wat de
| applicatie daarvoor wil onthouden, zoals het id van die taak.
*/
final class Step
{
    public const NEXT = 'next';

    public const WAIT = 'wait';

    public const STOP = 'stop';

    public const FAIL = 'fail';

    /** @param  array<string, mixed>  $state */
    private function __construct(
        public readonly string $kind,
        public readonly ?string $port = null,
        public readonly ?DateTimeImmutable $until = null,
        public readonly array $state = [],
        public readonly ?string $reason = null,
    ) {}

    /** Verder langs een uitgang; zonder naam de eerste van de soort. */
    public static function next(?string $port = null): self
    {
        return new self(self::NEXT, $port);
    }

    /** @param  array<string, mixed>  $state */
    public static function wait(?DateTimeInterface $until = null, ?string $port = null, array $state = []): self
    {
        return new self(self::WAIT, $port, $until === null ? null : DateTimeImmutable::createFromInterface($until), $state);
    }

    /** De flow eindigt hier, met opzet. */
    public static function stop(): self
    {
        return new self(self::STOP);
    }

    public static function fail(string $reason): self
    {
        return new self(self::FAIL, reason: $reason);
    }
}
