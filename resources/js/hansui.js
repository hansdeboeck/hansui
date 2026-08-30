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
|   data-copy                    klik om te kopieren
|   data-confirm                 bevestiging voor een gevaarlijke actie
|   data-autosubmit              een filter dat zijn formulier meteen indient
|   data-bulk*                   rijen aanvinken en er samen iets mee doen
|   data-sidebar*                de zijbalk op smalle schermen
|   data-theme-toggle            licht of donker
|   data-density-toggle          compacte of ruime regels
|   data-palette-open            een venster-event voor een zoekpalet
|
| De applicatie importeert dit in haar eigen app.js en voegt daar haar eigen
| helpers aan toe.
*/
// Flash-meldingen sluiten.
document.addEventListener('click', (e) => {
    const dismiss = e.target.closest('[data-dismiss]');
    if (dismiss) {
        dismiss.closest('[data-flash]')?.remove();
    }
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
        try { localStorage.setItem('growtail.theme', next); } catch (_) { /* privémodus */ }
        paintThemeIcon();
    }

    if (e.target.closest('[data-density-toggle]')) {
        const compact = document.documentElement.dataset.density === 'compact';
        if (compact) {
            delete document.documentElement.dataset.density;
        } else {
            document.documentElement.dataset.density = 'compact';
        }
        try { localStorage.setItem('growtail.density', compact ? 'ruim' : 'compact'); } catch (_) { /* privémodus */ }
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
