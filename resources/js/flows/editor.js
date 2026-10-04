/*
| De flow-editor: een canvas met stappen en verbindingen, een paneel om een
| stap in te vullen, en een palet om er een toe te voegen.
|
| WAT DE EDITOR BEWAART, staat in een verborgen veld (data-flow-input), als
| json zoals src/Flows/Graph.php het leest. Het formulier van de applicatie stuurt
| dat veld mee; de editor zelf post niets.
|
| HET FORMULIER VAN EEN STAP komt van de applicatie: een <template
| data-flow-form="soort"> met gewone velden (name="title", required,
| data-flow-summary). De editor kloont het in het paneel, vult het met de
| config van de stap en leest het terug bij elke toets. Een veld krijgt een
| eigen naam en form="…" dat nergens bestaat: wat in het paneel staat, wordt
| nooit zelf verstuurd, ook niet als de editor in een formulier staat.
|
| ALLES KAN MET HET TOETSENBORD. Een stap is een knop (Enter opent ze), het
| paneel kiest per uitgang wat erna komt ("Daarna"), pijltjes verschuiven,
| Delete haalt weg, Ctrl+Z draait terug. Slepen is sneller, niet nodig.
|
| GEEN INLINE STIJL IN HTML. Wat beweegt, krijgt zijn plaats via
| element.style (de CSSOM), dat een strikte CSP toelaat.
*/

import * as G from './graph.js';

const FIELDS = 'input[name], select[name], textarea[name]';
const SVG = 'http://www.w3.org/2000/svg';
const MIN_SCALE = 0.35;
const MAX_SCALE = 1.6;
const HISTORY = 100;
const GRID = 24;

let counter = 0;

const json = (text, fallback) => {
    try {
        return text ? JSON.parse(text) : fallback;
    } catch {
        return fallback;
    }
};

const clamp = (value, min, max) => Math.min(max, Math.max(min, value));

/** ":naam" in een zin vervangen, de langste sleutel eerst (zoals Laravel). */
export function format(text, replace = {}) {
    return Object.keys(replace)
        .sort((a, b) => b.length - a.length)
        .reduce((result, key) => result.split(`:${key}`).join(String(replace[key])), String(text ?? ''));
}

/**
 * Een samenvatting uit een patroon: {veld} is wat het veld toont, en een deel
 * tussen [haken] valt weg als een veld erin leeg is ("{amount} {unit}[, {workdays}]").
 */
export function summarize(pattern, display) {
    const fill = (text) => text.replace(/\{([\w.-]+)\}/g, (all, key) => display(key));

    return fill(String(pattern).replace(/\[([^\[\]]*)\]/g, (all, part) => {
        const keys = [...part.matchAll(/\{([\w.-]+)\}/g)].map((match) => match[1]);

        return keys.every((key) => display(key) !== '') ? part : '';
    }));
}

/** Zonder hoofdletters en accenten, om te zoeken. */
const plain = (text) => String(text ?? '').normalize('NFD').replace(/[̀-ͯ]/g, '').toLowerCase();

function element(tag, className, children = []) {
    const node = document.createElement(tag);

    if (className) {
        node.className = className;
    }

    for (const child of [].concat(children)) {
        if (child !== null && child !== undefined && child !== false) {
            node.append(child);
        }
    }

    return node;
}

function svg(tag, attributes = {}) {
    const node = document.createElementNS(SVG, tag);

    for (const [name, value] of Object.entries(attributes)) {
        node.setAttribute(name, value);
    }

    return node;
}

/**
 * Of een veld te zien is: data-flow-when="veld", "veld:a,b" of "veld!:a,b",
 * op het element of een ouder ervan in het formulier.
 */
export function whenMatches(expression, config) {
    const match = String(expression).trim().match(/^([\w.-]+)(!?)(?::(.*))?$/);

    if (!match) {
        return true;
    }

    const [, key, not, list] = match;
    const value = config?.[key];
    let result;

    if (list === undefined) {
        result = Array.isArray(value) ? value.length > 0 : Boolean(value) && value !== '0';
    } else {
        const values = list.split(',').map((item) => item.trim());
        result = Array.isArray(value) ? value.some((item) => values.includes(String(item))) : values.includes(String(value ?? ''));
    }

    return not ? !result : result;
}

export class FlowEditor {
    constructor(root) {
        this.root = root;
        this.uid = ++counter;

        // Alleen bekijken: stappen openen en het canvas verschuiven, niets wijzigen.
        this.readonly = root.hasAttribute('data-flow-readonly');

        this.types = G.typeMap(json(root.dataset.flowTypes, []));
        this.strings = json(root.dataset.flowStrings, {});
        this.serverIssues = new Map(Object.entries(json(root.dataset.flowIssues, {})));
        this.notes = new Map(Object.entries(json(root.dataset.flowNotes, {})));
        this.badges = new Map(Object.entries(json(root.dataset.flowBadges, {})));

        this.input = root.querySelector('[data-flow-input]');
        this.stage = root.querySelector('[data-flow-stage]') ?? root;
        this.canvas = root.querySelector('[data-flow-canvas]');
        this.world = root.querySelector('[data-flow-world]');
        this.edgeLayer = root.querySelector('[data-flow-edges]');
        this.nodeLayer = root.querySelector('[data-flow-nodes]');
        this.overlay = root.querySelector('[data-flow-overlay]');
        this.panel = root.querySelector('[data-flow-panel]');
        this.palette = root.querySelector('[data-flow-palette]');
        this.search = root.querySelector('[data-flow-search]');
        this.paletteList = root.querySelector('[data-flow-palette-list]');
        this.live = root.querySelector('[data-flow-live]');
        this.zoomLabel = root.querySelector('[data-flow-zoom-label]');
        this.overview = root.querySelector('[data-flow-overview]');

        this.templates = new Map([...root.querySelectorAll('template[data-flow-form]')].map((template) => [template.dataset.flowForm, template]));
        this.icons = new Map([...root.querySelectorAll('template[data-flow-icon]')].map((template) => [template.dataset.flowIcon, template]));
        this.chrome = new Map([...root.querySelectorAll('template[data-flow-chrome]')].map((template) => [template.dataset.flowChrome, template]));

        this.graph = G.parse(this.input?.value ?? '');

        // Een flow uit code heeft geen plaatsen; een flow met een paar nieuwe stappen krijgt alleen die erbij.
        if (this.graph.nodes.some((node) => node.x === null || node.y === null)) {
            this.graph = G.arrange(this.graph, this.types, this.graph.nodes.every((node) => node.x === null));
            this.sync();
        }

        // Een id dat weg is, komt niet terug: een run die op die stap wachtte, mag geen andere stap vinden.
        this.highest = Math.max(0, ...this.graph.nodes.map((node) => Number(node.id.match(/^n(\d+)$/)?.[1] ?? 0)));

        this.past = [];
        this.future = [];
        this.burst = null;
        this.dirty = false;
        this.selection = null;
        this.panelNode = null;
        this.hoverEdge = null;
        this.gesture = null;
        this.pointers = new Map();
        this.pinch = null;
        this.view = { x: 0, y: 0, scale: 1 };
        this.formCache = new Map();
        this.summaryCache = new Map();

        this.bind();
        this.measureFill();
        this.render();
        this.fit();
        this.followHash();

        // In een tab die nog dicht is, heeft het canvas geen maat: passend maken zodra het er een krijgt.
        // Wordt het canvas groter of kleiner (het venster, de editor die tot onderaan loopt), dan opnieuw,
        // zolang niemand zelf schoof of zoomde: wat iemand in beeld zette, blijft staan.
        if (typeof ResizeObserver !== 'undefined') {
            let size = '';

            new ResizeObserver(() => {
                const now = `${this.canvas.clientWidth}x${this.canvas.clientHeight}`;

                if (this.canvas.clientWidth > 0 && (!this.fitted || (now !== size && !this.viewChanged))) {
                    this.fit();
                }

                size = now;
            }).observe(this.canvas);

            // Wat boven de editor staat, kan verdwijnen (een melding die je sluit): dan begint hij hoger.
            if (this.root.hasAttribute('data-flow-fill')) {
                new ResizeObserver(() => this.measureFill()).observe(document.body);
            }
        }

        root.toggleAttribute('data-flow-ready', true);
        root.dispatchEvent(new CustomEvent('flows:ready', { bubbles: true, detail: { editor: this } }));
    }

    // ---- Toestand -----------------------------------------------------------

    node(id) {
        return G.findNode(this.graph, id);
    }

    type(node) {
        return this.types.get(node?.type) ?? { key: node?.type, label: node?.type ?? '', icon: '', tone: 'neutral', outputs: [], start: false, group: '', hint: '' };
    }

    sync() {
        if (this.input) {
            this.input.value = G.serialize(this.graph);
        }
    }

    /** Wat er was, onthouden om terug te kunnen. */
    record(before = this.graph) {
        this.past.push(G.serialize(before));

