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
`.modal-foot`, `.link-muted`, `.empty`, `.sidebar`. Voor een bibliotheek:
`.tree-item`, `.media-tile` + `.media-check` en `.media-badge`, `.dropzone`,
`.meter` + `.meter-fill`, `.locked` voor wat het abonnement niet heeft, en de
utilities `.grid-tiles`, `.grid-tiles-lg` en `.no-select`. Voor codes
`.code-input` en `.code-display`. Voor grafieken
`.chart` en zijn onderdelen.

`.check` op een `<label>` rond een vinkje en zijn tekst maakt er een rij van.

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
`<x-nav-mega-link>`, `<x-delta>`, `<x-chart.line>`, `<x-chart.columns>`,
`<x-chart.bars>`, `<x-chart.heatmap>` en `<x-cropper>`. Ook bereikbaar als
`<x-hansui::page>` wanneer een applicatie de korte naam zelf al gebruikt.

`<x-card>` is een kaal vlak, `<x-section>` diezelfde kaart met een kopregel
erboven, en `<x-table>` de vorm met een tabel erin. `<x-stat>` is het
kerncijfer van een dashboard, `<x-choice>` een radioknop die eruitziet als een
tegel, en `<x-tabs>` de pillenrij tussen samenhangende schermen.

`<x-stat>` neemt `change` (een percentage) en `invert` voor het verschil met de
vorige periode; dat tekent `<x-delta>`, dat ook los bestaat. Groen is beter,
rood slechter, en bij `invert` andersom, voor een cijfer waar minder beter is.

**Grafieken**, op de server getekend als SVG, zonder JavaScript-bibliotheek:

| Component | Voor | Invoer |
|---|---|---|
| `<x-chart.line>` | een of meer reeksen over de tijd | `series`: `[['label', 'color', 'points' => ['2026-01-01' => 12]]]`, `from-zero`, `unit` |
| `<x-chart.columns>` | een reeks per dag of per categorie | `data`: `['2026-01-01' => 12]`, `dates`, `color`, `unit` |
| `<x-chart.bars>` | liggende balken voor lange namen | `rows`: `[['label', 'value', 'hint', 'color']]`, `unit`, `decimals` |
| `<x-chart.heatmap>` | weekdag x uur | `cells`: `[weekdag => [uur => ['value', 'tip', 'weak']]]`, `less`, `more` |

