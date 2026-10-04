<?php

declare(strict_types=1);

namespace HansDeBoeck\HansUi\Flows;

/*
| Een flow netjes schikken, van links naar rechts.
|
| DE KOLOM is de langste weg vanaf het begin: een stap staat rechts van alles
| wat ervoor komt, ook waar twee takken samenkomen.
|
| DE RIJ volgt de eerste uitgang: wie een stap verder gaat langs `out` of
| `yes`, blijft op dezelfde hoogte, en een tweede uitgang (`no`) begint een
| rij lager. Zo is de hoofdweg een rechte lijn, en hangt elke zijweg eronder.
| Wat nergens aan hangt, komt onderaan.
|
| resources/js/flows/graph.js rekent precies hetzelfde (layout()), zodat Schikken in
| de browser en een flow die uit code komt, er hetzelfde uitzien;
| tests/fixtures/flows-layout.json houdt beide gelijk.
*/
final class Layout
{
    /** De breedte van een kolom: een stap (240) en de ruimte voor de verbinding. */
    public const COLUMN = 320;

    /** De hoogte van een rij: een stap (76) en lucht. */
    public const ROW = 150;

    /** @return array<string, array{0: int, 1: int}> id => [x, y] */
    public static function positions(Graph $graph): array
    {
        $nodes = $graph->nodes();

        if ($nodes === []) {
            return [];
        }

        $columns = self::columns($graph);
        $rows = self::rows($graph);
        $positions = [];

        foreach (array_keys($nodes) as $id) {
            $positions[$id] = [$columns[$id] * self::COLUMN, $rows[$id] * self::ROW];
        }

        return $positions;
    }

    /**
     * De langste weg naar elke stap, in de volgorde van Kahn: wat niets
     * meer voor zich heeft, komt aan de beurt, in de volgorde van de graaf.
     *
     * @return array<string, int>
     */
    private static function columns(Graph $graph): array
    {
        $indegree = [];
        $columns = [];

        foreach ($graph->nodes() as $node) {
            $indegree[$node->id] = count($graph->incoming($node->id));
            $columns[$node->id] = 0;
        }

        // Een id als "12" wordt als sleutel een getal: de wachtrij krijgt weer ids.
        $queue = array_map('strval', array_keys(array_filter($indegree, fn (int $count) => $count === 0)));

        while ($queue !== []) {
            $id = array_shift($queue);

            foreach ($graph->outgoing($id) as $edge) {
                $columns[$edge->to] = max($columns[$edge->to], $columns[$id] + 1);

                if (--$indegree[$edge->to] === 0) {
                    $queue[] = $edge->to;
                }
            }
        }

        return $columns;
    }

    /** @return array<string, int> */
    private static function rows(Graph $graph): array
    {
        $roots = [];
        $start = $graph->start()?->id;

        if ($start !== null) {
            $roots[] = $start;
        }

        foreach ($graph->nodes() as $node) {
            if ($node->id !== $start && $graph->incoming($node->id) === []) {
                $roots[] = $node->id;
            }
        }

        $rows = [];
        $next = 0;

        $visit = function (string $id) use (&$visit, &$rows, &$next, $graph): void {
            $rows[$id] = -1;
            $row = null;

            foreach ($graph->outgoing($id) as $edge) {
                if (! isset($rows[$edge->to])) {
                    $visit($edge->to);
                    $row ??= $rows[$edge->to];
                }
            }

            $rows[$id] = $row ?? $next++;
        };

        foreach ($roots as $root) {
            if (! isset($rows[$root])) {
                $visit($root);
            }
        }

        return $rows;
    }
}