        if (this.past.length > HISTORY) {
            this.past.shift();
        }

        this.future = [];
    }

    /** De graaf is veranderd: bewaren in het veld, tekenen, laten weten. */
    changed({ only = null } = {}) {
        this.dirty = true;
        this.sync();
        this.input?.dispatchEvent(new Event('change', { bubbles: true }));

        if (only) {
            this.renderNode(only);
            this.renderIssueButton();
        } else {
            this.render();
            this.refreshPanel();
        }

        this.renderHistoryButtons();
        this.root.dispatchEvent(new CustomEvent('flows:change', { bubbles: true, detail: { editor: this, graph: this.graph } }));
    }

    commit(graph, message = null) {
        if (this.readonly) {
            return;
        }

        this.burst = null;
        this.record();
        this.graph = graph;
        this.changed();

        if (message) {
            this.announce(message);
        }
    }

    undo() {
        if (this.readonly || !this.past.length) {
            return;
        }

        this.future.push(G.serialize(this.graph));
        this.graph = G.parse(this.past.pop());
        this.afterHistory(this.strings.undone);
    }

    redo() {
        if (this.readonly || !this.future.length) {
            return;
        }

        this.past.push(G.serialize(this.graph));
        this.graph = G.parse(this.future.pop());
        this.afterHistory(this.strings.redone);
    }

    afterHistory(message) {
        this.burst = null;
        this.summaryCache.clear();

        if (this.selection?.node && !this.node(this.selection.node)) {
            this.selection = null;
        }

        if (this.selection?.edge && !this.edge(this.selection.edge)) {
            this.selection = null;
        }

        this.changed();

        if (this.panelNode) {
            this.selection?.node ? this.openPanel(this.selection.node) : this.closePanel();
        }

        this.announce(message);
    }

    edge(key) {
        const [from, port] = String(key).split(':');

        return this.graph.edges.find((edge) => edge.from === from && edge.port === port) ?? null;
    }

    // ---- Wat nog niet af is ---------------------------------------------------

    /** De verplichte velden van een soort, uit haar formulier: [{key, label, when}]. */
    requiredFields(typeKey) {
        if (this.formCache.has(typeKey)) {
            return this.formCache.get(typeKey);
        }

        const template = this.templates.get(typeKey);
        const fields = [];

        if (template) {
            const form = element('div', '', template.content.cloneNode(true));

            for (const field of form.querySelectorAll(FIELDS)) {
                if (!field.required) {
                    continue;
                }

                const key = field.name.replace(/\[\]$/, '');
                const label = (field.id && form.querySelector(`label[for="${CSS.escape(field.id)}"]`)?.textContent)
                    || field.getAttribute('aria-label') || field.placeholder || key;
                const when = [];

                for (let parent = field; parent && parent !== form; parent = parent.parentElement) {
                    if (parent.dataset?.flowWhen) {
                        when.push(parent.dataset.flowWhen);
                    }
                }

                if (!fields.some((item) => item.key === key)) {
                    fields.push({ key, label: label.replace(/\s*\*\s*$/, '').trim(), when });
                }
            }
        }

        this.formCache.set(typeKey, fields);

        return fields;
    }

    missing(node) {
        const config = node.config ?? {};

        return this.requiredFields(node.type)
            .filter((field) => field.when.every((expression) => whenMatches(expression, config)))
            .filter((field) => {
                const value = config[field.key];

                return value === undefined || value === null || value === false || (typeof value === 'string' && value.trim() === '') || (Array.isArray(value) && !value.length);
            })
            .map((field) => format(this.strings.required, { veld: field.label }));
    }

    issues() {
        const found = G.issues(this.graph, this.types, { required: (node) => this.missing(node), strings: this.strings });

        for (const [id, messages] of this.serverIssues) {
            if (this.node(id)) {
                found.set(id, [...(found.get(id) ?? []), ...messages]);
            }
        }

        return found;
    }

    // ---- Het formulier van een stap ---------------------------------------------

    /** De config van een nieuwe stap: wat het formulier al ingevuld heeft, en de standaard van de soort. */
    defaults(typeKey) {
        const template = this.templates.get(typeKey);
        let config = {};

        if (template) {
            // Twee keer lezen: welke velden te zien zijn, hangt af van wat er al ingevuld is.
            const form = element('div', '', template.content.cloneNode(true));
            config = this.read(form, true);
            this.applyWhen(form, config);
            config = this.read(form, true);
        }

        return { ...config, ...structuredClone(this.types.get(typeKey)?.defaults ?? {}) };
    }

    /**
     * Lezen en tonen tot het klopt: wie het veld van een voorwaarde wijzigt,
     * maakt een andere waarde zichtbaar, en die telt dan.
     */
    settle(form) {
        let config = this.read(form);
        this.applyWhen(form, config);
        config = this.read(form);
        this.applyWhen(form, config);

        return config;
    }

    fields(form) {
        return [...form.querySelectorAll(FIELDS)];
    }

    /** Wat in een formulier staat, als config. */
    read(form, raw = false) {
        const config = {};
        const hidden = (field) => Boolean(field.closest('[hidden]'));
        const fields = this.fields(form);

        // Twee velden met dezelfde naam (een tekst en een keuzelijst voor dezelfde waarde): wat te zien is, wint.
        for (const field of [...fields.filter(hidden), ...fields.filter((field) => !hidden(field))]) {
            const name = raw ? field.name : (field.dataset.flowName ?? field.name);
            const key = raw ? name.replace(/\[\]$/, '') : field.dataset.flowField;
            const many = name.endsWith('[]');

            if (field.type === 'checkbox') {
                if (many) {
                    config[key] = [...(config[key] ?? []), ...(field.checked ? [field.value] : [])];
                } else {
                    config[key] = field.checked;
                }
            } else if (field.type === 'radio') {
                if (field.checked) {
                    config[key] = field.value;
                } else if (!(key in config)) {
                    config[key] = null;
                }
            } else if (field.tagName === 'SELECT' && field.multiple) {
                config[key] = [...field.selectedOptions].map((option) => option.value);
            } else if (field.type === 'number' || field.type === 'range') {
                config[key] = field.value === '' ? null : Number(field.value);
            } else if (field.type !== 'button' && field.type !== 'submit' && field.type !== 'file') {
                config[key] = field.value;
            }
        }

        return config;
    }

    /** Een formulier vullen met de config van een stap; wat er niet in staat, houdt de standaard van het formulier. */
    fill(form, config) {
        for (const field of this.fields(form)) {
            const key = field.dataset.flowField;

            if (!(key in config)) {
                continue;
            }

            const value = config[key];
            const many = (field.dataset.flowName ?? '').endsWith('[]');

            // Een waarde die de keuzelijst niet (meer) kent, blijft staan en zegt dat: anders zou de lijst
            // stil de eerste keuze tonen, en wie iets anders wijzigt, zou ze zonder het te weten bewaren.
            if (field.tagName === 'SELECT' && !field.multiple && value !== null && value !== '' && !field.closest('[hidden]')
                && ![...field.options].some((option) => option.value === String(value))) {
                const unknown = element('option', '', format(this.strings.unknownOption, { waarde: String(value) }));
                unknown.value = String(value);
                unknown.dataset.flowUnknown = '';
                field.append(unknown);
            }

            if (field.type === 'checkbox') {
                field.checked = many
                    ? Array.isArray(value) && value.map(String).includes(field.value)
                    : [true, 1, '1', 'on', 'true'].includes(value);
            } else if (field.type === 'radio') {
                field.checked = String(value ?? '') === field.value;
            } else if (field.tagName === 'SELECT' && field.multiple) {
                const values = (Array.isArray(value) ? value : [value]).map(String);

                for (const option of field.options) {
                    option.selected = values.includes(option.value);
                }
            } else {
                field.value = value ?? '';
            }
        }
    }

    applyWhen(form, config) {
        for (const part of form.querySelectorAll('[data-flow-when]')) {
            part.hidden = !whenMatches(part.dataset.flowWhen, config);
        }
    }

    /** Het formulier van een stap in het paneel: eigen ids, eigen namen, en niet verstuurbaar. */
    buildForm(node) {
        const template = this.templates.get(node.type);

        if (!template) {
            return null;
        }

        const form = element('div', 'flow-form', template.content.cloneNode(true));
        const prefix = `flow${this.uid}-${node.id}-`;
        form.dataset.flowFormFor = node.id;

        for (const tagged of form.querySelectorAll('[id]')) {
            tagged.id = prefix + tagged.id;
        }

        for (const attribute of ['for', 'aria-describedby', 'aria-labelledby', 'aria-controls', 'list']) {
            for (const tagged of form.querySelectorAll(`[${attribute}]`)) {
                tagged.setAttribute(attribute, tagged.getAttribute(attribute).split(/\s+/).map((id) => prefix + id).join(' '));
            }
        }

        for (const field of this.fields(form)) {
            field.dataset.flowName = field.name;
            field.dataset.flowField = field.name.replace(/\[\]$/, '');
            field.name = prefix + field.name;
            field.setAttribute('form', `${prefix}none`);
        }

        this.applyWhen(form, node.config ?? {});
        this.fill(form, node.config ?? {});

        return form;
    }

