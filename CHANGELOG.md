# Wat er veranderde

Dit package zit straks in zes applicaties op een gepinde versie. Wat hier staat
is wat je moet weten voor je die pin verzet.

## 0.8.0

### Nieuw

Wat socialtail en signagetail elk voor zich hadden, of allebei als kopie.

- **Grafieken**: `<x-chart.line>`, `<x-chart.columns>`, `<x-chart.bars>` en
  `<x-chart.heatmap>`, op de server getekend als SVG. Een as, een tabel onder
  elke lijn- en kolomgrafiek, een `data-tip` op elk teken. Het rekenwerk staat
  in `HansDeBoeck\HansUi\Chart` (`scale`, `tick`, `series`).
- **Grafiekkleuren**: `--series-1` tot `--series-8` en `--seq-0` tot `--seq-6`,
  met een eigen donkere reeks in beide donkere blokken. Nagekeken op
  onderscheid bij kleurenblindheid tegen wit en tegen `--n-0` in het donker.
- **`<x-delta>`** en **`change`/`invert` op `<x-stat>`**: het verschil met de
  vorige periode naast een kerncijfer.
- **Zevenendertig iconen** in `<x-icon>`, onder meer `link`, `inbox`, `globe`,
  `send`, `eye`, `heart`, `share`, `comment`, `reply`, `hash`, `sparkles`,
  `copy`, `external`, `upload`, `download`, `trend-up`, `trend-down`, `play`,
  `pause`, `refresh`, `filter`, `layers`, `drag`, `printer`, `video`.
- **Klassen voor een bibliotheek**: `.tree-item`, `.media-tile` met
  `.media-check` en `.media-badge`, `.dropzone`, `.meter` met `.meter-fill`,
  `.locked`, en de utilities `.grid-tiles`, `.grid-tiles-lg` en `.no-select`.
- **Gedrag**: `data-tip` (tooltips), het zoekpalet (`data-palette` en zijn
  onderdelen, het antwoord op `open-palette`), uploaden (`data-uploader` en
  zijn onderdelen) en `data-turnstile-melding`. De teksten van de uploader
  komen uit `data-upload-texts`, die van de botcontrole uit de waarde van
  het attribuut.

### Opgelost

- **`.check` op een `<label>`** maakte de label zelf 16 bij 16 pixels, zodat
  de tekst er letter per letter onder liep. Op een label wordt het nu een rij,
  en krijgt het vinkje erin de maat.

### Wat je moet doen

Haal uit je applicatie weg wat nu hier staat: dezelfde klassen in `app.css`,
eigen kopieën van het palet, de uploader, de botcontrole en de tooltip, en
eigen grafiekcomponenten. Blijven ze staan, dan wint die van de applicatie
(componenten) of staan de regels dubbel (css), en dan loopt het op den duur
uit elkaar.

Een `data-turnstile-melding` zonder waarde toont de Nederlandse terugvaltekst;
geef hem een vertaalde waarde. Voor de uploader geldt hetzelfde met
`data-upload-texts`.

## 0.7.0

### Opgelost

- **Het icoontje van het gekozen item in de donkere zijbalk was in de lichte
  stand niet te lezen.** Het stond op `--brand-deep`, en dat is de merkkleur
  voor een LICHT vlak: in de lichte stand 80% naar zwart gemengd. De zijbalk is
  daar niet licht -- `--nav` is in beide standen donker -- dus die verdiepte
  inkt kwam op de gekozen rij uit op 1,03:1. Het icoontje stond er wel, en je
  zag alleen dat er iets paars stond.

  Nieuw token **`--nav-accent`**, bij `--nav-hover`, `--nav-active` en
  `--nav-group-ink` in het blok dat niet meekantelt, met
  `var(--brand-primary-dark)` erin. `.nav-light` zet hem terug naar
  `--brand-deep`, want die navigatie staat wel op een vlak dat meekantelt.

  Met de merkkleur van signagetail gaat de gekozen rij van 1,03:1 naar 4,93:1
  in de lichte stand; in de donkere zakt ze van 7,91:1 naar 5,88:1, omdat de
  kleur nu in beide standen dezelfde is.

  **Wat je moet doen.** Niets, tenzij je het accent anders wil: zet dan
  `--nav-accent` op je zijbalk. Wie geen `--brand-primary-dark` opgeeft, houdt
  de afgeleide variant en dus ook het oude gedrag in de donkere stand.

