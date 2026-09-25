/*
| HansUI -- gedrag via data-attributen.
|
| Geen framework, geen build-afhankelijkheid, geen inline JavaScript in de
| Blade-views. Elk stuk gedrag hangt aan een data-attribuut, zodat een view
| leesbaar blijft en dit bestand de enige plaats is waar de gebeurtenissen
| vandaan komen.
|
| Alles luistert op `document` en niet op de elementen zelf: de helft van deze
| schermen wordt door Livewire opnieuw getekend, en een listener op een element
| dat vervangen wordt, is een listener die stilletjes ophoudt te werken.
|
| Wat dit bestand levert:
|
|   data-dismiss / data-flash    een melding wegklikken
|   data-menu-toggle             een uitklapmenu open- en dichtklappen
|   data-dropdown*               een uitklappaneel in de navigatiebalk
|   data-modal*                  een venster openen en sluiten
|   data-copy                    klik om te kopieren
|   data-confirm                 bevestiging voor een gevaarlijke actie
|   data-autosubmit              een filter dat zijn formulier meteen indient
|   data-bulk*                   rijen aanvinken en er samen iets mee doen
|   data-sidebar*                de zijbalk op smalle schermen
|   data-theme-toggle            licht of donker
|   data-density-toggle          compacte of ruime regels
|   data-palette-open            een venster-event voor een zoekpalet
|   data-regel*                  herhaalbare formulierregels
|   data-sortable*               slepen om te herordenen, met PUT naar de server
|   data-crop*                   een beeld bijsnijden, met muis, vinger of toetsenbord
|   data-context-*               een contextmenu, met waarden per element ingevuld
|   data-grid-nav                een raster doorlopen met de pijltjes
|   data-drag-item / data-drop-target   iets naar een doel slepen, met POST
|   data-hover-play              een video die speelt als je erover gaat
|   data-lightbox*               een groot voorbeeld met vorige en volgende
|   data-async                   een formulier bewaren zonder de pagina te verlaten
|   data-shortcut                een toets die doet wat een klik doet
|   data-kbd-mod                 Ctrl in een toets wordt ⌘ op een Mac
|   data-autogrow                een tekstvak dat meegroeit
|   data-draft                   onthouden wat je typte, tot het vertrokken is
|   data-count                   tekens tellen, met een grens
|   data-composer-*              het antwoordvak onder een gesprek
|   data-insert                  een tekst invoegen waar de cursor staat
|   data-scroll-here             bij het laden in beeld, in een lijst die zelf scrolt
|
| De vier helpers onder "Gebaren" -- spoor, veer, projectie en rubberband --
| zijn wat de zijbalk en het herordenen delen. Wie hier een gebaar bij bouwt,
| hoort ze te gebruiken en geen vijfde manier te verzinnen.
|
| De applicatie importeert dit in haar eigen app.js en voegt daar haar eigen
| helpers aan toe.
*/
/*
| Flash-meldingen sluiten.
|
| NIET meteen .remove(). Dat haalde de melding van staan naar weg zonder
| tussenstap, en iets dat zonder overgang verdwijnt leest als een fout in de
| pagina in plaats van als iets dat de gebruiker zelf deed. `data-uit` zet de
| overgang in gang -- die staat bij .alert in hansui.css -- en pas daarna gaat
| het element eruit.
|
| Opruimen op transitionend EN op een timer, want de eerste komt niet altijd:
| wie om minder beweging vroeg krijgt een overgang van 0,01ms die al voorbij
| kan zijn voor de listener erop staat, en een melding die geen .alert is
| (een applicatie mag data-flash overal op zetten) heeft helemaal geen
| overgang. Een melding die na het wegklikken blijft staan, is erger dan een
| die er hard uit springt.
*/
document.addEventListener('click', (e) => {
    const dismiss = e.target.closest('[data-dismiss]');
    if (!dismiss) {
        return;
    }

    const melding = dismiss.closest('[data-flash]');
    if (!melding) {
        return;
    }

    melding.setAttribute('data-uit', '');
    melding.addEventListener('transitionend', () => melding.remove(), { once: true });
    setTimeout(() => melding.remove(), 300);
});

/*
| Een uitklapmenu open- en dichtklappen.
|
| Growtail heeft dit niet meer -- daar verving een zijbalk de bovenbalk -- maar
| ota, pos, shippingtail en deboeckdev2 hangen er alle vier aan. Dat kwam pas
| boven toen ota als tweede consument overstapte, en dat is precies waarvoor een
| tweede consument dient.
|
| `data-menu-toggle` zonder waarde klapt #mobile-menu om: dat is de vorm die in
| die vier applicaties al staat. Met een waarde is het een selector, zodat een
| scherm er meer dan een kan hebben.
*/
document.addEventListener('click', (e) => {
    const toggle = e.target.closest('[data-menu-toggle]');
    if (!toggle) {
        return;
    }

    const doel = document.querySelector(toggle.getAttribute('data-menu-toggle') || '#mobile-menu');
    if (!doel) {
        return;
    }

    const open = doel.classList.toggle('hidden') === false;
    toggle.setAttribute('aria-expanded', String(open));
});

/*
| Een uitklappaneel in de navigatiebalk -- <x-nav-dropdown>.
|
| Dit stond in shippingtail en pos, en de component is hier byte-identiek
| binnengekomen ZONDER het gedrag dat eronder hing: het paneel kreeg 'hidden'
| mee en niets haalde dat er ooit af. Vandaar dit blok.
|
| Drie manieren om te sluiten, en ze zijn alle drie nodig. Buiten klikken, want
| dat is wat iemand doet die zich bedacht heeft. Escape, want de zijbalk kan het
| ook en een schil met twee verschillende antwoorden op dezelfde toets is een
| schil die je moet onthouden. En een tweede dropdown openen, want twee panelen
| die tegelijk over de balk hangen, overlappen elkaar.
*/
function hansuiSluitDropdowns(behalve = null) {
    document.querySelectorAll('[data-dropdown]').forEach((drop) => {
        if (drop === behalve) {
            return;
        }

        drop.querySelector('[data-dropdown-panel]')?.classList.add('hidden');
        drop.querySelector('[data-dropdown-toggle]')?.setAttribute('aria-expanded', 'false');
    });
}

document.addEventListener('click', (e) => {
    const toggle = e.target.closest('[data-dropdown-toggle]');

    if (toggle) {
        const drop = toggle.closest('[data-dropdown]');
        const paneel = drop?.querySelector('[data-dropdown-panel]');

        if (!paneel) {
            return;
        }

        const open = paneel.classList.toggle('hidden') === false;
        toggle.setAttribute('aria-expanded', String(open));
        hansuiSluitDropdowns(drop);

        return;
    }

    // Een klik BINNEN een paneel laat dat paneel staan -- er staan
    // formulieren en submenu's in -- en sluit alleen de andere.
    hansuiSluitDropdowns(e.target.closest('[data-dropdown]'));
});

document.addEventListener('keydown', (e) => {
    if (e.key !== 'Escape') {
        return;
    }

    // De focus terug naar de knop die het paneel opende: wie met het
    // toetsenbord sluit, staat anders in een paneel dat er niet meer is.
    const open = document.querySelector('[data-dropdown-toggle][aria-expanded="true"]');
    hansuiSluitDropdowns();
    open?.focus();
});

/*
| Het venster van <x-modal>.
|
| De <dialog> doet het werk -- showModal() legt de focus vast, verbergt de rest
| van de pagina voor een schermlezer, sluit op Escape en tekent zijn eigen waas.
| Wat hier staat is alleen WANNEER: welke knop opent, welke sluit.
|
| Een klik OP de dialog zelf is een klik op het waas: de kinderen vullen hem
| helemaal (padding: 0), dus alles wat binnenin gebeurt heeft een ander doelwit.
| Vandaar dat die ene vorm sluit en een klik in het venster niet.
*/
document.addEventListener('click', (e) => {
    const opener = e.target.closest('[data-modal-open]');
    if (opener) {
        document.querySelector(opener.getAttribute('data-modal-open'))?.showModal?.();
        return;
    }

    if (e.target.closest('[data-modal-close]')) {
        e.target.closest('dialog')?.close();
        return;
    }

    if (e.target.matches?.('dialog[data-modal]')) {
        e.target.close();
    }
});

/*
| Een venster dat meteen open moet: data-modal="open".
|
| Dit is de terugweg van een formulier IN een venster. De invoer komt met een
| validatiefout terug op een verse pagina, en die pagina tekent het venster
| dicht -- de gebruiker ziet dan een lijst met fouten die nergens naar wijst.
|
| Niet blind op DOMContentLoaded: als dit bestand als module geladen wordt
| (Vite doet dat) is de DOM er al en komt die gebeurtenis nooit meer.
*/
function hansuiOpenVensters() {
    document.querySelectorAll('dialog[data-modal="open"]').forEach((d) => d.showModal?.());
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', hansuiOpenVensters);
} else {
    hansuiOpenVensters();
}

// Klik-om-te-kopiëren voor readonly velden (secrets, URLs, tokens).
document.addEventListener('click', async (e) => {
    const field = e.target.closest('[data-copy]');
    if (!field) {
        return;
    }

    field.select?.();

    try {
        await navigator.clipboard.writeText(field.value || field.textContent || '');
        field.setAttribute('title', 'Gekopieerd');

        // Een knop in een contextmenu verdwijnt met het menu; zonder melding
        // weet niemand of het gelukt is. `data-copied` zegt het dan even
        // onderaan het scherm.
        if (field.hasAttribute('data-copied')) {
            hansuiToast(field.getAttribute('data-copied'));
        }
    } catch (_) {
        // Clipboard niet beschikbaar: de selectie volstaat om te kopiëren.
    }
});

/*
| Een knop die iets ANDERS kopieert dan zichzelf.
|
| data-copy zet de haak op het veld zelf, en dat werkt zolang het veld een
| invoerveld is dat je mag aanklikken. Voor een blok code werkt het niet: daar
| hoort de knop ERNAAST te staan, want een klik in de tekst is een klik om te
| selecteren. data-copy-target wijst met een selector aan wat er mee moet.
|
| De knop zegt even DAT het gelukt is, met de tekst uit data-copied -- dat woord
| hoort uit de vertaling van de applicatie te komen en niet uit dit bestand.
|
| De oorspronkelijke tekst wordt maar EEN keer onthouden. Twee klikken snel na
| elkaar zouden anders "Gekopieerd" als het origineel bewaren, en dan staat dat
| woord er voorgoed.
*/
document.addEventListener('click', async (e) => {
    const knop = e.target.closest('[data-copy-target]');
    if (!knop) {
        return;
    }

    const doel = document.querySelector(knop.getAttribute('data-copy-target'));
    if (!doel) {
        return;
    }

    try {
        await navigator.clipboard.writeText(doel.value ?? doel.textContent ?? '');
    } catch (_) {
        // Clipboard geweigerd. Dan liever niets beweren: een knop die
        // "Gekopieerd" toont terwijl het plakbord leeg is, is erger dan een
        // knop die niets doet.
        return;
    }

    const bevestiging = knop.getAttribute('data-copied');
    if (!bevestiging) {
        return;
    }

    knop.hansuiKopieerTekst ??= knop.textContent;
    hansuiReserveerBreedte(knop);

    clearTimeout(knop.hansuiKopieerTimer);
    hansuiWisselTekst(knop, bevestiging);

    knop.hansuiKopieerTimer = setTimeout(() => {
        hansuiWisselTekst(knop, knop.hansuiKopieerTekst);
    }, 1500);
});

/*
| De ruimte voor het langste woord vrijhouden.
|
| "Gekopieerd" is breder dan "Kopieer", dus de knop werd breder en alles
| ernaast schoof op -- onder de muis van wie er net op geklikt had. Beide
| woorden opmeten en de breedste nemen: min-width op alleen de oorspronkelijke
| breedte lost niets op, want dat houdt een knop tegen bij het KRIMPEN en de
| sprong hier is er een naar boven.
|
| Het meten is synchroon: tussen het zetten en het terugzetten van de tekst
| tekent de browser niet, dus er flitst niets.
|
| Een knop die nog verborgen is -- in een dicht uitklappaneel -- meet 0 breed.
| Die slaan we over, anders staat er een breedte van nul vast en wordt er nooit
| meer gemeten.
*/
function hansuiReserveerBreedte(knop) {
    const bevestiging = knop.getAttribute('data-copied');
    if (!bevestiging || knop.style.minWidth) {
        return;
    }

    knop.hansuiKopieerTekst ??= knop.textContent;

    const smal = knop.offsetWidth;
    knop.textContent = bevestiging;
    const breed = knop.offsetWidth;
    knop.textContent = knop.hansuiKopieerTekst;

    if (Math.max(smal, breed) > 0) {
        knop.style.minWidth = Math.max(smal, breed) + 'px';
    }
}

/*
| En dat AL BIJ HET LADEN, niet pas bij de eerste klik.
|
| Bij de eerste klik is het te laat: dat is de klik die iemand echt ziet
| gebeuren, en precies daar sprong de knop dan alsnog.
|
| Na document.fonts.ready, want een breedte gemeten in het terugvallettertype
| is de breedte van een ander lettertype. En bij elke klik nog eens, want
| Livewire tekent knoppen bij die er bij het laden niet waren.
*/
document.fonts?.ready.then(() => {
    document.querySelectorAll('[data-copy-target][data-copied]').forEach(hansuiReserveerBreedte);
});

/*
| Van het ene woord naar het andere, met een vervaging ertussen.
|
| De tekst wisselde hard. Zonder blur zie je bij zo'n wissel twee losse
| woorden en leest het als een omwisseling; met blur lopen ze in elkaar over
| en leest het als een verandering -- hetzelfde woord dat iets anders zegt.
| De opmaak staat op [data-wissel] in hansui.css; hier staat alleen wanneer.
|
| Halverwege wisselen en niet aan het eind: dan valt de wissel zelf in het
| onscherpste moment.
*/
function hansuiWisselTekst(knop, tekst) {
    knop.setAttribute('data-wissel', '');
    clearTimeout(knop.hansuiWisselTimer);

    knop.hansuiWisselTimer = setTimeout(() => {
        knop.textContent = tekst;
        knop.removeAttribute('data-wissel');
    }, 80);
}

// Bevestiging vragen voor gevaarlijke acties.
document.addEventListener('submit', (e) => {
    const form = e.target.closest('[data-confirm]');
    if (form && !window.confirm(form.getAttribute('data-confirm'))) {
        e.preventDefault();
    }
});

// Filters die hun formulier meteen indienen bij wijziging.
document.addEventListener('change', (e) => {
    const el = e.target.closest('[data-autosubmit]');
    if (el?.form) {
        el.form.requestSubmit ? el.form.requestSubmit() : el.form.submit();
    }
});

/*
| Meerdere rijen aanvinken en er samen iets mee doen.
|
| De balk verschijnt pas als er iets aangevinkt staat: een lege balk die altijd
| onderaan hangt, kost hoogte op elk scherm dat hij nooit gebruikt. De teller
| staat erin omdat "acht mensen" iets anders is dan "de hele pagina" -- en dat
| verschil hoort te blijken voor je op de knop duwt, niet erna.
|
| TWEE OPSTELLINGEN, en ze werken allebei zonder dat je iets hoeft te zeggen:
|
|  1. De vakjes staan AL in het formulier dat de actie uitvoert. Dan zijn ze zelf
|     de invoer en hoeft er niets gespiegeld te worden.
|  2. De balk bevat een EIGEN formulier -- of meerdere, een per actie. Dan worden
|     de aangevinkte waarden erin gespiegeld als verborgen velden.
|
| Het verschil is af te lezen: staat er een <form> IN de balk, dan is het geval
| twee. Groeit er ooit een derde opstelling bij, dan hoort die hier en niet in
| een applicatie -- deze functie is namelijk twee keer apart geschreven voor ze
| hier belandde, een keer met Engelse attribuutnamen en een keer met Nederlandse.
*/
function hansuiBulk(scope) {
    const items = [...scope.querySelectorAll('[data-bulk-item]')];
    const checked = items.filter((box) => box.checked);

    const bar = scope.querySelector('[data-bulk-bar]');
    if (bar) {
        bar.hidden = checked.length === 0;
    }

    /*
    | De teller. Zonder waarde op het attribuut is het een kaal getal; met
    | `data-bulk-count="zending|zendingen"` schrijft hij het zelfstandig
    | naamwoord erbij. Dat scheelt een applicatie een eigen teller enkel omdat
    | ze "3 zendingen" wil tonen in plaats van "3".
    */
    const counter = scope.querySelector('[data-bulk-count]');
    if (counter) {
        const woorden = (counter.getAttribute('data-bulk-count') || '').split('|');

        counter.textContent = woorden.length === 2
            ? `${checked.length} ${checked.length === 1 ? woorden[0] : woorden[1]}`
            : String(checked.length);
    }

    // Het vinkje bovenaan volgt de rijen: alles aan, alles uit, of half.
    const all = scope.querySelector('[data-bulk-all]');
    if (all) {
        all.checked = checked.length > 0 && checked.length === items.length;
        all.indeterminate = checked.length > 0 && checked.length < items.length;
    }

    /*
    | Opstelling twee: de waarden spiegelen naar elk formulier in de balk.
    |
    | Opnieuw opbouwen en niet bijhouden wat er wegviel: eenvoudiger, en er is
    | geen toestand die uit de pas kan lopen met de vakjes. De veldnaam komt van
    | `data-bulk="ids[]"`; zonder waarde is dat de standaard.
    */
    scope.dispatchEvent(new CustomEvent('bulk:change', {
        bubbles: true,
        detail: { count: checked.length, values: checked.map((box) => box.value) },
    }));

    /*
    | Een formulier BUITEN de balk dat dezelfde selectie nodig heeft -- een
    | venster "Verplaatsen naar" bijvoorbeeld -- wijst met
    | `data-bulk-form="#lijst"` naar de omhulling en krijgt de velden ook.
    */
    const naam = scope.getAttribute('data-bulk') || 'ids[]';
    const buiten = scope.id
        ? [...document.querySelectorAll(`form[data-bulk-form="#${CSS.escape(scope.id)}"]`)]
        : [];

    if (! bar && buiten.length === 0) {
        return;
    }

    [...(bar ? bar.querySelectorAll('form') : []), ...buiten].forEach((form) => {
        form.querySelectorAll('[data-bulk-field]').forEach((veld) => veld.remove());

        checked.forEach((box) => {
            const veld = document.createElement('input');
            veld.type = 'hidden';
            veld.name = form.getAttribute('data-bulk-name') || naam;
            veld.value = box.value;
            veld.setAttribute('data-bulk-field', '');
            form.appendChild(veld);
        });
    });
}

document.addEventListener('change', (e) => {
    const scope = e.target.closest('[data-bulk]');
    if (!scope) {
        return;
    }

    if (e.target.matches('[data-bulk-all]')) {
        scope.querySelectorAll('[data-bulk-item]').forEach((box) => {
            box.checked = e.target.checked;
        });
    }

    if (e.target.matches('[data-bulk-item], [data-bulk-all]')) {
        hansuiBulk(scope);
    }
});

/*
| Selecteren zoals in een verkenner.
|
| Een vinkje per rij volstaat voor tien rijen; voor tweehonderd foto's wil je
| wat elke bestandsverkenner kan: `Shift` + klik kiest alles tussen het vorige
| vinkje en dit, `Ctrl`/`Cmd` + klik op de RIJ (`data-bulk-row`) zet haar
| vinkje om zonder de link erin te volgen, `Ctrl`/`Cmd` + `A` kiest alles en
| `Escape` wist. `Ctrl` + spatie doet op een rij met de focus wat een klik op
| het vinkje doet, voor wie met het toetsenbord werkt en het vinkje niet in de
| tabvolgorde heeft staan.
|
| Het ANKER is het laatste vinkje dat met de hand omging; een bereik neemt
| diens nieuwe toestand over, net zoals een verkenner dat doet.
*/
function hansuiBulkVakje(el) {
    const rij = el.closest('[data-bulk-row]');

    return rij ? rij.querySelector('[data-bulk-item]') : null;
}