    /**
     * De samenvatting op een stap, uit haar formulier: de velden met
     * data-flow-summary na elkaar, of het patroon op de template zelf
     * (data-flow-summary="Wacht {amount} {unit}").
     */
    summary(node) {
        const key = JSON.stringify(node.config ?? {});
        const cached = this.summaryCache.get(node.id);

        if (cached && cached.key === key && cached.type === node.type) {
            return cached.text;
        }

        const template = this.templates.get(node.type);
        let text = '';

        if (template) {
            const form = this.buildForm(node);
            const shown = (field) => !field.closest('[hidden]');
            const display = (fieldKey) => this.fields(form)
                .filter((field) => field.dataset.flowField === fieldKey && shown(field))
                .map((field) => this.display(field))
                .filter(Boolean)
                .join(', ');

            const pattern = template.dataset.flowSummary;
            const requires = template.dataset.flowSummaryRequires;

            // Een voorwaarde zonder veld zegt nog niets: liever geen samenvatting dan "is".
            if (requires && !this.fields(form).some((field) => field.dataset.flowField === requires && field.value !== '')) {
                text = '';
            } else if (pattern) {
                text = summarize(pattern, display);
            } else {
                const keys = [...new Set(this.fields(form).filter((field) => field.hasAttribute('data-flow-summary')).map((field) => field.dataset.flowField))];
                text = keys.map(display).filter(Boolean).join(' · ');
            }
        }

        text = text.replace(/\s+/g, ' ').replace(/\s+([,.])/g, '$1').trim();
        this.summaryCache.set(node.id, { key, type: node.type, text });

        return text;
    }

    /** Wat een veld toont: de tekst van de gekozen optie, het label van een vinkje, de eerste regel. */
    display(field) {
        if (field.tagName === 'SELECT') {
            return [...field.selectedOptions]
                .filter((option) => option.value !== '')
                .map((option) => option.dataset.flowSummaryText ?? option.textContent.trim())
                .filter(Boolean)
                .join(', ');
        }

        if (field.type === 'checkbox' || field.type === 'radio') {
            if (!field.checked) {
                return '';
            }

            return field.dataset.flowSummaryText ?? field.closest('label')?.textContent.trim() ?? field.value;
        }

        const value = String(field.value ?? '').split('\n')[0].trim();

        return value.length > 80 ? `${value.slice(0, 79)}…` : value;
    }

    // ---- Tekenen --------------------------------------------------------------

    render() {
        this.issueMap = this.issues();
        this.renderNodes();
        this.renderEdges();
        this.renderOverlay();
        this.renderIssueButton();
        this.renderHistoryButtons();
    }

    renderNodes() {
        const order = G.ordered(this.graph, this.types);
        this.numbers = new Map(order.map((node, index) => [node.id, index + 1]));

        const focused = document.activeElement?.closest?.('[data-node]')?.dataset.node;
        const fragment = document.createDocumentFragment();

        for (const node of order) {
            fragment.append(this.nodeElement(node));
        }

        this.nodeLayer.replaceChildren(fragment);

        if (focused) {
            this.nodeElementFor(focused)?.focus({ preventScroll: true });
        }
    }

    nodeElementFor(id) {
        return this.nodeLayer.querySelector(`[data-node="${CSS.escape(id)}"]`);
    }

    /** Een stap opnieuw tekenen, op haar plaats (na het typen in het paneel). */
    renderNode(id) {
        this.issueMap = this.issues();
        const old = this.nodeElementFor(id);
        const node = this.node(id);

        if (old && node) {
            const focused = old.contains(document.activeElement);
            const fresh = this.nodeElement(node);
            old.replaceWith(fresh);

            if (focused) {
                fresh.focus({ preventScroll: true });
            }
        }

        this.renderPanelIssues();
    }

    icon(name) {
        const template = this.icons.get(name) ?? this.chrome.get(name);

        return template ? template.content.cloneNode(true) : document.createTextNode('');
    }

    chromeIcon(name) {
        return this.chrome.get(name)?.content.cloneNode(true) ?? document.createTextNode('');
    }

    nodeElement(node) {
        const type = this.type(node);
        const issues = this.issueMap?.get(node.id) ?? [];
        const summary = this.summary(node);
        const number = this.numbers?.get(node.id) ?? '';
        const selected = this.selection?.node === node.id;

        const card = element('div', 'flow-node');
        card.dataset.node = node.id;
        card.dataset.tone = type.tone ?? 'neutral';
        card.tabIndex = 0;
        card.setAttribute('role', 'button');
        card.setAttribute('aria-pressed', selected ? 'true' : 'false');
        card.setAttribute('aria-label', [`${number}. ${type.label}${summary ? `: ${summary}` : ''}`, ...issues].join('. '));
        card.style.transform = `translate(${node.x ?? 0}px, ${node.y ?? 0}px)`;

        if (type.start) {
            card.dataset.start = '';
        }

        if (issues.length) {
            card.dataset.issue = '';
        }

        const text = element('span', 'flow-node-text', [
            element('span', 'flow-node-label', type.label),
            summary ? element('span', 'flow-node-summary', summary) : null,
            issues.length ? element('span', 'flow-node-issue', [this.chromeIcon('warning'), element('span', '', issues.length > 1 ? `${issues[0]} (+${issues.length - 1})` : issues[0])]) : null,
        ]);

        card.append(element('span', 'flow-node-icon', this.icon(node.type)), text);

        // Een cijfer van de server ("12 wachten hier"): op de rand, niet in de tekst die al vol is.
        if (this.badges.has(node.id)) {
            card.append(element('span', 'flow-node-badge', String(this.badges.get(node.id))));
        }

        if (!type.start) {
            card.append(element('span', 'flow-port flow-port-in'));
        }

        type.outputs.forEach((output, index) => {
            const top = (G.NODE_HEIGHT * (index + 1)) / (type.outputs.length + 1);
            const port = element('span', 'flow-port flow-port-out');
            port.dataset.flowOut = output.key;
            port.style.top = `${top}px`;
            card.append(port);

            if (output.label) {
                const label = element('span', 'flow-port-label', output.label);
                label.style.top = `${top}px`;
                card.append(label);
            }
        });

        return card;
    }

    moveNodeElement(id) {
        const node = this.node(id);
        const card = this.nodeElementFor(id);

        if (node && card) {
            card.style.transform = `translate(${node.x}px, ${node.y}px)`;
        }
    }

    renderEdges() {
        const fragment = document.createDocumentFragment();

        for (const edge of this.graph.edges) {
            const from = this.node(edge.from);
            const to = this.node(edge.to);

            if (!from || !to) {
                continue;
            }

            const d = G.edgePath(G.outPoint(this.types, from, edge.port), G.inPoint(to));
            const group = svg('g', { class: 'flow-edge', 'data-edge': `${edge.from}:${edge.port}` });

            if (this.selection?.edge === `${edge.from}:${edge.port}`) {
                group.setAttribute('data-selected', '');
            }

            group.append(svg('path', { class: 'flow-edge-hit', d }), svg('path', { class: 'flow-edge-line', d }));
            fragment.append(group);
        }

        // Een uitgang zonder vervolg: een stippellijn naar de knop om er iets aan te hangen.
        for (const node of this.graph.nodes) {
            for (const output of this.type(node).outputs) {
                if (G.target(this.graph, node.id, output.key) === null) {
                    const point = G.outPoint(this.types, node, output.key);
                    fragment.append(svg('path', { class: 'flow-stub-line', d: `M ${point.x + 7} ${point.y} H ${point.x + 34}` }));
                }
            }
        }

        this.draft = svg('path', { class: 'flow-edge-draft', d: '' });
        fragment.append(this.draft);
        this.edgeLayer.replaceChildren(fragment);
    }

