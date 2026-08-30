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
plus een neutrale ladder `--n-0` tot `--n-10` en een schaduwladder
`--shadow-sm`, `--shadow-md`, `--shadow-lg`.

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
dus geen `bg-gray-900 text-white` gebruiken, maar `surface-dark` en `on-dark`,
of `surface-dark-tint` en `on-dark-soft` voor de gedempte varianten -- en
`line-dark` voor een scheidingslijn erop.

Hetzelfde geldt voor de statuskleuren: `red`, `emerald`, `amber` en `blue`
liggen op `--danger`, `--ok`, `--warn` en `--info`. **Zonder gaten** — stap 50
tot 200 is de zachte variant, 300 tot 950 de volle. Elke stap die een view kan
typen bestaat, en kantelt mee.

**Componentklassen.** `.btn` + varianten, `.card`, `.panel`, `.label`, `.input`,
`.help`, `.check`, `.badge` + varianten, `.alert` + varianten, `.tone-` + rol, `.th`,
`.td`, `.chip`, `.choice`, `.nav-item`, `.nav-group`, `.nav-light`,
`.dropdown-panel`, `.tab`, `.modal` + `.modal-head`, `.modal-body` en
`.modal-foot`, `.link-muted`, `.empty`, `.sidebar`.

`.nav-light` op de omhulling zet dezelfde `.nav-item` op een licht vlak. Geen
tweede klassenset maar andere tokens -- `--nav`, `--nav-ink`, `--nav-high`,
`--nav-hover`, `--nav-active` -- want een navigatie die twee keer geschreven
staat, loopt uit elkaar zodra er iets aan verandert.

**Blade-componenten**, zonder prefix: `<x-page>`, `<x-card>`, `<x-section>`,
`<x-table>`, `<x-th-sort>`, `<x-filter-bar>`, `<x-empty>`, `<x-result>`,
`<x-icon>`, `<x-button>`, `<x-badge>`, `<x-alert>`, `<x-stat>`, `<x-avatar>`,
`<x-field>`, `<x-choice>`, `<x-money>`, `<x-modal>`, `<x-code-block>`,
`<x-key-value>`, `<x-detail-list>`, `<x-detail-row>`, `<x-tabs>`,
`<x-nav-link>`, `<x-footer>`, `<x-nav-dropdown>`, `<x-nav-mega-group>`,
`<x-nav-mega-link>`. Ook bereikbaar als `<x-hansui::page>` wanneer een
applicatie de korte naam zelf al gebruikt.

`<x-card>` is een kaal vlak, `<x-section>` diezelfde kaart met een kopregel
erboven, en `<x-table>` de vorm met een tabel erin. `<x-stat>` is het
kerncijfer van een dashboard, `<x-choice>` een radioknop die eruitziet als een
tegel, en `<x-tabs>` de pillenrij tussen samenhangende schermen.

**De flash-partial** is een view en geen component, want ze leest de sessie:

```blade
@include('hansui::partials.flash')
```

## Gedrag

`hansui.js` hangt elk stuk gedrag aan een data-attribuut en luistert op
`document` — de helft van deze schermen wordt door Livewire opnieuw getekend, en
een listener op een element dat vervangen wordt is een listener die stilletjes
ophoudt te werken.

