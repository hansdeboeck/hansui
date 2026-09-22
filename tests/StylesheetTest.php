<?php

declare(strict_types=1);

namespace HansDeBoeck\HansUi\Tests;

use PHPUnit\Framework\Attributes\Test;

/**
 * Wat er in hansui.css niet stilletjes uit de pas mag lopen.
 */
final class StylesheetTest extends TestCase
{
    private function css(): string
    {
        return (string) file_get_contents($this->pakket('resources/css/hansui.css'));
    }

    /**
     * De inhoud van een blok, vanaf een selector tot het bijhorende sluithaakje.
     */
    private function blok(string $css, string $selector): string
    {
        $begin = strpos($css, $selector.' {');
        $this->assertNotFalse($begin, "Selector niet gevonden: {$selector}");

        $i = $begin + strlen($selector) + 2;
        $diepte = 1;

        for ($n = strlen($css); $i < $n && $diepte > 0; $i++) {
            $diepte += match ($css[$i]) {
                '{' => 1, '}' => -1, default => 0
            };
        }

        return substr($css, $begin, $i - $begin);
    }

    /**
     * @return array<string, string>
     */
    private function tokens(string $blok): array
    {
        preg_match_all('/(--[\w-]+)\s*:\s*([^;]+);/', $blok, $m, PREG_SET_ORDER);

        $uit = [];
        foreach ($m as [, $naam, $waarde]) {
            $uit[$naam] = preg_replace('/\s+/', ' ', trim($waarde));
        }

        ksort($uit);

        return $uit;
    }

    #[Test]
    public function de_twee_donkere_paletten_zijn_gelijk(): void
    {
        /*
        | Het donkere palet staat twee keer: een media-query voor wie niets
        | kiest, en een attribuutselector voor wie wel kiest. Die twee kunnen
        | geen regel delen, dus ze moeten samen wijzigen -- en dit is de test
        | die dat afdwingt in plaats van erop te hopen.
        */
        $css = $this->css();

        $viaSysteem = $this->tokens($this->blok($css, ":root:not([data-theme='light'])"));
        $viaKeuze = $this->tokens($this->blok($css, ":root[data-theme='dark']"));

        $this->assertNotEmpty($viaSysteem);
        $this->assertSame($viaSysteem, $viaKeuze, 'De twee donkere blokken lopen uit elkaar.');
    }

    #[Test]
    public function elk_gebruikt_token_bestaat_ook(): void
    {
        // var(--x, terugval) mag naar iets onbestaands wijzen -- dat is wat de
        // terugval doet. var(--x) zonder terugval niet: dat wordt leeg.
        $css = $this->css();

        preg_match_all('/(--[\w-]+)\s*:/', $css, $gedefinieerd);
        preg_match_all('/var\((--[\w-]+)\)/', $css, $gebruikt);

        $ontbreekt = array_diff(array_unique($gebruikt[1]), $gedefinieerd[1]);

        $this->assertSame([], array_values($ontbreekt), 'Deze tokens worden gebruikt maar nergens gezet.');
    }