    renderOverlay() {
        if (this.readonly) {
            this.overlay.replaceChildren();

            return;
        }

        const fragment = document.createDocumentFragment();

        for (const node of this.graph.nodes) {
            const type = this.type(node);

            for (const output of type.outputs) {
                if (G.target(this.graph, node.id, output.key) !== null) {
                    continue;
                }

                const point = G.outPoint(this.types, node, output.key);
                const button = element('button', 'flow-stub', this.chromeIcon('plus'));
                button.type = 'button';
                button.dataset.flowStub = '';
                button.dataset.from = node.id;
                button.dataset.port = output.key;
                button.style.transform = `translate(${point.x + 34}px, ${point.y - 12}px)`;
                button.setAttribute('aria-label', format(output.label ? this.strings.addAfterPort : this.strings.addAfter, { stap: `${this.numbers?.get(node.id) ?? ''}. ${type.label}`, uitgang: output.label }));
                fragment.append(button);
            }
        }

        const key = this.selection?.edge ?? this.hoverEdge;
        const edge = key ? this.edge(key) : null;

        if (edge) {
            const middle = G.edgeMiddle(G.outPoint(this.types, this.node(edge.from), edge.port), G.inPoint(this.node(edge.to)));
            const insert = element('button', '', this.chromeIcon('plus'));
            insert.type = 'button';
            insert.dataset.flowEdgeInsert = key;
            insert.setAttribute('aria-label', this.strings.insert);
            insert.title = this.strings.insert;

            const remove = element('button', '', this.chromeIcon('close'));
            remove.type = 'button';
            remove.dataset.flowEdgeRemove = key;
            remove.setAttribute('aria-label', this.strings.disconnect);
            remove.title = this.strings.disconnect;

            const actions = element('div', 'flow-edge-actions', [insert, remove]);
            actions.dataset.flowEdgeActions = key;
            actions.style.transform = `translate(${middle.x}px, ${middle.y}px) translate(-50%, -50%)`;
            fragment.append(actions);
        }

        this.overlay.replaceChildren(fragment);
    }

    renderSelection() {
        for (const card of this.nodeLayer.querySelectorAll('[data-node]')) {
            card.setAttribute('aria-pressed', this.selection?.node === card.dataset.node ? 'true' : 'false');
        }

        for (const group of this.edgeLayer.querySelectorAll('[data-edge]')) {
            group.toggleAttribute('data-selected', this.selection?.edge === group.dataset.edge);
        }

        this.renderOverlay();
    }

    renderIssueButton() {
        const button = this.root.querySelector('[data-flow-issues-button]');

        if (!button) {
            return;
        }

        const count = this.issueMap?.size ?? 0;
        button.hidden = count === 0;
        button.querySelector('[data-flow-issue-count]').textContent = count === 1 ? this.strings.issueOne : format(this.strings.issueMany, { aantal: count });
    }

    renderHistoryButtons() {
        const undo = this.root.querySelector('[data-flow-undo]');
        const redo = this.root.querySelector('[data-flow-redo]');

        if (undo) {
            undo.disabled = !this.past.length;
        }

        if (redo) {
            redo.disabled = !this.future.length;
        }
    }

    announce(message) {
        if (!this.live || !message) {
            return;
        }

        this.live.textContent = '';
        window.setTimeout(() => {
            this.live.textContent = message;
        }, 30);
    }

    // ---- Kijken: schuiven en zoomen ----------------------------------------------

    applyView({ fitted = false } = {}) {
        this.viewChanged = !fitted;

        const { x, y, scale } = this.view;
        this.world.style.transform = `translate(${x}px, ${y}px) scale(${scale})`;
        this.canvas.style.backgroundSize = `${GRID * scale}px ${GRID * scale}px`;
        this.canvas.style.backgroundPosition = `${x}px ${y}px`;

        if (this.zoomLabel) {
            this.zoomLabel.textContent = `${Math.round(scale * 100)}%`;
        }
    }

    toWorld(clientX, clientY) {
        const rect = this.canvas.getBoundingClientRect();

        return { x: (clientX - rect.left - this.view.x) / this.view.scale, y: (clientY - rect.top - this.view.y) / this.view.scale };
    }

    zoomAt(clientX, clientY, factor) {
        const rect = this.canvas.getBoundingClientRect();
        const before = this.toWorld(clientX, clientY);
        this.view.scale = clamp(this.view.scale * factor, MIN_SCALE, MAX_SCALE);
        this.view.x = clientX - rect.left - before.x * this.view.scale;
        this.view.y = clientY - rect.top - before.y * this.view.scale;
        this.applyView();
    }

    zoom(factor) {
        const rect = this.canvas.getBoundingClientRect();
        this.zoomAt(rect.left + rect.width / 2, rect.top + rect.height / 2, factor);
    }

    /** Het deel van het canvas dat niet onder het paneel of de knoppen ligt. */
    visibleArea() {
        const rect = this.canvas.getBoundingClientRect();
        const area = { left: 32, top: 72, right: rect.width - 32, bottom: rect.height - 64 };

        if (!this.panel.hidden) {
            const panel = this.panel.getBoundingClientRect();

            if (panel.left > rect.left + rect.width / 3) {
                area.right = Math.min(area.right, panel.left - rect.left - 24);
            } else {
                area.bottom = Math.min(area.bottom, panel.top - rect.top - 16);
            }
        }

        return area;
    }

    /** Alles in beeld, niet groter dan ware grootte. */
    fit() {
        const box = G.bounds(this.graph);
        const area = this.visibleArea();
        this.fitted = this.canvas.clientWidth > 0;

        if (!box || area.right <= area.left || area.bottom <= area.top) {
            this.view = { x: area.left, y: area.top, scale: 1 };
            this.applyView({ fitted: true });

            return;
        }

        const width = Math.max(1, box.maxX - box.minX + 60);
        const height = Math.max(1, box.maxY - box.minY);
        const scale = clamp(Math.min((area.right - area.left) / width, (area.bottom - area.top) / height, 1), MIN_SCALE, 1);

        // Past het niet, dan begint het beeld bij de start (links, bovenaan) in plaats van in het midden.
        const spareX = (area.right - area.left) - width * scale;
        const spareY = (area.bottom - area.top) - height * scale;

        this.view = {
            scale,
            x: area.left + Math.max(0, spareX / 2) - box.minX * scale,
            y: area.top + Math.max(0, spareY / 2) - box.minY * scale,
        };

        this.applyView({ fitted: true });
    }

    /**
     * Tot onderaan het venster (data-flow-fill): waar de editor begint, in de
     * pagina en niet in het venster, zodat de css hem de rest van de hoogte
     * geeft (--flow-fill-top). Alleen opnieuw zetten wat veranderde: elke
     * nieuwe hoogte meldt de ResizeObserver op de pagina weer.
     */
    measureFill() {
        if (!this.root.hasAttribute('data-flow-fill') || this.root.hasAttribute('data-fullscreen')) {
            return;
        }

        let top = this.root.getBoundingClientRect().top + window.scrollY;

        for (let parent = this.root.parentElement; parent && parent !== document.body; parent = parent.parentElement) {
            top += parent.scrollTop;
        }

        top = Math.max(0, Math.round(top));

        if (top !== this.fillTop) {
            this.fillTop = top;
            this.root.style.setProperty('--flow-fill-top', `${top}px`);
            this.root.toggleAttribute('data-flow-filled', true);
        }
    }

    /** Een stap in beeld schuiven als ze er (deels) buiten valt. */
    ensureVisible(id) {
        const node = this.node(id);

        if (!node) {
            return;
        }

        const area = this.visibleArea();
        const { scale } = this.view;
        const left = this.view.x + node.x * scale;
        const top = this.view.y + node.y * scale;
        const right = left + (G.NODE_WIDTH + 60) * scale;
        const bottom = top + G.NODE_HEIGHT * scale;
        let dx = 0;
        let dy = 0;

        if (right > area.right) {
            dx = area.right - right;
        }

        if (left + dx < area.left) {
            dx = area.left - left;
        }

        if (bottom > area.bottom) {
            dy = area.bottom - bottom;
        }

        if (top + dy < area.top) {
            dy = area.top - top;
        }

        if (dx || dy) {
            this.view.x += dx;
            this.view.y += dy;
            this.applyView();
        }
    }

    // ---- Kiezen ---------------------------------------------------------------

    select(selection, { panel = false, focus = false } = {}) {
        this.selection = selection;
        this.hoverEdge = null;

        if (selection?.node && (panel || this.panelNode)) {
            this.openPanel(selection.node, { focus });
        } else if (!selection?.node) {
            this.closePanel();
        }

        this.renderSelection();
    }

    // ---- Het paneel ------------------------------------------------------------

