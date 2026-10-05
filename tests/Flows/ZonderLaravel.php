<?php

declare(strict_types=1);

namespace HansDeBoeck\HansUi\Tests\Flows;

use Illuminate\Container\Container;
use PHPUnit\Framework\TestCase;

/**
 * De graaf, het schikken en de walker, zonder applicatie.
 *
 * Zo gebruikt een applicatie zonder Laravel ze, en zo komt een zin er in het
 * Nederlands uit. De container gaat leeg voor elke test: een testapplicatie
 * van een vorige test liet er anders haar vertaler in staan, en dan hing de
 * taal van een issue af van de volgorde van de tests.
 */
abstract class ZonderLaravel extends TestCase
{
    use MaaktFlows;

    protected function setUp(): void
    {
        parent::setUp();

        Container::setInstance(null);
    }
}
