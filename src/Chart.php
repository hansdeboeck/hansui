<?php

declare(strict_types=1);

namespace HansDeBoeck\HansUi;

/**
 * Het rekenwerk achter <x-chart.*>, zodat de views alleen tekenen.
 *
 * MOOIE ASWAARDEN: 0, 250, 500, 750, 1.000 en niet 0, 237, 474. Een as die
 * rare getallen toont, laat de lezer rekenen in plaats van kijken.
 */
final class Chart
{
    /**
     * Een bovengrens en de streepjes op de as.
     *
     * @return array{max: float|int, ticks: array<int, float|int>}
     */
    public static function scale(float $max, int $count = 4): array
    {
        if ($max <= 0) {
            return ['max' => 1, 'ticks' => [0, 1]];
        }

        $raw = $max / $count;
        $magnitude = 10 ** floor(log10($raw));
        $step = 10 * $magnitude;

        foreach ([1, 2, 2.5, 5, 10] as $m) {
            if ($m * $magnitude >= $raw) {
                $step = $m * $magnitude;
                break;
            }
        }

        $top = ceil($max / $step) * $step;

        $ticks = [];
        for ($v = 0; $v <= $top + $step / 2; $v += $step) {
            $ticks[] = $v;
        }

        return ['max' => $top, 'ticks' => $ticks];
    }

    /**
     * Het tekstje op een as: kort, met een punt voor duizendtallen.
     */
    public static function tick(float $value): string
    {
        return match (true) {
            $value >= 1_000_000 => rtrim(rtrim(number_format($value / 1_000_000, 1, ',', '.'), '0'), ',').' M',
            $value >= 10_000 => rtrim(rtrim(number_format($value / 1_000, 1, ',', '.'), '0'), ',').' k',
            default => number_format($value, $value < 10 && floor($value) != $value ? 1 : 0, ',', '.'),
        };
    }

    /**
     * De reekskleur voor een entiteit: vast per id, nooit per rang.
     *
     * Wie de kleur uit de volgorde in de lijst haalt, verft een kanaal anders
     * zodra een filter er een ander weghaalt. Een negende reeks krijgt geen
     * negende kleur maar weer de eerste; wie er meer dan acht naast elkaar
     * toont, hoort ze eerder te groeperen dan te kleuren.
     */
    public static function series(int $id): string
    {
        return 'var(--series-'.((($id - 1) % 8 + 8) % 8 + 1).')';
    }
}
