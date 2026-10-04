<?php

declare(strict_types=1);

namespace HansDeBoeck\HansUi\Flows;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/*
| Een veld met een flow (json, zoals de editor het verstuurt) nakijken.
|
|   'flow' => ['required', new ValidFlow($types)]
|   'flow' => ['required', new ValidFlow($types, complete: $request->boolean('active'))]
|
| Ongeldig (een soort die niet bestaat, een lus) is altijd een fout. Met
| `complete` mag er ook niets meer open staan (issues()): een flow die aan
| staat, moet af zijn. $extra zijn de eigen controles van de applicatie, zoals
| bij Graph::issues().
*/
final class ValidFlow implements ValidationRule
{
    /** @param  (Closure(Node, Graph): iterable<string>)|null  $extra */
    public function __construct(
        private readonly NodeTypes $types,
        private readonly bool $complete = false,
        private readonly ?Closure $extra = null,
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        try {
            $graph = is_array($value) ? Graph::fromArray($value, $this->types) : Graph::fromJson(is_string($value) ? $value : null, $this->types);
        } catch (InvalidGraph) {
            $fail(Messages::get('De flow kon niet gelezen worden. Laad de pagina opnieuw en probeer het nog eens.'));

            return;
        }

        if (! $this->complete) {
            return;
        }

        $count = count(array_unique(array_map(fn ($issue) => $issue->node, $graph->issues($this->extra))));

        if ($count > 0) {
            $fail($count === 1
                ? Messages::get('Een stap vraagt nog aandacht: vul ze aan, of bewaar de flow uitgeschakeld.')
                : Messages::get(':aantal stappen vragen nog aandacht: vul ze aan, of bewaar de flow uitgeschakeld.', ['aantal' => $count]));
        }
    }
}