## 0.6.0

### Verwijderd

- **Het puntje van `.badge` en de bol van `.alert`**, samen met `plain` op
  allebei de componenten en de klassen `.badge-plain` en `.alert-plain`.

  Bij de melding was de afweging vorige versie al gemaakt en stond ze in dit
  bestand: de bol is in alle vier de varianten dezelfde cirkel in de kleur die
  ook al in de rand zit, dus hij herhaalt wat er staat. Wat toen als knop
  binnenkwam, is nu de standaard.

  Bij de badge lag het anders. Daar was het argument niet "groen van rood
  onderscheiden" maar "dit als status laten lezen tussen gewone tekst in een
  tabel". Dat is een echt argument, alleen doet de pilvorm met een
  achtergrondkleur dat al; het puntje voegde er geen kanaal aan toe dat er niet
  was. En een schakelaar die je op de helft van de badges omzet, is een
  schakelaar die zegt dat de standaard niet klopt.

  **Wat je moet doen.** Zoek in je views naar `plain` op `<x-badge>` en
  `<x-alert>` en haal het weg. Blijft het staan, dan belandt het als los
  attribuut in de uitvoer -- lelijk, maar niet stuk.

  De melding schuift links iets in: waar de bol stond, begint nu de tekst.

### Weg uit de tests

- `StylesheetTest::elke_plain_schakelaar_haalt_het_puntje_ook_echt_weg()`. Die
  schraapte de `*-plain`-klassen uit de views en had een `assertNotEmpty` als
  vangnet voor het geval hij niets vond. Er is nu niets meer te vinden, en een
  test die een verdwenen schakelaar bewaakt, bewaakt niets.

## 0.5.0

### Toegevoegd

- **`plain` op `<x-alert>`**, en de klasse `.alert-plain`. Haalt de bol links
  weg.

  Dat mag hier, anders dan bij `<x-badge>`, en het verschil is de hele afweging.
  Het puntje van een badge draagt VORM waar de kleur alleen niet volstaat: een
  badge staat tussen gewone tekst in een tabel, en het puntje is wat hem als
  status leest. Op een melding is dat er al -- de achtergrond, de rand en de
  plaats op het scherm zeggen samen wat het is -- en de bol is in alle vier de
  varianten dezelfde cirkel in de kleur die ook al in de rand zit. Hij herhaalt
  dus wat er staat.

  Standaard blijft hij staan, want er is één plaats waar hij wel iets doet: een
  rij korte meldingen onder elkaar, waar hij het begin van elke regel markeert.
  Zet hem af op een losse melding die als zin moet lezen.

## 0.4.0

Gebaren. De vorige versie ging over overgangen: dingen die vanzelf bewegen als
er iets verandert. Deze gaat over wat er gebeurt terwijl een vinger op het
scherm ligt, en dat was in dit package precies een ding -- herordenen -- dat het
op een telefoon niet deed.

### Gerepareerd

- **`data-sortable` werkte niet op een aanraakscherm.** Hier stond HTML5
  drag-and-drop, en Android Chrome vuurt daar geen `dragstart` voor vanuit een
  aanraking terwijl iOS Safari het alleen via zijn eigen sleepmechanisme doet.
  De README beloofde herordenen; op de helft van de apparaten gebeurde er niets.
  Nu op Pointer Events, en dus overal.
- **Een trage veeg had geen snelheid.** Het venster waarover gemeten wordt liep
  vanaf `nu` terug in plaats van vanaf het laatste punt, en vond bij trage
  bewegingen dat laatste punt zelf: oudste en laatste waren dan hetzelfde en de
  uitkomst werd nul. Dit kwam pas boven bij het meten, niet bij het lezen.

