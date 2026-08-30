# HansUI

De gedeelde interface van de Laravel-applicaties op deboeck.dev: semantische
tokens, een componentlaag, Blade-componenten en de data-attribuuthelpers.

Ze bestond al — als een kopie van `resources/css/app.css` die van project naar
project meeverhuisde. Dat ging goed tot er iets ontbrak: `shippingtail` voegde
een `.badge-warning` toe, `growtail` vond diezelfde klasse onafhankelijk opnieuw
uit, en `ota` en `pos` hebben hem nog altijd niet. Dezelfde leemte, drie keer
anders gedicht.

## Installeren

```bash
composer require hansdeboeck/hansui
```

Daarna in `resources/css/app.css` van de applicatie:

```css
@import 'tailwindcss';
@import '../../vendor/hansdeboeck/hansui/resources/css/hansui.css';

@source '../../storage/framework/views/*.php';
@source '../views';
```

Tailwind v4 scant de Blade-bestanden van het package zelf; dat regelt
`hansui.css` met een eigen `@source`. De applicatie scant alleen de hare.

En in `resources/js/app.js`:

```js
import 'hansui';
```

met in `vite.config.js` een alias, of eenvoudiger, een rechtstreeks pad:

```js
import '../../vendor/hansdeboeck/hansui/resources/js/hansui.js';
```

## Wat je krijgt

**Tokens.** `--surface`, `--surface-hover`, `--surface-sunk`, `--line`,
`--line-strong`, `--ink`, `--ink-soft`, `--ink-faint`, `--paper`, `--brand`,
`--on-brand`, `--ok`, `--warn`, `--danger`, `--info`, elk met een `-soft`-paar,
plus een neutrale ladder `--n-0` tot `--n-10`.

**Drie themastanden.** Wie niets kiest krijgt `prefers-color-scheme`; wie kiest
krijgt een `data-theme` op `<html>` dat in beide richtingen wint. Zet het thema
in een inline script in de `<head>`, vóór de eerste verf:

```html
<script>
    try {
        var t = localStorage.getItem('hansui.theme');
        if (t === 'dark' || t === 'light') { document.documentElement.dataset.theme = t; }
        if (localStorage.getItem('hansui.density') === 'compact') { document.documentElement.dataset.density = 'compact'; }
    } catch (e) {}
</script>
```

**Tailwinds grijsschaal ligt op de tokens.** `text-gray-500` betekent voortaan
"gedempte tekst" en kantelt mee met het thema. Dat is wat overstappen goedkoop
maakt: een applicatie met tweeduizend `text-gray-500` in haar views krijgt een
donkere modus zonder dat er een view aangeraakt wordt. Ook wit is meegenomen,
zodat `bg-gray-900 text-white` op een knop vanzelf omdraait.

Een vlak dat **altijd** donker is — navigatie, aanmeldpagina, foutpagina — mag
dus geen `bg-gray-900 text-white` gebruiken, maar `surface-dark` en `on-dark`.

**Componentklassen.** `.btn` + varianten, `.card`, `.panel`, `.label`, `.input`,
`.help`, `.badge` + varianten, `.th`, `.td`, `.chip`, `.nav-item`, `.nav-group`,
`.empty`, `.sidebar`.

**Blade-componenten**, zonder prefix: `<x-page>`, `<x-table>`, `<x-th-sort>`,
`<x-filter-bar>`, `<x-empty>`, `<x-result>`, `<x-icon>`. Ook bereikbaar als
`<x-hansui::page>` wanneer een applicatie de korte naam zelf al gebruikt.

## Afwijken

Met tokens, ná de import:

```css
:root {
    --brand: #8a1538;
}
```

Niet door een klasse over te schrijven. Wie een klasse moet forken, heeft een
gat in dit package gevonden — dat hoort hier gedicht te worden, niet daar.

## Publiceren

| Tag | Inhoud |
|---|---|
| `hansui-lang` | `lang/nl/{auth,pagination,passwords,validation}.php` |
| `hansui-pagination` | de paginatieviews voor Blade en Livewire |
| `hansui-errors` | de foutpagina's |
| `hansui-views` | alle componenten en de flash-partial |

De **Nederlandse frameworkvertalingen moeten gepubliceerd worden**, niet
geladen. `Illuminate\Translation\FileLoader::loadPaths()` merget met
`array_replace_recursive` en het láátste pad wint; een package dat `addPath()`
doet, zou de applicatie overschrijven in plaats van andersom.

```bash
php artisan vendor:publish --tag=hansui-lang
php artisan vendor:publish --tag=hansui-pagination
```

## De regel die dit package klein houdt

**Het verandert alleen als twee applicaties hetzelfde nodig hebben.** Eén
applicatie die iets bijzonders wil, doet dat in haar eigen `app.css` met tokens.