function hansuiBulkBereik(scope, tot, aan) {
    const items = [...scope.querySelectorAll('[data-bulk-item]')];
    const van = items.indexOf(scope.hansuiAnker);
    const naar = items.indexOf(tot);

    if (van < 0 || naar < 0) {
        tot.checked = aan;
    } else {
        items.slice(Math.min(van, naar), Math.max(van, naar) + 1).forEach((box) => {
            box.checked = aan;
        });
    }

    hansuiBulk(scope);
}

function hansuiBulkAlles(scope, aan) {
    scope.querySelectorAll('[data-bulk-item]').forEach((box) => {
        box.checked = aan;
    });
    hansuiBulk(scope);
}

function hansuiIsTypveld(el) {
    return !!el?.closest?.('input:not([type="checkbox"]):not([type="radio"]):not([type="button"]):not([type="submit"]), textarea, select, [contenteditable="true"], [contenteditable=""]');
}

document.addEventListener('click', (e) => {
    const box = e.target.closest?.('[data-bulk-item]');
    const scope = e.target.closest?.('[data-bulk]');
    if (!scope) {
        return;
    }

    if (e.target.closest('[data-bulk-clear]')) {
        hansuiBulkAlles(scope, false);
        return;
    }

    // Het vinkje zelf: alleen Shift doet iets extra.
    if (box) {
        if (e.shiftKey && scope.hansuiAnker && scope.hansuiAnker !== box) {
            hansuiBulkBereik(scope, box, box.checked);
        }
        scope.hansuiAnker = box;

        return;
    }

    // Ergens anders op de rij, met een toets erbij.
    const vakje = hansuiBulkVakje(e.target);
    if (!vakje || !(e.ctrlKey || e.metaKey || e.shiftKey) || e.target.closest('[data-context-trigger]')) {
        return;
    }

    e.preventDefault();

    if (e.shiftKey && scope.hansuiAnker) {
        hansuiBulkBereik(scope, vakje, true);
    } else {
        vakje.checked = !vakje.checked;
        hansuiBulk(scope);
    }

    scope.hansuiAnker = vakje;
});

document.addEventListener('keydown', (e) => {
    if (e.defaultPrevented || hansuiMenu.open || hansuiIsTypveld(e.target)) {
        return;
    }

    const scope = e.target.closest?.('[data-bulk]');

    if (scope && (e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'a') {
        e.preventDefault();
        hansuiBulkAlles(scope, true);

        return;
    }

    if (scope && (e.ctrlKey || e.metaKey) && e.key === ' ') {
        const vakje = hansuiBulkVakje(e.target);
        if (vakje && e.target !== vakje) {
            e.preventDefault();
            vakje.checked = !vakje.checked;
            scope.hansuiAnker = vakje;
            hansuiBulk(scope);
        }

        return;
    }

    /*
    | Escape wist, maar alleen als er geen venster open staat (dat sluit
    | eerst) en de focus in de lijst staat, of nergens: wie net op een tegel
    | klikte en dan Escape drukt, heeft de focus vaak op <body>.
    */
    if (e.key === 'Escape' && !document.querySelector('dialog[open]')) {
        const scopes = scope ? [scope] : (e.target === document.body ? [...document.querySelectorAll('[data-bulk]')] : []);

        scopes.forEach((s) => {
            if (s.querySelector('[data-bulk-item]:checked')) {
                hansuiBulkAlles(s, false);
            }
        });
    }
});

/*
| De schil: zijbalk, thema en dichtheid.
|
| Alle drie zijn het voorkeuren van EEN gebruiker op EEN apparaat, niet van de
| organisatie. Ze staan daarom in localStorage en niet in de databank: een
| collega die dezelfde lijst compact wil, hoort daar niemand voor nodig te
| hebben, en een thema hoort niet in een AVG-uittreksel te belanden.
|
| Het zetten van het thema gebeurt AL in een inline script in de <head>, voor de
| eerste verf. Hier staat alleen wat er bij een klik verandert.
*/

/*
|------------------------------------------------------------------------------
| Gebaren
|------------------------------------------------------------------------------
|
| Vier stukjes gereedschap, en ze horen bij elkaar. Een gebaar dat goed aanvoelt
| doet altijd dezelfde vier dingen: het volgt de vinger een-op-een, het onthoudt
| hoe SNEL die vinger ging, het geeft die snelheid door aan wat er na het
| loslaten gebeurt, en het weigert hard te stoppen aan een rand.
|
| Die derde is degene die je ziet als hij ontbreekt. Een CSS-overgang begint
| altijd bij snelheid nul, dus hoe hard je ook geveegd hebt, op het moment dat je
| loslaat staat het ding even stil. Dat is de naad tussen slepen en animeren, en
| een veer is het enige dat hem dichtnaait.
*/

/*
| Waar de vinger was, en hoe snel.
|
| Zes punten en niet twee: de laatste twee punten van een gebaar zijn vaak een
| paar pixels uit elkaar en dat maakt de snelheid een loterij. Over een venster
| van 100ms meten is stabiel en nog steeds actueel.
*/
/*
| De aanwijzer vastpakken, en niet omvallen als dat niet kan.
|
| setPointerCapture gooit als het element niet meer in het document staat, en in
| dit package is dat geen theorie: Livewire hertekent halve schermen, en bij een
| lange druk zitten er 300ms tussen het aanraken en het beginnen. Zonder vangnet
| sneuvelt het hele gebaar met een halve toestand achter.
|
| Zonder vastpakken werkt het gebaar nog steeds -- alleen buiten het element om
| raken we de aanwijzer kwijt. Dat is beter dan niets kunnen slepen.
*/
function hansuiGrijp(el, pointerId) {
    try {
        el.setPointerCapture(pointerId);
    } catch (_) {
        // Zie hierboven.
    }
}

function hansuiSpoor() {
    const punten = [];

    return {
        voeg(x, y) {
            punten.push({ x, y, t: performance.now() });

            if (punten.length > 6) {
                punten.shift();
            }
        },
        snelheid() {
            const laatste = punten[punten.length - 1];

            /*
            | Een vinger die 100ms stil lag, heeft geen snelheid meer -- ook al
            | ging hij daarvoor hard. Wie eerst veegt, dan stilhoudt en dan pas
            | loslaat, bedoelt niet te werpen.
            */
            if (!laatste || performance.now() - laatste.t > 100) {
                return { x: 0, y: 0 };
            }

            /*
            | Het venster loopt vanaf het LAATSTE punt terug en niet vanaf nu, en
            | het laatste punt telt zelf niet mee als oudste.
            |
            | Hier stond `punten.find((punt) => nu - punt.t < 100)`, en dat vindt
            | bij trage bewegingen het laatste punt zelf: oudste en laatste zijn
            | dan hetzelfde punt en de snelheid komt op nul uit. Een veeg van
            | een halve seconde had daardoor geen vaart, hoe hard hij ook ging.
            */
            const eerder = punten.slice(0, -1);
            const oudste = eerder.find((punt) => laatste.t - punt.t < 100) ?? eerder[eerder.length - 1];

            if (!oudste || laatste.t === oudste.t) {
                return { x: 0, y: 0 };
            }

            const dt = (laatste.t - oudste.t) / 1000;

            return { x: (laatste.x - oudste.x) / dt, y: (laatste.y - oudste.y) / dt };
        },
    };
}

/*
| Een veer, met de twee knoppen van Apple in plaats van de drie uit de
| natuurkunde.
|
| `demping` 1 is kritisch gedempt: hij komt aan en schiet niet door. Onder 1
| veert hij na, en dat hoort alleen bij een gebaar dat zelf vaart had -- een
| menu dat kwam opdagen mag niet nadeinen, iets dat je weggeworpen hebt wel.
|
| `respons` is hoe snel hij bij het doel is, in seconden. Dat is GEEN duur: een
| veer heeft er geen. Wanneer hij stil ligt volgt uit die twee getallen en uit de
| snelheid waarmee hij vertrok.
|
| Vaste substappen van 1/240 seconde: bij een frame dat verspringt -- een tab die
| op de achtergrond stond -- loopt een enkele grote integratiestap uit de hand en
| schiet de veer het scherm af.
*/
function hansuiVeer({ van, naar, snelheid = 0, demping = 1, respons = 0.4, stap, klaar }) {
    const w = (2 * Math.PI) / respons;
    let x = van;
    let v = snelheid;
    let vorige = performance.now();
    let bezig = true;

    const frame = (nu) => {
        if (!bezig) {
            return;
        }

        let dt = Math.min((nu - vorige) / 1000, 0.064);
        vorige = nu;

        while (dt > 0) {
            const h = Math.min(dt, 1 / 240);
            v += (-2 * demping * w * v - w * w * (x - naar)) * h;
            x += v * h;
            dt -= h;
        }

        // Stil genoeg: een halve pixel is onder wat iemand ziet, en zonder deze
        // drempel loopt een veer nog seconden door op onzichtbare rest.
        if (Math.abs(x - naar) < 0.4 && Math.abs(v) < 4) {
            bezig = false;
            stap(naar);
            klaar?.();

            return;
        }

        stap(x);
        requestAnimationFrame(frame);
    };

    requestAnimationFrame(frame);

    return {
        stop() {
            bezig = false;
        },
    };
}

/*
| Waar dit heen zou rollen als je losliet.
|
| Dezelfde formule waarmee een scrollende pagina uitloopt. Niet de v^2/2a uit het
| leerboek: die beschrijft een wrijving die constant is, en scrollen remt
| exponentieel af. Het verschil is groot genoeg om te voelen.
|
| Hiermee kiest een veeg zijn doel op waar hij HEEN ging en niet op waar hij
| toevallig eindigde. Een korte, snelle veeg hoort te werken.
*/
function hansuiProjecteer(snelheid, vertraging = 0.998) {
    return ((snelheid / 1000) * vertraging) / (1 - vertraging);
}

/*
| Weerstand voorbij een rand.
|
| Hoe verder eroverheen, hoe minder het meegeeft. Hard stoppen leest als
| vastgelopen; toenemende weerstand leest als "hij luistert nog, maar hier is
| niets meer".
*/
function hansuiRubberband(voorbij, maat, constante = 0.55) {
    return (voorbij * maat * constante) / (maat + constante * Math.abs(voorbij));
}

// De zijbalk op smalle schermen.
document.addEventListener('click', (e) => {
    const scrim = document.querySelector('[data-sidebar-scrim]');
    const bar = document.querySelector('[data-sidebar]');
    const toggle = document.querySelector('[data-sidebar-toggle]');

    if (!bar) {
        return;
    }

    const open = () => {
        bar.dataset.open = 'true';
        scrim?.classList.remove('hidden');
        toggle?.setAttribute('aria-expanded', 'true');
    };

    const close = () => {
        delete bar.dataset.open;
        scrim?.classList.add('hidden');
        toggle?.setAttribute('aria-expanded', 'false');
    };

    if (e.target.closest('[data-sidebar-toggle]')) {
        bar.dataset.open === 'true' ? close() : open();
        return;
    }

    // Buiten klikken sluit hem, en een link erin ook: anders blijft de balk
    // over de pagina hangen die je net gekozen hebt.
    if (e.target.closest('[data-sidebar-scrim]') || (e.target.closest('[data-sidebar] a') && window.innerWidth < 1024)) {
        close();
    }
});

// Escape sluit de zijbalk.
document.addEventListener('keydown', (e) => {
    if (e.key !== 'Escape') {
        return;
    }

    const bar = document.querySelector('[data-sidebar]');
    if (bar && bar.dataset.open === 'true' && window.innerWidth < 1024) {
        delete bar.dataset.open;
        document.querySelector('[data-sidebar-scrim]')?.classList.add('hidden');
        document.querySelector('[data-sidebar-toggle]')?.setAttribute('aria-expanded', 'false');
    }
});

/*
| DE ZIJBALK MET EEN VEEG DICHT.
|
| Er zat een knop op en verder niets. Op een telefoon is wegvegen wat iemand
| probeert bij een paneel dat van links kwam -- en als dat niets doet, is de
| balk iets dat de pagina overneemt in plaats van iets dat je vasthebt.
|
| Alleen DICHT en niet open. Openen met een veeg vanaf de linkerrand vecht met
| het terug-gebaar van de browser, en dat gevecht win je niet: de gebruiker
| krijgt dan de vorige pagina waar hij een menu verwachtte.
|
| De beslissing hangt aan de SNELHEID en niet aan de afstand. Een korte, snelle
| veeg hoort te sluiten, ook al is de balk dan nog bijna helemaal open -- dat is
| wat de hand bedoelde. Vandaar dat er geprojecteerd wordt waar dit heen zou
| rollen, en pas daarna gekeken welk van de twee einden het dichtst bij ligt.
*/
let hansuiVeeg = null;
let hansuiVeegVeer = null;
let hansuiVeegX = 0;

document.addEventListener('pointerdown', (e) => {
    const bar = e.target.closest('[data-sidebar]');

    if (!bar || hansuiSleep || hansuiVeeg || bar.dataset.open !== 'true' || window.innerWidth >= 1024) {
        return;
    }

    /*
    | Een balk die nog aan het bewegen was, mag je zo weer vastpakken.
    |
    | Dit is het punt waar een gebaar levend of dood aanvoelt. Wie zijn balk
    | wegveegt en zich halverwege bedenkt, hoort hem terug te kunnen trekken
    | vanaf WAAR HIJ NU STAAT -- niet te moeten wachten tot hij dicht is en dan
    | opnieuw te beginnen. Vandaar dat de veer gestopt wordt en niet afgemaakt,
    | en dat de nieuwe greep verder telt vanaf de huidige stand.
    */
    const onderbroken = hansuiVeegVeer !== null;
    hansuiVeegVeer?.stop();
    hansuiVeegVeer = null;

    hansuiVeeg = {
        bar,
        scrim: document.querySelector('[data-sidebar-scrim]'),
        breedte: bar.offsetWidth,
        basis: onderbroken ? hansuiVeegX : 0,
        startX: e.clientX,
        startY: e.clientY,
        x: onderbroken ? hansuiVeegX : 0,
        spoor: hansuiSpoor(),
        pointerId: e.pointerId,
        // Wie iets pakt dat al beweegt, heeft zijn bedoeling al bewezen: daar
        // hoort geen drempel meer overheen.
        bezig: onderbroken,
    };

    hansuiVeeg.spoor.voeg(e.clientX, e.clientY);

    if (onderbroken) {
        hansuiGrijp(bar, e.pointerId);
        bar.style.transition = 'none';
    }
});

function hansuiVeegTeken(g, x) {
    hansuiVeegX = x;
    g.bar.style.transform = `translateX(${x}px)`;

    if (g.scrim) {
        g.scrim.style.opacity = String(Math.max(0, 1 + Math.min(x, 0) / g.breedte));
    }
}

document.addEventListener('pointermove', (e) => {
    const g = hansuiVeeg;
    if (!g || e.pointerId !== g.pointerId) {
        return;
    }

    const dx = e.clientX - g.startX;
    const dy = e.clientY - g.startY;
    g.spoor.voeg(e.clientX, e.clientY);

    if (!g.bezig) {
        // Meer horizontaal dan verticaal, anders is dit scrollen in de balk.
        if (Math.abs(dx) < 10 || Math.abs(dx) <= Math.abs(dy)) {
            if (Math.abs(dy) > 10) {
                hansuiVeeg = null;
            }

            return;
        }

        g.bezig = true;
        hansuiGrijp(g.bar, e.pointerId);
        g.bar.style.transition = 'none';

        if (g.scrim) {
            g.scrim.style.transition = 'none';
        }
    }

    // Naar links volgt hij een-op-een; naar rechts is er niets meer, dus daar
    // geeft hij steeds minder mee in plaats van tegen een muur te lopen.
    const rauw = g.basis + dx;
    g.x = rauw <= 0 ? rauw : hansuiRubberband(rauw, g.breedte);

    hansuiVeegTeken(g, g.x);
});

/*
| De klik die na een veeg toch nog komt, tegenhouden.
|
| Wie de balk wegveegt met zijn duim op een menu-item, stuurt daarna een `click`
| naar dat item -- en dan staat er een andere pagina waar iemand alleen het menu
| wilde wegdoen. In de capture-fase, want het gaat erom dat hij niemand bereikt.
|
| En met een timeout eromheen. Die klik komt in dezelfde beurt of hij komt niet;
| een luisteraar die blijft staan tot er ooit geklikt wordt, slikt een half uur
| later de eerste echte klik van de gebruiker op.
*/
function hansuiSlikDeKlik() {
    const slik = (klik) => {
        klik.preventDefault();
        klik.stopPropagation();
    };

    document.addEventListener('click', slik, { capture: true, once: true });
    setTimeout(() => document.removeEventListener('click', slik, { capture: true }), 0);
}

function hansuiVeegLos() {
    const g = hansuiVeeg;
    if (!g) {
        return;
    }

    hansuiVeeg = null;

    if (!g.bezig) {
        return;
    }

    const v = g.spoor.snelheid().x;
    const eind = g.x + hansuiProjecteer(v);
    const dicht = eind < -g.breedte / 2;
    const doel = dicht ? -g.breedte : 0;

    /*
    | Een tikje nadeinen bij het terugvallen, en niet bij het sluiten.
    |
    | Wie losliet zonder ver genoeg te zijn, krijgt de balk terug en die mag
    | laten merken dat hij vaart had. Wie hem wegduwt, ziet hem verdwijnen --
    | daar is nadeinen alleen maar wachten op iets dat toch al weg is.
    */
    hansuiVeegVeer = hansuiVeer({
        van: g.x,
        naar: doel,
        snelheid: v,
        demping: dicht ? 1 : 0.8,
        respons: 0.3,
        stap: (x) => hansuiVeegTeken(g, x),
        klaar: () => {
            hansuiVeegVeer = null;
            hansuiVeegX = 0;

            g.bar.style.transition = '';
            g.bar.style.transform = '';

            if (g.scrim) {
                g.scrim.style.transition = '';
                g.scrim.style.opacity = '';
            }

            if (dicht) {
                delete g.bar.dataset.open;
                g.scrim?.classList.add('hidden');
                document.querySelector('[data-sidebar-toggle]')?.setAttribute('aria-expanded', 'false');
            }
        },
    });

    hansuiSlikDeKlik();
}

document.addEventListener('pointerup', hansuiVeegLos);
document.addEventListener('pointercancel', hansuiVeegLos);

// Licht of donker. Drie standen: niets gekozen volgt het systeem; kiezen legt
// het vast in beide richtingen.
function currentTheme() {
    const stamp = document.documentElement.dataset.theme;
    if (stamp === 'dark' || stamp === 'light') {
        return stamp;
    }

    return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
}

function paintThemeIcon() {
    const dark = currentTheme() === 'dark';
    document.querySelectorAll('[data-theme-icon="light"]').forEach((el) => el.classList.toggle('hidden', dark));
    document.querySelectorAll('[data-theme-icon="dark"]').forEach((el) => el.classList.toggle('hidden', !dark));
}

document.addEventListener('click', (e) => {
    if (e.target.closest('[data-theme-toggle]')) {
        const next = currentTheme() === 'dark' ? 'light' : 'dark';
        document.documentElement.dataset.theme = next;
        try { localStorage.setItem('hansui.theme', next); } catch (_) { /* privémodus */ }
        paintThemeIcon();
    }

    if (e.target.closest('[data-density-toggle]')) {
        const compact = document.documentElement.dataset.density === 'compact';
        if (compact) {
            delete document.documentElement.dataset.density;
        } else {
            document.documentElement.dataset.density = 'compact';
        }
        /*
        | Bij "ruim" de sleutel WISSEN en niet op 'ruim' zetten: dat is
        | symmetrisch met het attribuut, dat ook verdwijnt in plaats van een
        | tegenwaarde te krijgen, en het script in de <head> kijkt toch alleen
        | of er 'compact' staat.
        */
        try {
            compact ? localStorage.removeItem('hansui.density') : localStorage.setItem('hansui.density', 'compact');
        } catch (_) { /* privémodus */ }
    }
});

paintThemeIcon();
window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', paintThemeIcon);

// Ctrl+K opent het zoekpalet. Het palet zelf is een Livewire-component en
// luistert op dit venster-event.
document.addEventListener('keydown', (e) => {
    if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') {
        e.preventDefault();
        window.dispatchEvent(new CustomEvent('open-palette'));
    }
});

