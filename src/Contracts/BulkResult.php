<?php

declare(strict_types=1);

namespace HansDeBoeck\HansUi\Contracts;

/**
 * De uitkomst van een handeling op meerdere records.
 *
 * EEN GEDEELTELIJK RESULTAAT IS HET NORMALE GEVAL, geen uitzondering. Geef je
 * tien mensen handschoenen en zijn er zes op voorraad, dan is zes uitgeven en
 * vier melden beter dan alles terugdraaien -- dan moet de gebruiker het opnieuw
 * doen -- en veel beter dan zwijgen, want dan denkt hij dat het gelukt is.
 *
 * Dit contract bestaat omdat <x-result> anders aan een concrete klasse van een
 * applicatie zou hangen. Een package dat App\Services\...\BulkResult typehint,
 * is geen package.
 */
interface BulkResult
{
    /**
     * Hoeveel er gelukt zijn.
     */
    public function doneCount(): int;

    /**
     * Wat er niet gelukt is: naam => reden.
     *
     * De REDEN hoort erbij en niet alleen het aantal. "Vier mislukt" laat de
     * gebruiker zoeken; "Nina Bodart: geen maat bekend" laat hem handelen.
     *
     * @return array<string, string>
     */
    public function failures(): array;

    public function hasFailures(): bool;
}
