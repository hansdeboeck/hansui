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
        await navigator.clipboard.writeText(field.value ?? field.textContent ?? '');
        field.setAttribute('title', 'Gekopieerd');
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
    if (! bar) {
        return;
    }

    const naam = scope.getAttribute('data-bulk') || 'ids[]';

    bar.querySelectorAll('form').forEach((form) => {
        form.querySelectorAll('[data-bulk-field]').forEach((veld) => veld.remove());

        checked.forEach((box) => {
            const veld = document.createElement('input');
            veld.type = 'hidden';
            veld.name = naam;
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