| Attribuut | Waar het op zit | Wat het doet |
|---|---|---|
| `data-flash` + `data-dismiss` | de melding en haar kruisje | klikken verwijdert de melding |
| `data-menu-toggle` | een knop | klapt `#mobile-menu` om, of de selector die je meegeeft |
| `data-dropdown` | de omhulling | markeert een uitklapmenu |
| `data-dropdown-toggle` | de knop erin | opent en sluit; `aria-expanded` volgt |
| `data-dropdown-panel` | het paneel erin | wat er open- en dichtgaat |
| `data-modal` | de `<dialog>` van `<x-modal>` | markeert een venster; met de waarde `open` staat het er meteen |
| `data-modal-open` | een knop | opent het venster dat de selector aanwijst |
| `data-modal-close` | een knop erin | sluit het venster; klikken op het waas ook |
| `data-copy` | een veld of een span | klikken kopieert de waarde |
| `data-copy-target` | een knop | klikken kopieert wat de selector aanwijst |
| `data-copied` | diezelfde knop | wat hij anderhalve seconde toont als het gelukt is |
| `data-confirm` | een `<form>` | vraagt met de waarde als vraag bevestiging voor het indienen |
| `data-autosubmit` | een select of input | dient zijn formulier in bij wijziging |
| `data-sidebar` | de zijbalk | wat er in- en uitschuift onder `lg` |
| `data-sidebar-toggle` | een knop | opent en sluit de zijbalk |
| `data-sidebar-scrim` | het waas erachter | klikken sluit |
| `data-theme-toggle` | een knop | licht of donker, onthouden in `hansui.theme` |
| `data-theme-icon` | twee iconen, `light` en `dark` | de een verbergt als de ander toont |
| `data-density-toggle` | een knop | compacte of ruime regels, in `hansui.density` |
| `data-palette-open` | een knop | stuurt het venster-event `open-palette` |

Ook `Ctrl`/`Cmd` + `K` stuurt `open-palette`, en `Escape` sluit een open
dropdown of de zijbalk.

**Bulkselectie.** Rijen aanvinken en er samen iets mee doen. De balk verschijnt
pas als er iets aanstaat — een lege balk die altijd onderaan hangt kost hoogte
op elk scherm dat hem nooit gebruikt — en de teller staat erin omdat "acht
mensen" iets anders is dan "de hele pagina".

| Attribuut | Wat het doet |
|---|---|
| `data-bulk` | de omhulling; de waarde is de veldnaam, standaard `ids[]` |
| `data-bulk-item` | een vakje per rij |
| `data-bulk-all` | het vakje bovenaan; volgt de rijen, ook half |
| `data-bulk-bar` | de balk; krijgt en verliest `hidden` |
| `data-bulk-count` | de teller; met enkelvoud en meervoud als waarde, gescheiden door een verticale streep, schrijft hij het woord erbij |
| `data-bulk-field` | zet je niet zelf: dit markeert de verborgen velden die het package in de balk spiegelt, zodat het ze bij de volgende wijziging weer kan opruimen |

Twee opstellingen, en ze werken allebei zonder dat je iets hoeft te zeggen:
staan de vakjes al ín het formulier dat de actie uitvoert, dan zijn ze zelf de
invoer; bevat de balk eigen formulieren, dan worden de aangevinkte waarden erin
gespiegeld als verborgen velden. `tests/browser/bulk.html` zet ze naast elkaar.

**Herhaalbare formulierregels.** Een blok waar je regels aan toevoegt en uit
weghaalt, zonder dat de veldnamen gaan botsen.

| Attribuut | Wat het doet |
|---|---|
| `data-regels` | de omhulling |
| `data-regel-lijst` | waar de regels in komen |
| `data-regel-sjabloon` | een `<template>` met één lege regel; `__INDEX__` wordt de rijindex |
| `data-regel-toevoegen` | een knop die er een regel bij zet |
| `data-regel-verwijderen` | een knop die zijn regel weghaalt |
| `data-regel` | één regel |

De teller telt door en hergebruikt geen vrijgekomen nummers: gaten geven niets
voor een PHP-array, en hergebruik zou twee velden dezelfde naam geven zodra er
middenin iets verwijderd is. De laatste regel wordt niet verwijderd maar
leeggemaakt — een formulier zonder enkele regel laat de gebruiker klemzitten.

**Slepen om te herordenen.**

| Attribuut | Wat het doet |
|---|---|
| `data-sortable` | de lijst of het raster |
| `data-id` | op elk kind; dit is wat er naar de server gaat |
| `data-sortable-url` | waar de nieuwe volgorde met `PUT` naartoe gaat |
| `data-sortable-token` | het CSRF-token voor die aanvraag |