    openPanel(id, { focus = false } = {}) {
        const node = this.node(id);

        if (!node) {
            this.closePanel();

            return;
        }

        const type = this.type(node);
        const titleId = `flow${this.uid}-panel-title`;
        this.panelNode = id;

        const close = element('button', 'flow-tool', this.chromeIcon('close'));
        close.type = 'button';
        close.dataset.flowClose = '';
        close.setAttribute('aria-label', this.strings.close);
        close.title = this.strings.close;

        const title = element('p', 'flow-panel-title', type.label);
        title.id = titleId;

        const head = element('div', 'flow-panel-head', [
            element('span', 'flow-node-icon', this.icon(node.type)),
            element('div', 'flow-panel-heading', [title, type.hint ? element('p', 'flow-panel-hint', type.hint) : null]),
            close,
        ]);
        head.dataset.tone = type.tone ?? 'neutral';

        const issues = element('div', 'flow-panel-issues');
        issues.dataset.flowPanelIssues = '';

        const notes = (this.notes.get(id) ?? []).map((note) => element('p', 'flow-panel-note', String(note)));

        const form = this.buildForm(node);

        if (form && this.readonly) {
            for (const field of form.querySelectorAll('input, select, textarea')) {
                field.disabled = true;
            }
        }

        const body = element('div', 'flow-panel-body', [issues, ...notes, form, this.nextSection(node)]);

        const parts = [head, body];

        if (!type.start && !this.readonly) {
            const duplicate = element('button', 'btn btn-secondary btn-sm', [this.chromeIcon('copy'), element('span', '', this.strings.duplicate)]);
            duplicate.type = 'button';
            duplicate.dataset.flowDuplicate = id;

            const remove = element('button', 'btn btn-danger-outline btn-sm', [this.chromeIcon('trash'), element('span', '', this.strings.remove)]);
            remove.type = 'button';
            remove.dataset.flowRemove = id;

            parts.push(element('div', 'flow-panel-foot', [duplicate, remove]));
        }

        this.panel.replaceChildren(...parts);
        this.panel.setAttribute('aria-labelledby', titleId);
        this.panel.hidden = false;
        this.renderPanelIssues();

        if (form) {
            this.root.dispatchEvent(new CustomEvent('flows:form', {
                bubbles: true,
                detail: { editor: this, node, form, graph: this.graph, start: G.startOf(this.graph, this.types) },
            }));
        }

        this.ensureVisible(id);

        // Met een muis meteen in het eerste veld; op een telefoon zou dat het toetsenbord over het canvas leggen.
        if (focus) {
            const fine = window.matchMedia?.('(pointer: fine)').matches ?? true;
            const first = this.panel.querySelector('[data-flow-form-for] :is(input, select, textarea):not([type=hidden])');
            (fine && first ? first : close).focus({ preventScroll: true });
        }
    }

    /** Het paneel bijwerken na een wijziging buiten het formulier (verbinden, terugdraaien). */
    refreshPanel() {
        if (!this.panelNode) {
            return;
        }

        const node = this.node(this.panelNode);

        if (!node) {
            this.closePanel();

            return;
        }

        const old = this.panel.querySelector('[data-flow-next-section]');
        const fresh = this.nextSection(node);

        if (old && fresh) {
            old.replaceWith(fresh);
        }

        this.renderPanelIssues();
    }

    renderPanelIssues() {
        const box = this.panel.querySelector('[data-flow-panel-issues]');

        if (!box || !this.panelNode) {
            return;
        }

        const issues = this.issueMap?.get(this.panelNode) ?? [];
        box.replaceChildren(...issues.map((issue) => element('p', 'flow-panel-issue', [this.chromeIcon('warning'), element('span', '', issue)])));
        box.hidden = !issues.length;
    }

    closePanel() {
        if (this.panelNode === null && this.panel.hidden) {
            return;
        }

        this.panelNode = null;
        this.burst = null;
        this.panel.hidden = true;
        this.panel.replaceChildren();
    }

    /** "Daarna": per uitgang kiezen wat erna komt, zonder te slepen. */
    nextSection(node) {
        const type = this.type(node);

        if (!type.outputs.length) {
            return null;
        }

        const order = G.ordered(this.graph, this.types);
        const several = type.outputs.length > 1;
        const section = element('div', 'flow-next', several ? element('p', 'flow-next-title', this.strings.next) : null);
        section.dataset.flowNextSection = '';

        for (const output of type.outputs) {
            const current = G.target(this.graph, node.id, output.key);
            const select = element('select', 'input');
            select.dataset.flowNext = `${node.id}:${output.key}`;
            select.id = `flow${this.uid}-next-${node.id}-${output.key}`;
            select.disabled = this.readonly;

            const option = (value, label) => {
                const item = element('option', '', label);
                item.value = value;
                item.selected = value === (current ?? '');

                return item;
            };

            select.append(option('', this.strings.nothing));

            for (const candidate of order) {
                if (candidate.id === current || G.canConnect(this.graph, this.types, node.id, output.key, candidate.id) === null) {
                    const summary = this.summary(candidate);
                    const label = `${this.numbers?.get(candidate.id) ?? ''}. ${this.type(candidate).label}${summary ? ` · ${summary}` : ''}`;
                    select.append(option(candidate.id, label.length > 70 ? `${label.slice(0, 69)}…` : label));
                }
            }

            if (!this.readonly) {
                select.append(option('__new', this.strings.newStep));
            }

            const label = element('label', several ? 'flow-next-label' : 'label', several ? output.label || output.key : this.strings.next);
            label.htmlFor = select.id;

            section.append(element('div', 'flow-next-row', [label, select]));
        }

        return section;
    }

    onPanelInput(event, change = false) {
        if (this.readonly) {
            return;
        }

        const field = event.target.closest('[data-flow-field]');

        if (field) {
            const form = field.closest('[data-flow-form-for]');
            const id = form.dataset.flowFormFor;
            const key = `${id}:${field.dataset.flowField}`;

            if (this.burst !== key) {
                this.record();
                this.burst = key;
            }

            const config = this.settle(form);
            this.graph = G.updateNode(this.graph, id, { config });
            this.serverIssues.delete(id);
            this.changed({ only: id });

            return;
        }

        const next = event.target.closest('[data-flow-next]');

        if (next && change) {
            const [from, port] = next.dataset.flowNext.split(':');

            if (next.value === '__new') {
                next.value = G.target(this.graph, from, port) ?? '';
                this.openPalette({ from, port, anchor: next.getBoundingClientRect() });
            } else if (next.value === '') {
                this.commit(G.disconnect(this.graph, from, port), this.strings.disconnected);
            } else if (G.canConnect(this.graph, this.types, from, port, next.value) === null) {
                this.commit(G.connect(this.graph, from, port, next.value), this.strings.connected);
            }
        }
    }

    // ---- Het palet -------------------------------------------------------------

    openPalette({ from = null, port = null, at = null, anchor = null } = {}) {
        this.paletteContext = { from, port, at };
        this.paletteActive = 0;
        this.paletteReturn = document.activeElement;
        this.search.value = '';
        this.renderPalette();
        this.palette.hidden = false;

        // Bij wat het opende, binnen het werkvlak; op een telefoon is het een blad onderaan (css).
        const root = this.stage.getBoundingClientRect();
        const width = this.palette.offsetWidth;
        const height = this.palette.offsetHeight;
        const x = anchor ? (anchor.right ?? anchor.x) - root.left + 12 : (root.width - width) / 2;
        const y = anchor ? (anchor.top ?? anchor.y) - root.top - 16 : (root.height - height) / 3;

        this.palette.style.left = `${clamp(x, 12, Math.max(12, root.width - width - 12))}px`;
        this.palette.style.top = `${clamp(y, 12, Math.max(12, root.height - height - 12))}px`;

        this.search.focus({ preventScroll: true });
    }

    closePalette({ restore = true } = {}) {
        if (this.palette.hidden) {
            return;
        }

        this.palette.hidden = true;
        this.paletteContext = null;

        if (restore && this.paletteReturn?.isConnected) {
            this.paletteReturn.focus({ preventScroll: true });
        }
    }

    available() {
        const counts = new Map();

        for (const node of this.graph.nodes) {
            counts.set(node.type, (counts.get(node.type) ?? 0) + 1);
        }

        return [...this.types.values()].filter((type) => !type.start && !(type.max && (counts.get(type.key) ?? 0) >= type.max));
    }

    renderPalette() {
        const query = plain(this.search.value.trim());

        // Wie zoekt, krijgt eerst wat zo heet, dan wat zo begint, dan wat het in de uitleg of de groep zegt.
        const rank = (type) => {
            const label = plain(type.label);

            if (label.startsWith(query)) {
                return 0;
            }

            if (label.includes(query)) {
                return 1;
            }

            return plain(`${type.hint ?? ''} ${type.group ?? ''}`).includes(query) ? 2 : 3;
        };

        const types = query
            ? this.available().map((type, index) => ({ type, index, rank: rank(type) })).filter((item) => item.rank < 3)
                .sort((a, b) => a.rank - b.rank || a.index - b.index).map((item) => item.type)
            : this.available();

        this.paletteItems = types;
        this.paletteActive = clamp(this.paletteActive ?? 0, 0, Math.max(0, types.length - 1));

        const fragment = document.createDocumentFragment();
        let group = null;

        types.forEach((type, index) => {
            if (!query && (type.group ?? '') !== group) {
                group = type.group ?? '';

                if (group) {
                    fragment.append(element('p', 'flow-palette-group', group));
                }
            }

            const item = element('button', 'flow-palette-item', [
                element('span', 'flow-node-icon', this.icon(type.key)),
                element('span', 'flow-palette-text', [element('span', 'flow-palette-label', type.label), type.hint ? element('span', 'flow-palette-hint', type.hint) : null]),
            ]);
            item.type = 'button';
            item.id = `flow${this.uid}-pick-${type.key}`;
            item.dataset.flowPick = type.key;
            item.dataset.tone = type.tone ?? 'neutral';
            item.setAttribute('role', 'option');
            item.setAttribute('aria-selected', index === this.paletteActive ? 'true' : 'false');
            item.tabIndex = -1;

            if (index === this.paletteActive) {
                item.dataset.active = '';
            }

            fragment.append(item);
        });

        if (!types.length) {
            fragment.append(element('p', 'flow-palette-empty', this.strings.noResults));
        }

        this.paletteList.replaceChildren(fragment);
        this.search.setAttribute('aria-activedescendant', types.length ? `flow${this.uid}-pick-${types[this.paletteActive].key}` : '');
        this.paletteList.querySelector('[data-active]')?.scrollIntoView({ block: 'nearest' });
    }