Een as, nooit twee. Een lijn en een kolom krijgen een tabel eronder ("Als
tabel") en elk teken een `data-tip`; een reeks draagt haar naam in de legende.
De kleuren komen uit `--series-1` tot `--series-8`, in een vaste volgorde en
gevalideerd op onderscheid bij kleurenblindheid, en `--seq-0` tot `--seq-6`
voor de heatmap; beide met een eigen donkere reeks. Kies een kleur per
entiteit en niet per rang, met `HansDeBoeck\HansUi\Chart::series($id)`: dan
verandert een kanaal niet van kleur als een filter er een ander weghaalt.
`Chart::scale()` en `Chart::tick()` geven mooie aswaarden, voor wie zelf tekent.

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
| `data-sidebar` | de zijbalk | wat er in- en uitschuift onder `lg`; wegvegen sluit hem |
| `data-sidebar-toggle` | een knop | opent en sluit de zijbalk |
| `data-sidebar-scrim` | het waas erachter | klikken sluit |
| `data-theme-toggle` | een knop | licht of donker, onthouden in `hansui.theme` |
| `data-theme-icon` | twee iconen, `light` en `dark` | de een verbergt als de ander toont |
| `data-density-toggle` | een knop | compacte of ruime regels, in `hansui.density` |
| `data-palette-open` | een knop | stuurt het venster-event `open-palette` |
| `data-tip` | om het even wat | toont de waarde in een zwevend kadertje bij hover of focus |

Drie attributen zet `hansui.js` ZELF, en die schrijf je dus niet in een view:
`data-uit` op een melding die weggeklikt is, `data-wissel` op een kopieerknop
terwijl zijn tekst omslaat, en `data-sleept` op de rij die op dat moment aan de
vinger hangt. Daarbij komen `data-over` op een `.dropzone` waar een bestand
boven hangt, en `data-drop` met de waarde `over` op een `.tree-item` waar de
applicatie iets boven sleept. Ze staan allemaal in `hansui.css` -- ze staan hier omdat een
applicatie die haar eigen meldingen, kopieerknoppen of lijsten opmaakt, anders
op een selector botst die ze nergens beschreven ziet.

Ook `Ctrl`/`Cmd` + `K` stuurt `open-palette`, en `Escape` sluit een open
dropdown of de zijbalk.

**Het zoekpalet.** Een `<dialog>` die opengaat op `open-palette`, met de
index in de HTML: zoeken is dan meteen, zonder aanvraag.

| Attribuut | Wat het doet |
|---|---|
| `data-palette` | de `<dialog>`; zet er ook `data-modal` op |
| `data-palette-input` | het zoekveld; pijltjes kiezen, Enter opent |
| `data-palette-list` | de lijst met regels |
| `data-palette-item` | een regel, meestal een link; de waarde is waarop gezocht wordt, anders de tekst |
| `data-palette-empty` | wat er staat als niets past |
| `data-active` | zet je niet zelf: de regel die Enter opent |

Er wordt op woordgrens gezocht: "gent" vindt "Etalage Gent" maar niet
"Urgentie".

**Uploaden.** Slepen, plakken of kiezen, een bestand na het andere, met een
balk per bestand. Na de laatste herlaadt de pagina.

| Attribuut | Wat het doet |
|---|---|
| `data-uploader` | het vak; draagt `data-action`, `data-token` en optioneel `data-folder`, `data-max-bytes`, `data-free-bytes` |
| `data-upload-texts` | op datzelfde vak: een JSON-object met de teksten (`tooBig`, `noSpace`, `waiting`, `done`, `duplicate`, `failed`, `offline`) |
| `data-upload-surface` | waar je mag loslaten; standaard de hele pagina |
| `data-upload-list` | de `<ul>` waar de regels in komen |
| `data-upload-panel` | wat rond die lijst staat; verliest `hidden` bij de eerste regel |
| `data-no-browse` | op een knop in het vak die het bestandsvenster niet moet openen |
| `data-state` | zet je niet zelf: de status van een regel |

Het bestand gaat als `file` naar `data-action`, met `folder_id`. Van een video
gaan er ook `duration_ms`, `width`, `height` en een `poster` als data-URL mee:
de browser kan ze lezen, de server zou er ffmpeg voor nodig hebben. Antwoordt
de server met `duplicate: true`, dan staat er "stond er al".

**Filteren terwijl je typt.** Voor een lijst die al op de pagina staat.

| Attribuut | Wat het doet |
|---|---|
| `data-filter` | op een zoekveld; de waarde is de selector van de lijst |
| `data-filter-item` | een regel in die lijst; de waarde is waarop gezocht wordt, anders de tekst |
| `data-filter-empty` | wat er in de lijst verschijnt als niets past |

**Tonen naargelang een veld.** `data-show-when` op een element toont het alleen
als een formulierveld een bepaalde waarde heeft: `herhalen[freq]=week,maand`,
of zonder `=` zodra het veld iets heeft. Het veld wordt eerst in hetzelfde
formulier gezocht. Zet `required` alleen op velden die altijd zichtbaar zijn.

**Een code in losse vakjes.** Voor een koppel- of verificatiecode.

| Attribuut | Wat het doet |
|---|---|
| `data-code-group` | de omhulling; waarde leeg of `alnum` (letters en cijfers, in hoofdletters) of `digits` |
| `data-code-cell` | een vakje van een teken, met de klasse `.code-input` |
| `data-code-value` | het verborgen veld met de hele code |
| `data-code-autofocus` | op de groep: bij het laden de cursor in het eerste vakje |

Typen springt naar het volgende vakje, Backspace naar het vorige, plakken
verdeelt de code. Een volle code dient het formulier in, tenzij de groep
`data-autosubmit="false"` draagt. `.code-display` toont een code groot.

**Een botcontrole die niet rond raakt.** `data-turnstile-melding` op een
verborgen melding in het formulier: bij een `turnstile:failed`-event verschijnt
ze, met de waarde van het attribuut als tekst. Wie zelf afbrak, ziet niets.

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
| `data-sortable-handle` | optioneel, ergens IN een kind: dan sleep je alleen daaraan |

Bij het loslaten gaat de volgorde als `{volgorde: [id, ...]}` naar de server.
Slepen werkt zowel verticaal als horizontaal, en nooit van de ene lijst naar de
andere.

**Zet er een greep in als de rij zelf iets anders doet.** Zonder greep pakt een
vinger de hele rij, en omdat verticaal slepen op een aanraakscherm ook scrollen
is, moet hij dan eerst 300ms lang drukken. Met een greep vervalt dat wachten:
uit waar er geduwd wordt, blijkt de bedoeling al. Een lijst waarin je vaak
ordent, hoort er een te hebben.

**Herordenen met het toetsenbord bestaat nog niet.** Dat is een gat en geen
keuze: wie niet kan slepen, kan deze lijst niet ordenen. Zorg er tot die tijd
voor dat de volgorde ook ergens anders te wijzigen is -- een veld met een
nummer in het bewerkformulier is genoeg -- als de volgorde er echt toe doet.

**Bijsnijden.** Een kader over een beeld: slepen verschuift het, de hoeken
maken het groter of kleiner, drukken naast het kader zet het daar neer. Met
de muis, een vinger en het toetsenbord: pijltjes verschuiven, `Shift` +
pijltjes vergroten (rechts, omlaag) of verkleinen (links, omhoog), met `Alt`
erbij in kleine stapjes. Geen bibliotheek: het is een `<img>` met een kader
erover.

```blade
<x-cropper :src="$foto->url()" :ratios="['free', '1:1', '4:5' => 'Instagram 4:5']" ratio="4:5" name="uitsnede"/>
```

| Attribuut | Wat het doet |
|---|---|
| `data-crop` | de omhulling met het `<img>` erin; de waarde is het label van het kader voor een schermlezer |
| `data-crop-ratio` | een knop met een verhouding: `1:1`, `4:5`, `9:16`, `16:9`, `1.91:1`, of `free`; de knop met `aria-pressed="true"` is waarmee het begint |
| `data-crop-x` | een (verborgen) veld dat de linkerrand krijgt, in procenten van de breedte |
| `data-crop-y` | idem, de bovenrand, in procenten van de hoogte |
| `data-crop-width` | idem, de breedte |
| `data-crop-height` | idem, de hoogte |
| `data-crop-handle` | zet je niet zelf: de vier hoeken (`nw`, `ne`, `sw`, `se`) van het kader dat `hansui.js` tekent |

De knoppen en de velden horen bij de dichtstbijzijnde bijsnijder: zet ze in
dezelfde omhulling, dan kunnen er twee op een pagina staan zonder id's. Elke
wijziging stuurt ook een `crop:change`-event op de omhulling, met in `detail`
`x`, `y`, `width` en `height` (procenten, vier decimalen), `ratio` en `pixels`
(de maat van wat er overblijft in het echte beeld).

De verhouding geldt voor de pixels van het beeld, niet voor het kader op het
scherm. Staan de vier velden ingevuld als het beeld laadt, dan begint het
kader daar; anders begint het met het grootste kader in de gekozen verhouding,
in het midden. Een nieuw `src` begint opnieuw; stuur `crop:reset` op de
omhulling om opnieuw te beginnen met hetzelfde beeld (maak de velden dan eerst
leeg). De hoogte van het beeld begrens je met `--crop-max-height` (standaard
`60vh`). Het snijden zelf doet de server: de browser meldt alleen waar.

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
