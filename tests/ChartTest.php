<?php

declare(strict_types=1);

namespace HansDeBoeck\HansUi\Tests;

use HansDeBoeck\HansUi\Chart;
use PHPUnit\Framework\Attributes\Test;

final class ChartTest extends TestCase
{
    #[Test]
    public function de_as_krijgt_mooie_waarden(): void
    {
        $this->assertSame(['max' => 1000.0, 'ticks' => [0, 250.0, 500.0, 750.0, 1000.0]], Chart::scale(937));
        $this->assertSame(['max' => 1, 'ticks' => [0, 1]], Chart::scale(0));
    }

    #[Test]
    public function een_tick_is_kort(): void
    {
        $this->assertSame('1.250', Chart::tick(1250));
        $this->assertSame('12,5 k', Chart::tick(12500));
        $this->assertSame('2 M', Chart::tick(2_000_000));
        $this->assertSame('0,5', Chart::tick(0.5));
    }

    #[Test]
    public function een_reeks_houdt_haar_kleur_per_id(): void
    {
        $this->assertSame('var(--series-1)', Chart::series(1));
        $this->assertSame('var(--series-8)', Chart::series(8));
        $this->assertSame('var(--series-1)', Chart::series(9));
    }
}
