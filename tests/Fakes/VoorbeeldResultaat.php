<?php

declare(strict_types=1);

namespace HansDeBoeck\HansUi\Tests\Fakes;

use HansDeBoeck\HansUi\Contracts\BulkResult;

/**
 * Een BulkResult om <x-result> mee te kunnen tekenen.
 *
 * Hij zit hier en niet in het package: het contract bestaat juist zodat er in
 * src/ geen concrete klasse hoeft te staan.
 */
final class VoorbeeldResultaat implements BulkResult
{
    /**
     * @param  array<string, string>  $mislukt
     */
    public function __construct(
        private readonly int $gelukt = 3,
        private readonly array $mislukt = ['Nina Bodart' => 'geen maat bekend'],
    ) {}

    public function doneCount(): int
    {
        return $this->gelukt;
    }

    /**
     * @return array<string, string>
     */
    public function failures(): array
    {
        return $this->mislukt;
    }

    public function hasFailures(): bool
    {
        return $this->mislukt !== [];
    }
}
