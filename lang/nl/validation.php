<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Validatiemeldingen
|--------------------------------------------------------------------------
|
| Zonder dit bestand toont ELK formulier in de applicatie de rauwe sleutel:
| "validation.required" in plaats van "Vul dit veld in." De applicatie draait op
| APP_LOCALE=nl met APP_FALLBACK_LOCALE=nl, dus er is geen Engelse terugval die
| dat verbergt -- de sleutel zelf komt op het scherm.
|
| De rest van de interface werkt met Nederlandse sleutels in __(), waardoor een
| ontbrekende vertaling gewoon de Nederlandse zin oplevert. Voor de regels van
| het validatiepakket gaat dat niet op: die sleutels zijn Engels en gestructureerd
| (validation.max.string), en moeten hier vertaald worden.
|
| InterfaceLanguageTest bewaakt dat dit bestand elke sleutel uit lang/en dekt.
|
*/

return [
    'accepted' => 'Dit veld moet aanvaard worden.',
    'accepted_if' => 'Dit veld moet aanvaard worden wanneer :other gelijk is aan :value.',
    'active_url' => 'Dit is geen geldige URL.',
    'after' => 'Dit moet een datum na :date zijn.',
    'after_or_equal' => 'Dit moet een datum op of na :date zijn.',
    'alpha' => 'Dit veld mag alleen letters bevatten.',
    'alpha_dash' => 'Dit veld mag alleen letters, cijfers, streepjes en liggende streepjes bevatten.',
    'alpha_num' => 'Dit veld mag alleen letters en cijfers bevatten.',
    'any_of' => 'Dit veld is ongeldig.',
    'array' => 'Dit veld moet een reeks waarden zijn.',
    'array_keys' => 'Dit veld mist een of meer verplichte sleutels: :values.',
    'ascii' => 'Dit veld mag alleen gewone letters, cijfers en leestekens bevatten.',
    'base64' => 'Dit veld moet geldige base64 bevatten.',
    'before' => 'Dit moet een datum voor :date zijn.',
    'before_or_equal' => 'Dit moet een datum op of voor :date zijn.',
    'between' => [
        'array' => 'Dit veld moet tussen :min en :max items bevatten.',
        'file' => 'Dit bestand moet tussen :min en :max kilobytes groot zijn.',
        'numeric' => 'Deze waarde moet tussen :min en :max liggen.',
        'string' => 'Dit veld moet tussen :min en :max tekens bevatten.',
    ],
    'boolean' => 'Dit veld moet ja of nee zijn.',
    'can' => 'Dit veld bevat een waarde waar u geen recht op hebt.',
    'confirmed' => 'De bevestiging komt niet overeen.',
    'contains' => 'Dit veld mist een verplichte waarde.',
    'current_password' => 'Het wachtwoord is onjuist.',
    'date' => 'Dit is geen geldige datum.',
    'date_equals' => 'Dit moet de datum :date zijn.',
    'date_format' => 'Dit komt niet overeen met de vorm :format.',
    'decimal' => 'Dit veld moet :decimal cijfers na de komma hebben.',
    'declined' => 'Dit veld moet geweigerd worden.',
    'declined_if' => 'Dit veld moet geweigerd worden wanneer :other gelijk is aan :value.',
    'different' => 'Dit veld en :other moeten verschillen.',
    'digits' => 'Dit veld moet :digits cijfers bevatten.',
    'digits_between' => 'Dit veld moet tussen :min en :max cijfers bevatten.',
    'dimensions' => 'Deze afbeelding heeft ongeldige afmetingen.',
    'distinct' => 'Deze waarde komt dubbel voor.',
    'doesnt_contain' => 'Dit veld mag geen van deze waarden bevatten: :values.',
    'doesnt_end_with' => 'Dit veld mag niet eindigen op: :values.',
    'doesnt_start_with' => 'Dit veld mag niet beginnen met: :values.',
    'email' => 'Dit is geen geldig e-mailadres.',
    'encoding' => 'Dit veld heeft een ongeldige tekencodering.',
    'ends_with' => 'Dit veld moet eindigen op een van: :values.',
    'enum' => 'Deze keuze is ongeldig.',
    'exists' => 'Deze keuze bestaat niet.',
    'extensions' => 'Dit bestand moet een van deze extensies hebben: :values.',
    'file' => 'Dit moet een bestand zijn.',
    'filled' => 'Dit veld mag niet leeg zijn.',
    'gt' => [
        'array' => 'Dit veld moet meer dan :value items bevatten.',
        'file' => 'Dit bestand moet groter zijn dan :value kilobytes.',
        'numeric' => 'Deze waarde moet groter zijn dan :value.',
        'string' => 'Dit veld moet meer dan :value tekens bevatten.',
    ],
    'gte' => [
        'array' => 'Dit veld moet :value items of meer bevatten.',
        'file' => 'Dit bestand moet :value kilobytes of groter zijn.',
        'numeric' => 'Deze waarde moet :value of meer zijn.',
        'string' => 'Dit veld moet :value tekens of meer bevatten.',
    ],
    'hex_color' => 'Dit is geen geldige kleurcode.',
    'image' => 'Dit moet een afbeelding zijn.',
    'in' => 'Deze keuze is ongeldig.',
    'in_array' => 'Deze waarde komt niet voor in :other.',
    'in_array_keys' => 'Dit veld moet ten minste een van deze sleutels bevatten: :values.',
    'integer' => 'Dit veld moet een geheel getal zijn.',
    'ip' => 'Dit is geen geldig IP-adres.',
    'ipv4' => 'Dit is geen geldig IPv4-adres.',
    'ipv6' => 'Dit is geen geldig IPv6-adres.',
    'json' => 'Dit veld moet geldige JSON bevatten.',
    'list' => 'Dit veld moet een lijst zijn.',
    'lowercase' => 'Dit veld mag alleen kleine letters bevatten.',
    'lt' => [
        'array' => 'Dit veld moet minder dan :value items bevatten.',
        'file' => 'Dit bestand moet kleiner zijn dan :value kilobytes.',
        'numeric' => 'Deze waarde moet kleiner zijn dan :value.',
        'string' => 'Dit veld moet minder dan :value tekens bevatten.',
    ],
    'lte' => [
        'array' => 'Dit veld mag niet meer dan :value items bevatten.',
        'file' => 'Dit bestand mag niet groter zijn dan :value kilobytes.',
        'numeric' => 'Deze waarde mag niet groter zijn dan :value.',
        'string' => 'Dit veld mag niet meer dan :value tekens bevatten.',
    ],
    'mac_address' => 'Dit is geen geldig MAC-adres.',
    'max' => [
        'array' => 'Dit veld mag niet meer dan :max items bevatten.',
        'file' => 'Dit bestand mag niet groter zijn dan :max kilobytes.',
        'numeric' => 'Deze waarde mag niet groter zijn dan :max.',
        'string' => 'Dit veld mag niet meer dan :max tekens bevatten.',
    ],
    'max_digits' => 'Dit veld mag niet meer dan :max cijfers bevatten.',
    'mimes' => 'Dit bestand moet van het type :values zijn.',
    'mimetypes' => 'Dit bestand moet van het type :values zijn.',
    'min' => [
        'array' => 'Dit veld moet ten minste :min items bevatten.',
        'file' => 'Dit bestand moet ten minste :min kilobytes groot zijn.',
        'numeric' => 'Deze waarde moet ten minste :min zijn.',
        'string' => 'Dit veld moet ten minste :min tekens bevatten.',
    ],
    'min_digits' => 'Dit veld moet ten minste :min cijfers bevatten.',
    'missing' => 'Dit veld mag niet meegestuurd worden.',
    'missing_if' => 'Dit veld mag niet meegestuurd worden wanneer :other gelijk is aan :value.',
    'missing_unless' => 'Dit veld mag niet meegestuurd worden tenzij :other gelijk is aan :value.',
    'missing_with' => 'Dit veld mag niet meegestuurd worden samen met :values.',
    'missing_with_all' => 'Dit veld mag niet meegestuurd worden samen met :values.',
    'multiple_of' => 'Deze waarde moet een veelvoud van :value zijn.',
    'not_in' => 'Deze keuze is ongeldig.',
    'not_regex' => 'Dit veld heeft een ongeldige vorm.',
    'numeric' => 'Dit veld moet een getal zijn.',
    'password' => [
        'letters' => 'Het wachtwoord moet ten minste een letter bevatten.',
        'mixed' => 'Het wachtwoord moet ten minste een hoofdletter en een kleine letter bevatten.',
        'numbers' => 'Het wachtwoord moet ten minste een cijfer bevatten.',
        'symbols' => 'Het wachtwoord moet ten minste een leesteken bevatten.',
        'uncompromised' => 'Dit wachtwoord komt voor in een bekend datalek. Kies een ander.',
    ],
    'present' => 'Dit veld moet meegestuurd worden.',
    'present_if' => 'Dit veld moet meegestuurd worden wanneer :other gelijk is aan :value.',
    'present_unless' => 'Dit veld moet meegestuurd worden tenzij :other gelijk is aan :value.',
    'present_with' => 'Dit veld moet meegestuurd worden samen met :values.',
    'present_with_all' => 'Dit veld moet meegestuurd worden samen met :values.',
    'prohibited' => 'Dit veld is hier niet toegestaan.',
    'prohibited_if' => 'Dit veld is niet toegestaan wanneer :other gelijk is aan :value.',
    'prohibited_if_accepted' => 'Dit veld is niet toegestaan wanneer :other aanvaard is.',
    'prohibited_if_declined' => 'Dit veld is niet toegestaan wanneer :other geweigerd is.',
    'prohibited_unless' => 'Dit veld is niet toegestaan tenzij :other een van :values is.',
    'prohibits' => 'Dit veld sluit :other uit.',
    'regex' => 'Dit veld heeft een ongeldige vorm.',
    'required' => 'Vul dit veld in.',
    'required_array_keys' => 'Dit veld moet de sleutels :values bevatten.',
    'required_if' => 'Vul dit veld in wanneer :other gelijk is aan :value.',
    'required_if_accepted' => 'Vul dit veld in wanneer :other aanvaard is.',
    'required_if_declined' => 'Vul dit veld in wanneer :other geweigerd is.',
    'required_unless' => 'Vul dit veld in tenzij :other een van :values is.',
    'required_with' => 'Vul dit veld in wanneer :values ingevuld is.',
    'required_with_all' => 'Vul dit veld in wanneer :values ingevuld zijn.',
    'required_without' => 'Vul dit veld in wanneer :values leeg is.',
    'required_without_all' => 'Vul dit veld in wanneer geen van :values ingevuld is.',
    'same' => 'Dit veld en :other moeten gelijk zijn.',
    'size' => [
        'array' => 'Dit veld moet precies :size items bevatten.',
        'file' => 'Dit bestand moet precies :size kilobytes groot zijn.',
        'numeric' => 'Deze waarde moet precies :size zijn.',
        'string' => 'Dit veld moet precies :size tekens bevatten.',
    ],
    'starts_with' => 'Dit veld moet beginnen met een van: :values.',
    'string' => 'Dit veld moet tekst zijn.',
    'timezone' => 'Dit is geen geldige tijdzone.',
    'ulid' => 'Dit is geen geldige ULID.',
    'unique' => 'Deze waarde is al in gebruik.',
    'uploaded' => 'Het opladen van dit bestand is mislukt.',
    'uppercase' => 'Dit veld mag alleen hoofdletters bevatten.',
    'url' => 'Dit is geen geldige URL.',
    'uuid' => 'Dit is geen geldige UUID.',

    /*
    |--------------------------------------------------------------------------
    | Eigen meldingen per veld
    |--------------------------------------------------------------------------
    |
    | Voor het geval een regel op een bepaald veld iets anders moet zeggen dan de
    | algemene formulering hierboven.
    |
    */

    'custom' => [
        'reason' => [
            'required' => 'Een reden is verplicht. Ze wordt mee bewaard in de historiek.',
        ],
        'password' => [
            'confirmed' => 'De twee wachtwoorden komen niet overeen.',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Veldnamen
    |--------------------------------------------------------------------------
    |
    | Vervangt :attribute. Zonder deze lijst staat er "Vul first_name in" op het
    | scherm; de kolomnaam is Engels omdat de CODE Engels is, maar de gebruiker
    | leest Nederlands.
    |
    */

    'attributes' => [
        'first_name' => 'voornaam',
        'last_name' => 'achternaam',
        'name' => 'naam',
        'email' => 'e-mailadres',
        'password' => 'wachtwoord',
        'phone' => 'telefoonnummer',
        'date_of_birth' => 'geboortedatum',
        'employee_number' => 'personeelsnummer',
        'started_on' => 'datum in dienst',
        'ended_on' => 'datum uit dienst',
        'status' => 'status',
        'contract_type' => 'contracttype',
        'fte' => 'voltijds equivalent',
        'org_unit_id' => 'eenheid',
        'coach_id' => 'begeleider',
        'mentor_id' => 'peter of meter',
        'competency_id' => 'competentie',
        'scale_id' => 'schaal',
        'level_value' => 'niveau',
        'measured_on' => 'meetdatum',
        'conversation_type_id' => 'gesprekstype',
        'planned_for' => 'gepland op',
        'due_on' => 'vervaldatum',
        'held_on' => 'gehouden op',
        'summary' => 'verslag',
        'title' => 'titel',
        'owner_id' => 'verantwoordelijke',
        'quantity' => 'aantal',
        'unit_price_cents' => 'eenheidsprijs',
        'supplier_id' => 'leverancier',
        'asset_type_id' => 'soort middel',
        'serial_number' => 'serienummer',
        'issued_on' => 'uitgegeven op',
        'expires_on' => 'vervalt op',
        'valid_from' => 'geldig vanaf',
        'reason' => 'reden',
        'reason_category' => 'soort reden',
        'months' => 'aantal maanden',
        'percentage' => 'percentage',
        'amount_cents' => 'bedrag',
        'starts_on' => 'begint op',
        'code' => 'code',
        'description' => 'omschrijving',
        'slug' => 'sleutel',
        'domain' => 'domein',
        'locale' => 'taal',
        'timezone' => 'tijdzone',
    ],
];
