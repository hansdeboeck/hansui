<?php

declare(strict_types=1);

namespace HansDeBoeck\HansUi\Flows;

use InvalidArgumentException;

/*
| Een flow die niet gelezen kan worden: een soort die niet bestaat, een
| verbinding naar een stap die er niet is, een lus, twee starts.
|
| De editor maakt zoiets niet; wie het toch stuurt, knutselde aan het
| verzoek. De boodschap is voor een ontwikkelaar (Engels, met het id erbij);
| een gebruiker krijgt de zin van de validatieregel.
*/
final class InvalidGraph extends InvalidArgumentException {}
