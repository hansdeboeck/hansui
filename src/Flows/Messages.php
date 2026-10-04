<?php

declare(strict_types=1);

namespace HansDeBoeck\HansUi\Flows;

/*
| De zinnen die een gebruiker te zien krijgt (een issue, een label van de
| editor), in het Nederlands als bron, zoals in HansUI.
|
| Binnen Laravel gaan ze door de vertaler (`__()`), zodat lang/fr.json ze
| vertaalt; zonder Laravel komen ze er in het Nederlands uit, met :naam
| ingevuld.
*/
final class Messages
{
    /** @param  array<string, string|int>  $replace */
    public static function get(string $text, array $replace = []): string
    {
        if (function_exists('__') && function_exists('app') && app()->bound('translator')) {
            $translated = __($text, $replace);

            return is_string($translated) ? $translated : $text;
        }

        $pairs = [];

        foreach ($replace as $key => $value) {
            $pairs[':'.$key] = (string) $value;
        }

        // strtr met een lijst neemt de langste sleutel eerst: :naam vervangt :naamgeving niet half.
        return strtr($text, $pairs);
    }
}