### Toegevoegd

- **Vier helpers voor gebaren** in `hansui.js`, onder "Gebaren": een spoor dat
  de snelheid bijhoudt, een veer met de twee knoppen van Apple (`demping` en
  `respons`) in plaats van de drie uit de natuurkunde, de projectie waarmee een
  scrollende pagina uitloopt, en weerstand voorbij een rand. Wie hier een gebaar
  bij bouwt, hoort deze te gebruiken en geen vijfde manier te verzinnen.

  De veer is er om een reden die je pas ziet als hij ontbreekt: een CSS-overgang
  begint altijd bij snelheid nul. Hoe hard je ook geveegd hebt, op het moment
  van loslaten staat het ding even stil. Dat is de naad tussen slepen en
  animeren.
- **`data-sortable-handle`**, optioneel, ergens in een kind. Dan sleep je alleen
  daaraan. Zonder greep pakt een vinger de hele rij, en omdat verticaal slepen
  ook scrollen is, moet die dan eerst 300ms lang drukken. Met een greep vervalt
  dat wachten.
- **De zijbalk sluit met een veeg.** De beslissing hangt aan de SNELHEID en niet
  aan de afstand: een korte, snelle veeg sluit, ook al is de balk dan nog bijna
  helemaal open. Wie halverwege van gedachten verandert, kan hem tijdens het
  wegglijden weer vastpakken en terugtrekken -- vanaf waar hij op dat moment
  staat, zonder sprong. Alleen sluiten en niet openen: openen met een veeg
  vanaf de linkerrand vecht met het terug-gebaar van de browser.

### Wat er niet in zit

**Herordenen met het toetsenbord.** Dat is een gat en geen keuze: wie niet kan
slepen, kan een `data-sortable` niet ordenen. Het is een eigen ontwerp --
oppakken, verplaatsen, neerleggen, en een schermlezer die zegt wat er gebeurt --
en geen regel of tien bij het bovenstaande. Tot dat er is: zorg dat de volgorde
ook ergens anders te wijzigen is als ze er echt toe doet.

## 0.3.0

Beweging. Dit bestand zette alles op tokens behalve die, en de uitzondering
kostte hier het meest: elke overgang nam Tailwinds standaard mee (150ms op
`cubic-bezier(0.4, 0, 0.2, 1)`, een in-out curve die traag begint), geen enkel
aanklikbaar element gaf terugkoppeling bij het indrukken, en de dingen die het
vaakst open- en dichtgaan hadden helemaal geen overgang.

LET OP BIJ HET VERZETTEN VAN DE PIN. `--ease-out` en `--ease-in-out` liggen nu
op Tailwinds eigen namen, net als de grijsschaal: een `ease-out` die al in een
view van jouw applicatie staat, krijgt de sterke variant. Dat is de bedoeling,
maar het is wel een zichtbare wijziging die je nergens hebt aangevraagd.

### Gewijzigd

- **Drie curves als token.** `--ease-out`, `--ease-in-out` en `--ease-drawer`.
  De eerste twee liggen op Tailwinds eigen namen, net als de grijsschaal: wie
  `ease-out` in een view typt, krijgt voortaan de sterke variant. `@layer base`
  wint van de theme-laag, dus daar is verder niets voor nodig.
- **Elk aanklikbaar element deukt in.** `.btn`, `.tab`, `.choice`, `.nav-item`
  en `.alert-dismiss` krijgen `scale()` op `:active`, uit `--press` of
  `--press-soft` -- de zachte variant voor een breed vlak, waar drie procent
  een schok is in plaats van een bevestiging. Op een formulier dat een seconde
  nadenkt is dit het verschil tussen "hij doet het" en nog een keer duwen.
