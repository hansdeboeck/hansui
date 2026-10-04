<?php

declare(strict_types=1);

namespace HansDeBoeck\HansUi\Flows;

/*
| Een flow uitvoeren, stap voor stap.
|
| DE WALKER KENT GEEN STAPPEN. Wat een taak maken of een mail sturen is, weet
| de applicatie: ze geeft een functie die per stap iets doet en een Step
| teruggeeft (verder, wachten, stoppen, mislukken). De walker volgt de
| verbindingen, telt, en zegt waar het eindigde (Walk).
|
|   $walk = (new Walker($graph))->resume($graph->start()->id, 'out', $handler);
|   if ($walk->waiting()) { bewaar $walk->node, $walk->port en $walk->until }
|
| Wat de functie gooit, gaat door naar wie de walker riep: die beslist of
| het een fout van de run is of van de applicatie.
*/
final class Walker
{
    public function __construct(
        private readonly Graph $graph,
        private readonly int $maxSteps = Graph::MAX_NODES,
    ) {}

    /** @param  callable(Node, list<string>): Step  $handler */
    public function from(string $id, callable $handler): Walk
    {
        $current = $this->graph->node($id);

        if ($current === null) {
            return new Walk(Walk::FAILED, [], $id, reason: Messages::get('Deze stap bestaat niet meer.'));
        }

        $visited = [];

        while ($current !== null) {
            if (count($visited) >= $this->maxSteps) {
                return new Walk(Walk::LIMIT, $visited, $current->id, reason: Messages::get('Meer dan :aantal stappen na elkaar.', ['aantal' => $this->maxSteps]));
            }

            $visited[] = $current->id;
            $step = $handler($current, $visited);

            if ($step->kind === Step::WAIT) {
                return new Walk(Walk::WAITING, $visited, $current->id, $step->port, $step->until, $step->state);
            }

            if ($step->kind === Step::STOP) {
                return new Walk(Walk::FINISHED, $visited, $current->id);
            }

            if ($step->kind === Step::FAIL) {
                return new Walk(Walk::FAILED, $visited, $current->id, reason: $step->reason);
            }

            $type = $this->graph->types()->require($current->type);
            $port = $step->port ?? $type->firstOutput($current->config);

            if ($port !== null && ! $type->hasOutput($port, $current->config)) {
                return new Walk(Walk::FAILED, $visited, $current->id, reason: sprintf('Node "%s" has no output "%s".', $current->id, $port));
            }

            $last = $current->id;
            $current = $port === null ? null : $this->graph->next($current->id, $port);
        }

        return new Walk(Walk::FINISHED, $visited, $last ?? null);
    }

    /**
     * Verder na een stap die wachtte (of na de start): langs haar uitgang
     * naar de volgende. Is er geen volgende, dan is de flow af.
     *
     * @param  callable(Node, list<string>): Step  $handler
     */
    public function resume(string $id, ?string $port, callable $handler): Walk
    {
        if (! $this->graph->has($id)) {
            return new Walk(Walk::FAILED, [], $id, reason: Messages::get('Deze stap bestaat niet meer.'));
        }

        $next = $this->graph->next($id, $port);

        return $next === null ? new Walk(Walk::FINISHED, [], $id) : $this->from($next->id, $handler);
    }
}
