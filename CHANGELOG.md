# Wat er veranderde

Dit package zit straks in zes applicaties op een gepinde versie. Wat hier staat
is wat je moet weten voor je die pin verzet.

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