- **Vier dingen kwamen uit het niets.** Het uitklappaneel van
  `<x-nav-dropdown>`, het venster van `<x-modal>` met zijn waas, en de melding
  die je wegklikt: alle vier gingen ze van er niet zijn naar er staan zonder
  tussenstap. Nu met `@starting-style` en `transition-behavior: allow-discrete`,
  dus zonder JavaScript dat klassen bijhoudt. Het paneel groeit uit zijn knop
  (`transform-origin` volgt de `left-0`/`right-0` die de component al zet); het
  venster blijft uit het midden komen, want dat hangt aan geen enkele knop.
- **De zijbalk op de curve van een lade.** 0.2s op `ease` was te kort en te
  slap voor een paneel dat de volle hoogte aflegt: hij schoot erin. Nu 320ms op
  de curve die iOS zijn laden geeft. De waas eronder vervaagt in dezelfde duur
  en dezelfde curve mee, in plaats van er hard in te knallen terwijl de balk
  ernaast gleed.
- **De hover achter `@media (hover: hover)`.** Op een aanraakscherm bleef hij
  na een tik hangen, en bij `.choice` en `.nav-item` leest dat als "deze is
  gekozen". Het is dezelfde voorwaarde die Tailwind v4 zelf aan zijn
  hover-variant hangt, dus een `.choice` gedraagt zich nu als een
  `hover:bg-gray-50` drie regels verderop in dezelfde view.
- **Benoemde eigenschappen in plaats van `transition`.** Dat kortschrift zet er
  twintig tegelijk aan en laat de curve en de duur impliciet.

### Gerepareerd

- **`prefers-reduced-motion` maakte van een spinner een flikkering.** De regel
  zette `animation-duration: 0.001ms` op alles, zonder
  `animation-iteration-count`. Een animatie met `infinite` -- `animate-spin`
  staat in elke applicatie -- werd daardoor geen stilstaand beeld maar een
  animatie die duizenden keren per seconde opnieuw begint, uitgerekend voor de
  lezer die om minder beweging vroeg. En het sloeg te breed: nu gaan
  `--press`, `--press-soft` en `--scale-in` naar 1 en staat de lade meteen op
  haar plaats, terwijl de vervagingen blijven. Minder beweging is zachter, niet
  niets.
- **De kopieerknop versprong van breedte.** "Gekopieerd" is breder dan
  "Kopieer", dus de knop groeide en alles ernaast schoof op -- onder de muis
  van wie er net op geklikt had. `hansui.js` meet nu bij het laden (na
  `document.fonts.ready`, anders meet je een ander lettertype) beide woorden en
  houdt de breedste ruimte vrij. De wissel zelf loopt via een lichte blur:
  zonder blur zie je twee losse woorden over elkaar en leest het als een
  omwisseling, met blur als een verandering.
- **Een weggeklikte melding verdween met `.remove()`**, van staan naar weg
  zonder tussenstap. Dat leest als een fout in de pagina in plaats van als iets
  dat je zelf deed. `data-uit` zet nu de overgang in gang; het opruimen gebeurt
  daarna, op `transitionend` en op een timer -- want die eerste komt niet altijd.

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
  verse pagina -- en die tekende het venster dicht. `.modal` zet zelf
  `margin: auto` terug: de browser centreert een open dialog daarmee, maar
  Preflight zet `margin: 0` op alles en wint van de useragent -- zonder die
  regel plakt elk venster in de linkerbovenhoek.
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
- **Zevenendertig iconen erbij** in `<x-icon>`, plus een `size`-prop voor een
  andere maat. Via `class` ging dat mis: die wordt bij de standaard gemerged, en
  Tailwind sorteert oplopend -- `class="h-3.5 w-3.5"` verloor dus stil van de
  `h-4` die de component zelf meebrengt. De namen zijn generiek -- van
  `briefcase` tot `pin`, `bank` en niet `sbc`: een naam die naar het domein van
  een applicatie verwijst, wordt in de tweede applicatie niet meer gevonden, en
  dan komt dezelfde tekening er onder een tweede naam bij.

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