    #[Test]
    public function geen_token_staat_er_zonder_gebruiker(): void
    {
        /*
        | Een token dat nergens gebruikt wordt, staat in beide themablokken en
        | is dus twee plaatsen om iets verkeerd te onderhouden.
        |
        | MAAR: de maat is generiek-of-niet, niet het aantal gebruikers. De
        | ladders en de rollen hieronder zijn de PUBLIEKE woordenschat van dit
        | package en horen compleet te zijn, ook waar het er zelf nog niet
        | alles van gebruikt -- --shadow-md is daar het voorbeeld van. Wat deze
        | test vangt is het andere geval: een token dat er terloops bij kwam,
        | in geen van beide ladders past, en dat niemand aanroept.
        |
        | De drie curves horen daar ook bij. --ease-out en --ease-drawer worden
        | hier wel gebruikt, --ease-in-out nog niet -- maar ze liggen op
        | Tailwinds eigen namen, dus een applicatie die `ease-in-out` typt
        | krijgt hem. Een familie van drie waarvan er een ontbreekt, is precies
        | het gat waar de grijsschaal hierboven ook door misging.
        */
        $publiek = '/^--(n-\d+|paper|surface|surface-hover|surface-sunk|line|line-strong'
            .'|ink|ink-soft|ink-faint|nav|nav-ink|nav-high|brand|brand-primary|brand-primary-dark'
            .'|brand-deep|brand-soft|brand-line|on-brand|ok|ok-soft|danger|danger-soft'
            .'|warn|warn-soft|info|info-soft|row-y|font-sans|shadow-(sm|md|lg)'
            .'|ease-(out|in-out|drawer)|color-.*'
            // De grafiekkleuren staan in inline stijlen van de views
            // (style="fill: var(--series-3)"), niet in dit stijlblad.
            .'|series-\d|seq-\d)$/';

        $css = $this->css();

        preg_match_all('/^\s*(--[\w-]+)\s*:/m', $css, $gedefinieerd);
        preg_match_all('/var\((--[\w-]+)[,)]/', $css, $gebruikt);

        $ongebruikt = array_filter(
            array_unique($gedefinieerd[1]),
            fn (string $t): bool => ! in_array($t, $gebruikt[1], true) && preg_match($publiek, $t) !== 1,
        );

        $this->assertSame([], array_values($ongebruikt), 'Deze tokens worden nergens gebruikt.');
    }

    #[Test]
    public function elke_semantische_tint_die_een_view_gebruikt_is_toegewezen(): void
    {
        /*
        | DIT IS DE TEST DIE DE FLASH-MELDING HAD MOETEN VANGEN.
        |
        | Het @theme-blok legde Tailwinds schalen op de tokens, maar niet alle
        | stappen: `text-emerald-800` en `border-emerald-200` stonden er niet
        | in. De achtergrond kantelde dus mee naar het donker en de tekst niet
        | -- donkergroen op bijna-zwart, contrast 1,4:1.
        |
        | Grijs hoeft hier niet bij: dat is de hele schaal, en die is compleet.
        */
        $css = $this->css();
        $ontbreekt = [];

        foreach ($this->bladeBestanden() as $view) {
            preg_match_all(
                '/\b(?:text|bg|border|divide|ring|from|to|via)-(red|emerald|amber|blue|green|yellow|indigo|rose)-(\d{2,3})\b/',
                (string) file_get_contents($view),
                $m,
                PREG_SET_ORDER,
            );

            foreach ($m as [$klasse, $schaal, $stap]) {
                if (! str_contains($css, "--color-{$schaal}-{$stap}:")) {
                    $ontbreekt[] = basename($view).': '.$klasse;
                }
            }
        }

        $this->assertSame([], $ontbreekt, 'Deze tinten kantelen niet mee met het thema.');
    }

    #[Test]
    public function een_venster_staat_in_het_midden(): void
    {
        /*
        | Een open <dialog> wordt door de browser gecentreerd met `margin: auto`.
        | Preflight zet `margin: 0` op * en wint daarvan, dus .modal moet het
        | terugzetten -- anders plakt elk venster in de linkerbovenhoek. Precies
        | de soort regel die bij een opruimbeurt sneuvelt omdat ze overbodig lijkt.
        */
        $this->assertMatchesRegularExpression(
            '/margin:\s*auto/',
            $this->blok($this->css(), '.modal'),
            'Zonder margin:auto staat het venster niet in het midden.',
        );
    }

    #[Test]
    public function een_knop_toont_waar_de_focus_staat(): void
    {
        /*
        | Hier stond `focus-visible:outline-none` zonder vervanging. Omdat
        | @layer components van @layer base wint, haalde dat de ring uit de
        | basislaag weg: geen enkele knop liet zien waar de focus stond.
        */
        // Op het BLOK en niet op het bestand: het commentaar hierboven noemt de
        // regel juist om uit te leggen waarom ze weg moest.
        $css = $this->css();

        $this->assertStringNotContainsString('outline-none', $this->blok($css, '.btn'));
        $this->assertMatchesRegularExpression(
            '/\.btn:focus-visible\s*\{[^}]*box-shadow/',
            $css,
            'De .btn mist een zichtbare focusring.',
        );
    }
}