document.addEventListener('click', (e) => {
    if (e.target.closest('[data-palette-open]')) {
        window.dispatchEvent(new CustomEvent('open-palette'));
    }
});

/*
| Herhaalbare formulierregels: data-regels.
|
| Een [data-regels]-blok bevat een [data-regel-lijst], een <template
| data-regel-sjabloon> met een lege regel, en knoppen om toe te voegen of te
| verwijderen. In het sjabloon staat __INDEX__ waar de rijindex hoort.
|
| De teller telt door en hergebruikt geen vrijgekomen nummers: gaten in de
| nummering geven niets voor een PHP-array, en hergebruik zou twee velden
| dezelfde naam geven zodra er middenin iets verwijderd is.
*/
document.addEventListener('click', (e) => {
    const toevoegen = e.target.closest('[data-regel-toevoegen]');
    if (toevoegen) {
        e.preventDefault();
        const blok = toevoegen.closest('[data-regels]');
        const lijst = blok?.querySelector('[data-regel-lijst]');
        const sjabloon = blok?.querySelector('[data-regel-sjabloon]');
        if (!lijst || !sjabloon) {
            return;
        }

        const index = Number(blok.dataset.regelsTeller ?? lijst.children.length);
        blok.dataset.regelsTeller = index + 1;
        lijst.insertAdjacentHTML('beforeend', sjabloon.innerHTML.replaceAll('__INDEX__', index));

        return;
    }

    const verwijderen = e.target.closest('[data-regel-verwijderen]');
    if (verwijderen) {
        e.preventDefault();
        const rij = verwijderen.closest('[data-regel]');
        const lijst = rij?.parentElement;
        // De laatste regel blijft staan en wordt leeggemaakt: een formulier
        // zonder enkele regel heeft geen zin en laat de gebruiker klemzitten.
        if (lijst && lijst.children.length > 1) {
            rij.remove();
        } else {
            rij?.querySelectorAll('input').forEach((veld) => {
                veld.value = '';
            });
        }
    }
});

/*
|------------------------------------------------------------------------------
| Sleepbaar herordenen: data-sortable
|------------------------------------------------------------------------------
|
| Een [data-sortable] met kinderen [data-id]. Bij het neerleggen gaat de nieuwe
| volgorde als {volgorde: [id, ...]} met PUT naar data-sortable-url, met het
| CSRF-token uit data-sortable-token. Dat contract is niet veranderd.
|
| WAT WEL VERANDERDE: hier stond HTML5 drag-and-drop -- `draggable="true"` met
| dragstart, dragover en dragend. Dat is een API die de browser laat beslissen
| wat er beweegt, en het levert drie problemen op die geen van drieen opgelost
| kunnen worden zolang hij er staat.
|
| Het ERGSTE is dat hij op een aanraakscherm niet aangaat. Android Chrome vuurt
| geen dragstart vanuit een aanraking en iOS Safari doet het alleen via zijn
| eigen sleepmechanisme. Herordenen werkte dus niet op een telefoon, terwijl de
| README het wel beloofde.
|
| Verder tekent de browser een spookafbeelding die je niet kunt opmaken, en komt
| er tijdens het slepen geen snelheid vrij -- dus kan wat na het loslaten gebeurt
| nooit aansluiten op wat de hand deed.
|
| Nu op Pointer Events. De rij volgt de vinger een-op-een, de andere rijen
| schuiven opzij, en bij het loslaten brengen twee veren hem naar zijn vak met de
| snelheid waarmee hij losgelaten werd.
|
| WAT ER NOG NIET IS: herordenen met het toetsenbord. Dat is geen detail -- wie
| niet sleept kan deze lijst niet ordenen -- maar het is een eigen ontwerp
| (oppakken, verplaatsen, neerleggen, en een schermlezer die zegt wat er gebeurt)
| en geen regel of tien aan het onderstaande. Het staat in de README als wat het
| is: een gat.
*/
let hansuiSleep = null;
let hansuiSleepRust = null;

/*
| Het gebaar begint pas als de bedoeling duidelijk is.
|
| Met een muis na acht pixels, genoeg om een klik van een sleep te scheiden.
|
| Met een vinger na een lange druk van 300ms, en dat verschil is geen smaak: op
| een aanraakscherm is verticaal slepen ook de manier om te SCROLLEN. Wie meteen
| grijpt, maakt de lijst onscrollbaar. Wie eerst indrukt, zegt dat hij deze rij
| bedoelt en niet de pagina.
|
| Een rij met een [data-sortable-handle] slaat dat wachten over: daar blijkt de
| bedoeling al uit WAAR er geduwd wordt. Die greep krijgt in de CSS
| `touch-action: none`, zodat de browser er zelf niet mee scrolt.
*/
document.addEventListener('pointerdown', (e) => {
    if (hansuiSleep || (e.pointerType === 'mouse' && e.button !== 0)) {
        return;
    }

    const item = e.target.closest('[data-sortable] [data-id]');
    if (!item) {
        return;
    }

    /*
    | Een rij die nog naar zijn vak veerde, eerst neerleggen.
    |
    | Anders wordt er opgemeten terwijl er nog een transform op staat -- en
    | getBoundingClientRect telt die mee, dus elk vak in de lijst zou een stukje
    | verschoven zijn. Neerleggen is hier goedkoop: de veer was toch al bijna
    | thuis, dus er valt nauwelijks iets te zien.
    */
    if (hansuiSleepRust) {
        const rust = hansuiSleepRust;
        hansuiSleepRust = null;
        rust.veren.forEach((veer) => veer.stop());
        hansuiSleepLeg(rust.s);
    }

    // Niet vanaf iets dat zelf al een taak heeft.
    if (e.target.closest('a, button, input, select, textarea')) {
        return;
    }

    const greep = item.querySelector('[data-sortable-handle]');
    if (greep && !greep.contains(e.target)) {
        return;
    }

    const houder = item.closest('[data-sortable]');
    const items = [...houder.querySelectorAll('[data-id]')];
    const van = items.indexOf(item);

    hansuiSleep = {
        item,
        houder,
        items,
        van,
        naar: van,
        // De vakken worden EEN keer opgemeten. Alles wat daarna beweegt, beweegt
        // met transform, dus deze blijven kloppen -- en toetsen tegen een vak
        // dat zelf opzij geschoven is, laat de lijst tussen twee volgordes
        // flikkeren.
        vakken: items.map((el) => el.getBoundingClientRect()),
        startX: e.clientX,
        startY: e.clientY,
        dx: 0,
        dy: 0,
        spoor: hansuiSpoor(),
        pointerId: e.pointerId,
        bezig: false,
        drempel: greep || e.pointerType === 'mouse' ? 8 : Infinity,
        wacht: null,
    };

    hansuiSleep.spoor.voeg(e.clientX, e.clientY);

    if (hansuiSleep.drempel === Infinity) {
        hansuiSleep.wacht = setTimeout(hansuiSleepBegin, 300);
    }
});

function hansuiSleepBegin() {
    const s = hansuiSleep;
    if (!s || s.bezig) {
        return;
    }

    s.bezig = true;
    s.item.setAttribute('data-sleept', '');
    hansuiGrijp(s.item, s.pointerId);
}

/*
| Waar de rij nu hoort, en waar de andere dan heen moeten.
|
| Elke andere rij schuift een vak op richting het gat: die uit vak i gaat naar
| vak i-1 of i+1. Omdat dat met de OPGEMETEN vakken gebeurt en niet met een
| aangenomen rijhoogte, werkt dit ook als de rijen niet even hoog zijn -- en
| werkt het ook voor een raster, waar opschuiven ook naar links of rechts is.
*/
function hansuiSleepSchik() {
    const s = hansuiSleep;
    const vak = s.vakken[s.van];
    const midX = vak.left + vak.width / 2 + s.dx;
    const midY = vak.top + vak.height / 2 + s.dy;

    const boven = s.vakken.findIndex(
        (v) => midX >= v.left && midX <= v.right && midY >= v.top && midY <= v.bottom,
    );

    if (boven !== -1) {
        s.naar = boven;
    }

    s.items.forEach((el, i) => {
        if (i === s.van) {
            return;
        }

        let vakje = i;
        if (s.naar > s.van && i > s.van && i <= s.naar) {
            vakje = i - 1;
        }
        if (s.naar < s.van && i >= s.naar && i < s.van) {
            vakje = i + 1;
        }

        el.style.transform =
            vakje === i
                ? ''
                : `translate(${s.vakken[vakje].left - s.vakken[i].left}px, ${s.vakken[vakje].top - s.vakken[i].top}px)`;
    });
}

document.addEventListener('pointermove', (e) => {
    const s = hansuiSleep;
    if (!s || e.pointerId !== s.pointerId) {
        return;
    }

    s.dx = e.clientX - s.startX;
    s.dy = e.clientY - s.startY;
    s.spoor.voeg(e.clientX, e.clientY);

    if (!s.bezig) {
        const weg = Math.hypot(s.dx, s.dy);

        // Bewegen terwijl de lange druk nog loopt, is scrollen en geen sleep.
        if (s.wacht && weg > 10) {
            clearTimeout(s.wacht);
            hansuiSleep = null;

            return;
        }

        if (weg < s.drempel) {
            return;
        }

        hansuiSleepBegin();
    }

    s.item.style.transform = `translate(${s.dx}px, ${s.dy}px)`;
    hansuiSleepSchik();
});

/*
| Loslaten: twee veren, een voor x en een voor y.
|
| En niet een veer op de afstand. Als de vinger horizontaal sneller ging dan
| verticaal, en beide assen hangen aan dezelfde veer, dan komt de trage as te
| vroeg aan en de snelle te laat: de rij loopt schuin naar zijn vak in plaats van
| de boog te maken die de hand beschreef.
|
| Kritisch gedempt, want dit is neerleggen en geen worp. Een rij die nadeint in
| een lijst met vijftig regels ziet eruit alsof hij niet zeker weet waar hij
| hoort.
*/
function hansuiSleepLos() {
    const s = hansuiSleep;
    if (!s) {
        return;
    }

    clearTimeout(s.wacht);
    hansuiSleep = null;

    if (!s.bezig) {
        return;
    }

    const v = s.spoor.snelheid();
    const rustX = s.vakken[s.naar].left - s.vakken[s.van].left;
    const rustY = s.vakken[s.naar].top - s.vakken[s.van].top;

    let x = s.dx;
    let y = s.dy;
    let af = 0;

    const teken = () => {
        s.item.style.transform = `translate(${x}px, ${y}px)`;
    };
    const klaar = () => {
        if (++af === 2) {
            hansuiSleepRust = null;
            hansuiSleepLeg(s);
        }
    };

    const veren = [
        hansuiVeer({
            van: s.dx,
            naar: rustX,
            snelheid: v.x,
            respons: 0.35,
            stap: (w) => {
                x = w;
                teken();
            },
            klaar,
        }),
        hansuiVeer({
            van: s.dy,
            naar: rustY,
            snelheid: v.y,
            respons: 0.35,
            stap: (w) => {
                y = w;
                teken();
            },
            klaar,
        }),
    ];

    hansuiSleepRust = { s, veren };

    // Een rij die ook een link is, hoort na het verslepen niet te navigeren.
    hansuiSlikDeKlik();
}

document.addEventListener('pointerup', hansuiSleepLos);
document.addEventListener('pointercancel', hansuiSleepLos);

// Escape legt hem terug waar hij lag. Wie halverwege van gedachten verandert,
// hoort niet eerst te moeten mikken.
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && hansuiSleep) {
        hansuiSleep.naar = hansuiSleep.van;
        hansuiSleepSchik();
        hansuiSleepLos();
    }
});

/*
| Neerleggen: de DOM verzetten en alle transforms weghalen, in EEN beurt.
|
| Op dat moment staat alles op het scherm al precies waar de nieuwe opmaak het
| ook zou zetten, dus er valt niets te zien. Maar de overgang op transform moet
| er wel even uit: zonder die onderbreking animeert elke rij van "oude transform
| op nieuwe plek" naar "geen transform", en dat is de sprong die je juist wilde
| vermijden.
*/
function hansuiSleepLeg(s) {
    s.items.forEach((el) => {
        el.style.transition = 'none';
    });

    if (s.naar !== s.van) {
        const anker = s.items[s.naar];
        anker.parentNode.insertBefore(s.item, s.naar > s.van ? anker.nextSibling : anker);
    }

    s.items.forEach((el) => {
        el.style.transform = '';
    });
    s.item.removeAttribute('data-sleept');

    // De herberekening afdwingen voor de overgangen terugkomen.
    void s.houder.offsetHeight;

    s.items.forEach((el) => {
        el.style.transition = '';
    });

    if (s.naar === s.van) {
        return;
    }

    const volgorde = [...s.houder.querySelectorAll('[data-id]')].map((el) => Number(el.dataset.id));

    fetch(s.houder.dataset.sortableUrl, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            Accept: 'application/json',
            'X-CSRF-TOKEN': s.houder.dataset.sortableToken ?? '',
        },
        body: JSON.stringify({ volgorde }),
    });
}

/*
|------------------------------------------------------------------------------
| Tooltips: data-tip
|------------------------------------------------------------------------------
|
| Elk element met `data-tip` toont zijn tekst in een zwevend kadertje dat de
| muis volgt. Gemaakt voor de grafieken van <x-chart.*>, waar de server tekent
| en dit alleen de hover toevoegt, maar het werkt op alles. Een regeleinde in
| de tekst blijft een regeleinde.
|
| Het kadertje bestaat een keer per pagina en krijgt role="status", zodat een
| schermlezer die op een staaf landt de waarde hoort.
*/
let hansuiTip = null;

function hansuiToonTip(doel, x, y) {
    if (!hansuiTip) {
        hansuiTip = document.createElement('div');
        hansuiTip.className = 'chart-tip';
        hansuiTip.setAttribute('role', 'status');
        document.body.appendChild(hansuiTip);
    }

    hansuiTip.textContent = doel.getAttribute('data-tip');
    hansuiTip.hidden = false;

    const vak = hansuiTip.getBoundingClientRect();
    const links = Math.min(window.innerWidth - vak.width - 8, x + 14);
    const boven = y + vak.height + 20 > window.innerHeight ? y - vak.height - 10 : y + 14;

    hansuiTip.style.left = `${Math.max(8, links)}px`;
    hansuiTip.style.top = `${Math.max(8, boven)}px`;
}

document.addEventListener('pointermove', (e) => {
    const doel = e.target.closest?.('[data-tip]');

    if (doel) {
        hansuiToonTip(doel, e.clientX, e.clientY);
    } else if (hansuiTip) {
        hansuiTip.hidden = true;
    }
});

document.addEventListener('focusin', (e) => {
    const doel = e.target.closest?.('[data-tip]');

    if (doel) {
        const vak = doel.getBoundingClientRect();
        hansuiToonTip(doel, vak.left + vak.width / 2, vak.top);
    }
});

/*
|------------------------------------------------------------------------------
| Het zoekpalet: data-palette
|------------------------------------------------------------------------------
|
| Ctrl/Cmd + K en `data-palette-open` sturen `open-palette` (zie hoger); hier
| wordt het opgevangen door een <dialog data-palette>. De index staat in de
| HTML en komt niet van de server: een paar honderd regels zijn een paar
| kilobyte, en dan is zoeken meteen, ook als de verbinding hapert.
|
| MATCHEN OP WOORDGRENS en niet op substring: wie "gent" typt, wil "Etalage
| Gent" vinden maar niet elke naam waar toevallig "gent" in het midden staat.
*/
function hansuiPalet() {
    const venster = document.querySelector('[data-palette]');
    if (!venster || venster.dataset.paletKlaar) return;
    venster.dataset.paletKlaar = '1';

    const invoer = venster.querySelector('[data-palette-input]');
    const lijst = venster.querySelector('[data-palette-list]');
    const leeg = venster.querySelector('[data-palette-empty]');
    const regels = [...lijst.querySelectorAll('[data-palette-item]')].map((el) => ({
        el,
        hooiberg: (el.getAttribute('data-palette-item') || el.textContent).toLowerCase(),
    }));

    const huidig = () => lijst.querySelector('[data-palette-item][data-active="true"]');

    const markeer = (el) => {
        lijst.querySelectorAll('[data-palette-item]').forEach((r) => r.removeAttribute('data-active'));
        el.setAttribute('data-active', 'true');
        el.scrollIntoView({ block: 'nearest' });
    };

    const past = (hooiberg, term) => term.split(/\s+/).every((woord) =>
        hooiberg.split(/[\s\-/·,]+/).some((deel) => deel.startsWith(woord)) || hooiberg.startsWith(woord));

    const filter = (term) => {
        let zichtbaar = 0;

        regels.forEach(({ el, hooiberg }) => {
            const ja = term === '' || past(hooiberg, term);
            el.hidden = !ja;
            if (ja) zichtbaar += 1;
        });

        if (leeg) leeg.hidden = zichtbaar > 0;

        const eerste = regels.find(({ el }) => !el.hidden);
        if (eerste) markeer(eerste.el);
    };

    const stap = (richting) => {
        const zichtbaar = [...lijst.querySelectorAll('[data-palette-item]:not([hidden])')];
        if (zichtbaar.length === 0) return;

        const i = zichtbaar.indexOf(huidig());
        markeer(zichtbaar[(i + richting + zichtbaar.length) % zichtbaar.length]);
    };

    window.addEventListener('open-palette', () => {
        if (!venster.open) venster.showModal?.();
        invoer.value = '';
        filter('');
        invoer.focus();
    });

    invoer.addEventListener('input', () => filter(invoer.value.trim().toLowerCase()));

    invoer.addEventListener('keydown', (e) => {
        if (e.key === 'Enter') {
            e.preventDefault();
            huidig()?.click();
        }

        if (e.key === 'ArrowDown' || e.key === 'ArrowUp') {
            e.preventDefault();
            stap(e.key === 'ArrowDown' ? 1 : -1);
        }
    });

    lijst.addEventListener('pointermove', (e) => {
        const regel = e.target.closest('[data-palette-item]');
        if (regel) markeer(regel);
    });
}

document.addEventListener('DOMContentLoaded', hansuiPalet);

/*
|------------------------------------------------------------------------------
| Uploaden: data-uploader
|------------------------------------------------------------------------------
|
| Slepen, plakken of kiezen: alle drie leveren ze een FileList op, en het
| verschil hoort niet in de rest van deze code te zitten.
|
| EEN VOOR EEN en niet allemaal tegelijk. Twintig gelijktijdige uploads van een
| telefoon op 4G zijn twintig verbindingen die elkaar verdringen; op een rij
| loopt de eerste vol terwijl de rest wacht, en dat verwacht wie kijkt ook.
|
| Het vak met `data-uploader` draagt waar het heen moet (`data-action`,
| `data-token`) en optioneel `data-folder`, `data-max-bytes` en
| `data-free-bytes`. De teksten komen uit `data-upload-texts`, een JSON-object,
| zodat ze vertaald uit de view komen.
|
| Van een VIDEO leest de browser de duur, de afmetingen en een posterbeeld: hij
| heeft het bestand al in handen, en de server zou er ffmpeg voor nodig hebben.
*/
const hansuiUpload = { rij: [], bezig: false };

function hansuiUploadTekst(zone, sleutel, terugval, waarden = {}) {
    let teksten = {};
    try { teksten = JSON.parse(zone.getAttribute('data-upload-texts') || '{}'); } catch (e) { /* geen teksten */ }

    return Object.entries(waarden).reduce(
        (tekst, [naam, waarde]) => tekst.replace(`:${naam}`, waarde),
        teksten[sleutel] || terugval,
    );
}

