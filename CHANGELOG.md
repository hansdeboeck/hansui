# Wat er veranderde

Dit package zit straks in zes applicaties op een gepinde versie. Wat hier staat
is wat je moet weten voor je die pin verzet.

## 0.2.0

Het paneel van `streek` stapte over van Metronic naar dit package, en dat is de
tweede soort consument die dit package tot nu toe niet had: geen applicatie die
haar eigen componentlaag verving, maar een die uit een gekocht thema kwam. Wat
daar generiek aan bleek, staat hier nu; wat het domein van streek kent -- jobs,
rangen, dispatch -- bleef daar.

### Toegevoegd

- **`<x-section>`**: de kaart met een kopregel erboven. Dat was het gat tussen
  `<x-card>` (kaal vlak) en `<x-table>` (tekent zijn eigen kop): in het paneel
  van streek stond die vorm drieentachtig keer met de hand, en de helft ervan
  had de ondertitel of de knop net ergens anders staan.
- **`<x-stat>`**: het kerncijfer van een dashboard, met `.tone-`-klassen voor
  het vlak achter de icoon. Vijfentwintig keer nagebouwd, in vier maten -- en
  twee cijfers naast elkaar in verschillende maten zien er even belangrijk uit
  terwijl ze het niet zijn.
- **`<x-modal>`**, als echte `<dialog>` met `data-modal`, `data-modal-open` en
  `data-modal-close` in `hansui.js`. Nagebouwde vensters moeten zelf de focus
  vasthouden, `Escape` afvangen, de pagina eronder verbergen en het waas
  tekenen; `showModal()` doet die vier. `:open` zet het venster meteen open,
  want een formulier in een venster komt met zijn validatiefouten terug op een
  verse pagina -- en die tekende het venster dicht.
- **`<x-avatar>`**: een foto, of de eerste letter als er geen is. De terugval is
  het punt: waar ze ontbrak stond er een gebroken plaatje.
- **`<x-choice>`** en `.choice`: een radioknop als tegel. De aangevinkte staat
  komt uit `:has(:checked)` en niet uit JavaScript dat een klasse bijhoudt --
  dat laatste klopt niet meer zodra een formulier met oude invoer terugkomt.
- **`<x-tabs>`** en `.tab`: de pillenrij tussen samenhangende schermen, op
  `aria-current` net als `.nav-item`.
- **`<x-alert>`**: dezelfde balk als de flash-partial, maar voor waar een scherm
  zelf iets te zeggen heeft.
- **`.check`** voor vinkjes en radioknoppen: het native element in de merkkleur,
  via `accent-color`. En **`color-scheme`** in alle drie de themablokken, zodat
  wat de browser zelf tekent -- een vinkje, de uitklaplijst van een `<select>`,
  een scrollbalk, een datumkiezer -- meekantelt. Dat is de enige plek waar
  tokens niet bij kunnen, en ze bleven wit op een donkere pagina.
- **`.line-dark`**, de scheidingslijn op een altijd-donker vlak. `border-white/10`
  kantelt daar mee met het thema en verdwijnt dus in de lichte stand.
- **Een slot op `<x-footer>`**, zodat een applicatie haar eigen regel kwijt kan
  zonder de component over te slaan.
- **Zevenendertig iconen erbij** in `<x-icon>`, van `briefcase` tot `pin`. De
  namen zijn generiek en niet die van een applicatie: `bank` en niet `sbc`,
  `flame` en niet `brandweer` -- anders wordt dezelfde tekening in de tweede
  applicatie onder een tweede naam opnieuw toegevoegd.

### Gewijzigd

- **`<x-money :decimals="0">`** voor bedragen zonder centen. Een spelsaldo of
  een begroting in duizenden toont er geen, en "€ 1.284.000,00" is vier tekens
  ruis in een kolom die toch al breed is. De standaard blijft twee.

## 0.1.0

De eerste uitgebrachte versie, en meteen de eerste die ergens onder ligt:
`bugtail` verving zijn hele eigen componentlaag hierdoor.

### Gerepareerd

- **`<x-nav-dropdown>` ging nooit open.** De component kwam byte-identiek uit
  `shippingtail`, maar de listener waarop ze leunde bleef achter. `hansui.js`
  kent nu `data-dropdown`: klik buiten sluit, `Escape` sluit, `aria-expanded`
  volgt, en twee panelen staan nooit samen open.
- **De thema- en dichtheidskeuze werd vergeten.** De knop schreef naar
  `growtail.theme` en `growtail.density`; het script in de `<head>` las
  `hansui.theme` en `hansui.density`. Nu allebei `hansui.*`, en bij "ruim"
  wordt de sleutel gewist in plaats van op een tegenwaarde gezet.
