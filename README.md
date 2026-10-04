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
`.tree-item`, `.media-tile` + `.media-check`, `.media-badge`, `.media-thumb`,
`.media-icon`, `.media-meta`, `.media-open` en `.media-more`, `.dropzone`,
`.meter` + `.meter-fill`, `.locked` voor wat het abonnement niet heeft, en de
utilities `.grid-tiles-sm`, `.grid-tiles`, `.grid-tiles-lg` en `.no-select`.
Voor menu's en meldingen `.context-menu` met `.context-sep`, `.context-label`,
`.context-kbd` en `.context-danger`, en `.toast`; voor het voorbeeldvenster
`.lightbox` en zijn onderdelen. Voor codes
`.code-input` en `.code-display`. Voor grafieken
`.chart` en zijn onderdelen. Voor een werkblad `.panes` met `.pane`,
`.pane-head`, `.pane-body`, `.pane-foot` en `.pane-empty`, `.list-row` en zijn
onderdelen, `.thread` met `.thread-day` en `.message` met zijn onderdelen,
`.composer` met zijn onderdelen, `.segmented` met `.segment` en zijn tonen, en
`.kbd` voor een toets.

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
`<x-chart.bars>`, `<x-chart.heatmap>`, `<x-cropper>`, `<x-lightbox>`, `<x-steps>`,
`<x-panes>`, `<x-list-row>`, `<x-message>`, `<x-thread-day>`, `<x-composer>`,
`<x-ago>`, `<x-shortcuts>` en `<x-flow-editor>` (zie [Flows](#flows)). Ook bereikbaar als
`<x-hansui::page>` wanneer een applicatie de korte naam zelf al gebruikt.

`<x-card>` is een kaal vlak, `<x-section>` diezelfde kaart met een kopregel
erboven, en `<x-table>` de vorm met een tabel erin. `<x-stat>` is het
kerncijfer van een dashboard, `<x-choice>` een radioknop die eruitziet als een
tegel, en `<x-tabs>` de pillenrij tussen samenhangende schermen.

`<x-steps>` toont waar een proces staat dat zichzelf doorloopt (een domein
dat op zijn DNS en dan op zijn certificaat wacht): `items` is
`[['label', 'hint', 'state' => 'done|current|error|todo']]`, met `label` voor
een schermlezer. Geen wizard: er valt niets aan te klikken. De toestand staat
in `data-step` op elke `.step` (met `.step-dot`, `.step-label` en
`.step-hint`), en ook als teken en als verborgen tekst, zodat ze niet op kleur
alleen leunt.

`<x-avatar>` tekent een foto of de beginletter. Met `tint` krijgt die letter een
kleur die bij de persoon blijft (uit de naam, of uit de tekst die je meegeeft,
zoals een e-mailadres), en de slot `badge` zet er een bolletje rechtsonder op.

`<x-ago>` zegt kort hoe lang geleden: "nu", "12 min", "3 u", "2 d", en na een
week de datum. De volledige tijd staat in de `title`. Geef `zone` mee als de
applicatie de tijdzone van de lezer kent.

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

**Een werkblad**, voor een lijst die je afwerkt: een inbox, tickets,
bestellingen die klaargezet moeten worden. De lijst links, wat je opende in het
midden en de details rechts, en elk scrolt op zich. Op een telefoon zijn het
twee schermen: de lijst, of wat er open staat.

```blade
<x-panes :detail="$open !== null">
    <x-slot:head class="flex items-center gap-3">…titel en knoppen…</x-slot:head>

    @include('hansui::partials.flash')

    <x-slot:list class="pane" aria-label="Gesprekken">
        <div class="pane-body" data-bulk>
            @foreach ($gesprekken as $g)
                <x-list-row :href="route('inbox.show', $g)" :active="$g->is($open)" :strong="$g->wacht"
                            :check="$g->id" :count="$g->berichten_count">
                    <x-slot:avatar><x-avatar :name="$g->naam" :tint="$g->email"/></x-slot:avatar>
                    <x-slot:title>{{ $g->naam }}</x-slot:title>
                    <x-slot:time><x-ago :time="$g->updated_at"/></x-slot:time>
                    {{ $g->laatsteZin }}
                </x-list-row>
            @endforeach
        </div>
    </x-slot:list>

    <x-slot:main class="pane" aria-label="Gesprek">
        <header class="pane-head p-4">…</header>
        <div class="pane-body thread thread-end">
            <x-thread-day :date="$bericht->created_at"/>
            <x-message type="in" :name="$bericht->naam" :time="$bericht->created_at" :body="$bericht->tekst"/>
        </div>
        <x-composer :action="route('inbox.reply', $open)" :draft="'inbox.'.$open->id">
            <x-slot:actions><button class="btn btn-primary btn-sm">Versturen</button></x-slot:actions>
        </x-composer>
    </x-slot:main>
</x-panes>
```

| Component | Wat het is | Wat je meegeeft |
|---|---|---|
| `<x-panes>` | het werkblad; wat in de slot staat, komt tussen de kop en de panelen en blijft ook op een telefoon staan (meldingen) | `detail`, en de slots `head`, `list`, `main` en `aside` met hun eigen klassen |
| `<x-list-row>` | een rij die een link is, met een vinkje over de avatar voor `data-bulk` | `href`, `title`, `active`, `strong`, `count`, `check`, `check-label`, `shortcut`, en de slots `avatar`, `time`, `subject`, `meta`; de slot zelf is het voorbeeld |
| `<x-message>` | een bericht: `in`, `out`, `auto`, `failed` of `note`, als ballon of als kaart (`layout="card"`, voor een mail) | `type`, `layout`, `name`, `address`, `to`, `time`, `zone`, `body`, `label`, `error`, `tint`, en de slots `avatar`, `top`, `retry`; de slot komt na de tekst |
| `<x-thread-day>` | een nieuwe dag: "Vandaag", "Gisteren", "maandag 3 maart" | `date`, `zone` |
| `<x-composer>` | het antwoordvak: groeit mee, onthoudt, verstuurt met `Ctrl` + `Enter` | `action`, `method`, `name`, `id`, `label`, `placeholder`, `value`, `draft`, `values`, `rows`, `required`, `maxlength`, en de slots `head`, `below`, `tools`, `actions` |
| `<x-shortcuts>` | het overzicht van de sneltoetsen, in een venster | `id`, `title`, `keys` (`['J' => 'Volgende']`) |

De maten zijn tokens met een terugval: `--panes-offset` (wat boven en onder
het werkblad staat, standaard 6.5rem), `--panes-list` (de lijst, 20rem en vanaf
`xl` 23rem) en `--panes-aside` (de zijkolom, 17rem). De zijkolom staat er pas
vanaf `2xl`; daaronder hoort dezelfde inhoud in een `<x-modal>` achter een
knop.

`.segmented` is een rij keuzes waarvan er een aanstaat: `.segment` op een link
met `aria-current`, een knop met `aria-pressed` of een label rond een
radioknop. `.segment-success`, `.segment-warning` en `.segment-danger` kleuren
de keuze die aanstaat.

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
| `data-copied` | diezelfde knop, of een element met `data-copy` | wat hij anderhalve seconde toont als het gelukt is; bij `data-copy` onderaan het scherm |
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
| `data-upload-hint` | op het `data-upload-surface`: een tekst die over de hele pagina verschijnt zolang er een bestand boven hangt |

Er mogen meer vakken op een pagina staan (een groot vak in een lege lijst en
hetzelfde in een uploadvenster): elk opent zijn eigen bestandskeuze, slepen op
de pagina en plakken gaan naar het eerste. Staat de lijst in een dicht
`<dialog>`, dan gaat dat open zodra er een
bestand binnenkomt: wie op de pagina loslaat, ziet anders niets gebeuren. Een
element dat binnen de pagina versleept wordt (`data-drag-item`) is nooit een
upload.

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
| `data-bulk-row` | een rij of tegel met een vakje erin: `Ctrl`/`Cmd` + klik zet het vakje om, `Shift` + klik kiest een bereik, zonder de link erin te volgen |
| `data-bulk-name` | een formulier in de balk dat de waarden onder een andere veldnaam wil dan die van `data-bulk` |
| `data-bulk-clear` | een knop in de omhulling die alles uitvinkt |
| `data-bulk-form` | een formulier buiten de balk, met de selector van de omhulling (die dan een id nodig heeft) als waarde: het krijgt dezelfde verborgen velden |

Zoals in een verkenner: `Shift` + klik op een vakje kiest alles tussen het
vorige vakje en dit, `Ctrl`/`Cmd` + `A` met de focus in de lijst kiest alles,
`Escape` wist de selectie, en `Ctrl` + spatie op een `data-bulk-row` met de
focus zet haar vakje om. Elke wijziging stuurt `bulk:change` op de omhulling,
met `count` en `values` in `detail`.

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

**Een contextmenu.** Rechtsklikken, de knop met drie puntjes, de
ContextMenu-toets of `Shift` + `F10`: alle vier openen hetzelfde menu, want
niet iedereen heeft een rechtermuisknop. Het menu staat een keer op de pagina
als gewone HTML; de waarden van het element waarop het opengaat, worden erin
ingevuld.

```blade
<div data-context-menu="#bestand" data-context-item='{"id": 12, "name": "Etalage"}'>
    <button type="button" data-context-trigger aria-label="Acties">…</button>
</div>

<div id="bestand" role="menu" class="context-menu" hidden>
    <p class="context-label" data-context-text="name"></p>
    <a role="menuitem" href="/bestanden/{id}">Openen</a>
    <button type="button" role="menuitem" data-context-key="F2"
            data-modal-open="#hernoemen" data-context-fill="#hernoemen">Hernoemen</button>
    <hr role="separator" class="context-sep">
    <form method="POST" action="/bestanden/{id}" data-confirm="{name} verwijderen?">
        <button role="menuitem" class="context-danger">Verwijderen</button>
    </form>
</div>
```

| Attribuut | Waar het op zit | Wat het doet |
|---|---|---|
| `data-context-menu` | het element (of de omhulling: een lege plek) | rechtsklikken opent het menu dat de selector aanwijst; het dichtstbijzijnde wint |
| `data-context-item` | datzelfde element of een voorouder | een JSON-object met de waarden die in het menu ingevuld worden |
| `data-context-trigger` | een knop erin | opent het menu onder zichzelf; met een selector als waarde een ander menu |
| `data-context-text` | iets in het menu | krijgt de waarde met die sleutel als tekst |
| `data-context-value` | een veld | krijgt de waarde als `.value` |
| `data-context-if` | een item, formulier of veld | staat er alleen als de uitdrukking klopt: `sleutel`, `!sleutel`, `sleutel=a,b`, `sleutel!=a`; een veld dat er niet staat, gaat ook niet mee |
| `data-context-disabled` | een item | dezelfde uitdrukking; klopt ze, dan is het item onbruikbaar (`aria-disabled`) |
| `data-context-ids` | een formulier in het menu | krijgt een verborgen veld per id: de selectie, of het ene element; de waarde is de veldnaam |
| `data-context-id` | zet je niet zelf | de verborgen velden die `data-context-ids` maakt |
| `data-context-sub` | een item | opent het `role="menu"` dat erna staat als submenu |
| `data-context-key` | een item | een sneltoets (`F2`, `Delete`) die het item uitvoert op het element met de focus, zonder menu |
| `data-context-click` | een item | klikt op wat de selector BINNEN het element aanwijst (een vakje, een link) |
| `data-context-fill` | een item, of een knop buiten het menu | vult dezelfde waarden ook in het element dat de selector aanwijst: een venster dat het item opent. Buiten een menu zijn het de waarden van waar de knop staat; in een `data-bulk`-lijst zonder eigen rij is dat de selectie |
| `data-context-open` | zet je niet zelf | op het element waarvan het menu openstaat |

`{sleutel}` in om het even welk attribuut in het menu wordt de waarde: `href`,
`action`, `value`, `data-confirm`. Het sjabloon wordt onthouden, dus het
volgende element begint er weer van. Naast wat in `data-context-item` staat,
zijn er altijd `ids`, `count`, `selected`, `checked` en `selection`: dat
laatste klopt als het element in een `data-bulk`-lijst aangevinkt staat en er
meer aanstaan, en dan gaat het menu over de hele selectie.

In het menu doen de pijltjes, `Home`, `End`, `Enter`, spatie, de eerste letter
en `Escape` wat ze in een menu van het besturingssysteem doen; rechts en links
gaan een submenu in en uit. Het menu blijft binnen het venster, sluit bij een
klik ernaast of bij scrollen, en zet de focus terug waar hij stond. Het stuurt
`context:open` op het element, met `menu` en `values` in `detail`.
`tests/browser/verkenner.html` zet het menu, de selectie, het raster en het
voorbeeldvenster samen op een pagina.

**Een raster met pijltjes.**

| Attribuut | Wat het doet |
|---|---|
| `data-grid-nav` | de omhulling: een tabstop voor het hele raster |
| `data-grid-item` | wat de focus krijgt; de pijltjes gaan naar links, rechts, en naar de rij erboven of eronder zoals ze op het scherm staat, `Home` en `End` naar het eerste en laatste, spatie klikt |

**Slepen naar een doel.** Een tegel naar een map slepen, met de muis. Voor
een vinger of het toetsenbord hoort er ook een andere weg te zijn: een item
"Verplaatsen naar" in het contextmenu.

| Attribuut | Wat het doet |
|---|---|
| `data-drag-item` | wat je vastpakt, met `draggable="true"`; de waarde is het id. Staat het aangevinkt in een `data-bulk`-lijst, dan gaat de selectie mee |
| `data-drag-label` | op het item of de lijst: enkelvoud en meervoud, gescheiden door een verticale streep, voor het aantal naast de muis |
| `data-drop-target` | waar je loslaat; de waarde is de url waar een POST naartoe gaat |
| `data-drop-name` | de veldnaam van de id's; anders die van `data-bulk`, anders `ids[]` |
| `data-drop-fields` | JSON met extra velden, zoals de map waar het heen gaat |
| `data-drop-token` | het CSRF-token; anders dat uit `<meta name="csrf-token">` |
| `data-drop` | zet je niet zelf: `over` terwijl er iets boven hangt, `busy` tijdens de aanvraag |
| `data-dragging` | zet je niet zelf: wat er versleept wordt |

Na een geslaagde aanvraag gaat `drop:done` op het doel; wie dat niet
tegenhoudt (`preventDefault()`), krijgt een herladen pagina. Een fout komt
onderaan het scherm, met de `message` uit het antwoord.

**Een video die speelt bij hover.** `data-hover-play` op een `<video>` laat haar
gedempt spelen zolang de muis boven het element staat dat de waarde aanwijst
(een voorouder, zoals `.media-tile`), of boven de video zelf. Niet op een
aanraakscherm en niet voor wie om minder beweging vroeg.

**Het voorbeeldvenster.** Een link die een groot voorbeeld opent, met vorige en
volgende; met `Ctrl` + klik of zonder script blijft het een gewone link.

```blade
<a href="{{ route('foto.show', $foto) }}" data-lightbox="fotos"
   data-lightbox-src="{{ $foto->url() }}" data-lightbox-aside="{{ route('foto.paneel', $foto) }}">…</a>

<x-lightbox id="voorbeeld" group="fotos" :aside="true"/>
```

| Attribuut | Wat het doet |
|---|---|
| `data-lightbox` | de link; de waarde is de groep waarin vorige en volgende zoeken |
| `data-lightbox-view` | de `<dialog>` van `<x-lightbox>`, met de groep als waarde |
| `data-lightbox-src` | wat er getoond wordt; anders de `href` |
| `data-lightbox-type` | `image` (standaard), `video`, `audio`, `frame` (een pdf of pagina in een kader; op een aanraakscherm een link), of iets anders: dat krijgt een icoon en een link |
| `data-lightbox-poster` | het posterbeeld van een video |
| `data-lightbox-title` | de titel bovenaan; anders `aria-label` of de tekst |
| `data-lightbox-alt` | de alt-tekst van het beeld |
| `data-lightbox-aside` | op de link: een url waarvan het zijpaneel als HTML komt, een keer per url |
| `data-lightbox-stage`, `data-lightbox-caption`, `data-lightbox-prev`, `data-lightbox-next`, `data-lightbox-count`, `data-lightbox-fallback`, `data-lightbox-open` | de onderdelen die `<x-lightbox>` al tekent |

De pijltjes links en rechts bladeren, vegen ook, `Escape` sluit, en daarna
staat de focus op de link van het laatste beeld. De waarden uit
`data-context-item` worden in het venster ingevuld zoals in een contextmenu,
dus een vast zijpaneel met `{id}` erin werkt ook. Het venster stuurt
`lightbox:show` (met `index`, `total` en `opener`), het paneel `lightbox:aside`
als het binnen is. `window.HansUI.lightboxForget(url)` vergeet een opgehaald
paneel, voor na een wijziging.

**Bewaren zonder de pagina te verlaten.** `data-async` op een formulier stuurt
het met fetch en `Accept: application/json`; de pagina blijft staan. De waarde
is wat er bij succes gemeld wordt, in het element met `data-async-status` in
het formulier, anders onderaan het scherm. Een validatiefout toont de eerste
melding daar en zet `aria-invalid` op het veld. `data-async-offline` is de
tekst als de verbinding wegvalt. Na afloop `async:done`, met het antwoord in
`detail`; `data-confirm` werkt er gewoon op.

**Sneltoetsen.** Voor een scherm waar je veel na elkaar doet.

| Attribuut | Wat het doet |
|---|---|
| `data-shortcut` | op een link, knop of veld: die toets (of die toetsen, gescheiden door een spatie) klikt erop, of zet de cursor in het veld |
| `data-kbd-mod` | op een `<kbd>`: Ctrl wordt ⌘ op een Mac |

Nooit terwijl je typt, nooit met `Ctrl`, `Cmd` of `Alt` erbij, en met een
venster open alleen wat in dat venster staat. Wat in iets met `hidden` of
`inert` staat, uitgeschakeld is of in een dicht venster zit, telt niet; wat
alleen op een smal scherm uit beeld is, telt wel. Staan er twee met dezelfde
toets, dan wint wat zichtbaar is en daarna het laatste in de pagina: wat een
scherm zelf aanbiedt, wint van de balk van de applicatie.

**Het antwoordvak.** Wat `<x-composer>` tekent, en wat ook los werkt.

| Attribuut | Wat het doet |
|---|---|
| `data-composer-form` | op het formulier: `Ctrl`/`Cmd` + `Enter` in het tekstvak doet wat de zichtbare verstuurknop doet, `Escape` laat het vak los, en het vertrekt een keer |
| `data-composer-placeholder` | op een radioknop erin: als hij aangaat, wordt dit de voorzettekst en staat de cursor in het vak |
| `data-composer-note` | op de radioknop van een notitie: het vak kleurt zolang hij aanstaat |
| `data-composer-values` | op het formulier: JSON met wat `{sleutel}` wordt in een ingevoegde tekst |
| `data-insert` | op een knop in het formulier: zet de waarde waar de cursor staat, met `{sleutel}` ingevuld |
| `data-autogrow` | op een tekstvak: het groeit mee tot zijn `max-height` |
| `data-draft` | op een tekstvak: onthoudt wat je typt onder deze sleutel, tot het formulier vertrekt |
| `data-count-for` | op een element: telt de tekens in het veld dat de selector aanwijst |
| `data-count-max` | op datzelfde element: de grens, dan staat er "12 / 280" |

Niet `data-composer` zonder meer: socialtail gebruikt dat al voor de opsteller
van zijn berichten, en daar startte het dan het verkeerde script. Om dezelfde
reden telt `data-count-for` en niet `data-count`: in socialtail staat dat al op
de tellers van die opsteller, met de grens op het veld zelf.

Het concept staat in `localStorage` van dit toestel, onder `hansui.concept.`
en de waarde van `data-draft`. Staat er al tekst in het vak (na een
validatiefout), dan wint die; een concept van meer dan twee weken oud komt niet
meer terug. Voorbij de grens zet `hansui.js` zelf `data-danger` op de teller.

**In beeld bij het laden.** `data-scroll-here` op een element in een lijst of
een gesprek dat zelf scrolt: dat opent met dit element bovenaan in beeld (het
laatste bericht), of met de waarde `center` in het midden, als het er nog niet
stond (het gesprek dat open is, ook als het de dertigste rij is). De pagina
zelf scrolt nooit.

**Een melding onderaan.** Voor wie zelf iets wil melden:
`window.dispatchEvent(new CustomEvent('toast', { detail: 'Bewaard' }))`, of
`{ detail: { text: 'Mislukt', tone: 'danger' } }`.

## Flows

Flows tekenen en uitvoeren, zoals in n8n: een start, stappen die na elkaar
komen, voorwaarden die splitsen in ja en nee, en wachten tot morgen of tot een
taak af is. Vier delen, in `src/Flows` en `<x-flow-editor>`:

- **Een graaf** (`Graph`): lezen uit json, controleren (geen lus, precies een
  start, een uitgang met hoogstens een verbinding), wijzigen en terugschrijven.
  Wat nog niet af is (een leeg verplicht veld, een stap die nergens aan hangt),
  zijn `issues()` en geen fouten.
- **Een editor in de browser** (`<x-flow-editor>`): slepen, verbinden, een stap
  ertussen zetten, terugdraaien, zoomen, volledig scherm, en alles ook met het
  toetsenbord. Gewoon JavaScript zonder afhankelijkheden, en css op de tokens
  hierboven.
- **Een walker** (`Walker`): voert een flow stap voor stap uit met een functie
  van de applicatie, en zegt waar hij wacht, tot wanneer en langs welke uitgang
  hij daarna verdergaat.
- **Schikken** (`Layout`): van links naar rechts, de hoofdweg als rechte lijn;
  de server en de browser rekenen precies hetzelfde.

De graaf, het schikken en de walker hebben geen applicatie nodig. De editor
staat apart van `hansui.css` en `hansui.js`, want de meeste schermen tekenen
geen flow; een applicatie die hem gebruikt, neemt hem op na HansUI:

```css
/* resources/css/app.css */
@import '../../vendor/hansdeboeck/hansui/resources/css/flows.css';
```

```js
// resources/js/app.js
import '../../vendor/hansdeboeck/hansui/resources/js/flows.js';
```

### De soorten stappen

Een applicatie zegt welke stappen er zijn. Een soort heeft een naam, een icoon,
een kleur als rol (`neutral`, `info`, `ok`, `warn`, `danger`, `brand`),
uitgangen en verplichte velden:

```php
use HansDeBoeck\HansUi\Flows\NodeType;
use HansDeBoeck\HansUi\Flows\NodeTypes;

$types = new NodeTypes([
    new NodeType('trigger', __('Als dit gebeurt'), icon: 'bolt', tone: 'warn', start: true, required: ['event' => __('Gebeurtenis')]),
    new NodeType('condition', __('Voorwaarde'), icon: 'filter', tone: 'info', outputs: ['yes' => __('Ja'), 'no' => __('Nee')], group: __('Logica')),
    new NodeType('wait', __('Wachten'), icon: 'clock', group: __('Logica'), defaults: ['amount' => 1, 'unit' => 'days']),
    new NodeType('task', __('Taak maken'), icon: 'check', tone: 'ok', group: __('Acties'), required: ['title' => __('Titel')]),
]);
```

- `start: true` is waar een flow begint. Er is er precies een, en er gaat
  niets naartoe.
- `outputs` zijn namen (`out`, of `yes` en `no`); een verbinding onthoudt de
  naam, zodat een uitgang die erbij komt niets verschuift. Een soort zonder
  uitgangen is een einde.
- `required` noemt de velden die ingevuld moeten zijn, met de naam voor de
  melding.
- `max` beperkt hoe vaak een soort in een flow mag staan; `group` en `hint`
  zijn voor het palet.
- `branches` maakt een splitsing met meer takken: elke waarde in die sleutel
  van de config wordt een uitgang, voor de vaste uitgangen (hoogstens
  `NodeType::MAX_BRANCHES`, nu vier). Een waarde moet een geldige naam voor
  een uitgang zijn (kleine letters, cijfers, `_` of `-`, beginnend met een
  letter); de editor toont als label de tekst bij die waarde in het
  formulier. Wie de uitgangen van een stap wil, vraagt ze met haar config:
  `$type->outputsFor($node->config)` of `$graph->outputsOf($id)`.

```php
new NodeType('split', __('Splitsen'), icon: 'route', outputs: ['other' => __('Anders')], branches: 'values');
```

```blade
<template data-flow-form="split">
    <x-field name="field" :label="__('Op')">
        <select id="field" name="field" class="input"><option value="priority">{{ __('Prioriteit') }}</option></select>
    </x-field>
    <fieldset data-flow-when="field:priority">
        <label class="check"><input type="checkbox" name="values[]" value="high"> {{ __('Hoog') }}</label>
        <label class="check"><input type="checkbox" name="values[]" value="urgent"> {{ __('Dringend') }}</label>
    </fieldset>
</template>
```

Een tak die uit de keuze valt, verliest zijn verbinding, in de editor en in
`Graph::withNode()`. Van twee velden met dezelfde naam telt wat te zien is,
ook voor een lijst: de vinkjes van een verborgen deel tellen dan niet mee.

### Een flow lezen en bewaren

```php
use HansDeBoeck\HansUi\Flows\Graph;

$graph = Graph::fromJson($request->input('flow'), $types);   // InvalidGraph als het niet klopt
$graph->start();                       // de start
$graph->next('n3', 'yes');             // de stap na de uitgang "yes"
$graph->ordered();                     // alle stappen, in leesvolgorde
$graph->issues();                      // wat nog niet af is: [Issue(node, message), ...]
$graph->toJson();                      // om te bewaren
```

Een nieuwe flow is `Graph::starting($types, 'trigger', ['event' => 'deal.won'])`.
In code bouw je er een met de `Builder`:

```php
use HansDeBoeck\HansUi\Flows\Builder;

$flow = new Builder($types);
$start = $flow->add('trigger', ['event' => 'deal.won']);
$check = $flow->then($start, 'condition', ['field' => 'value_cents', 'operator' => '>=', 'value' => '5000']);
$flow->then($check, 'task', ['title' => 'Bellen'], port: 'yes');

$graph = $flow->graph();   // gecontroleerd en geschikt
```

Een wijziging (`withNode`, `withoutNode`, `connect`, `disconnect`, `renamed`,
`arranged`) geeft een nieuwe graaf terug, opnieuw gecontroleerd.

Nakijken in een request:

```php
use HansDeBoeck\HansUi\Flows\ValidFlow;

$request->validate([
    'flow' => ['required', new ValidFlow($types, complete: $request->boolean('active'))],
]);
```

Ongeldig is altijd een fout. Met `complete` mag er ook niets meer open staan:
een flow die aan staat, moet af zijn. Een derde argument voegt eigen controles
toe (een team dat niet meer bestaat), zoals bij `issues()`.

De zinnen voor een gebruiker gaan door `__()`, met het Nederlands als sleutel:
`lang/en.json` hier heeft het Engels, een applicatie vult de rest aan.

### De editor

```blade
<form id="automatisatie" method="POST" action="...">
    @csrf
    @method('PUT')
</form>

<x-flow-editor :graph="$graph" name="flow" form="automatisatie" :issues="$graph->issuesByNode($extra)">
    <x-slot:header>
        <a href="..." class="link-muted">Terug</a>
        <input name="name" form="automatisatie" class="input">
        <x-button type="submit" form="automatisatie">Bewaren</x-button>
    </x-slot:header>

    {{-- een <template> per soort, zie hieronder --}}
</x-flow-editor>
```

| Wat je meegeeft | Wat het doet |
|---|---|
| `graph` | de flow, met haar soorten |
| `test` | een url om proef te draaien (zie hieronder); zonder is er geen knop Proefdraaien |
| `name`, `form` | het verborgen veld met de flow, en het id van het formulier dat het verstuurt: zet de editor **buiten** dat formulier (een formulier in een formulier bestaat niet) |
| `issues` | wat de server zelf vond, per stap; wat de editor kan zien (een leeg verplicht veld, een stap die nergens aan hangt), rekent hij live |
| `notes` | zinnen per stap voor in het paneel (een geheim om een webhook te controleren, een uitleg) |
| `badges` | een kort woord of cijfer per stap op het canvas ("12 wachten hier", "61% geopend") |
| `icon` | de component voor de iconen van de stappen; standaard `<x-icon>`, die van de applicatie als ze er een heeft |
| `label` | de naam van het canvas voor een schermlezer |
| `readonly` | alleen bekijken: een stap opent nog in het paneel (met de velden uit), het canvas verschuift en zoomt, maar er is niets om toe te voegen, te verbinden of weg te halen, en het veld heeft geen naam, zodat er niets verstuurd wordt |
| `fill` | loopt van waar de editor begint tot onderaan het venster (nooit lager dan `--flow-fill-min`, standaard 28rem; de ruimte eronder is `--flow-fill-gap`), voor een pagina die vooral de editor is; zonder is hij `--flow-height` hoog |
| `flush` | van rand tot rand: geen rand, geen ronde hoeken en geen ruimte eronder, voor een pagina die alleen de editor is |
| slot `header` | een balk bovenaan, voor wat de pagina anders boven de editor zou zetten (de weg terug, de naam, Bewaren); de editor zet er zelf Volledig scherm achter |
| slot `notices` | onder die balk, alleen als er iets in staat: een melding, wat er nog ontbreekt |
| slot `overview` | een tweede weergave naast de flow, voor wat de pagina anders onder de editor zou zetten (de laatste keren, de cijfers); met `label` (standaard Overzicht) en `hash` (standaard `overzicht`) |
| slot `example` | de velden om een voorbeeld te kiezen bij Proefdraaien (een deal, een contact) |
| de slot zelf | de formulieren van de stappen, een `<template>` per soort |

Na een validatiefout toont de editor wat de gebruiker had (`old()`), als dat
een geldige flow is.

**Volledig scherm** (de knop, of F op het canvas) legt de editor over het hele
venster, met css, zodat het overal werkt, ook op een iPhone en in een
ingesloten pagina. Waar de browser het toelaat, verdwijnen ook zijn balken (het
hele document gaat volledig scherm, zodat een `<dialog>` van de applicatie
erboven blijft). Escape of dezelfde knop zet het terug.

**Het overzicht** komt in de balk als keuze naast de flow (Flow, Uitvoeringen)
en neemt de plaats van het canvas in; het scrolt zelf, zodat een pagina met
`fill` nooit hoeft te scrollen. Wie het kiest, krijgt `#overzicht` (of de
`hash`) in de url, zonder nieuwe stap in de geschiedenis: herladen, een link en
de knop Terug komen er weer in uit. Een formulier dat bewaart, stuurt terug
naar de url zonder, en dan staat de flow er weer. De toetsen van de flow gelden
in het overzicht niet.

```blade
<x-slot:overview :label="__('Uitvoeringen')" hash="uitvoeringen" class="space-y-6">
    @include('automations.partials.runs')
</x-slot:overview>
```

In de editor:

| Wat | Hoe |
|---|---|
| Een stap toevoegen | de knop Stap toevoegen, de + aan een uitgang, of slepen van een uitgang naar een lege plek |
| Verbinden | slepen van een uitgang naar een stap, of kiezen bij Daarna in het paneel |
| Ertussen zetten, loskoppelen | de knoppen op een verbinding |
| Invullen | een klik op een stap opent het paneel |
| Verschuiven | slepen, of de pijltjes (Shift voor grotere stappen) |
| Weghalen | Delete, of de knop in het paneel; een reeks sluit weer aan |
| Terugdraaien | Ctrl+Z en Ctrl+Shift+Z |
| Kijken | slepen op het canvas, scrollen, Ctrl+scrollen of knijpen om te zoomen, 0 om alles te tonen |
| Volledig scherm | de knop, of F; Escape zet het terug |
| Meer tegelijk kiezen | Shift+klik, Shift en slepen op een lege plek (een kader), of Ctrl+A; samen slepen of weghalen |
| Kopiëren en plakken | Ctrl+C, Ctrl+X en Ctrl+V, ook naar een andere flow; de start gaat niet mee |
| Een notitie | de knop Notitie; slepen, en een klik opent haar tekst |

Een lus kan niet: een verbinding die er een zou maken, weigert de editor voor
ze getekend is. Op een smal scherm zijn het paneel en het palet een blad
onderaan.

### Het formulier van een stap

Per soort een `<template data-flow-form="soort">` in de slot van de editor, met
gewone velden. De editor kloont het in het paneel, vult het met de config van
de stap, en schrijft elke wijziging terug.

```blade
<template data-flow-form="task" data-flow-summary="{title} · {to}">
    <x-field name="title" :label="__('Titel')">
        <input id="title" name="title" class="input" required>
    </x-field>
    <x-field name="to" :label="__('Voor')">
        <select id="to" name="to" class="input">...</select>
    </x-field>
    <div data-flow-when="to:team">
        ...
    </div>
</template>
```

- **De naam van een veld is de sleutel in de config.** Een getal
  (`type="number"`) wordt een getal, een vinkje `true` of `false`, `naam[]` of
  `multiple` een lijst.
- **Wat in het formulier al staat** (`value`, `selected`, `checked`), is de
  config van een nieuwe stap. `defaults` van de soort komen erbovenop.
- **`required`** maakt van een leeg veld een issue op de stap ("Nog in te
  vullen: Titel", met de tekst van het label).
- **De samenvatting** op de stap komt uit het patroon `data-flow-summary` op de
  template (`{veld}` is wat het veld toont: de tekst van de gekozen optie, het
  label van een vinkje), of anders uit de velden met `data-flow-summary`, na
  elkaar. `data-flow-summary-requires="veld"` toont het patroon alleen als dat
  veld ingevuld is, en `data-flow-summary-text` op een optie of een vinkje is
  wat er dan staat in plaats van zijn tekst.
- **`data-flow-when="veld:a,b"`** toont een deel alleen als het veld een van die
  waarden heeft; `veld!:a,b` als het er geen van heeft, `veld` als het
  ingevuld of aangevinkt is. Een verborgen veld is nooit verplicht.
- Ids krijgen een voorvoegsel en namen een eigen naam met een `form` dat
  nergens bestaat: wat in het paneel staat, wordt nooit zelf verstuurd.

### Notities, de weg van een uitvoering, versies en proefdraaien

**Notities** staan in de json naast de stappen (`stickies`, zie het formaat):
een applicatie die de json bewaart, heeft er niets voor te doen; een die haar
stappen als rijen bewaart, bewaart ze apart (`$graph->stickies()`,
`$graph->withStickies()`). Ze doen niet mee met de wandeling of de controles.

**De weg van een uitvoering**: een knop of link in de editor (meestal in het
overzicht) met `data-flow-trace` en json. De editor toont dan de flow, wat ze
niet deed gedempt, de verbindingen van de weg in de kleur van info, de stap
waar ze nu staat met een ring (rood voor `failed` en `stopped`, groen voor
`done`), en bovenaan een balk met `label` en `detail` en een knop terug naar
de hele flow (of Escape). `notes` is een zin per stap, onder de stap en in
haar paneel.

```blade
<button type="button" data-flow-trace="{{ json_encode(['nodes' => ['start', 'n1', 'n3'], 'current' => 'n3', 'status' => 'waiting', 'label' => 'Offerte 2026-014', 'detail' => 'wacht tot 9 okt.', 'notes' => ['n1' => 'Ja: € 12.000']]) }}">…</button>
```

**Een versie laden**: een knop met `data-flow-load` (de json van een flow) en
`data-flow-load-label` (zoals "4 okt. 17:20"). De editor zet die flow erin
als een wijziging: terug te draaien met Ctrl+Z, en pas bewaard met Bewaren.
Een soort die niet meer bestaat, valt weg. De versies zelf houdt de
applicatie bij.

**Proefdraaien**: met `test` (een url) en de slot `example` staat er een knop
Proefdraaien. De editor stuurt met een POST de flow zoals ze nu in de editor
staat (`flow`, ook als ze niet bewaard is), de velden uit `example` en het
CSRF-token, en verwacht dezelfde json als `data-flow-trace` terug. Een fout
(een 422 met `message`) staat in het venster. Er gebeurt niets: de applicatie
rekent de weg uit zonder een taak te maken of een mail te sturen.

Wie het formulier wil aanpassen voor het getoond wordt (opties wegfilteren
naargelang de start), luistert op `flows:form`; elke wijziging is een
`flows:change`, en een editor die klaar is, stuurt `flows:ready`:

```js
document.addEventListener('flows:form', (event) => {
    const { node, form, start } = event.detail;
    // ...
});
```

### De haken van de editor

`<x-flow-editor>` tekent ze; een eigen opmaak die ze allemaal heeft, werkt ook.

| Attribuut | Wat het is |
|---|---|
| `data-flow-editor` | de omhulling; draagt `data-flow-types`, `data-flow-strings`, `data-flow-issues`, `data-flow-notes` en `data-flow-badges` (json), en naargelang de props `data-flow-readonly`, `data-flow-fill`, `data-flow-flush` en `data-flow-header` |
| `data-flow-stickies` | de laag met de notities, in `data-flow-world` |
| `data-flow-sticky-add` | de knop Notitie |
| `data-flow-trace` | een knop of link die de weg van een uitvoering toont (json, zie hierboven) |
| `data-flow-trace-bar` | de balk bovenaan bij een weg, met `data-flow-trace-text` en de knop `data-flow-trace-close` |
| `data-flow-load` | een knop die een versie laadt (json), met `data-flow-load-label` |
| `data-flow-test-open` | de knop Proefdraaien |
| `data-flow-test-panel` | het formulier van Proefdraaien, met `data-flow-test-close`, `data-flow-test-error` en de knop `data-flow-test-run` |
| `data-flow-input` | het verborgen veld met de flow |
| `data-flow-stage` | het werkvlak: alles onder de balk, wat erin zweeft rekent vanaf hier |
| `data-flow-canvas` | wat verschuift en zoomt, met `data-flow-world` erin, en daarin `data-flow-edges` (de svg), `data-flow-nodes` en `data-flow-overlay` |
| `data-flow-add` | de knop Stap toevoegen |
| `data-flow-undo`, `data-flow-redo`, `data-flow-arrange` | terugdraaien, opnieuw doen, schikken |
| `data-flow-issues-button` | de knop naar de eerste stap die aandacht vraagt, met het aantal in `data-flow-issue-count` |
| `data-flow-zoom-in`, `data-flow-zoom-out`, `data-flow-fit` | zoomen, en alles tonen; het percentage staat in `data-flow-zoom-label` |
| `data-flow-fullscreen` | de knoppen Volledig scherm, met `aria-pressed` |
| `data-flow-view` | in de balk: `flow` of `overview`, met `aria-pressed` |
| `data-flow-overview` | het overzicht; de waarde is de hash |
| `data-flow-panel` | het paneel van een stap |
| `data-flow-palette` | het palet, met `data-flow-search` en `data-flow-palette-list` |
| `data-flow-live` | een `aria-live`-regel voor wat een schermlezer hoort |
| `data-flow-loading` | "Laden…" voor een schermlezer, tot de editor er is |
| `data-flow-icon` | een `<template>` per soort met haar icoon |
| `data-flow-chrome` | een `<template>` per icoon van de editor zelf |
| `data-flow-form` | een `<template>` per soort met haar formulier, met `data-flow-summary`, `data-flow-summary-requires` en `data-flow-when` erin |

Wat de editor zelf zet en wat je dus niet in een view schrijft, maar wel kan
opmaken: `data-flow-ready` en `data-flow-filled` op de omhulling als hij er
is en tot onderaan loopt, `data-fullscreen` erop in volledig scherm en
`data-flow-fullscreen` dan op `<html>`, `data-flow-tracing` als er een weg
getoond wordt; `data-sticky` op een notitie, met `data-empty`, en in haar
paneel `data-flow-sticky-text` en `data-flow-sticky-remove`; `data-picked` op
wat meer tegelijk gekozen is; `data-trace` (`visited` of `current`) en
`data-trace-status` op een stap van een weg; `data-node` op een stap, met
`data-tone`, `data-start`, `data-issue`, `data-dragging` terwijl ze versleept
wordt, `data-drop` (`ok` of `no`) terwijl er een verbinding boven hangt en
`data-refused` even als die niet kan; `data-flow-out` op een uitgang, en
`data-flow-out-label` op haar naam (de knop erna staat na die naam);
`data-edge` op een verbinding, met `data-selected` als ze gekozen is, en
`data-flow-edge-actions` met `data-flow-edge-insert` en `data-flow-edge-remove`
erop; in het paneel `data-flow-form-for`, `data-flow-field`,
`data-flow-panel-issues`, `data-flow-next-section` en `data-flow-next`; en
`data-active` op de keuze in het palet die Enter neemt.

### Uitvoeren

De walker kent geen stappen: de applicatie geeft een functie die per stap iets
doet en zegt hoe het verder moet.

```php
use HansDeBoeck\HansUi\Flows\Node;
use HansDeBoeck\HansUi\Flows\Step;
use HansDeBoeck\HansUi\Flows\Walker;

$handler = function (Node $node): Step {
    return match ($node->type) {
        'condition' => Step::next($this->matches($node) ? 'yes' : 'no'),
        'wait' => Step::wait(now()->addDays($node->get('amount'))),
        'task' => $this->task($node) ? Step::next() : Step::fail('Geen klant.'),
        default => Step::next(),
    };
};

$walk = (new Walker($graph))->resume($graph->start()->id, 'out', $handler);

if ($walk->waiting()) {
    // bewaar $walk->node, $walk->port, $walk->until en $walk->state bij de run
}

// later, als de tijd om is:
$walk = (new Walker($graph))->resume($run->node, $run->port, $handler);
```

`Step::wait()` zonder tijdstip wacht op iets dat de applicatie zelf meldt (een
taak die af is); `$state` is wat ze daarvoor onthoudt. Een fout die de functie
gooit, gaat door naar wie de walker riep.

### Het formaat

```json
{
    "version": 1,
    "nodes": [
        { "id": "start", "type": "trigger", "config": { "event": "deal.won" }, "x": 0, "y": 0 },
        { "id": "n1", "type": "task", "config": { "title": "Bellen" }, "x": 360, "y": 0 }
    ],
    "edges": [
        { "from": "start", "port": "out", "to": "n1" }
    ]
}
```

Een id is 1 tot 40 letters, cijfers, `_` of `-`: een applicatie die stappen als
rijen bewaart, gebruikt het id van de rij, en geeft een stap uit de editor
(`n4`) na het bewaren dat id met `renamed()`. Een positie mag ontbreken;
`arranged()` en de editor zetten ze dan zelf.

Notities staan erbij als `"stickies": [{"id": "s1", "text": "…", "x": 0, "y":
200}]`, hoogstens vijftig van elk duizend tekens; een flow zonder notities
schrijft de sleutel niet.

Wat niet kan, en met reden:

- Een uitgang heeft een verbinding: twee dingen tegelijk na een stap zet je na
  elkaar.
- Geen lussen: een flow loopt vooruit. Herhalen doet een applicatie met een
  nieuwe run.
- Hoogstens 200 stappen en 256 kB json.
- De editor rekent met stappen van 240 bij 76 pixels; wie dat in de css
  wijzigt, wijzigt het ook in `graph.js`.

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