function hansuiUploader() {
    /*
    | MEER DAN EEN VAK mag: een groot vak in een lege lijst en hetzelfde vak in
    | het uploadvenster. Elk vak opent zijn eigen bestandskeuze; slepen op de
    | pagina en plakken gaan naar het eerste, want die twee horen bij de
    | pagina en niet bij een vak.
    */
    const zones = [...document.querySelectorAll('[data-uploader]')].filter((z) => !z.dataset.uploaderKlaar);
    if (!zones.length) return;

    const lijst = document.querySelector('[data-upload-list]');

    zones.forEach((zone) => {
        zone.dataset.uploaderKlaar = '1';
        const invoer = zone.querySelector('input[type="file"]');

        zone.addEventListener('click', (e) => {
            if (e.target.closest('input, a, button[data-no-browse]')) return;
            invoer?.click();
        });

        invoer?.addEventListener('change', () => {
            hansuiUploadZet(invoer.files, zone, lijst);
            invoer.value = '';
        });
    });

    if (document.body.dataset.uploaderVlak) return;
    document.body.dataset.uploaderVlak = '1';

    const zone = zones[0];
    const vlak = document.querySelector('[data-upload-surface]') || document.body;
    // Een tegel die BINNEN de pagina versleept wordt (data-drag-item) is geen
    // upload, ook al hangt er in sommige browsers een beeld als bestand aan.
    const heeftBestanden = (e) => !hansuiSleepNaar.ids && [...(e.dataTransfer?.types || [])].includes('Files');
    let diepte = 0;

    // dragenter en dragleave vuren ook voor elk kind: tellen in plaats van
    // omschakelen, anders knippert het kader bij elke rand die je kruist.
    vlak.addEventListener('dragenter', (e) => {
        if (!heeftBestanden(e)) return;
        e.preventDefault();
        diepte += 1;
        zone.setAttribute('data-over', 'true');
        vlak.setAttribute('data-over', 'true');
    });

    vlak.addEventListener('dragover', (e) => {
        if (!heeftBestanden(e)) return;
        e.preventDefault();
        e.dataTransfer.dropEffect = 'copy';
    });

    vlak.addEventListener('dragleave', () => {
        diepte = Math.max(0, diepte - 1);
        if (diepte === 0) {
            zone.removeAttribute('data-over');
            vlak.removeAttribute('data-over');
        }
    });

    vlak.addEventListener('drop', (e) => {
        if (!heeftBestanden(e)) return;
        e.preventDefault();
        diepte = 0;
        zone.removeAttribute('data-over');
        vlak.removeAttribute('data-over');
        hansuiUploadZet(e.dataTransfer.files, zone, lijst);
    });

    // Plakken uit het klembord: een schermafdruk komt zo binnen zonder dat
    // iemand hem eerst moet opslaan.
    document.addEventListener('paste', (e) => {
        if (hansuiIsTypveld(e.target)) return;
        const bestanden = [...(e.clipboardData?.files || [])];
        if (bestanden.length) hansuiUploadZet(bestanden, zone, lijst);
    });
}

function hansuiUploadZet(bestanden, zone, lijst) {
    const max = Number(zone.dataset.maxBytes || 0);
    const vrij = Number(zone.dataset.freeBytes || 0);
    let gepland = 0;

    for (const bestand of bestanden) {
        if (max && bestand.size > max) {
            hansuiUploadRij(zone, lijst, bestand, 'fout',
                hansuiUploadTekst(zone, 'tooBig', 'Te groot voor dit abonnement (max :max).', { max: hansuiBytes(max) }));
            continue;
        }

        // De ruimte wordt HIER al afgetrokken, voor de eerste byte vertrekt: wie
        // tien bestanden sleept met 40 MB over, hoort dat nu te zien en niet na
        // tien minuten uploaden. De server controleert het opnieuw.
        if (vrij && gepland + bestand.size > vrij) {
            hansuiUploadRij(zone, lijst, bestand, 'fout',
                hansuiUploadTekst(zone, 'noSpace', 'Niet genoeg vrije opslag. Ruim eerst op of kies een hoger plan.'));
            continue;
        }

        gepland += bestand.size;
        hansuiUpload.rij.push({ bestand, el: hansuiUploadRij(zone, lijst, bestand, 'wacht', hansuiUploadTekst(zone, 'waiting', 'wachten')) });
    }

    hansuiUploadPomp(zone);
}

async function hansuiUploadPomp(zone) {
    if (hansuiUpload.bezig || hansuiUpload.rij.length === 0) return;

    hansuiUpload.bezig = true;

    const taak = hansuiUpload.rij.shift();
    const form = new FormData();

    form.append('file', taak.bestand);
    if (zone.dataset.folder) form.append('folder_id', zone.dataset.folder);

    if (taak.bestand.type.startsWith('video/')) {
        try {
            const meta = await hansuiVideoMeta(taak.bestand);
            if (meta.duur) form.append('duration_ms', Math.round(meta.duur * 1000));
            if (meta.breedte) form.append('width', meta.breedte);
            if (meta.hoogte) form.append('height', meta.hoogte);
            if (meta.poster) form.append('poster', meta.poster);
        } catch (e) {
            // Een codec die deze browser niet kent: het bestand gaat gewoon
            // mee zonder duur, en dat is beter dan een upload die afketst.
        }
    }

    const verzoek = new XMLHttpRequest();
    verzoek.open('POST', zone.dataset.action, true);
    verzoek.setRequestHeader('X-CSRF-TOKEN', zone.dataset.token ?? '');
    verzoek.setRequestHeader('Accept', 'application/json');

    verzoek.upload.addEventListener('progress', (e) => {
        if (!e.lengthComputable || !taak.el) return;
        const procent = Math.round((e.loaded / e.total) * 100);
        taak.el.querySelector('.meter-fill').style.width = `${procent}%`;
        taak.el.querySelector('[data-state]').textContent = `${procent}%`;
    });

    const volgende = () => {
        hansuiUpload.bezig = false;
        hansuiUploadPomp(zone);
    };

    verzoek.addEventListener('load', () => {
        let antwoord = {};
        try { antwoord = JSON.parse(verzoek.responseText); } catch (e) { /* leeg antwoord */ }

        if (verzoek.status >= 200 && verzoek.status < 300) {
            hansuiUploadStaat(taak.el, 'ok', antwoord.duplicate
                ? hansuiUploadTekst(zone, 'duplicate', 'stond er al')
                : hansuiUploadTekst(zone, 'done', 'klaar'));
        } else {
            hansuiUploadStaat(taak.el, 'fout', antwoord.message
                || hansuiUploadTekst(zone, 'failed', 'Mislukt (:status).', { status: verzoek.status }));
        }

        volgende();

        if (hansuiUpload.rij.length === 0) {
            // Een lijst met "klaar" zegt niets over wat er nu in de bibliotheek
            // staat. Herladen is eerlijker dan tegels bijtekenen en hopen dat de
            // sortering klopt.
            window.setTimeout(() => window.location.reload(), 700);
        }
    });

    verzoek.addEventListener('error', () => {
        hansuiUploadStaat(taak.el, 'fout', hansuiUploadTekst(zone, 'offline', 'De verbinding viel weg.'));
        volgende();
    });

    verzoek.send(form);
}

function hansuiUploadRij(zone, lijst, bestand, staat, tekst) {
    if (!lijst) return null;

    const el = document.createElement('li');
    el.className = 'flex items-center gap-3 border-b border-gray-200 px-3 py-2 text-sm last:border-0';
    el.innerHTML = `
        <span class="min-w-0 flex-1 truncate text-gray-900"></span>
        <span class="flex-none text-xs text-gray-500">${hansuiBytes(bestand.size)}</span>
        <span class="w-16 flex-none sm:w-28"><span class="meter"><span class="meter-fill" style="width:0%"></span></span></span>
        <span class="w-24 flex-none truncate text-right text-xs text-gray-500 sm:w-40" data-state></span>
    `;
    el.firstElementChild.textContent = bestand.name;
    el.querySelector('[data-state]').textContent = tekst;

    if (staat === 'fout') {
        el.querySelector('.meter-fill').style.width = '100%';
        hansuiUploadStaat(el, 'fout', tekst);
    }

    lijst.append(el);
    lijst.closest('[data-upload-panel]')?.removeAttribute('hidden');

    // Wie op de pagina loslaat, ziet de lijst niet als die in een dicht
    // venster staat -- en dan lijkt het of er niets gebeurt tot de pagina
    // herlaadt. Het venster gaat dus open.
    const venster = lijst.closest('dialog');
    if (venster && !venster.open) venster.showModal?.();

    return el;
}

function hansuiUploadStaat(el, staat, tekst) {
    if (!el) return;

    const label = el.querySelector('[data-state]');
    label.textContent = tekst;
    label.className = `w-24 flex-none truncate text-right text-xs sm:w-40 ${staat === 'ok' ? 'text-emerald-600' : 'text-red-600'}`;

    const balk = el.querySelector('.meter-fill');
    if (staat === 'ok') balk.style.width = '100%';
    if (staat === 'fout') balk.setAttribute('data-level', 'danger');
}

/*
| Een video even openen voor haar duur, afmetingen en een posterbeeld. Dat
| beeld komt van een seconde in en niet van nul: het eerste frame is vaker
| zwart dan niet, en een bibliotheek vol zwarte tegels vind je niets in terug.
*/
function hansuiVideoMeta(bestand) {
    return new Promise((klaar, mislukt) => {
        const adres = URL.createObjectURL(bestand);
        const video = document.createElement('video');
        const opruimen = () => URL.revokeObjectURL(adres);

        video.preload = 'metadata';
        video.muted = true;
        video.playsInline = true;

        const wekker = window.setTimeout(() => {
            opruimen();
            mislukt(new Error('timeout'));
        }, 10000);

        video.addEventListener('loadedmetadata', () => {
            const meta = {
                duur: Number.isFinite(video.duration) ? video.duration : null,
                breedte: video.videoWidth || null,
                hoogte: video.videoHeight || null,
                poster: null,
            };

            video.currentTime = Math.min(1, (video.duration || 2) / 2);

            video.addEventListener('seeked', () => {
                window.clearTimeout(wekker);

                try {
                    const doek = document.createElement('canvas');
                    const schaal = Math.min(1, 480 / (video.videoWidth || 480));

                    doek.width = Math.round((video.videoWidth || 480) * schaal);
                    doek.height = Math.round((video.videoHeight || 270) * schaal);
                    doek.getContext('2d').drawImage(video, 0, 0, doek.width, doek.height);
                    meta.poster = doek.toDataURL('image/jpeg', 0.7);
                } catch (e) {
                    // De duur is het waardevolle deel, en die is er al.
                }

                opruimen();
                klaar(meta);
            }, { once: true });
        }, { once: true });

        video.addEventListener('error', () => {
            window.clearTimeout(wekker);
            opruimen();
            mislukt(new Error('decode'));
        }, { once: true });

        video.src = adres;
    });
}

function hansuiBytes(bytes) {
    if (bytes < 1024) return `${bytes} B`;

    const eenheden = ['kB', 'MB', 'GB', 'TB'];
    const macht = Math.min(Math.floor(Math.log(bytes) / Math.log(1024)), eenheden.length);

    return `${(bytes / 1024 ** macht).toFixed(macht > 1 ? 1 : 0)} ${eenheden[macht - 1]}`;
}

document.addEventListener('DOMContentLoaded', hansuiUploader);

/*
|------------------------------------------------------------------------------
| Een botcontrole die niet rond raakt: data-turnstile-melding
|------------------------------------------------------------------------------
|
| Een botcontrole zoals Turnstile houdt het versturen tegen tot er een token
| is. Lukt dat niet, dan meldt ze dat met een `turnstile:failed`-event op het
| formulier en doet ze verder niets. Zonder dit blijft de knop stil hangen:
| de bezoeker drukt, er gebeurt niets, en niemand weet waarom.
|
| De melding staat in het formulier en niet in een toast, om dezelfde reden als
| de banners hierboven: wat vanzelf verdwijnt, mist iemand. De tekst is de
| waarde van het attribuut. ZELF AFBREKEN is geen fout; dan verdwijnt alleen
| de melding.
*/
document.addEventListener('turnstile:failed', (e) => {
    const melding = e.target.querySelector?.('[data-turnstile-melding]');
    if (!melding) return;

    if (e.detail?.cancelled) {
        melding.classList.add('hidden');
        return;
    }

    melding.textContent = melding.getAttribute('data-turnstile-melding')
        || 'De controle is niet gelukt. Ververs de pagina en probeer het opnieuw.';
    melding.classList.remove('hidden');
});

/*
|------------------------------------------------------------------------------
| Bijsnijden: data-crop
|------------------------------------------------------------------------------
|
| Een kader over een beeld dat je versleept, aan de hoeken groter maakt en
| met het toetsenbord bijstuurt. Wat eruit komt zijn vier getallen in
| PROCENTEN van het beeld: x, y, breedte en hoogte. Procenten en geen pixels,
| want wat hier op het scherm staat is bijna nooit het beeld op ware grootte,
| en de server die de pixels snijdt kent de echte maat beter dan wij.
|
| De verhouding (1:1, 4:5, 1.91:1 ...) geldt voor de PIXELS van het beeld en
| niet voor het kader op het scherm. Bij een beeld dat verkleind getoond wordt
| is dat hetzelfde; bij een beeld dat een browser uitrekt niet, en dan hoort de
| uitsnede nog steeds te kloppen.
|
| De knoppen en de velden horen bij de dichtstbijzijnde bijsnijder: zet ze in
| dezelfde omhulling. Zo kan een pagina er twee naast elkaar hebben zonder dat
| er een id tussen moet.
|
| Het kader verschuiven volgt de vinger een-op-een; voorbij de rand geeft het
| nog wat mee en veert het terug. De getallen die naar buiten gaan, blijven
| altijd binnen het beeld.
*/
const hansuiCrop = new WeakMap();
let hansuiCropGreep = null;

// Kleiner dan dit op het scherm kan je het kader niet meer vastpakken.
const HANSUI_CROP_MIN = 24;

function hansuiCropRatio(waarde) {
    const [a, b] = String(waarde ?? '').split(':').map(Number);

    return a > 0 && b > 0 ? a / b : null;
}

function hansuiCropKlem(waarde, min, max) {
    return Math.min(Math.max(waarde, min), Math.max(min, max));
}

// De dichtstbijzijnde voorouder waar iets in zit dat op de selector past.
function hansuiCropBuur(el, selector) {
    for (let x = el.parentElement; x; x = x.parentElement) {
        if (x.querySelector(selector)) {
            return x;
        }
    }

    return null;
}

function hansuiCropVan(knop) {
    return hansuiCropBuur(knop, '[data-crop]')?.querySelector('[data-crop]') ?? null;
}

function hansuiCropKnoppen(crop) {
    const buur = hansuiCropBuur(crop, '[data-crop-ratio]');

    return buur ? [...buur.querySelectorAll('[data-crop-ratio]')].filter((k) => hansuiCropVan(k) === crop) : [];
}

function hansuiCropVelden(crop) {
    const houder = crop.querySelector('[data-crop-x]') ? crop : hansuiCropBuur(crop, '[data-crop-x]');

    return {
        x: houder?.querySelector('[data-crop-x]'),
        y: houder?.querySelector('[data-crop-y]'),
        width: houder?.querySelector('[data-crop-width]'),
        height: houder?.querySelector('[data-crop-height]'),
    };
}

// Hoeveel keer de breedte de hoogte is, in delen van het beeld: h = w * k.
function hansuiCropK(s) {
    return s.ratio ? s.W / (s.H * s.ratio) : null;
}

/*
| Het grootste kader in deze verhouding, zo dicht mogelijk rond een middelpunt.
|
| Bij het kiezen van een verhouding en bij de start. Het GROOTSTE en niet een
| kader van dezelfde oppervlakte: wie 4:5 kiest, wil zien wat er van zijn foto
| overblijft, en dat is meestal bijna alles.
*/
function hansuiCropVul(s, cx, cy) {
    const k = hansuiCropK(s);

    if (k === null) {
        return;
    }

    [s.w, s.h] = k <= 1 ? [1, k] : [1 / k, 1];
    s.x = hansuiCropKlem(cx - s.w / 2, 0, 1 - s.w);
    s.y = hansuiCropKlem(cy - s.h / 2, 0, 1 - s.h);
}

function hansuiCropBouw(crop) {
    let kader = crop.querySelector('.crop-frame');

    if (kader) {
        return kader;
    }

    kader = document.createElement('div');
    kader.className = 'crop-frame';
    kader.tabIndex = 0;
    kader.setAttribute('role', 'group');
    kader.setAttribute('aria-label', crop.getAttribute('data-crop')
        || 'Uitsnede. Pijltjes verschuiven, shift en pijltjes vergroten of verkleinen.');

    ['nw', 'ne', 'sw', 'se'].forEach((hoek) => {
        const greep = document.createElement('span');
        greep.className = 'crop-handle';
        greep.setAttribute('data-crop-handle', hoek);
        greep.setAttribute('aria-hidden', 'true');
        kader.appendChild(greep);
    });

    const maat = document.createElement('span');
    maat.className = 'crop-size';
    kader.appendChild(maat);

    crop.appendChild(kader);

    return kader;
}

/*
| Beginnen, of opnieuw beginnen: bij het laden van het beeld en bij
| `crop:reset`.
|
| Staan de vier velden al ingevuld, dan is dat de uitsnede van de vorige keer
| en begint het kader daar. Anders het grootste kader in de gekozen verhouding,
| in het midden -- of het hele beeld, als er geen verhouding gekozen is.
*/
function hansuiCropStart(crop) {
    const img = crop.querySelector('img');

    if (!img || !img.complete || !img.naturalWidth) {
        return;
    }

    hansuiCrop.get(crop)?.veer?.stop();

    const knop = hansuiCropKnoppen(crop).find((k) => k.getAttribute('aria-pressed') === 'true');
    const velden = hansuiCropVelden(crop);
    const gezet = ['x', 'y', 'width', 'height'].map((sleutel) => parseFloat(velden[sleutel]?.value ?? ''));
    const s = {
        W: img.naturalWidth,
        H: img.naturalHeight,
        ratio: hansuiCropRatio(knop?.getAttribute('data-crop-ratio')),
        naam: knop?.getAttribute('data-crop-ratio') ?? 'free',
        x: 0,
        y: 0,
        w: 1,
        h: 1,
        toon: null,
        veer: null,
    };

    hansuiCrop.set(crop, s);
    hansuiCropBouw(crop);

    if (gezet.every(Number.isFinite) && gezet[2] > 0 && gezet[3] > 0) {
        s.w = hansuiCropKlem(gezet[2] / 100, 0.01, 1);
        s.h = hansuiCropKlem(gezet[3] / 100, 0.01, 1);
        s.x = hansuiCropKlem(gezet[0] / 100, 0, 1 - s.w);
        s.y = hansuiCropKlem(gezet[1] / 100, 0, 1 - s.h);
    } else {
        hansuiCropVul(s, 0.5, 0.5);
    }

    hansuiCropTeken(crop);
}

/*
| Het kader tekenen, en als het echt veranderde ook melden.
|
| `toon` is wat er op het scherm staat terwijl het kader voorbij een rand hangt
| of terugveert. Wat er gemeld wordt is altijd de uitsnede binnen het beeld:
| een formulier dat halverwege een veer verstuurd wordt, krijgt geen -3%.
*/
function hansuiCropTeken(crop, melden = true) {
    const s = hansuiCrop.get(crop);
    const kader = crop.querySelector('.crop-frame');

    if (!s || !kader) {
        return;
    }

    const [x, y, w, h] = s.toon ?? [s.x, s.y, s.w, s.h];

    kader.style.left = `${x * 100}%`;
    kader.style.top = `${y * 100}%`;
    kader.style.width = `${w * 100}%`;
    kader.style.height = `${h * 100}%`;

    const breed = Math.round(s.w * s.W);
    const hoog = Math.round(s.h * s.H);
    const maat = kader.querySelector('.crop-size');

    if (maat) {
        maat.textContent = `${breed} × ${hoog}`;
    }

    if (!melden) {
        return;
    }

    const rond = (waarde) => Math.round(waarde * 1000000) / 10000;
    const detail = { x: rond(s.x), y: rond(s.y), width: rond(s.w), height: rond(s.h), ratio: s.naam, pixels: { width: breed, height: hoog } };
    const velden = hansuiCropVelden(crop);

    ['x', 'y', 'width', 'height'].forEach((sleutel) => {
        if (velden[sleutel]) {
            velden[sleutel].value = String(detail[sleutel]);
        }
    });

    crop.dispatchEvent(new CustomEvent('crop:change', { bubbles: true, detail }));
}