Bij het loslaten gaat de volgorde als `{volgorde: [id, ...]}` naar de server.
Slepen werkt zowel verticaal als horizontaal, en nooit van de ene lijst naar de
andere.

## Afwijken

Met tokens, ná de import — en met **één knop per thema**:

```css
:root {
    --brand-primary: #8a1538;
    --brand-primary-dark: #d98ba4;
}
```

`--brand`, `--brand-deep`, `--brand-soft`, `--brand-line` en `--on-brand` worden
hieruit afgeleid. Zet die dus **niet** zelf: dan loopt de familie uit elkaar en
wordt een magenta knop bij hover groen.

De tweede regel mag weg. Laat je hem staan, dan is je merk in het donker even
sterk als in het licht. Laat je hem weg, dan wordt de lichte kleur naar wit
gemengd: correct en leesbaar, maar gedempter, want naar wit mengen trekt de
verzadiging eruit.

Wijk af met tokens en niet door een klasse over te schrijven. Wie een klasse
moet forken, heeft een gat in dit package gevonden — dat hoort hier gedicht te
worden, niet daar.

## De invoer van `<x-field>`

De component tekent label, hulptekst en foutregel, maar de **invoer komt uit de
slot**: een select, een textarea en een groep radioknoppen horen in dezelfde
omlijsting, en een component die dat probeert te dekken dekt het slecht. De
keerzijde is dat ze geen attributen op jouw `<input>` kan zetten. Wat ze wél
doet is de ids voorspelbaar maken, zodat jij ze aanwijst:

```blade
<x-field name="email" :label="__('E-mail')" :help="__('Werkadres.')">
    <input id="email" name="email" type="email" class="input"
           aria-describedby="email-error email-help">
</x-field>
```

Het label wijst met `for=` naar het id gelijk aan `name`, dus dat id moet er
staan. De ids met achtervoegsel `-help` en `-error` bestaan alleen wanneer er
een hulptekst of een fout is; een `aria-describedby` die naar niets wijst is
onschadelijk, dus beide mogen er altijd in.

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

De **eigen strings van dit package gaan precies andersom**: `lang/en.json`
wordt geladen en niet gepubliceerd. De sleutels zijn hier Nederlands, dus een
applicatie op `nl` ziet dat bestand nooit; een applicatie op `en` krijgt de
Engelse zinnen zonder er iets voor te doen. Dat mag hier wel, want
`loadJsonPaths()` zet de paden van de packages juist VOOR die van de applicatie:
wie een zin anders wil, zet de sleutel in zijn eigen `lang/en.json` en wint.

```bash
php artisan vendor:publish --tag=hansui-lang
php artisan vendor:publish --tag=hansui-pagination
```

De foutpagina's zetten geen merk vast: standaard staat er de beginletter uit
`config('app.name')`. Wie een echt logo wil, vult in de gepubliceerde pagina de
sectie `logo` in, en de sectie `owner` voor de voettekst.

## Meewerken

```bash
composer test
```

```bash
composer lint
```

De tests bewaken vooral wat een package als dit stil laat afbrokkelen: dat elke
component tekent — met én zonder prefix — dat de twee donkere paletten gelijk
blijven, dat elke Tailwind-tint die een view gebruikt ook echt op een token
ligt, en dat er geen `data-`-haak in een view staat waar niets op luistert.

Die laatste bestaat omdat `<x-nav-dropdown>` hier byte-identiek uit
`shippingtail` binnenkwam terwijl de listener waarop hij leunde achterbleef: het
paneel stond op `hidden` en ging nooit open.

## De regel die dit package klein houdt

**Erin hoort wat in een ANDERE applicatie ook bruikbaar zou zijn** -- ook als er
vandaag maar één consument is. Wat het domein van één applicatie kent, blijft
daar staan, en wie alleen een afwijkend uiterlijk wil, regelt dat met tokens in
de eigen `app.css`.

De maat is dus generiek-of-niet, niet het aantal consumenten. Een component die
nog nergens wordt aangeroepen is geen reden om hem te verwijderen; hooguit een
reden om hem alsnog toe te passen.
