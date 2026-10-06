<?php

declare(strict_types=1);

namespace HansDeBoeck\HansUi\Flows;

use JsonSerializable;

/*
| Iets dat nog niet klopt aan een stap, maar de flow niet ongeldig maakt: een
| titel die nog leeg is, een stap die nergens aan hangt. Een flow met een
| issue mag bewaard worden (een half werk is ook werk), maar niet aan.
*/
final class Issue implements JsonSerializable
{
    public function __construct(
        public readonly string $node,
        public readonly string $message,
    ) {}

    /** @return array{node: string, message: string} */
    public function toArray(): array
    {
        return ['node' => $this->node, 'message' => $this->message];
    }

    /** @return array{node: string, message: string} */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