/*
| Groter of kleiner vanuit een hoek: de tegenoverliggende hoek blijft staan.
|
| Met een verhouding volgt het kader de richting waarin de hand het verst ging,
| en stopt het waar een van beide kanten tegen de rand komt. Zonder verhouding
| gaan breedte en hoogte elk hun eigen weg.
*/
function hansuiCropSchaal(s, anker, px, py, minX, minY) {
    const links = px < anker.x;
    const boven = py < anker.y;
    const ruimteX = links ? anker.x : 1 - anker.x;
    const ruimteY = boven ? anker.y : 1 - anker.y;
    const k = hansuiCropK(s);

    let w = Math.min(Math.abs(px - anker.x), ruimteX);
    let h = Math.min(Math.abs(py - anker.y), ruimteY);

    if (k !== null) {
        w = Math.max(w, h / k, minX, minY / k);
        w = Math.min(w, ruimteX, ruimteY / k);
        h = w * k;
    } else {
        w = hansuiCropKlem(w, minX, ruimteX);
        h = hansuiCropKlem(h, minY, ruimteY);
    }

    s.w = Math.min(w, 1);
    s.h = Math.min(h, 1);

    // Tegen de rand gedrukt met te weinig ruimte: dan schuift het kader mee
    // in plaats van uit het beeld te lopen.
    s.x = hansuiCropKlem(links ? anker.x - s.w : anker.x, 0, 1 - s.w);
    s.y = hansuiCropKlem(boven ? anker.y - s.h : anker.y, 0, 1 - s.h);
}

document.addEventListener('pointerdown', (e) => {
    if (hansuiCropGreep || (e.pointerType === 'mouse' && e.button !== 0)) {
        return;
    }

    const crop = e.target.closest?.('[data-crop]');
    const s = crop ? hansuiCrop.get(crop) : null;

    if (!s) {
        return;
    }

    // Geen beeld dat meesleept en geen tekst die blauw wordt.
    e.preventDefault();

    s.veer?.stop();
    s.veer = null;
    s.toon = null;

    const vak = crop.getBoundingClientRect();
    const kader = crop.querySelector('.crop-frame');
    const hoek = e.target.closest('[data-crop-handle]')?.getAttribute('data-crop-handle') ?? null;

    /*
    | Naast het kader gedrukt: het kader springt erheen en het slepen gaat
    | meteen door. Wie op het stuk van de foto drukt dat hij wil houden, hoort
    | niet eerst te moeten mikken op een rand.
    */
    if (!hoek && !kader.contains(e.target)) {
        s.x = hansuiCropKlem((e.clientX - vak.left) / vak.width - s.w / 2, 0, 1 - s.w);
        s.y = hansuiCropKlem((e.clientY - vak.top) / vak.height - s.h / 2, 0, 1 - s.h);
        hansuiCropTeken(crop);
    }

    hansuiCropGreep = {
        crop,
        hoek,
        vak,
        pointerId: e.pointerId,
        startX: e.clientX,
        startY: e.clientY,
        van: { x: s.x, y: s.y },
        anker: hoek ? {
            x: hoek.includes('w') ? s.x + s.w : s.x,
            y: hoek.includes('n') ? s.y + s.h : s.y,
        } : null,
    };

    crop.classList.add('crop-busy');
    hansuiGrijp(crop, e.pointerId);
    kader.focus({ preventScroll: true });
});

document.addEventListener('pointermove', (e) => {
    const g = hansuiCropGreep;

    if (!g || e.pointerId !== g.pointerId) {
        return;
    }

    const s = hansuiCrop.get(g.crop);
    const { vak } = g;

    if (g.hoek) {
        hansuiCropSchaal(
            s,
            g.anker,
            (e.clientX - vak.left) / vak.width,
            (e.clientY - vak.top) / vak.height,
            HANSUI_CROP_MIN / vak.width,
            HANSUI_CROP_MIN / vak.height,
        );
        s.toon = null;
    } else {
        const vrijX = g.van.x + (e.clientX - g.startX) / vak.width;
        const vrijY = g.van.y + (e.clientY - g.startY) / vak.height;

        s.x = hansuiCropKlem(vrijX, 0, 1 - s.w);
        s.y = hansuiCropKlem(vrijY, 0, 1 - s.h);

        const toonX = s.x + hansuiRubberband((vrijX - s.x) * vak.width, vak.width) / vak.width;
        const toonY = s.y + hansuiRubberband((vrijY - s.y) * vak.height, vak.height) / vak.height;

        s.toon = toonX === s.x && toonY === s.y ? null : [toonX, toonY, s.w, s.h];
    }

    hansuiCropTeken(g.crop);
});

function hansuiCropLos(e) {
    const g = hansuiCropGreep;

    if (!g || e.pointerId !== g.pointerId) {
        return;
    }

    hansuiCropGreep = null;
    g.crop.classList.remove('crop-busy');

    const s = hansuiCrop.get(g.crop);

    if (!s?.toon) {
        return;
    }

    // Terug binnen het beeld, met een veer: hard terugspringen leest als een fout.
    const [vanX, vanY] = s.toon;

    s.veer = hansuiVeer({
        van: 0,
        naar: 100,
        respons: 0.3,
        stap: (t) => {
            s.toon = [vanX + ((s.x - vanX) * t) / 100, vanY + ((s.y - vanY) * t) / 100, s.w, s.h];
            hansuiCropTeken(g.crop, false);
        },
        klaar: () => {
            s.toon = null;
            s.veer = null;
            hansuiCropTeken(g.crop, false);
        },
    });
}

document.addEventListener('pointerup', hansuiCropLos);
document.addEventListener('pointercancel', hansuiCropLos);

/*
| Het toetsenbord: pijltjes verschuiven, shift en pijltjes maken het kader
| groter (rechts, omlaag) of kleiner (links, omhoog), rond zijn midden. Met
| alt erbij gaat het in kleine stapjes, voor wie op de pixel wil mikken.
*/
document.addEventListener('keydown', (e) => {
    const kader = e.target.closest?.('.crop-frame');
    const crop = kader?.closest('[data-crop]');
    const s = crop ? hansuiCrop.get(crop) : null;
    const pijl = { ArrowLeft: [-1, 0], ArrowRight: [1, 0], ArrowUp: [0, -1], ArrowDown: [0, 1] }[e.key];

    if (!s || !pijl) {
        return;
    }

    e.preventDefault();

    const stap = e.altKey ? 0.002 : 0.01;
    const vak = crop.getBoundingClientRect();
    const minX = HANSUI_CROP_MIN / Math.max(1, vak.width);
    const minY = HANSUI_CROP_MIN / Math.max(1, vak.height);

    if (e.shiftKey) {
        const [cx, cy] = [s.x + s.w / 2, s.y + s.h / 2];
        const k = hansuiCropK(s);

        if (k !== null) {
            s.w = hansuiCropKlem(s.w + (pijl[0] || pijl[1]) * stap, Math.max(minX, minY / k), Math.min(1, 1 / k));
            s.h = s.w * k;
        } else {
            s.w = hansuiCropKlem(s.w + pijl[0] * stap, minX, 1);
            s.h = hansuiCropKlem(s.h + pijl[1] * stap, minY, 1);
        }

        s.x = hansuiCropKlem(cx - s.w / 2, 0, 1 - s.w);
        s.y = hansuiCropKlem(cy - s.h / 2, 0, 1 - s.h);
    } else {
        s.x = hansuiCropKlem(s.x + pijl[0] * stap, 0, 1 - s.w);
        s.y = hansuiCropKlem(s.y + pijl[1] * stap, 0, 1 - s.h);
    }

    hansuiCropTeken(crop);
});

// Een verhouding kiezen. "Vrij" (of wat geen verhouding is) laat het kader staan.
document.addEventListener('click', (e) => {
    const knop = e.target.closest?.('[data-crop-ratio]');
    const crop = knop ? hansuiCropVan(knop) : null;

    if (!crop) {
        return;
    }

    hansuiCropKnoppen(crop).forEach((k) => k.setAttribute('aria-pressed', k === knop ? 'true' : 'false'));

    const s = hansuiCrop.get(crop);

    // Het beeld is er nog niet: de ingedrukte knop wordt gelezen bij de start.
    if (!s) {
        return;
    }

    s.naam = knop.getAttribute('data-crop-ratio');
    s.ratio = hansuiCropRatio(s.naam);
    hansuiCropVul(s, s.x + s.w / 2, s.y + s.h / 2);
    hansuiCropTeken(crop);
});

// Een nieuw beeld in de bijsnijder begint opnieuw. `load` bubbelt niet, dus
// in de vangfase.
document.addEventListener('load', (e) => {
    const crop = e.target.tagName === 'IMG' ? e.target.closest('[data-crop]') : null;

    if (crop) {
        hansuiCropStart(crop);
    }
}, true);

document.addEventListener('crop:reset', (e) => {
    if (e.target.matches?.('[data-crop]')) {
        hansuiCropStart(e.target);
    }
});

function hansuiCroppers() {
    document.querySelectorAll('[data-crop]').forEach(hansuiCropStart);
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', hansuiCroppers);
} else {
    hansuiCroppers();
}

/*
|------------------------------------------------------------------------------
| Filteren terwijl je typt: data-filter
|------------------------------------------------------------------------------
|
| Een zoekveld met `data-filter="#lijst"` verbergt in die lijst de elementen met
| `data-filter-item` die niet passen. De waarde van `data-filter-item` is waarop
| gezocht wordt, anders de tekst. Matchen op woordgrens, zoals het zoekpalet:
| "gent" vindt "Etalage Gent" maar niet "Urgentie". `data-filter-empty` in de
| lijst verschijnt als niets past.
|
| Geen aanvraag naar de server: dit is voor lijsten die al op de pagina staan,
| tot een paar honderd regels.
*/
function hansuiFilterPast(hooiberg, term) {
    return term.split(/\s+/).every((woord) =>
        hooiberg.split(/[\s\-/·,.:;()]+/).some((deel) => deel.startsWith(woord)) || hooiberg.startsWith(woord));
}

document.addEventListener('input', (e) => {
    const veld = e.target.closest?.('[data-filter]');
    if (!veld) return;

    const lijst = document.querySelector(veld.getAttribute('data-filter'));
    if (!lijst) return;

    const term = veld.value.trim().toLowerCase();
    let zichtbaar = 0;

    lijst.querySelectorAll('[data-filter-item]').forEach((regel) => {
        const hooiberg = (regel.getAttribute('data-filter-item') || regel.textContent).toLowerCase();
        const ja = term === '' || hansuiFilterPast(hooiberg, term);
        regel.hidden = !ja;
        if (ja) zichtbaar += 1;
    });

    const leeg = lijst.querySelector('[data-filter-empty]');
    if (leeg) leeg.hidden = zichtbaar > 0;
});

/*
|------------------------------------------------------------------------------
| Tonen naargelang een veld: data-show-when
|------------------------------------------------------------------------------
|
| `data-show-when="naam=waarde"` toont een element alleen als het formulierveld
| met die naam die waarde heeft; meer waarden scheid je met een komma
| ("herhalen[freq]=week,maand"). Zonder `=` volstaat dat het veld iets heeft:
| een aangevinkt vakje, een gekozen optie, getypte tekst.
|
| Het veld wordt eerst in hetzelfde formulier gezocht, dan in het document.
| Wat verborgen staat, gaat gewoon mee met het formulier: de server leest wat
| bij de keuze hoort. Een verborgen verplicht veld blokkeert het versturen
| wel; zet `required` dus alleen op wat altijd zichtbaar is.
*/
function hansuiVeldwaarden(naam, bij) {
    const vorm = bij.closest('form');
    const velden = [...(vorm || document).querySelectorAll(`[name="${CSS.escape(naam)}"]`)];

    return velden.flatMap((veld) => {
        if (veld.type === 'checkbox' || veld.type === 'radio') return veld.checked ? [veld.value] : [];
        if (veld.multiple) return [...veld.selectedOptions].map((o) => o.value);

        return veld.value === '' ? [] : [veld.value];
    });
}

function hansuiToonWanneer() {
    document.querySelectorAll('[data-show-when]').forEach((el) => {
        const regel = el.getAttribute('data-show-when');
        const [naam, lijst] = regel.includes('=') ? [regel.slice(0, regel.indexOf('=')), regel.slice(regel.indexOf('=') + 1)] : [regel, null];
        const waarden = hansuiVeldwaarden(naam, el);

        el.hidden = lijst === null
            ? waarden.length === 0
            : !lijst.split(',').some((w) => waarden.includes(w) || (w === '' && waarden.length === 0));
    });
}

document.addEventListener('change', hansuiToonWanneer);
document.addEventListener('input', (e) => {
    if (e.target.matches?.('input[type="text"], input[type="number"], textarea')) hansuiToonWanneer();
});
document.addEventListener('DOMContentLoaded', hansuiToonWanneer);

/*
|------------------------------------------------------------------------------
| Een code in losse vakjes: data-code-group
|------------------------------------------------------------------------------
|
| Zes vakjes van een teken, voor een koppelcode of een verificatiecode. Meer
| werk dan een veld, en het waard: wie een code overtypt van een ander scherm,
| ziet in een oogopslag hoever hij is.
|
| `data-code-cell` op elk vakje, `data-code-value` op het verborgen veld dat de
| hele code naar de server brengt. De waarde van `data-code-group` kiest de
| tekens: leeg of `alnum` voor letters en cijfers (kleine letters worden
| hoofdletters), `digits` voor alleen cijfers. PLAKKEN verdeelt de code over de
| vakjes. Is de code vol, dan wordt het formulier ingediend, tenzij
| `data-autosubmit="false"` op de groep staat. `data-code-autofocus` zet de
| cursor bij het laden in het eerste vakje.
*/
function hansuiCodeTekens(groep, tekst) {
    const soort = groep.getAttribute('data-code-group');

    return soort === 'digits'
        ? tekst.replace(/\D/g, '')
        : tekst.toUpperCase().replace(/[^A-Z0-9]/g, '');
}

function hansuiCodeVakjes(groep) {
    return [...groep.querySelectorAll('[data-code-cell]')];
}

function hansuiCodeBewaar(groep) {
    const waarde = hansuiCodeVakjes(groep).map((v) => v.value).join('');
    const verborgen = groep.querySelector('[data-code-value]');
    if (verborgen) verborgen.value = waarde;

    const vorm = groep.closest('form');
    if (waarde.length === hansuiCodeVakjes(groep).length && vorm && groep.dataset.autosubmit !== 'false') {
        vorm.requestSubmit();
    }
}

document.addEventListener('input', (e) => {
    const vak = e.target.closest?.('[data-code-cell]');
    const groep = vak?.closest('[data-code-group]');
    if (!groep) return;

    const vakjes = hansuiCodeVakjes(groep);
    const i = vakjes.indexOf(vak);
    vak.value = hansuiCodeTekens(groep, vak.value).slice(0, 1);

    if (vak.value && i < vakjes.length - 1) {
        vakjes[i + 1].focus();
        vakjes[i + 1].select();
    }

    hansuiCodeBewaar(groep);
});

document.addEventListener('keydown', (e) => {
    const vak = e.target.closest?.('[data-code-cell]');
    const groep = vak?.closest('[data-code-group]');
    if (!groep) return;

    const vakjes = hansuiCodeVakjes(groep);
    const i = vakjes.indexOf(vak);

    if (e.key === 'Backspace' && !vak.value && i > 0) {
        e.preventDefault();
        vakjes[i - 1].focus();
        vakjes[i - 1].value = '';
        hansuiCodeBewaar(groep);
    } else if (e.key === 'ArrowLeft' && i > 0) {
        e.preventDefault();
        vakjes[i - 1].focus();
    } else if (e.key === 'ArrowRight' && i < vakjes.length - 1) {
        e.preventDefault();
        vakjes[i + 1].focus();
    }
});

document.addEventListener('focusin', (e) => {
    if (e.target.matches?.('[data-code-cell]')) e.target.select();
});

document.addEventListener('paste', (e) => {
    const vak = e.target.closest?.('[data-code-cell]');
    const groep = vak?.closest('[data-code-group]');
    if (!groep) return;

    e.preventDefault();

    const vakjes = hansuiCodeVakjes(groep);
    const i = vakjes.indexOf(vak);
    const tekst = hansuiCodeTekens(groep, e.clipboardData?.getData('text') || '');

    [...tekst].slice(0, vakjes.length - i).forEach((teken, n) => {
        vakjes[i + n].value = teken;
    });

    vakjes[Math.min(i + tekst.length, vakjes.length - 1)].focus();
    hansuiCodeBewaar(groep);
});

document.addEventListener('DOMContentLoaded', () => {
    document.querySelector('[data-code-group][data-code-autofocus] [data-code-cell]')?.focus();
});

/*
|------------------------------------------------------------------------------
| Een melding onderaan: het toast-event
|------------------------------------------------------------------------------
|
| Voor wat gelukt is terwijl het element dat erom vroeg al weg is: een knop in
| een contextmenu, een formulier in een voorbeeldvenster. Een regel, een paar
| seconden, en role="status" zodat een schermlezer hem voorleest.
|
|   window.dispatchEvent(new CustomEvent('toast', { detail: 'Link gekopieerd' }))
|
| of `{ detail: { text: 'Mislukt', tone: 'danger' } }` voor een fout.
*/
let hansuiToastEl = null;

function hansuiToast(tekst, toon = null) {
    if (!tekst) return;

    if (!hansuiToastEl) {
        hansuiToastEl = document.createElement('div');
        hansuiToastEl.className = 'toast';
        hansuiToastEl.setAttribute('role', 'status');
        hansuiToastEl.setAttribute('aria-live', 'polite');
        document.body.appendChild(hansuiToastEl);
    }

    // In een open <dialog> hoort hij in dat venster: de rest van de pagina
    // ligt dan onder het waas en is voor een schermlezer weg.
    const venster = [...document.querySelectorAll('dialog[open]')].pop();
    (venster || document.body).appendChild(hansuiToastEl);

    hansuiToastEl.textContent = tekst;
    hansuiToastEl.toggleAttribute('data-danger', toon === 'danger');
    hansuiToastEl.setAttribute('data-shown', '');

    clearTimeout(hansuiToastEl.hansuiTimer);
    hansuiToastEl.hansuiTimer = setTimeout(() => hansuiToastEl.removeAttribute('data-shown'), 2600);
}

window.addEventListener('toast', (e) => {
    const d = e.detail;
    typeof d === 'string' ? hansuiToast(d) : hansuiToast(d?.text, d?.tone);
});

