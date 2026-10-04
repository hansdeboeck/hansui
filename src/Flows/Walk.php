<?php

declare(strict_types=1);

namespace HansDeBoeck\HansUi\Flows;

use DateTimeImmutable;

/*
| Hoe een wandeling door een flow afliep (zie Walker).
|
|   finished  het einde gehaald, of een stap zei stop
|   waiting   wacht op `node`, tot `until` (of tot de applicatie het zegt), en
|             gaat daarna verder langs `port`
|   failed    een stap mislukte, met de reden
|   limit     meer stappen dan toegelaten: een vangnet, want een flow heeft
|             geen lussen
|
| `visited` zijn de stappen die in deze wandeling uitgevoerd werden, in
| volgorde: voor een logboek van de run.
*/
final class Walk
{
    public const FINISHED = 'finished';

    public const WAITING = 'waiting';

    public const FAILED = 'failed';

    public const LIMIT = 'limit';

    /**
     * @param  list<string>  $visited
     * @param  array<string, mixed>  $state
     */
    public function __construct(
        public readonly string $status,
        public readonly array $visited = [],
        public readonly ?string $node = null,
        public readonly ?string $port = null,
        public readonly ?DateTimeImmutable $until = null,
        public readonly array $state = [],
        public readonly ?string $reason = null,
    ) {}

    public function finished(): bool
    {
        return $this->status === self::FINISHED;
    }

    public function waiting(): bool
    {
        return $this->status === self::WAITING;
    }

    public function failed(): bool
    {
        return $this->status === self::FAILED || $this->status === self::LIMIT;
    }
}