    pick(typeKey) {
        const context = this.paletteContext ?? {};
        this.closePalette({ restore: false });

        if (context.from) {
            this.addAfter(context.from, context.port, typeKey, context.at);
        } else {
            this.addLoose(typeKey, context.at);
        }
    }

    // ---- Stappen toevoegen en weghalen --------------------------------------------

    newId() {
        const id = G.nextId(this.graph, 'n', this.highest);
        this.highest = Number(id.slice(1));

        return id;
    }

    addAfter(from, port, typeKey, at = null) {
        const source = this.node(from);
        const type = this.types.get(typeKey);

        if (!source || !type) {
            return;
        }

        const id = this.newId();
        const after = G.target(this.graph, from, port);
        let graph = this.graph;
        let position;

        if (at) {
            position = { x: G.snap(at.x - 20), y: G.snap(at.y - G.NODE_HEIGHT / 2) };
        } else {
            const point = G.outPoint(this.types, source, port);
            position = { x: G.snap(source.x + G.COLUMN), y: G.snap(point.y - G.NODE_HEIGHT / 2) };

            // Ertussen: wat erna kwam, schuift een kolom op als het te dicht staat.
            if (after !== null && this.node(after).x < position.x + G.COLUMN) {
                const moving = G.descendants(graph, this.types, after);
                graph = { ...graph, nodes: graph.nodes.map((node) => (moving.has(node.id) ? { ...node, x: node.x + G.COLUMN } : node)) };
            }

            position = G.freeSpot(graph, position.x, position.y);
        }

        const node = { id, type: typeKey, config: this.defaults(typeKey), x: position.x, y: position.y };
        this.commit(G.insertAfter(graph, this.types, from, port, node), format(this.strings.added, { stap: type.label }));
        this.select({ node: id }, { panel: true, focus: true });
    }

    addLoose(typeKey, at = null) {
        const type = this.types.get(typeKey);

        if (!type) {
            return;
        }

        const area = this.visibleArea();
        const rect = this.canvas.getBoundingClientRect();
        const center = at ?? this.toWorld(rect.left + (area.left + area.right) / 2, rect.top + (area.top + area.bottom) / 2);
        const position = G.freeSpot(this.graph, G.snap(center.x - G.NODE_WIDTH / 2), G.snap(center.y - G.NODE_HEIGHT / 2));
        const node = { id: this.newId(), type: typeKey, config: this.defaults(typeKey), x: position.x, y: position.y };

        this.commit(G.addNode(this.graph, node), format(this.strings.added, { stap: type.label }));
        this.select({ node: node.id }, { panel: true, focus: true });
    }

    /** "Stap toevoegen": na de gekozen stap, of aan het einde van de hoofdweg. */
    addFromToolbar(anchor) {
        const selected = this.selection?.node ? this.node(this.selection.node) : null;

        if (selected && this.type(selected).outputs.length) {
            const outputs = this.type(selected).outputs;
            const free = outputs.find((output) => G.target(this.graph, selected.id, output.key) === null) ?? outputs[0];
            this.openPalette({ from: selected.id, port: free.key, anchor });

            return;
        }

        let current = G.startOf(this.graph, this.types);

        while (current) {
            const first = this.type(current).outputs[0];

            if (!first) {
                break;
            }

            const next = G.target(this.graph, current.id, first.key);

            if (next === null) {
                this.openPalette({ from: current.id, port: first.key, anchor });

                return;
            }

            current = this.node(next);
        }

        this.openPalette({ anchor });
    }

    remove(id) {
        const node = this.node(id);

        if (!node) {
            return;
        }

        if (this.type(node).start) {
            this.announce(this.strings.startStays);

            return;
        }

        this.commit(G.removeNode(this.graph, this.types, id), format(this.strings.removed, { stap: this.type(node).label }));
        this.select(null);
        this.canvas.focus({ preventScroll: true });
    }

    duplicate(id) {
        const node = this.node(id);

        if (!node || this.type(node).start) {
            return;
        }

        const position = G.freeSpot(this.graph, node.x + 20, node.y + G.ROW / 2);
        const copy = { id: this.newId(), type: node.type, config: structuredClone(node.config ?? {}), x: position.x, y: position.y };

        this.commit(G.addNode(this.graph, copy), format(this.strings.duplicated, { stap: this.type(node).label }));
        this.select({ node: copy.id }, { panel: true, focus: true });
    }

    removeSelection() {
        if (this.selection?.node) {
            this.remove(this.selection.node);
        } else if (this.selection?.edge) {
            const edge = this.edge(this.selection.edge);

            if (edge) {
                this.commit(G.disconnect(this.graph, edge.from, edge.port), this.strings.disconnected);
            }

            this.select(null);
        }
    }

    arrange() {
        this.commit(G.arrange(this.graph, this.types), this.strings.arranged);
        this.fit();
    }

    firstIssue() {
        const id = G.ordered(this.graph, this.types).find((node) => this.issueMap?.has(node.id))?.id;

        if (id) {
            this.select({ node: id }, { panel: true, focus: true });
        }
    }

    /**
     * Volledig scherm: de editor over het hele venster, met css, zodat het
     * overal werkt (ook op een iPhone en in een ingesloten pagina, waar de
     * browser een element niet volledig scherm zet). Waar het mag, verdwijnen
     * ook de balken van de browser: dan het hele document en niet de editor,
     * zodat een venster van de applicatie (een dialog) erboven blijft.
     */
    /**
     * De flow, of het overzicht ernaast (slot overview): wat de pagina anders
     * onder de editor zette. Het overzicht staat in de url (#overzicht), zodat
     * een link, herladen en de knop Terug erin uitkomen; wisselen maakt geen
     * nieuwe stap in de geschiedenis.
     */
    showView(view, { remember = true } = {}) {
        if (!this.overview || this.stage === this.root) {
            return;
        }

        const overview = view === 'overview';

        this.stage.hidden = overview;
        this.overview.hidden = !overview;
        this.root.querySelectorAll('[data-flow-view]').forEach((button) => {
            button.setAttribute('aria-pressed', button.dataset.flowView === (overview ? 'overview' : 'flow') ? 'true' : 'false');
        });

        if (remember) {
            const url = new URL(window.location.href);
            url.hash = overview ? this.overviewHash() : '';
            window.history.replaceState(window.history.state, '', url);
        }
    }

    overviewHash() {
        return `#${this.overview?.dataset.flowOverview || 'overzicht'}`;
    }

    followHash() {
        if (this.overview) {
            this.showView(window.location.hash === this.overviewHash() ? 'overview' : 'flow', { remember: false });
        }
    }

    toggleFullscreen(on = !this.root.hasAttribute('data-fullscreen')) {
        this.root.toggleAttribute('data-fullscreen', on);
        document.documentElement.toggleAttribute('data-flow-fullscreen', on);

        for (const button of this.root.querySelectorAll('[data-flow-fullscreen]')) {
            button.setAttribute('aria-pressed', on ? 'true' : 'false');
        }

        if (on) {
            Promise.resolve(document.documentElement.requestFullscreen?.())
                .then(() => {
                    this.nativeFullscreen = Boolean(document.fullscreenElement);
                })
                .catch(() => {
                    this.nativeFullscreen = false;
                });
        } else {
            if (this.nativeFullscreen && document.fullscreenElement) {
                document.exitFullscreen?.()?.catch?.(() => {});
            }

            this.nativeFullscreen = false;
            this.measureFill();
        }

        this.announce(on ? this.strings.fullscreenOn : this.strings.fullscreenOff);
    }

    // ---- Luisteren ---------------------------------------------------------------