/*
|------------------------------------------------------------------------------
| Waarden invullen: data-context-item
|------------------------------------------------------------------------------
|
| Een contextmenu staat EEN keer op de pagina, en toch hoort "Verwijderen" op
| tegel 12 naar /media/12 te wijzen. De tegel draagt daarom haar waarden als
| JSON in `data-context-item`, en bij het openen worden ze ingevuld:
|
|   - `{sleutel}` in om het even welk attribuut (href, action, value,
|     data-confirm ...) wordt de waarde; het origineel wordt onthouden, dus
|     de volgende tegel begint weer van het sjabloon;
|   - `data-context-text="sleutel"` krijgt de waarde als tekst;
|   - `data-context-value="sleutel"` krijgt de waarde als .value (een veld);
|   - `data-context-if="sleutel"` staat er alleen als de waarde iets is, met
|     `!sleutel` alleen als ze niets is, met `sleutel=a,b` of `sleutel!=a` op
|     een van die waarden; een veld dat er niet staat, gaat ook niet mee;
|   - `data-context-disabled` met dezelfde vorm maakt een item onbruikbaar
|     (aria-disabled) in plaats van het weg te laten;
|   - `data-context-ids` op een formulier krijgt een verborgen veld per id: de
|     hele selectie, of dat ene element. De waarde is de veldnaam (standaard
|     die van `data-bulk`, anders `ids[]`).
|
| Het voorbeeldvenster gebruikt dezelfde invulling voor zijn zijpaneel.
*/
function hansuiWaarden(el) {
    const bron = el?.closest?.('[data-context-item]');
    let waarden = {};

    try {
        waarden = JSON.parse(bron?.getAttribute('data-context-item') || '{}') || {};
    } catch (_) { /* geen geldige JSON: dan zonder waarden */ }

    /*
    | De selectie. Rechtsklikken op een AANGEVINKTE rij terwijl er meer
    | aanstaan, gaat over die hele selectie (`selection`, `count`, `ids`);
    | rechtsklikken op een andere rij gaat alleen over die rij.
    */
    const scope = el?.closest?.('[data-bulk]');
    const vakje = bron?.querySelector('[data-bulk-item]') ?? (el ? hansuiBulkVakje(el) : null);
    const aan = scope ? [...scope.querySelectorAll('[data-bulk-item]:checked')] : [];
    const eigen = waarden.id ?? vakje?.value;

    // Zonder eigen rij (een knop in de balk, een lege plek) gaat het over wat
    // er geselecteerd staat, zodra dat iets is.
    const zonderRij = !bron && !vakje;

    waarden.checked = !!vakje?.checked;
    waarden.selected = aan.length;
    waarden.selection = zonderRij ? aan.length > 0 : !!vakje?.checked && aan.length > 1;
    waarden.count = waarden.selection ? aan.length : (zonderRij ? 0 : 1);
    waarden.ids = waarden.selection ? aan.map((b) => b.value) : (eigen !== undefined ? [String(eigen)] : []);
    waarden.hansuiNaam = scope?.getAttribute('data-bulk') || 'ids[]';

    return waarden;
}

function hansuiTest(uitdrukking, waarden) {
    const expr = (uitdrukking || '').trim();
    if (!expr) return true;

    const leeg = (v) => v === undefined || v === null || v === '' || v === false || v === 0 || v === '0'
        || (Array.isArray(v) && v.length === 0);

    const m = expr.match(/^([\w.-]+)\s*(!?=)\s*(.*)$/);
    if (m) {
        const lijst = m[3].split(',').map((x) => x.trim());
        const in_ = lijst.includes(String(waarden[m[1]] ?? ''));

        return m[2] === '=' ? in_ : !in_;
    }

    return expr.startsWith('!') ? leeg(waarden[expr.slice(1)]) : !leeg(waarden[expr]);
}

function hansuiVul(wortel, waarden) {
    const alle = [wortel, ...wortel.querySelectorAll('*')];

    alle.forEach((el) => {
        // Het sjabloon een keer vastleggen, bij de eerste invulling.
        if (!el.hansuiSjabloon) {
            el.hansuiSjabloon = {};
            [...el.attributes].forEach((a) => {
                if (/\{[a-z0-9_]+\}/i.test(a.value)) el.hansuiSjabloon[a.name] = a.value;
            });
        }

        Object.entries(el.hansuiSjabloon).forEach(([naam, sjabloon]) => {
            el.setAttribute(naam, sjabloon.replace(/\{([a-z0-9_]+)\}/gi, (_, k) => waarden[k] ?? ''));
        });
    });

    wortel.querySelectorAll('[data-context-text]').forEach((el) => {
        el.textContent = waarden[el.getAttribute('data-context-text')] ?? '';
    });

    wortel.querySelectorAll('[data-context-value]').forEach((el) => {
        el.value = waarden[el.getAttribute('data-context-value')] ?? '';
    });

    [wortel, ...wortel.querySelectorAll('[data-context-if]')].forEach((el) => {
        if (!el.hasAttribute('data-context-if')) return;
        const ja = hansuiTest(el.getAttribute('data-context-if'), waarden);
        el.hidden = !ja;
        if ('disabled' in el && el.matches('input, select, textarea, button, fieldset')) el.disabled = !ja;
    });

    wortel.querySelectorAll('[data-context-disabled]').forEach((el) => {
        el.setAttribute('aria-disabled', String(hansuiTest(el.getAttribute('data-context-disabled'), waarden)));
    });

    wortel.querySelectorAll('form[data-context-ids]').forEach((form) => {
        form.querySelectorAll('input[data-context-id]').forEach((v) => v.remove());
        const naam = form.getAttribute('data-context-ids') || waarden.hansuiNaam || 'ids[]';

        (waarden.ids || []).forEach((id) => {
            const v = document.createElement('input');
            v.type = 'hidden';
            v.name = naam;
            v.value = id;
            v.setAttribute('data-context-id', '');
            form.appendChild(v);
        });
    });
}

/*
|------------------------------------------------------------------------------
| Het contextmenu: data-context-menu
|------------------------------------------------------------------------------
|
| Rechtsklikken op een element met `data-context-menu="#menu"` opent dat menu
| waar de muis staat, met de waarden van het element ingevuld (zie hierboven).
| Het dichtstbijzijnde element wint: een tegel met een eigen menu in een raster
| met een menu voor "een lege plek" krijgt het hare.
|
| Niet iedereen heeft een rechtermuisknop. Een knop met `data-context-trigger`
| opent hetzelfde menu onder zichzelf (een vinger, een toetsenbord), en de
| ContextMenu-toets of `Shift` + `F10` op een element met de focus ook.
|
| Het menu zelf is gewone HTML met role="menu": links, knoppen en formulieren
| met role="menuitem", een <hr role="separator"> ertussen. Een item met
| `data-context-sub` opent het role="menu" dat erna staat als submenu. Met
| `data-context-key="F2"` werkt een item ook als sneltoets op het element met
| de focus, zonder dat het menu opengaat. `data-context-click=".x"` klikt op
| wat die selector BINNEN het element aanwijst, en `data-context-fill="#x"`
| vult dezelfde waarden ook in dat element in (een venster dat het item opent).
|
| Het toetsenbord doet wat het in een menu van het besturingssysteem doet:
| pijltjes, Home en End, Enter of spatie, de eerste letter, Escape, en rechts
| en links in en uit een submenu.
*/
const hansuiMenu = { open: null, doel: null, terug: null, waarden: {} };

const HANSUI_ITEM = '[role="menuitem"], [role="menuitemcheckbox"], [role="menuitemradio"]';

function hansuiMenuItems(menu) {
    return [...menu.querySelectorAll(HANSUI_ITEM)].filter((it) =>
        it.closest('[role="menu"]') === menu && it.getClientRects().length > 0);
}

// Zichtbaar, ook als het een omhulling met display: contents is (een <form>).
function hansuiZichtbaar(el) {
    if (el.hidden) return false;
    if (getComputedStyle(el).display === 'contents') return [...el.children].some(hansuiZichtbaar);

    return el.getClientRects().length > 0;
}

// Verborgen door iets TUSSEN het element en de grens (het menu zelf telt niet).
function hansuiVerborgenIn(el, grens) {
    for (let x = el; x && x !== grens; x = x.parentElement) {
        if (x.hidden) return true;
    }

    return false;
}

function hansuiMenuScheiding(menu) {
    // Een scheidingslijn naast een weggevallen groep is een dubbele lijn.
    let vorige = null;
    [...menu.querySelectorAll('[role="separator"]')].forEach((sep) => { sep.hidden = false; });

    const kinderen = [...menu.children].filter(hansuiZichtbaar);
    kinderen.forEach((k, i) => {
        if (!k.matches('[role="separator"]')) {
            vorige = k;
            return;
        }
        const erna = kinderen.slice(i + 1).find((x) => !x.matches('[role="separator"]'));
        if (!vorige || vorige.matches('[role="separator"]') || !erna) k.hidden = true;
        vorige = k.hidden ? vorige : k;
    });
}

function hansuiMenuPlaats(menu, x, y, anker = null) {
    menu.style.left = '0px';
    menu.style.top = '0px';

    const b = menu.offsetWidth;
    const h = menu.offsetHeight;
    const vw = document.documentElement.clientWidth;
    const vh = window.innerHeight;

    if (anker) {
        const r = anker.getBoundingClientRect();
        x = r.right - b > 8 && r.left + b > vw - 8 ? r.right - b : r.left;
        y = r.bottom + 4 + h > vh - 8 && r.top - h - 4 > 8 ? r.top - h - 4 : r.bottom + 4;
    } else {
        if (x + b > vw - 8) x = Math.max(8, x - b);
        if (y + h > vh - 8) y = Math.max(8, y - h);
    }

    menu.style.left = `${Math.max(8, Math.min(x, vw - b - 8))}px`;
    menu.style.top = `${Math.max(8, Math.min(y, vh - h - 8))}px`;
}

function hansuiMenuSluitSubs(menu) {
    menu.querySelectorAll('[data-context-sub][aria-expanded="true"]').forEach((ouder) => {
        ouder.setAttribute('aria-expanded', 'false');
        const sub = ouder.nextElementSibling;
        if (sub) sub.hidden = true;
    });
}

function hansuiMenuOpenSub(ouder, focus = false) {
    const sub = ouder.nextElementSibling;
    if (!sub?.matches('[role="menu"]')) return;

    const menu = ouder.closest('[role="menu"]');
    hansuiMenuSluitSubs(menu);

    ouder.setAttribute('aria-expanded', 'true');
    sub.hidden = false;
    hansuiMenuScheiding(sub);

    const r = ouder.getBoundingClientRect();
    const m = menu.getBoundingClientRect();
    const b = sub.offsetWidth;
    const vw = document.documentElement.clientWidth;
    const x = m.right + b - 4 < vw - 8 ? m.right - 4 : m.left - b + 4;

    hansuiMenuPlaats(sub, Math.max(8, x), r.top - 5);
    sub.style.left = `${Math.max(8, x)}px`;

    if (focus) hansuiMenuItems(sub)[0]?.focus();
}

function hansuiMenuOpen(menu, doel, { x = 0, y = 0, anker = null, toetsenbord = false } = {}) {
    hansuiMenuSluit(false);

    const waarden = hansuiWaarden(doel);
    hansuiVul(menu, waarden);

    hansuiMenu.open = menu;
    hansuiMenu.doel = doel;
    hansuiMenu.waarden = waarden;
    hansuiMenu.terug = doel.contains(document.activeElement) ? document.activeElement
        : (anker || doel.querySelector('[data-grid-item]') || doel);

    if (!menu.hasAttribute('tabindex')) menu.setAttribute('tabindex', '-1');
    menu.hidden = false;
    menu.setAttribute('data-open', '');
    hansuiMenuScheiding(menu);
    hansuiMenuPlaats(menu, x, y, anker);

    const knop = anker?.matches('[data-context-trigger]') ? anker : null;
    knop?.setAttribute('aria-expanded', 'true');
    menu.hansuiKnop = knop;

    doel.setAttribute('data-context-open', '');

    // Met het toetsenbord meteen op het eerste item; met de muis op het menu
    // zelf, zodat er niets opgelicht staat dat de muis niet aanwees.
    toetsenbord ? hansuiMenuItems(menu)[0]?.focus() : menu.focus({ preventScroll: true });

    doel.dispatchEvent(new CustomEvent('context:open', { bubbles: true, detail: { menu, values: waarden } }));
}

function hansuiMenuSluit(focusTerug = true) {
    const menu = hansuiMenu.open;
    if (!menu) return;

    const hadFocus = menu.contains(document.activeElement);

    hansuiMenuSluitSubs(menu);
    menu.hidden = true;
    menu.removeAttribute('data-open');
    menu.hansuiKnop?.setAttribute('aria-expanded', 'false');
    hansuiMenu.doel?.removeAttribute('data-context-open');

    const terug = hansuiMenu.terug;
    hansuiMenu.open = null;
    hansuiMenu.doel = null;

    if (focusTerug && hadFocus && terug?.isConnected) terug.focus({ preventScroll: true });
}

function hansuiMenuVan(doel, selector = null) {
    return document.querySelector(selector || doel.getAttribute('data-context-menu') || '');
}

document.addEventListener('contextmenu', (e) => {
    const doel = e.target.closest?.('[data-context-menu]');

    // Een typveld houdt zijn eigen menu: knippen en plakken horen daar.
    if (!doel || hansuiIsTypveld(e.target)) {
        hansuiMenuSluit(false);
        return;
    }

    // Binnen een open menu nog eens rechtsklikken doet niets.
    if (hansuiMenu.open?.contains(e.target)) {
        e.preventDefault();
        return;
    }

    const menu = hansuiMenuVan(doel);
    if (!menu) return;

    e.preventDefault();

    // De ContextMenu-toets geeft geen muispositie mee: dan onder het element.
    const viaToets = e.button !== 2 && e.clientX === 0 && e.clientY === 0;
    hansuiMenuOpen(menu, doel, viaToets
        ? { anker: document.activeElement !== document.body ? document.activeElement : doel, toetsenbord: true }
        : { x: e.clientX, y: e.clientY });
});

document.addEventListener('click', (e) => {
    const knop = e.target.closest?.('[data-context-trigger]');

    if (knop) {
        e.preventDefault();
        const doel = knop.closest('[data-context-menu]') || knop;
        const menu = hansuiMenuVan(doel, knop.getAttribute('data-context-trigger'));

        if (!menu) return;
        if (hansuiMenu.open === menu && hansuiMenu.doel === doel) {
            hansuiMenuSluit();
            return;
        }

        // e.detail is 0 bij een klik via Enter of spatie.
        hansuiMenuOpen(menu, doel, { anker: knop, toetsenbord: e.detail === 0 });
        return;
    }

    const menu = hansuiMenu.open;
    if (!menu) return;

    if (!menu.contains(e.target)) {
        hansuiMenuSluit(false);
        return;
    }

    const item = e.target.closest(HANSUI_ITEM);
    if (!item) return;

    if (item.getAttribute('aria-disabled') === 'true') {
        e.preventDefault();
        return;
    }

    if (item.hasAttribute('data-context-sub')) {
        e.preventDefault();
        hansuiMenuOpenSub(item, e.detail === 0);
        return;
    }

    hansuiMenuDoe(item, hansuiMenu.doel, hansuiMenu.waarden);

    // Sluiten NA de standaardactie: een verzendknop in een formulier in het
    // menu verzendt ook als het menu intussen verborgen is.
    hansuiMenuSluit(!item.matches('[data-modal-open], [data-context-fill]'));
});

/*
| `data-context-fill` werkt ook BUITEN een menu: een knop in de selectiebalk
| die hetzelfde venster opent als het menu-item, vult het met de waarden van
| waar hij staat -- in een `data-bulk`-lijst is dat de selectie.
*/
document.addEventListener('click', (e) => {
    const knop = e.target.closest?.('[data-context-fill]');
    if (!knop || knop.closest('[role="menu"]')) return;

    hansuiMenuDoe(knop, knop, hansuiWaarden(knop));
});

function hansuiMenuDoe(item, doel, waarden) {
    const klik = item.getAttribute('data-context-click');
    if (klik && doel) {
        const wat = doel.matches(klik) ? doel : doel.querySelector(klik);
        setTimeout(() => wat?.click(), 0);
    }

    const vul = item.getAttribute('data-context-fill');
    if (vul) {
        const el = document.querySelector(vul);
        if (el) {
            hansuiVul(el, waarden);
            // Het veld dat de focus kreeg, is het veld dat je wil overtypen.
            setTimeout(() => {
                const actief = el.querySelector('[autofocus]') || (el.contains(document.activeElement) ? document.activeElement : null);
                actief?.select?.();
            }, 0);
        }
    }
}

document.addEventListener('pointerover', (e) => {
    const menu = hansuiMenu.open;
    if (!menu || e.pointerType === 'touch') return;

    const item = e.target.closest?.(HANSUI_ITEM);
    if (!item || !menu.contains(item)) return;

    const niveau = item.closest('[role="menu"]');
    if (item.hasAttribute('data-context-sub')) {
        if (item.getAttribute('aria-expanded') !== 'true') hansuiMenuOpenSub(item);
    } else {
        hansuiMenuSluitSubs(niveau);
    }

    item.focus({ preventScroll: true });
});

document.addEventListener('keydown', (e) => {
    const menu = hansuiMenu.open;

    // Het menu openen met het toetsenbord.
    if (!menu && (e.key === 'ContextMenu' || (e.shiftKey && e.key === 'F10'))) {
        const doel = document.activeElement?.closest?.('[data-context-menu]');
        const m = doel && !hansuiIsTypveld(document.activeElement) ? hansuiMenuVan(doel) : null;

        if (m) {
            e.preventDefault();
            hansuiMenuOpen(m, doel, { anker: document.activeElement, toetsenbord: true });
        }
        return;
    }

    if (!menu) {
        hansuiMenuSneltoets(e);
        return;
    }

    const niveau = document.activeElement?.closest?.('[role="menu"]');
    const huidig = niveau && menu.contains(niveau) ? niveau : menu;
    const items = hansuiMenuItems(huidig);
    const i = items.indexOf(document.activeElement);
    const naar = (n) => items[(n + items.length) % items.length]?.focus();

    switch (e.key) {
        case 'ArrowDown': e.preventDefault(); naar(i + 1); break;
        case 'ArrowUp': e.preventDefault(); naar(i < 0 ? -1 : i - 1); break;
        case 'Home': e.preventDefault(); naar(0); break;
        case 'End': e.preventDefault(); naar(-1); break;
        case 'ArrowRight':
            if (document.activeElement?.hasAttribute('data-context-sub')) {
                e.preventDefault();
                hansuiMenuOpenSub(document.activeElement, true);
            }
            break;
        case 'ArrowLeft':
            if (huidig !== menu) {
                e.preventDefault();
                const ouder = huidig.previousElementSibling;
                hansuiMenuSluitSubs(ouder.closest('[role="menu"]'));
                ouder.focus();
            }
            break;
        case 'Escape':
            e.preventDefault();
            e.stopPropagation();
            if (huidig !== menu) {
                const ouder = huidig.previousElementSibling;
                hansuiMenuSluitSubs(ouder.closest('[role="menu"]'));
                ouder.focus();
            } else {
                hansuiMenuSluit();
            }
            break;
        case 'Tab':
            e.preventDefault();
            hansuiMenuSluit();
            break;
        case 'Enter':
        case ' ':
            if (i >= 0) {
                e.preventDefault();
                items[i].click();
            }
            break;
        default:
            // De eerste letter: naar het volgende item dat ermee begint.
            if (e.key.length === 1 && /\S/.test(e.key) && !e.ctrlKey && !e.metaKey && !e.altKey) {
                const letter = e.key.toLocaleLowerCase();
                const rest = [...items.slice(i + 1), ...items.slice(0, i + 1)];
                rest.find((it) => it.textContent.trim().toLocaleLowerCase().startsWith(letter))?.focus();
            }
    }
}, true);

/*
| Een sneltoets van een menu-item, zonder het menu te openen: `F2` op een tegel
| met de focus doet wat "Hernoemen" in haar menu doet. Alleen items die er bij
| DEZE waarden staan en bruikbaar zijn, tellen mee.
*/
function hansuiMenuSneltoets(e) {
    if (e.defaultPrevented || e.ctrlKey || e.metaKey || e.altKey || hansuiIsTypveld(e.target)) return;
    if (document.querySelector('dialog[open]') && !e.target.closest?.('dialog[open]')) return;

    const doel = e.target.closest?.('[data-context-menu]');
    const menu = doel ? hansuiMenuVan(doel) : null;
    if (!menu || !menu.querySelector('[data-context-key]')) return;

    const waarden = hansuiWaarden(doel);
    hansuiVul(menu, waarden);

    const item = [...menu.querySelectorAll('[data-context-key]')].find((it) =>
        it.getAttribute('data-context-key').split(/[\s,]+/).some((k) => k.toLowerCase() === e.key.toLowerCase())
        && !hansuiVerborgenIn(it, menu)
        && it.getAttribute('aria-disabled') !== 'true');

    if (!item) return;

    e.preventDefault();
    hansuiMenu.terug = e.target;
    hansuiMenuDoe(item, doel, waarden);
    item.click();
}