- **Knoppen toonden niet waar de focus stond.** `.btn` zette
  `focus-visible:outline-none` zonder vervanging, en omdat `@layer components`
  van `@layer base` wint, haalde dat de ring uit de basislaag weg. `.btn` heeft
  nu een eigen ring, in dezelfde vorm als `.input`.
- **Flash-meldingen waren onleesbaar in donkere modus.** `emerald-200`,
  `emerald-800` en `red-800` stonden niet in `@theme`, dus die bleven Tailwinds
  eigen kleur houden terwijl de achtergrond wel meekantelde: donkergroen op
  bijna-zwart, contrast 1,4:1. De semantische schalen zijn nu sluitend — 50 tot
  200 zacht, 300 tot 950 vol — en de melding heeft een eigen `.alert`.
- **`<x-detail-list :stacked>` had geen opmaak.** Het `data-detail-stacked` dat
  de component uitzond, kwam nergens aan; `space-y-3` zette bovendien evenveel
  ruimte tussen een label en zijn eigen waarde als tussen twee paren.

### Gewijzigd

- **Afwijken gaat nu via `--brand-primary`** (en optioneel
  `--brand-primary-dark`), niet meer via `--brand`. De rest van de familie —
  `--brand`, `--brand-deep`, `--brand-soft`, `--brand-line`, `--on-brand` —
  wordt eruit afgeleid. Daarvoor stond die familie vast: een applicatie die
  `--brand` overschreef kreeg een knop die bij hover van kleur wisselde, en in
  donkere modus bijna-zwart op haar eigen donkere merkkleur.
- **De componenten gebruiken de utilities die dit package zelf aanlegt.**
  Negenentwintig inline `style=`-attributen zijn vervangen door
  `text-gray-900`, `border-gray-200` en de rest. Die zijn voor een applicatie
  overschrijfbaar, overleven een strikte CSP, en het package volgt eindelijk
  zijn eigen argument.
- **De foutpagina zet geen merk meer vast.** Standaard de beginletter uit
  `config('app.name')`, met de secties `logo` en `owner` om af te wijken. En
  `@vite` staat achter een controle op het manifest, want anders geeft de
  foutpagina zelf een fout.
- **`<x-nav-dropdown>` verwijst intern met de prefix** (`<x-hansui::icon>`).
  Zonder prefix zou een applicatie die `x-icon` overschrijft, haar eigen icoon
  in ons paneel krijgen.

### Toegevoegd

- `.alert` + `.alert-success` / `-danger` / `-warning` / `-info`,
  `.dropdown-panel`, `.link-muted`, `.on-dark-soft`, `.surface-dark-tint`.
- **Vijf componenten waar alleen de klasse al bestond**: `<x-button>`,
  `<x-badge>`, `<x-card>`, `<x-code-block>` en `<x-key-value>`. De eerste drie
  schreef elke applicatie zelf boven op `.btn`, `.badge` en `.card` -- dezelfde
  opmaak, net anders opgeschreven, wat hier de aanleiding voor alles is.
- **`.nav-light`**, voor een navigatie op een licht vlak. `.nav-item` had zijn
  twee overlays hardgecodeerd als `rgb(255 255 255 / ...)`, en die zijn
  onzichtbaar op wit. Ze zijn nu `--nav-hover`, `--nav-active` en
  `--nav-group-ink`, zodat de lichte variant dezelfde klasse gebruikt in plaats
  van hem te forken.
- **`.badge-solid`**, voor het uiterste van een schaal: fataal naast fout,
  geblokkeerd naast open. Zonder dat verschil leest de ergste regel als de rest.
- **`data-copy-target`** in `hansui.js`. `data-copy` zet de haak op het veld
  zelf, en dat werkt niet voor een blok code: daar hoort de knop ernaast, want
  een klik in de tekst is een klik om te selecteren.
- **`lang/en.json`**, geladen en niet gepubliceerd. De sleutels van dit package
  zijn Nederlands, dus een applicatie op `en` kreeg er Nederlands uit -- en dat
  merk je pas in een scherm, want er ontbreekt niets.
- Een testsuite die bewaakt wat dit soort package stil laat afbrokkelen: dat
  elke component tekent, dat de twee donkere paletten gelijk blijven, dat elke
  Tailwind-tint uit een view op een token ligt, en dat er geen `data-`-haak in
  een view staat waar niets op luistert.
- Een hoofdstuk **Gedrag** in de README. De hele data-attribuutlaag stond
  nergens beschreven.

### Onderhoud

- `HansUiServiceProvider::COMPONENTS` werd nergens gebruikt terwijl het
  commentaar beweerde dat de lijst de registratie stuurde. De lijst is nu een
  inventaris die door een test tegen de map wordt gehouden.
- De provider gebruikt `$this->app->langPath()` en `resourcePath()` in plaats
  van de globale helpers. Die wonen in `illuminate/foundation`, dat dit package
  niet vraagt.