    bind() {
        this.root.addEventListener('click', (event) => this.onClick(event));
        this.root.addEventListener('keydown', (event) => this.onKeyDown(event));

        this.canvas.addEventListener('pointerdown', (event) => this.onPointerDown(event));
        this.canvas.addEventListener('pointermove', (event) => this.onPointerMove(event));
        this.canvas.addEventListener('pointerup', (event) => this.onPointerUp(event));
        this.canvas.addEventListener('pointercancel', (event) => this.onPointerUp(event, true));
        this.canvas.addEventListener('wheel', (event) => this.onWheel(event), { passive: false });

        this.edgeLayer.addEventListener('pointerover', (event) => {
            const key = event.target.closest?.('[data-edge]')?.dataset.edge;

            if (key && key !== this.hoverEdge && !this.gesture) {
                window.clearTimeout(this.hoverTimer);
                this.hoverEdge = key;
                this.renderOverlay();
            }
        });

        this.edgeLayer.addEventListener('pointerout', () => this.leaveEdge());
        this.overlay.addEventListener('pointerover', (event) => {
            if (event.target.closest('[data-flow-edge-actions]')) {
                window.clearTimeout(this.hoverTimer);
            }
        });
        this.overlay.addEventListener('pointerout', (event) => {
            if (event.target.closest('[data-flow-edge-actions]')) {
                this.leaveEdge();
            }
        });

        this.panel.addEventListener('input', (event) => this.onPanelInput(event));
        this.panel.addEventListener('change', (event) => this.onPanelInput(event, true));
        this.panel.addEventListener('focusout', () => {
            this.burst = null;
        });

        this.search.addEventListener('input', () => {
            this.paletteActive = 0;
            this.renderPalette();
        });

        this.palette.addEventListener('focusout', (event) => {
            if (!this.palette.contains(event.relatedTarget) && event.relatedTarget !== null) {
                this.closePalette({ restore: false });
            }
        });

        if (this.root.hasAttribute('data-flow-fill')) {
            window.addEventListener('resize', () => this.measureFill());
            window.addEventListener('load', () => this.measureFill());
        }

        // Een link naar #overzicht op dezelfde pagina, of de knop Terug.
        window.addEventListener('hashchange', () => this.followHash());

        // Escape in het volledig scherm van de browser verlaat dat eerst: dan ook dat van de editor.
        document.addEventListener('fullscreenchange', () => {
            if (!document.fullscreenElement && this.nativeFullscreen && this.root.hasAttribute('data-fullscreen')) {
                this.toggleFullscreen(false);
            }
        });

        // Wat nog niet bewaard is, laat je niet zonder het te vragen achter; het formulier versturen is bewaren.
        window.addEventListener('beforeunload', (event) => {
            if (this.dirty) {
                event.preventDefault();
                event.returnValue = '';
            }
        });

        this.input?.form?.addEventListener('submit', () => {
            this.sync();
            this.dirty = false;
        });
    }

    leaveEdge() {
        window.clearTimeout(this.hoverTimer);
        this.hoverTimer = window.setTimeout(() => {
            if (this.hoverEdge) {
                this.hoverEdge = null;
                this.renderOverlay();
            }
        }, 350);
    }

    onClick(event) {
        const target = event.target.closest('button');

        if (!target || !this.root.contains(target)) {
            return;
        }

        const data = target.dataset;

        if ('flowAdd' in data) {
            this.addFromToolbar(target.getBoundingClientRect());
        } else if ('flowUndo' in data) {
            this.undo();
        } else if ('flowRedo' in data) {
            this.redo();
        } else if ('flowArrange' in data) {
            this.arrange();
        } else if ('flowZoomIn' in data) {
            this.zoom(1.2);
        } else if ('flowZoomOut' in data) {
            this.zoom(1 / 1.2);
        } else if ('flowFit' in data) {
            this.fit();
        } else if ('flowFullscreen' in data) {
            this.toggleFullscreen();
        } else if ('flowView' in data) {
            this.showView(data.flowView);
        } else if ('flowIssuesButton' in data) {
            this.firstIssue();
        } else if ('flowStub' in data) {
            this.select({ node: data.from });
            this.openPalette({ from: data.from, port: data.port, anchor: target.getBoundingClientRect() });
        } else if ('flowEdgeInsert' in data) {
            const edge = this.edge(data.flowEdgeInsert);

            if (edge) {
                this.openPalette({ from: edge.from, port: edge.port, anchor: target.getBoundingClientRect() });
            }
        } else if ('flowEdgeRemove' in data) {
            const edge = this.edge(data.flowEdgeRemove);

            if (edge) {
                this.hoverEdge = null;
                this.commit(G.disconnect(this.graph, edge.from, edge.port), this.strings.disconnected);
                this.select(null);
            }
        } else if ('flowClose' in data) {
            const id = this.panelNode;
            this.closePanel();
            this.nodeElementFor(id)?.focus({ preventScroll: true });
        } else if ('flowRemove' in data) {
            this.remove(data.flowRemove);
        } else if ('flowDuplicate' in data) {
            this.duplicate(data.flowDuplicate);
        } else if ('flowPick' in data) {
            this.pick(data.flowPick);
        }
    }

    onKeyDown(event) {
        // Wat in het overzicht gebeurt, is van de pagina: de toetsen van de flow gelden daar niet.
        if (this.overview?.contains(event.target)) {
            if (event.key === 'Escape' && this.root.hasAttribute('data-fullscreen')) {
                event.preventDefault();
                this.toggleFullscreen(false);
            }

            return;
        }

        if (event.key === 'Escape') {
            if (!this.palette.hidden) {
                event.preventDefault();
                this.closePalette();
            } else if (this.gesture?.kind === 'connect') {
                this.cancelGesture();
            } else if (this.panelNode) {
                event.preventDefault();
                const id = this.panelNode;
                this.closePanel();
                this.nodeElementFor(id)?.focus({ preventScroll: true });
            } else if (this.selection) {
                this.select(null);
            } else if (this.root.hasAttribute('data-fullscreen')) {
                event.preventDefault();
                this.toggleFullscreen(false);
            }

            return;
        }

        if (!this.palette.hidden && event.target === this.search) {
            if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
                event.preventDefault();
                this.paletteActive = clamp(this.paletteActive + (event.key === 'ArrowDown' ? 1 : -1), 0, Math.max(0, this.paletteItems.length - 1));
                this.renderPalette();
            } else if (event.key === 'Enter') {
                event.preventDefault();
                const type = this.paletteItems[this.paletteActive];

                if (type) {
                    this.pick(type.key);
                }
            }

            return;
        }

        if (event.target.closest('input, select, textarea, [contenteditable]')) {
            return;
        }

        const mod = event.ctrlKey || event.metaKey;
        const key = event.key.toLowerCase();
        const onCanvas = event.target === this.root || this.canvas.contains(event.target);
        const card = event.target.closest('[data-node]');

        if (key === 'f' && !mod && !event.altKey && (onCanvas || event.target === this.root)) {
            event.preventDefault();
            this.toggleFullscreen();

            return;
        }

        if (this.readonly) {
            if (card && (event.key === 'Enter' || event.key === ' ')) {
                event.preventDefault();
                this.select({ node: card.dataset.node }, { panel: true, focus: true });
            } else if (onCanvas && !mod && !event.altKey) {
                this.zoomKey(event.key);
            }

            return;
        }

        if (mod && key === 'z') {
            event.preventDefault();
            event.shiftKey ? this.redo() : this.undo();

            return;
        }

        if (mod && key === 'y') {
            event.preventDefault();
            this.redo();

            return;
        }

        if (mod || event.altKey) {
            return;
        }

        if ((event.key === 'Delete' || event.key === 'Backspace') && onCanvas && (card || this.selection)) {
            event.preventDefault();
            card ? this.remove(card.dataset.node) : this.removeSelection();

            return;
        }

        if (card && (event.key === 'Enter' || event.key === ' ')) {
            event.preventDefault();
            this.select({ node: card.dataset.node }, { panel: true, focus: true });

            return;
        }

        if (card && event.key.startsWith('Arrow')) {
            event.preventDefault();
            const step = event.shiftKey ? 50 : 10;
            const node = this.node(card.dataset.node);
            const dx = event.key === 'ArrowLeft' ? -step : event.key === 'ArrowRight' ? step : 0;
            const dy = event.key === 'ArrowUp' ? -step : event.key === 'ArrowDown' ? step : 0;
            const burst = `move:${node.id}`;

            if (this.burst !== burst) {
                this.record();
                this.burst = burst;
            }

            this.graph = G.updateNode(this.graph, node.id, { x: node.x + dx, y: node.y + dy });
            this.moveNodeElement(node.id);
            this.renderEdges();
            this.renderOverlay();
            this.dirty = true;
            this.sync();
            this.renderHistoryButtons();
            this.ensureVisible(node.id);

            return;
        }