// Scrollen of het venster verkleinen: het menu hangt dan naast zijn plek.
window.addEventListener('scroll', (e) => {
    if (hansuiMenu.open && !hansuiMenu.open.contains(e.target)) hansuiMenuSluit(false);
}, true);
window.addEventListener('resize', () => hansuiMenuSluit(false));
window.addEventListener('blur', () => hansuiMenuSluit(false));

/*
|------------------------------------------------------------------------------
| Een raster met pijltjes: data-grid-nav
|------------------------------------------------------------------------------
|
| Tweehonderd tegels zijn tweehonderd tabstops, en dat is geen raster maar een
| hindernis. Met `data-grid-nav` op de omhulling en `data-grid-item` op wat de
| focus krijgt, is het raster een stop: Tab gaat erin en eruit, de pijltjes
| bewegen erbinnen. Omhoog en omlaag kijken naar de RIJ zoals ze op het scherm
| staat, want hoeveel tegels er naast elkaar passen, hangt af van de breedte.
| Home en End gaan naar het eerste en laatste item; spatie klikt, net als
| Enter op een link.
|
| Het item dat het laatst de focus had, houdt tabindex="0": wie terugtabt,
| komt terug waar hij was.
*/
function hansuiRasterItems(raster) {
    return [...raster.querySelectorAll('[data-grid-item]')].filter((it) =>
        it.closest('[data-grid-nav]') === raster && it.getClientRects().length > 0);
}

function hansuiRasterBegin(raster) {
    const items = hansuiRasterItems(raster);
    if (!items.length) return;

    const actief = items.find((it) => it.getAttribute('tabindex') === '0') || items[0];
    items.forEach((it) => it.setAttribute('tabindex', it === actief ? '0' : '-1'));
}

function hansuiRasterStart() {
    document.querySelectorAll('[data-grid-nav]').forEach(hansuiRasterBegin);
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', hansuiRasterStart);
} else {
    hansuiRasterStart();
}

document.addEventListener('focusin', (e) => {
    const item = e.target.closest?.('[data-grid-item]');
    const raster = item?.closest('[data-grid-nav]');
    if (!raster) return;

    hansuiRasterItems(raster).forEach((it) => it.setAttribute('tabindex', it === item ? '0' : '-1'));
});

function hansuiRasterBuur(items, huidig, richting) {
    const r = huidig.getBoundingClientRect();
    const midden = r.left + r.width / 2;

    const rijen = items
        .map((it) => ({ it, r: it.getBoundingClientRect() }))
        .filter(({ r: o }) => (richting > 0 ? o.top > r.top + r.height / 2 : o.bottom < r.bottom - r.height / 2));

    if (!rijen.length) return null;

    const rijTop = richting > 0 ? Math.min(...rijen.map((x) => x.r.top)) : Math.max(...rijen.map((x) => x.r.top));
    const rij = rijen.filter((x) => Math.abs(x.r.top - rijTop) < 4);

    return rij.reduce((best, x) =>
        Math.abs(x.r.left + x.r.width / 2 - midden) < Math.abs(best.r.left + best.r.width / 2 - midden) ? x : best).it;
}

document.addEventListener('keydown', (e) => {
    if (e.defaultPrevented || hansuiMenu.open || e.altKey || hansuiIsTypveld(e.target)) return;

    const raster = e.target.closest?.('[data-grid-nav]');
    const huidig = e.target.closest?.('[data-grid-item]');
    if (!raster || !huidig || huidig.closest('[data-grid-nav]') !== raster) return;

    const items = hansuiRasterItems(raster);
    const i = items.indexOf(huidig);
    let naar = null;

    switch (e.key) {
        case 'ArrowRight': naar = items[i + 1]; break;
        case 'ArrowLeft': naar = items[i - 1]; break;
        case 'ArrowDown': naar = hansuiRasterBuur(items, huidig, 1); break;
        case 'ArrowUp': naar = hansuiRasterBuur(items, huidig, -1); break;
        case 'Home': naar = items[0]; break;
        case 'End': naar = items[items.length - 1]; break;
        case ' ':
            if (e.ctrlKey || e.metaKey || e.target !== huidig || huidig.matches('button, input')) return;
            e.preventDefault();
            huidig.click();
            return;
        default:
            return;
    }

    e.preventDefault();

    if (naar) {
        naar.focus();
        naar.scrollIntoView({ block: 'nearest', inline: 'nearest' });
    }
});

/*
|------------------------------------------------------------------------------
| Slepen naar een doel: data-drag-item en data-drop-target
|------------------------------------------------------------------------------
|
| Een tegel naar een map slepen, zoals in elke verkenner. `data-drag-item` op
| wat je vastpakt (de waarde is het id, met draggable="true"), en
| `data-drop-target="/url"` op waar je het loslaat. Staat de tegel aangevinkt
| in een `data-bulk`-lijst, dan gaat de hele selectie mee.
|
| Bij het loslaten gaat er een POST naar de url met een veld per id (de naam
| uit `data-drop-name`, anders die van `data-bulk`, anders `ids[]`), de velden
| uit `data-drop-fields` (JSON) en het CSRF-token uit `data-drop-token` of de
| <meta name="csrf-token">. Daarna `drop:done` op het doel; wie dat niet
| tegenhoudt, krijgt een herladen pagina, want wat er nu waar staat weet de
| server beter dan dit script.
|
| `data-drag-label="bestand|bestanden"` zet bij meer dan een het aantal naast
| de muis, want een stapel die je niet ziet, sleep je niet met vertrouwen.
|
| Met de muis. Wie met een vinger of het toetsenbord werkt, verplaatst via het
| contextmenu: een venster met de mappen is daar duidelijker dan slepen.
*/
const hansuiSleepNaar = { ids: null, bron: null, naam: 'ids[]' };

document.addEventListener('dragstart', (e) => {
    const item = e.target.closest?.('[data-drag-item]');
    if (!item) return;

    const scope = item.closest('[data-bulk]');
    const vakje = item.querySelector('[data-bulk-item]') ?? hansuiBulkVakje(item);
    const aan = scope ? [...scope.querySelectorAll('[data-bulk-item]:checked')] : [];
    const ids = vakje?.checked && aan.length > 1 ? aan.map((b) => b.value) : [item.getAttribute('data-drag-item')];

    hansuiSleepNaar.ids = ids;
    hansuiSleepNaar.bron = item;
    hansuiSleepNaar.naam = scope?.getAttribute('data-bulk') || 'ids[]';

    e.dataTransfer.effectAllowed = 'move';
    e.dataTransfer.setData('text/plain', ids.join(','));

    (vakje?.checked && aan.length > 1 ? aan.map((b) => b.closest('[data-drag-item]')) : [item])
        .forEach((el) => el?.setAttribute('data-dragging', ''));

    const woorden = (item.getAttribute('data-drag-label') || scope?.getAttribute('data-drag-label') || '').split('|');
    if (ids.length > 1 && e.dataTransfer.setDragImage) {
        const spook = document.createElement('div');
        spook.className = 'drag-ghost';
        spook.textContent = woorden.length === 2 ? `${ids.length} ${woorden[1]}` : String(ids.length);
        document.body.appendChild(spook);
        e.dataTransfer.setDragImage(spook, -10, -10);
        setTimeout(() => spook.remove(), 0);
    }
});

document.addEventListener('dragend', () => {
    document.querySelectorAll('[data-dragging]').forEach((el) => el.removeAttribute('data-dragging'));
    document.querySelectorAll('[data-drop-target][data-drop]').forEach((el) => el.removeAttribute('data-drop'));
    hansuiSleepNaar.ids = null;
    hansuiSleepNaar.bron = null;
});

/*
| Het doel onder de muis oplichten, en alleen dat. Via dragover en niet via
| dragleave: die vuurt ook bij elk kind dat je kruist, en niet elke browser
| zegt dan waarheen (relatedTarget is vaak leeg).
*/
document.addEventListener('dragover', (e) => {
    if (!hansuiSleepNaar.ids) return;

    const doel = e.target.closest?.('[data-drop-target]');

    document.querySelectorAll('[data-drop-target][data-drop="over"]').forEach((el) => {
        if (el !== doel) el.removeAttribute('data-drop');
    });

    if (!doel) return;

    e.preventDefault();
    e.dataTransfer.dropEffect = 'move';
    doel.setAttribute('data-drop', 'over');
});

document.addEventListener('dragleave', (e) => {
    // Het venster uit: niets staat nog onder de muis.
    if (hansuiSleepNaar.ids && !e.relatedTarget && (e.clientX <= 0 || e.clientY <= 0
        || e.clientX >= window.innerWidth || e.clientY >= window.innerHeight)) {
        document.querySelectorAll('[data-drop-target][data-drop="over"]').forEach((el) => el.removeAttribute('data-drop'));
    }
});

document.addEventListener('drop', async (e) => {
    const doel = e.target.closest?.('[data-drop-target]');
    if (!doel || !hansuiSleepNaar.ids) return;

    e.preventDefault();
    const ids = hansuiSleepNaar.ids;
    doel.setAttribute('data-drop', 'busy');

    const form = new FormData();
    const naam = doel.getAttribute('data-drop-name') || hansuiSleepNaar.naam;
    ids.forEach((id) => form.append(naam, id));

    try {
        Object.entries(JSON.parse(doel.getAttribute('data-drop-fields') || '{}'))
            .forEach(([k, v]) => form.append(k, v ?? ''));
    } catch (_) { /* geen extra velden */ }

    const token = doel.getAttribute('data-drop-token') || document.querySelector('meta[name="csrf-token"]')?.content || '';
    form.append('_token', token);

    try {
        const antwoord = await fetch(doel.getAttribute('data-drop-target'), {
            method: 'POST',
            body: form,
            headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': token },
            credentials: 'same-origin',
        });
        let json = {};
        try { json = await antwoord.json(); } catch (_) { /* geen json */ }

        if (!antwoord.ok) throw new Error(json.message || String(antwoord.status));

        const verder = doel.dispatchEvent(new CustomEvent('drop:done', { bubbles: true, cancelable: true, detail: { ids, response: json } }));
        if (verder) window.location.reload();
    } catch (fout) {
        doel.removeAttribute('data-drop');
        hansuiToast(fout.message, 'danger');
    }
});

/*
|------------------------------------------------------------------------------
| Een video die speelt als je erover gaat: data-hover-play
|------------------------------------------------------------------------------
|
| Een tegel met een stilstaand posterbeeld zegt weinig over wat er in een video
| van dertig seconden gebeurt. Op een <video> met `data-hover-play` speelt ze
| gedempt zolang de muis boven het element staat dat de waarde aanwijst (een
| voorouder, zoals `.media-tile`), of boven de video zelf als de waarde leeg
| is. Bij het weggaan staat ze weer op het begin. Wie om minder beweging vroeg,
| krijgt niets.
*/
function hansuiHoverVideo(e, spelen) {
    if (e.pointerType === 'touch' || window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

    e.target.querySelectorAll?.('video[data-hover-play]').forEach((video) => {
        const gebied = video.getAttribute('data-hover-play');
        if ((gebied ? video.closest(gebied) : video) !== e.target) return;

        if (spelen) {
            video.muted = true;
            video.play?.().catch(() => { /* nog niet geladen of geweigerd: dan de poster */ });
        } else {
            video.pause?.();
            try { video.currentTime = 0; } catch (_) { /* nog geen metadata */ }
        }
    });
}

document.addEventListener('pointerenter', (e) => hansuiHoverVideo(e, true), true);
document.addEventListener('pointerleave', (e) => hansuiHoverVideo(e, false), true);

/*
|------------------------------------------------------------------------------
| Het voorbeeldvenster: data-lightbox
|------------------------------------------------------------------------------
|
| Een link met `data-lightbox="groep"` opent bij een klik niet de pagina maar
| een groot voorbeeld in de <dialog data-lightbox-view> van <x-lightbox>, met
| de andere links van dezelfde groep als vorige en volgende. Zonder script, of
| met `Ctrl` + klik, blijft het een gewone link naar de pagina.
|
| Wat er getoond wordt, staat op de link: `data-lightbox-src` (anders href),
| `data-lightbox-type` (image, video, audio of iets anders, dat een icoon en
| een link krijgt), `data-lightbox-poster`, `data-lightbox-title` en
| `data-lightbox-alt`. `data-lightbox-aside="/url"` haalt het zijpaneel op als
| HTML, eenmaal per url; de waarden uit `data-context-item` worden ook in het
| venster ingevuld, zoals in een contextmenu.
|
| Pijltjes links en rechts, vegen op een aanraakscherm, Escape sluit. Na het
| sluiten staat de focus op de link van wat je als laatste bekeek, want dat is
| waar je in het raster gebleven bent.
*/
const hansuiLicht = { venster: null, items: [], index: 0, cache: new Map() };

function hansuiLichtVenster(groep) {
    return document.querySelector(`dialog[data-lightbox-view="${CSS.escape(groep)}"]`)
        || document.querySelector('dialog[data-lightbox-view]');
}

function hansuiLichtOpen(opener) {
    const groep = opener.getAttribute('data-lightbox') || '';
    const venster = hansuiLichtVenster(groep);
    if (!venster) return false;

    hansuiLicht.venster = venster;
    hansuiLicht.items = [...document.querySelectorAll('[data-lightbox]')]
        .filter((el) => (el.getAttribute('data-lightbox') || '') === groep && el.getClientRects().length > 0);
    hansuiLicht.index = Math.max(0, hansuiLicht.items.indexOf(opener));

    if (!venster.open) venster.showModal();
    hansuiLichtToon();

    return true;
}

function hansuiLichtToon() {
    const { venster, items, index } = hansuiLicht;
    const el = items[index];
    if (!venster || !el) return;

    const podium = venster.querySelector('[data-lightbox-stage]');
    const src = el.getAttribute('data-lightbox-src') || el.getAttribute('href') || '';
    const soort = el.getAttribute('data-lightbox-type') || 'image';
    const titel = el.getAttribute('data-lightbox-title') || el.getAttribute('aria-label') || el.textContent.trim();
    let media;

    podium.querySelectorAll('video, audio').forEach((m) => m.pause());
    podium.replaceChildren();

    if (soort === 'image') {
        media = document.createElement('img');
        media.alt = el.getAttribute('data-lightbox-alt') || '';
        media.decoding = 'async';
        media.draggable = false;
    } else if (soort === 'frame' && !window.matchMedia('(pointer: coarse)').matches) {
        // Een pdf of een pagina: de browser toont ze zelf, in een kader. Niet
        // op een telefoon: daar blijft zo'n kader vaak grijs, en een link die
        // de pdf in de eigen lezer van het toestel opent, werkt wel.
        media = document.createElement('iframe');
        media.title = titel;
        media.setAttribute('loading', 'lazy');
    } else if (soort === 'video' || soort === 'audio') {
        media = document.createElement(soort);
        media.controls = true;
        media.autoplay = !window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        media.playsInline = true;
        media.preload = 'metadata';
        if (el.hasAttribute('data-lightbox-poster') && soort === 'video') media.poster = el.getAttribute('data-lightbox-poster');
    }

    if (media) {
        podium.setAttribute('aria-busy', 'true');
        media.addEventListener(soort === 'image' || soort === 'frame' ? 'load' : 'loadedmetadata', () => podium.removeAttribute('aria-busy'), { once: true });
        media.addEventListener('error', () => podium.removeAttribute('aria-busy'), { once: true });
        media.src = src;
        podium.append(media);
    } else {
        const sjabloon = venster.querySelector('template[data-lightbox-fallback]');
        const blok = sjabloon ? sjabloon.content.cloneNode(true) : document.createElement('div');
        podium.append(blok);
        podium.removeAttribute('aria-busy');
        podium.querySelectorAll('a[data-lightbox-open]').forEach((a) => { a.href = src; });
    }

    venster.querySelectorAll('[data-lightbox-caption]').forEach((t) => { t.textContent = titel; });
    venster.querySelectorAll('[data-lightbox-count]').forEach((t) => {
        t.textContent = items.length > 1 ? `${index + 1} / ${items.length}` : '';
    });
    const hadFocus = document.activeElement;
    venster.querySelectorAll('[data-lightbox-prev]').forEach((k) => { k.disabled = index === 0; });
    venster.querySelectorAll('[data-lightbox-next]').forEach((k) => { k.disabled = index === items.length - 1; });

    // Een knop die uitgeschakeld wordt terwijl hij de focus heeft (vorige op
    // het eerste beeld), laat de focus op <body> vallen, buiten het venster.
    if (hadFocus?.disabled && venster.contains(hadFocus)) {
        venster.querySelector('[data-lightbox-prev]:not(:disabled), [data-lightbox-next]:not(:disabled), [data-modal-close]')?.focus();
    }

    hansuiVul(venster, hansuiWaarden(el));
    hansuiLichtPaneel(el);

    // De buren alvast ophalen: vooruit bladeren hoort niet te wachten.
    [items[index - 1], items[index + 1]].forEach((buur) => {
        if (buur && (buur.getAttribute('data-lightbox-type') || 'image') === 'image') {
            new Image().src = buur.getAttribute('data-lightbox-src') || buur.getAttribute('href');
        }
    });

    venster.dispatchEvent(new CustomEvent('lightbox:show', { bubbles: true, detail: { index, total: items.length, opener: el } }));
}

async function hansuiLichtPaneel(el) {
    const paneel = hansuiLicht.venster.querySelector('[data-lightbox-aside]');
    const url = el.getAttribute('data-lightbox-aside');
    if (!paneel || !url) return;

    paneel.setAttribute('aria-busy', 'true');

    try {
        if (!hansuiLicht.cache.has(url)) {
            const antwoord = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest', Accept: 'text/html' }, credentials: 'same-origin' });
            if (!antwoord.ok) throw new Error(String(antwoord.status));
            hansuiLicht.cache.set(url, await antwoord.text());
        }

        // Intussen verder gebladerd? Dan is dit paneel van een ander beeld.
        if (hansuiLicht.items[hansuiLicht.index] !== el) return;

        paneel.innerHTML = hansuiLicht.cache.get(url);
        paneel.scrollTop = 0;
        paneel.dispatchEvent(new CustomEvent('lightbox:aside', { bubbles: true, detail: { opener: el } }));
    } catch (_) {
        paneel.textContent = paneel.getAttribute('data-lightbox-aside') || '';
    } finally {
        if (hansuiLicht.items[hansuiLicht.index] === el) paneel.removeAttribute('aria-busy');
    }
}

function hansuiLichtGa(stap) {
    const n = hansuiLicht.index + stap;
    if (n < 0 || n >= hansuiLicht.items.length) return;

    hansuiLicht.index = n;
    hansuiLichtToon();
}

/*
| Een paneel dat na een wijziging opnieuw moet: vergeet wat er onthouden is,
| zodat de volgende keer de nieuwe versie komt.
*/
function hansuiLichtVergeet(url = null) {
    url ? hansuiLicht.cache.delete(url) : hansuiLicht.cache.clear();
}

document.addEventListener('click', (e) => {
    const opener = e.target.closest?.('[data-lightbox]');

    if (opener && !e.defaultPrevented && e.button === 0 && !e.ctrlKey && !e.metaKey && !e.shiftKey && !e.altKey) {
        if (hansuiLichtOpen(opener)) e.preventDefault();
        return;
    }

    if (e.target.closest?.('[data-lightbox-prev]')) hansuiLichtGa(-1);
    if (e.target.closest?.('[data-lightbox-next]')) hansuiLichtGa(1);
});

document.addEventListener('keydown', (e) => {
    const venster = hansuiLicht.venster;
    if (!venster?.open || !(venster.contains(e.target) || e.target === document.body)
        || hansuiIsTypveld(e.target) || e.target.matches?.('video, audio')) return;

    if (e.key === 'ArrowLeft') { e.preventDefault(); hansuiLichtGa(-1); }
    if (e.key === 'ArrowRight') { e.preventDefault(); hansuiLichtGa(1); }
});

let hansuiLichtVeeg = null;

document.addEventListener('pointerdown', (e) => {
    if (e.target.closest?.('[data-lightbox-stage]') && e.isPrimary) {
        hansuiLichtVeeg = { x: e.clientX, y: e.clientY, t: performance.now() };
    }
});

document.addEventListener('pointerup', (e) => {
    const v = hansuiLichtVeeg;
    hansuiLichtVeeg = null;
    if (!v || !hansuiLicht.venster?.open) return;

    const dx = e.clientX - v.x;
    const dy = e.clientY - v.y;
    if (Math.abs(dx) > 50 && Math.abs(dx) > Math.abs(dy) * 1.5 && performance.now() - v.t < 800) {
        hansuiLichtGa(dx < 0 ? 1 : -1);
    }
});

document.addEventListener('close', (e) => {
    /*
    | Een venster dat uit een contextmenu openging, geeft de focus terug aan
    | het menu-item dat het opende -- en dat is intussen verborgen. Dan liever
    | naar het element waar het menu over ging.
    */
    setTimeout(() => {
        const actief = document.activeElement;
        if ((!actief || actief === document.body || actief.closest('[hidden]')) && hansuiMenu.terug?.isConnected) {
            hansuiMenu.terug.focus({ preventScroll: true });
        }
    }, 0);

    if (!e.target.matches?.('dialog[data-lightbox-view]')) return;

    e.target.querySelectorAll('[data-lightbox-stage] video, [data-lightbox-stage] audio').forEach((m) => m.pause());
    e.target.querySelector('[data-lightbox-stage]')?.replaceChildren();

    const laatste = hansuiLicht.items[hansuiLicht.index];
    if (laatste?.isConnected) {
        setTimeout(() => {
            laatste.focus({ preventScroll: true });
            laatste.scrollIntoView({ block: 'nearest' });
        }, 0);
    }
}, true);

/*
|------------------------------------------------------------------------------
| Bewaren zonder de pagina te verlaten: data-async
|------------------------------------------------------------------------------
|
| Een formulier met `data-async` gaat met fetch naar zijn action, met
| Accept: application/json, en de pagina blijft staan. Voor een veld in een
| voorbeeldvenster of een zijpaneel: wie een alt-tekst bewaart, hoort daarna
| gewoon verder te kunnen bladeren.
|
| De waarde van `data-async` is wat er bij succes gemeld wordt, in het element
| met `data-async-status` in het formulier (of anders onderaan het scherm). Een
| validatiefout (422) toont de eerste melding daar. Na afloop `async:done` op
| het formulier, met het antwoord van de server in `detail`.
|
| `data-confirm` werkt er gewoon op: die luistert eerst.
*/
document.addEventListener('submit', async (e) => {
    const form = e.target.closest?.('form[data-async]');
    if (!form || e.defaultPrevented) return;

    e.preventDefault();

    const status = form.querySelector('[data-async-status]');
    const knoppen = [...form.querySelectorAll('button[type="submit"], button:not([type])')];
    const zeg = (tekst, fout = false) => {
        if (status) {
            status.textContent = tekst;
            status.toggleAttribute('data-danger', fout);
        } else {
            hansuiToast(tekst, fout ? 'danger' : null);
        }
    };

    form.setAttribute('aria-busy', 'true');
    knoppen.forEach((k) => { k.disabled = true; });
    form.querySelectorAll('[aria-invalid]').forEach((v) => v.removeAttribute('aria-invalid'));

    try {
        const antwoord = await fetch(form.action, {
            method: (form.getAttribute('method') || 'POST').toUpperCase(),
            body: new FormData(form, e.submitter),
            headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin',
        });

        let json = {};
        try { json = await antwoord.json(); } catch (_) { /* geen json */ }

        if (antwoord.status === 422 && json.errors) {
            const [veld, meldingen] = Object.entries(json.errors)[0];
            form.querySelector(`[name="${CSS.escape(veld)}"]`)?.setAttribute('aria-invalid', 'true');
            zeg(meldingen[0], true);
        } else if (!antwoord.ok) {
            zeg(json.message || String(antwoord.status), true);
        } else {
            zeg(json.message || form.getAttribute('data-async') || '');
            form.dispatchEvent(new CustomEvent('async:done', { bubbles: true, detail: json }));
        }
    } catch (_) {
        zeg(form.getAttribute('data-async-offline') || 'Offline', true);
    } finally {
        form.removeAttribute('aria-busy');
        knoppen.forEach((k) => { k.disabled = false; });
    }
});

/*
|------------------------------------------------------------------------------
| Sneltoetsen: data-shortcut
|------------------------------------------------------------------------------
|
| `data-shortcut="j"` op een link, knop of veld: die toets doet wat een klik
| doet, of zet bij een tekstveld de cursor erin. Voor een lijst die je afwerkt
| -- J en K bladeren, E handelt af, / zoekt -- en `?` op de knop die het
| overzicht opent (<x-shortcuts>). Meer toetsen voor hetzelfde element scheid
| je met een spatie.
|
| NOOIT TERWIJL JE TYPT, en nooit met Ctrl, Cmd of Alt erbij: die zijn van de
| browser en van het besturingssysteem. Staat er een venster open, dan tellen
| alleen de toetsen in dat venster.
|
| WAT ER NIET IS, TELT NIET: een element in iets met `hidden` of `inert`, een
| uitgeschakelde knop, iets in een dicht venster. Wat alleen op een smal
| scherm uit beeld is (een klasse als `hidden xl:flex`), telt wel: de toetsen
| horen niet te veranderen met de breedte van het venster.
|
| Staan er twee met dezelfde toets, dan wint wat zichtbaar is, en daarna het
| LAATSTE in de pagina: wat een scherm zelf aanbiedt, staat na de balk van de
| applicatie, en hoort het van die balk te winnen.
*/
function hansuiToetsBeschikbaar(el) {
    if (el.disabled || el.getAttribute('aria-disabled') === 'true' || el.closest('[hidden], [inert]')) {
        return false;
    }

    const venster = el.closest('dialog');

    return !venster || venster.open;
}

document.addEventListener('keydown', (e) => {
    if (e.defaultPrevented || e.repeat || e.isComposing || e.ctrlKey || e.metaKey || e.altKey
        || hansuiMenu.open || hansuiIsTypveld(e.target)) {
        return;
    }

    const venster = [...document.querySelectorAll('dialog[open]')].pop();
    const toets = e.key.toLowerCase();

    const kandidaten = [...(venster || document).querySelectorAll('[data-shortcut]')].filter((el) =>
        el.getAttribute('data-shortcut').split(/\s+/).some((k) => k !== '' && k.toLowerCase() === toets)
        && hansuiToetsBeschikbaar(el));

    if (kandidaten.length === 0) {
        return;
    }

    const zichtbaar = kandidaten.filter((el) => el.getClientRects().length > 0);
    const doel = (zichtbaar.length ? zichtbaar : kandidaten).pop();

    e.preventDefault();

    if (hansuiIsTypveld(doel)) {
        doel.focus();
    } else {
        doel.click();
    }
});

/*
| `data-kbd-mod` op een <kbd> met Ctrl erin: op een Mac wordt dat ⌘. Dit
| package luistert overal naar Ctrl EN Cmd, dus de hint hoort te zeggen wat
| er op dit toestel onder de duim ligt.
*/
function hansuiModToets() {
    const apple = /mac|iphone|ipad|ipod/i.test(navigator.userAgentData?.platform || navigator.platform || navigator.userAgent);
    if (!apple) {
        return;
    }

    document.querySelectorAll('[data-kbd-mod]').forEach((kbd) => {
        kbd.textContent = kbd.textContent.replace(/\bCtrl\b/g, '⌘');
    });
}

/*
|------------------------------------------------------------------------------
| Een tekstvak dat meegroeit: data-autogrow
|------------------------------------------------------------------------------
|
| Het vak wordt zo hoog als wat erin staat, tot zijn max-height; daarna scrolt
| het zelf. Een antwoord van drie regels hoort niet in een vak van een regel
| te staan, en een leeg vak niet een half scherm in te nemen.
*/
function hansuiGroei(veld) {
    // Een verborgen vak meet nul; dat zou het op nul zetten tot iemand typt.
    if (veld.getClientRects().length === 0) {
        return;
    }

    veld.style.height = 'auto';
    veld.style.height = `${veld.scrollHeight + veld.offsetHeight - veld.clientHeight}px`;
}

/*
|------------------------------------------------------------------------------
| Onthouden tot het vertrokken is: data-draft
|------------------------------------------------------------------------------
|
| `data-draft="gesprek.12"` op een tekstvak bewaart wat je typt in dit toestel,
| onder die sleutel. Wie tussendoor een ander gesprek opent of de pagina
| herlaadt, vindt zijn half antwoord terug. Weg zodra het formulier vertrekt.
|
| Staat er al tekst in het vak (oude invoer na een validatiefout), dan wint
| die. Een concept van meer dan twee weken oud komt niet meer terug: dan is
| het gesprek intussen wel door iemand anders beantwoord.
*/
const HANSUI_CONCEPT_DAGEN = 14;

function hansuiConceptSleutel(veld) {
    return `hansui.concept.${veld.getAttribute('data-draft')}`;
}

function hansuiBewaarConcept(veld) {
    const sleutel = hansuiConceptSleutel(veld);

    try {
        if (veld.value.trim() === '') {
            localStorage.removeItem(sleutel);
        } else {
            localStorage.setItem(sleutel, JSON.stringify({ tekst: veld.value, tijd: Date.now() }));
        }
    } catch (_) {
        // Een privévenster of een volle opslag: dan onthouden we het niet, meer niet.
    }
}

function hansuiHerstelConcept(veld) {
    if (veld.value !== '') {
        return;
    }

    const sleutel = hansuiConceptSleutel(veld);

    try {
        const concept = JSON.parse(localStorage.getItem(sleutel) || 'null');
        if (!concept?.tekst) {
            return;
        }

        if (Date.now() - (concept.tijd || 0) > HANSUI_CONCEPT_DAGEN * 864e5) {
            localStorage.removeItem(sleutel);
            return;
        }

        veld.value = concept.tekst;
    } catch (_) {
        // idem
    }
}

function hansuiVergeetConcepten(form) {
    form.querySelectorAll('[data-draft]').forEach((veld) => {
        try {
            localStorage.removeItem(hansuiConceptSleutel(veld));
        } catch (_) {
            // idem
        }
    });
}

/*
|------------------------------------------------------------------------------
| Tekens tellen: data-count
|------------------------------------------------------------------------------
|
| `data-count="#bericht"` op een element toont hoeveel tekens er in dat veld
| staan, met `data-count-max="280"` erbij als "12 / 280", en met
| `data-danger` zodra het er te veel zijn. Leeg bij een leeg veld: een 0
| zegt niets.
|
| Tekens zoals een mens ze telt, niet zoals JavaScript: een emoji is er een.
| Een netwerk dat anders telt (een link als 23), telt op de server na.
*/
function hansuiTel(teller) {
    const veld = document.querySelector(teller.getAttribute('data-count'));
    if (!veld) {
        return;
    }

    const max = Number(teller.getAttribute('data-count-max')) || 0;
    const gebruikt = [...veld.value].length;

    teller.textContent = gebruikt === 0 ? '' : (max ? `${gebruikt} / ${max}` : String(gebruikt));
    teller.toggleAttribute('data-danger', max > 0 && gebruikt > max);
}

document.addEventListener('input', (e) => {
    const veld = e.target;

    if (veld.matches?.('textarea[data-autogrow]')) {
        hansuiGroei(veld);
    }

    if (veld.matches?.('[data-draft]')) {
        hansuiBewaarConcept(veld);
    }

    document.querySelectorAll('[data-count]').forEach((teller) => {
        try {
            if (veld.matches?.(teller.getAttribute('data-count'))) {
                hansuiTel(teller);
            }
        } catch (_) {
            // Een selector die niet klopt, telt niets.
        }
    });
});

/*
|------------------------------------------------------------------------------
| Het antwoordvak: data-composer-form
|------------------------------------------------------------------------------
|
| Een <form data-composer-form> met een tekstvak erin (<x-composer> tekent het):
|
|   - Ctrl/Cmd + Enter in het vak doet wat een klik op de zichtbare
|     verstuurknop doet; Escape laat het vak los, zodat de sneltoetsen van
|     het scherm weer werken.
|   - Het formulier vertrekt een keer, ook bij een dubbele klik of twee keer
|     Ctrl + Enter.
|   - Een radioknop met `data-composer-placeholder` zet, als hij aangaat, die
|     tekst als voorzettekst in het vak en de cursor erin: "Schrijf een
|     antwoord" of "Schrijf een notitie voor je team".
|   - Een knop met `data-insert` zet zijn tekst waar de cursor staat, met
|     `{sleutel}` ingevuld uit `data-composer-values` (JSON): een
|     standaardantwoord met de voornaam van de klant erin.
*/
function hansuiComposerVeld(composer) {
    return composer?.querySelector('textarea') || null;
}

function hansuiComposerStand(composer) {
    const keuze = composer.querySelector('[data-composer-placeholder]:checked');
    const veld = hansuiComposerVeld(composer);

    if (keuze && veld) {
        veld.placeholder = keuze.getAttribute('data-composer-placeholder');
    }
}

/*
| Invoegen waar de cursor staat. Heeft het vak nog nooit de focus gehad, dan
| achteraan: een cursor die de browser op nul zette, is geen plaats die
| iemand koos. Achter tekst die niet op een nieuwe regel eindigt, komt er een.
*/
function hansuiVoegIn(veld, tekst) {
    const geraakt = veld.hansuiGeraakt === true;
    const begin = geraakt ? (veld.selectionStart ?? veld.value.length) : veld.value.length;
    const eind = geraakt ? (veld.selectionEnd ?? veld.value.length) : veld.value.length;
    const voor = veld.value.slice(0, begin);
    const lijm = voor !== '' && !voor.endsWith('\n') && begin === veld.value.length ? '\n' : '';

    veld.value = voor + lijm + tekst + veld.value.slice(eind);
    veld.focus();
    veld.selectionStart = veld.selectionEnd = begin + lijm.length + tekst.length;
    veld.dispatchEvent(new Event('input', { bubbles: true }));
}

document.addEventListener('focusin', (e) => {
    if (e.target.matches?.('[data-composer-form] textarea')) {
        e.target.hansuiGeraakt = true;
    }
});

document.addEventListener('keydown', (e) => {
    const veld = e.target.closest?.('[data-composer-form] textarea');
    if (!veld) {
        return;
    }

    if (e.key === 'Enter' && (e.ctrlKey || e.metaKey)) {
        e.preventDefault();

        // De knop die je ziet: in de stand notitie is dat een andere dan in de stand antwoorden.
        const form = veld.form;
        const knoppen = [...form.querySelectorAll('button[type="submit"], button:not([type]), input[type="submit"]')];
        const knop = knoppen.find((k) => k.getClientRects().length > 0 && !k.closest('[hidden]'));

        if (veld.value.trim() === '') {
            return;
        }

        if (knop && !knop.disabled) {
            form.requestSubmit(knop);
        } else if (knoppen.length === 0) {
            form.requestSubmit();
        }
    } else if (e.key === 'Escape' && !e.defaultPrevented) {
        veld.blur();
    }
});

document.addEventListener('click', (e) => {
    // Ook bij een klik op wat al aanstond: R in de stand antwoorden zet de cursor in het vak.
    const stand = e.target.closest?.('[data-composer-placeholder]');
    if (stand && stand.matches('input')) {
        const composer = stand.closest('[data-composer-form]');
        const veld = hansuiComposerVeld(composer);
        if (composer && veld) {
            hansuiComposerStand(composer);
            veld.focus();

            // Een klik op het label zet de focus daarna nog op de radioknop; dan terug.
            setTimeout(() => {
                if (document.activeElement === stand) {
                    veld.focus();
                }
            }, 0);
        }
        return;
    }

    const knop = e.target.closest?.('[data-insert]');
    if (!knop) {
        return;
    }

    const composer = knop.closest('[data-composer-form]');
    const veld = hansuiComposerVeld(composer);
    if (!veld) {
        return;
    }

    let waarden = {};
    try {
        waarden = JSON.parse(composer.getAttribute('data-composer-values') || '{}');
    } catch (_) {
        // Geen of kapotte waarden: dan blijft {sleutel} staan, en dat ziet wie verstuurt.
    }

    const tekst = knop.getAttribute('data-insert').replace(/\{(\w+)\}/g, (heel, sleutel) =>
        Object.hasOwn(waarden, sleutel) ? String(waarden[sleutel]) : heel);

    hansuiVoegIn(veld, tekst);
    hansuiSluitDropdowns();
});

document.addEventListener('change', (e) => {
    if (e.target.matches?.('[data-composer-placeholder]')) {
        const composer = e.target.closest('[data-composer-form]');
        if (composer) {
            hansuiComposerStand(composer);
        }
    }
});

document.addEventListener('submit', (e) => {
    const form = e.target;
    if (e.defaultPrevented || !form.matches?.('form')) {
        return;
    }

    if (form.matches('[data-composer-form]')) {
        if (form.getAttribute('aria-busy') === 'true') {
            e.preventDefault();
            return;
        }

        form.setAttribute('aria-busy', 'true');
    }

    // Een formulier met data-async blijft staan; dat vergeet zijn concept pas als het aankwam.
    if (!form.matches('[data-async]')) {
        hansuiVergeetConcepten(form);
    }
});

document.addEventListener('async:done', (e) => hansuiVergeetConcepten(e.target));

// Terug naar een pagina uit het geheugen van de browser: dan is het formulier niet meer bezig.
window.addEventListener('pageshow', (e) => {
    if (e.persisted) {
        document.querySelectorAll('form[data-composer-form][aria-busy]').forEach((form) => form.removeAttribute('aria-busy'));
    }
});

/*
|------------------------------------------------------------------------------
| In beeld bij het laden: data-scroll-here
|------------------------------------------------------------------------------
|
| Een lijst of een gesprek dat zelf scrolt, opent waar het om gaat: een
| gesprek bij het laatste bericht (`data-scroll-here`, bovenaan in beeld), een
| lijst bij het item dat open staat, ook als het de dertigste rij is
| (`data-scroll-here="center"`, alleen als het niet al in beeld staat).
|
| Alleen binnen een omhulling die zelf scrolt. De pagina zelf blijft staan:
| een scherm dat bij het laden verspringt, verliest wie net begon te lezen.
*/
function hansuiScrolhouder(el) {
    for (let ouder = el.parentElement; ouder && ouder !== document.body; ouder = ouder.parentElement) {
        const stijl = getComputedStyle(ouder).overflowY;
        if ((stijl === 'auto' || stijl === 'scroll') && ouder.scrollHeight > ouder.clientHeight) {
            return ouder;
        }
    }

    return null;
}

function hansuiInBeeld() {
    document.querySelectorAll('[data-scroll-here]').forEach((el) => {
        const houder = hansuiScrolhouder(el);
        if (!houder) {
            return;
        }

        const top = el.getBoundingClientRect().top - houder.getBoundingClientRect().top + houder.scrollTop;

        if (el.getAttribute('data-scroll-here') === 'center') {
            const zichtbaar = top >= houder.scrollTop && top + el.offsetHeight <= houder.scrollTop + houder.clientHeight;
            if (!zichtbaar) {
                houder.scrollTop = Math.max(0, top - (houder.clientHeight - el.offsetHeight) / 2);
            }
        } else {
            houder.scrollTop = Math.max(0, top - 16);
        }
    });
}

/*
| Bij het laden: concepten terug, vakken op maat, tellers en voorzetteksten
| juist, Ctrl als ⌘ op een Mac, en de lijst en het gesprek waar het om gaat.
| In die volgorde: een teruggezet concept verandert de hoogte van het vak.
*/
function hansuiWerkblad() {
    document.querySelectorAll('[data-draft]').forEach(hansuiHerstelConcept);
    document.querySelectorAll('textarea[data-autogrow]').forEach(hansuiGroei);
    document.querySelectorAll('[data-count]').forEach((teller) => {
        try {
            hansuiTel(teller);
        } catch (_) {
            // idem
        }
    });
    document.querySelectorAll('[data-composer-form]').forEach(hansuiComposerStand);
    hansuiModToets();
    hansuiInBeeld();
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', hansuiWerkblad);
} else {
    hansuiWerkblad();
}

window.HansUI = Object.assign(window.HansUI || {}, {
    toast: hansuiToast,
    lightboxForget: hansuiLichtVergeet,
});