        if (onCanvas) {
            this.zoomKey(event.key);
        }
    }

    zoomKey(key) {
        if (key === '+' || key === '=') {
            this.zoom(1.2);
        } else if (key === '-') {
            this.zoom(1 / 1.2);
        } else if (key === '0') {
            this.fit();
        }
    }

    onWheel(event) {
        if (event.target.closest('[data-flow-panel], [data-flow-palette]')) {
            return;
        }

        event.preventDefault();

        if (event.ctrlKey || event.metaKey) {
            this.zoomAt(event.clientX, event.clientY, clamp(Math.exp(-event.deltaY * 0.0025), 0.8, 1.25));

            return;
        }

        const unit = event.deltaMode === 1 ? 16 : 1;
        const horizontal = event.shiftKey && !event.deltaX;
        this.view.x -= (horizontal ? event.deltaY : event.deltaX) * unit;
        this.view.y -= (horizontal ? 0 : event.deltaY) * unit;
        this.applyView();
    }

    onPointerDown(event) {
        if (event.pointerType === 'mouse' && event.button !== 0) {
            return;
        }

        if (event.target.closest('button, input, select, textarea, a, [data-flow-panel], [data-flow-palette], .flow-toolbar, .flow-zoom')) {
            return;
        }

        this.pointers.set(event.pointerId, { x: event.clientX, y: event.clientY });

        if (this.pointers.size === 2) {
            this.startPinch();

            return;
        }

        if (this.pointers.size > 2) {
            return;
        }

        if (!this.palette.hidden) {
            this.closePalette({ restore: false });
        }

        const port = event.target.closest('[data-flow-out]');
        const card = event.target.closest('[data-node]');
        const edge = event.target.closest('[data-edge]');
        const start = { startX: event.clientX, startY: event.clientY, moved: false, pointer: event.pointerId };

        this.canvas.setPointerCapture?.(event.pointerId);

        if (this.readonly) {
            this.gesture = { ...start, kind: 'pan', node: card?.dataset.node ?? null, edge: card ? null : edge?.dataset.edge ?? null, origin: { x: this.view.x, y: this.view.y } };
        } else if (port && card) {
            this.gesture = { ...start, kind: 'connect', from: card.dataset.node, port: port.dataset.flowOut };
        } else if (card) {
            const node = this.node(card.dataset.node);
            this.gesture = { ...start, kind: 'drag', id: node.id, origin: { x: node.x, y: node.y }, before: this.graph };
        } else {
            this.gesture = { ...start, kind: 'pan', edge: edge?.dataset.edge ?? null, origin: { x: this.view.x, y: this.view.y } };
        }
    }

    onPointerMove(event) {
        if (this.pointers.has(event.pointerId)) {
            this.pointers.set(event.pointerId, { x: event.clientX, y: event.clientY });
        }

        if (this.pinch) {
            this.movePinch();

            return;
        }

        const gesture = this.gesture;

        if (!gesture || gesture.pointer !== event.pointerId) {
            return;
        }

        const dx = event.clientX - gesture.startX;
        const dy = event.clientY - gesture.startY;

        if (!gesture.moved && Math.hypot(dx, dy) < 4) {
            return;
        }

        if (!gesture.moved) {
            gesture.moved = true;
            this.hoverEdge = null;

            if (gesture.kind === 'drag') {
                this.nodeElementFor(gesture.id)?.toggleAttribute('data-dragging', true);
            }
        }

        if (gesture.kind === 'pan') {
            this.view.x = gesture.origin.x + dx;
            this.view.y = gesture.origin.y + dy;
            this.applyView();
        } else if (gesture.kind === 'drag') {
            this.graph = G.updateNode(this.graph, gesture.id, {
                x: Math.round(gesture.origin.x + dx / this.view.scale),
                y: Math.round(gesture.origin.y + dy / this.view.scale),
            });
            this.moveNodeElement(gesture.id);
            this.renderEdges();
            this.renderOverlay();
        } else if (gesture.kind === 'connect') {
            const from = G.outPoint(this.types, this.node(gesture.from), gesture.port);
            const to = this.toWorld(event.clientX, event.clientY);
            this.draft?.setAttribute('d', G.edgePath(from, to));
            this.markDrop(event.clientX, event.clientY, gesture);
        }
    }

    markDrop(clientX, clientY, gesture) {
        const card = document.elementFromPoint(clientX, clientY)?.closest?.('[data-node]');
        const id = card?.dataset.node ?? null;

        for (const other of this.nodeLayer.querySelectorAll('[data-drop]')) {
            if (other !== card) {
                other.removeAttribute('data-drop');
            }
        }

        if (card) {
            card.dataset.drop = G.canConnect(this.graph, this.types, gesture.from, gesture.port, id) === null ? 'ok' : 'no';
        }
    }

    onPointerUp(event, cancelled = false) {
        this.pointers.delete(event.pointerId);

        if (this.pinch) {
            if (this.pointers.size < 2) {
                this.pinch = null;
            }

            return;
        }

        const gesture = this.gesture;

        if (!gesture || gesture.pointer !== event.pointerId) {
            return;
        }

        this.gesture = null;
        this.draft?.setAttribute('d', '');

        for (const card of this.nodeLayer.querySelectorAll('[data-drop], [data-dragging]')) {
            card.removeAttribute('data-drop');
            card.removeAttribute('data-dragging');
        }

        if (cancelled) {
            if (gesture.kind === 'drag' && gesture.moved) {
                this.graph = gesture.before;
                this.render();
            }

            return;
        }

        if (gesture.kind === 'pan') {
            if (gesture.moved) {
                return;
            }

            if (gesture.node) {
                this.select({ node: gesture.node }, { panel: true });
            } else {
                gesture.edge ? this.select({ edge: gesture.edge }) : this.select(null);
            }
        } else if (gesture.kind === 'drag') {
            if (gesture.moved) {
                const node = this.node(gesture.id);
                this.graph = G.updateNode(this.graph, gesture.id, { x: G.snap(node.x), y: G.snap(node.y) });
                this.burst = null;
                this.record(gesture.before);
                this.changed();
            } else {
                this.select({ node: gesture.id }, { panel: true });
            }
        } else if (gesture.kind === 'connect') {
            this.finishConnect(gesture, event);
        }
    }

    finishConnect(gesture, event) {
        const anchor = { x: event.clientX, y: event.clientY };

        if (!gesture.moved) {
            this.select({ node: gesture.from });
            this.openPalette({ from: gesture.from, port: gesture.port, anchor });

            return;
        }

        const card = document.elementFromPoint(event.clientX, event.clientY)?.closest?.('[data-node]');

        if (card && this.nodeLayer.contains(card)) {
            const reason = G.canConnect(this.graph, this.types, gesture.from, gesture.port, card.dataset.node);

            if (reason) {
                this.announce(this.strings[reason] ?? '');
                this.flash(card);
            } else {
                this.commit(G.connect(this.graph, gesture.from, gesture.port, card.dataset.node), this.strings.connected);
            }

            return;
        }

        // Losgelaten op een lege plek: daar komt een nieuwe stap, al verbonden.
        this.openPalette({ from: gesture.from, port: gesture.port, at: this.toWorld(event.clientX, event.clientY), anchor });
    }

    /** Een stap die niet kan, kort laten zien waarom niet (de reden staat in de live-regio). */
    flash(card) {
        card.dataset.refused = '';
        window.setTimeout(() => card.removeAttribute('data-refused'), 600);
    }

    cancelGesture() {
        if (this.gesture) {
            this.onPointerUp({ pointerId: this.gesture.pointer }, true);
        }
    }

    startPinch() {
        if (this.gesture?.kind === 'drag' && this.gesture.moved) {
            this.graph = this.gesture.before;
            this.render();
        }

        this.gesture = null;
        this.draft?.setAttribute('d', '');

        const [a, b] = [...this.pointers.values()];
        this.pinch = {
            distance: Math.hypot(a.x - b.x, a.y - b.y) || 1,
            middle: { x: (a.x + b.x) / 2, y: (a.y + b.y) / 2 },
            view: { ...this.view },
        };
    }

    movePinch() {
        const [a, b] = [...this.pointers.values()];

        if (!a || !b) {
            return;
        }

        const rect = this.canvas.getBoundingClientRect();
        const { distance, middle, view } = this.pinch;
        const scale = clamp(view.scale * (Math.hypot(a.x - b.x, a.y - b.y) / distance), MIN_SCALE, MAX_SCALE);
        const anchor = { x: (middle.x - rect.left - view.x) / view.scale, y: (middle.y - rect.top - view.y) / view.scale };
        const now = { x: (a.x + b.x) / 2, y: (a.y + b.y) / 2 };

        this.view = { scale, x: now.x - rect.left - anchor.x * scale, y: now.y - rect.top - anchor.y * scale };
        this.applyView();
    }
}

const editors = new WeakMap();

/** Elke editor in een stuk van de pagina starten (een keer per element). */
export function initFlows(root = document) {
    for (const element of root.querySelectorAll('[data-flow-editor]')) {
        if (!editors.has(element)) {
            editors.set(element, new FlowEditor(element));
        }
    }
}

export function editorFor(element) {
    return editors.get(element) ?? null;
}
