/*
| De graaf van een flow in de browser: stappen, verbindingen en wat ermee mag.
|
| DEZELFDE REGELS ALS src/Flows/Graph.php. Een uitgang heeft hoogstens een
| verbinding, naar de start gaat niets, en een lus kan niet: een verbinding
| die er een zou maken, weigert canConnect() al voor ze getekend is. De
| server controleert alles opnieuw; wat hier staat, is er om de gebruiker
| niet te laten bouwen wat daar geweigerd wordt.
|
| GEEN DOM. Alles hier neemt een graaf en geeft een nieuwe terug, zodat de
| editor kan terugdraaien (een vorige graaf is een vorige toestand) en de
| tests in node draaien (tests/js).
|
| layout() rekent precies wat src/Flows/Layout.php rekent; tests/fixtures/flows-layout.json
| houdt beide gelijk.
|
| TAKKEN UIT DE CONFIG: een soort met `branches` (zie NodeType.php) heeft een
| uitgang per waarde in config[branches], voor haar vaste uitgangen; outputs()
| is de enige plaats die dat weet. NOTITIES (stickies) staan naast de stappen
| en doen niet mee met de wandeling.
*/

export const COLUMN = 300;
export const ROW = 150;
export const NODE_WIDTH = 240;
export const NODE_HEIGHT = 76;
export const MAX_BRANCHES = 4;
export const MAX_STICKIES = 50;
export const STICKY_WIDTH = 220;

const PORT = /^[a-z][a-z0-9_-]{0,19}$/;
const ID = /^[A-Za-z0-9_-]{1,40}$/;

const isObject = (value) => value !== null && typeof value === 'object' && !Array.isArray(value);
const coordinate = (value) => (typeof value === 'number' && Number.isFinite(value) ? Math.round(value) : null);

/** De graaf uit json of een object; wat er niet in hoort, valt weg. */
export function parse(value) {
    let data = value;

    if (typeof value === 'string') {
        try {
            data = JSON.parse(value);
        } catch {
            data = null;
        }
    }

    const seen = new Set();
    const nodes = (Array.isArray(data?.nodes) ? data.nodes : [])
        .filter((node) => isObject(node) && typeof node.id === 'string' && typeof node.type === 'string' && !seen.has(node.id) && seen.add(node.id))
        .map((node) => ({
            id: node.id,
            type: node.type,
            config: isObject(node.config) ? structuredClone(node.config) : {},
            x: coordinate(node.x),
            y: coordinate(node.y),
        }));

    const ports = new Set();
    const edges = (Array.isArray(data?.edges) ? data.edges : [])
        .filter((edge) => isObject(edge) && seen.has(edge.from) && seen.has(edge.to) && typeof edge.port === 'string' && edge.from !== edge.to)
        .filter((edge) => !ports.has(`${edge.from}\0${edge.port}`) && ports.add(`${edge.from}\0${edge.port}`))
        .map((edge) => ({ from: edge.from, port: edge.port, to: edge.to }));

    const notes = new Set();
    const stickies = (Array.isArray(data?.stickies) ? data.stickies : [])
        .filter((sticky) => isObject(sticky) && typeof sticky.id === 'string' && ID.test(sticky.id) && !notes.has(sticky.id) && notes.add(sticky.id))
        .slice(0, MAX_STICKIES)
        .map((sticky) => ({ id: sticky.id, text: typeof sticky.text === 'string' ? sticky.text.slice(0, 1000) : '', x: coordinate(sticky.x), y: coordinate(sticky.y) }));

    return { version: 1, nodes, edges, stickies };
}

export function serialize(graph) {
    const data = {
        version: 1,
        nodes: graph.nodes.map((node) => ({ id: node.id, type: node.type, config: node.config, x: node.x, y: node.y })),
        edges: graph.edges.map((edge) => ({ from: edge.from, port: edge.port, to: edge.to })),
    };

    // Zonder notities ook geen lege lijst: een flow die niemand aanraakte, blijft dezelfde json.
    if (graph.stickies?.length) {
        data.stickies = graph.stickies.map((sticky) => ({ id: sticky.id, text: sticky.text, x: sticky.x, y: sticky.y }));
    }

    return JSON.stringify(data);
}

/** De soorten per sleutel; een Map blijft een Map. */
export function typeMap(types) {
    if (types instanceof Map) {
        return types;
    }

    return new Map((types ?? []).map((type) => [type.key, type]));
}

export function findNode(graph, id) {
    return graph.nodes.find((node) => node.id === id) ?? null;
}

/**
 * De uitgangen van een stap: die uit haar config (branches), dan die van
 * haar soort. Een uitgang uit de config heeft geen label: de editor leest
 * het uit het formulier.
 */
export function outputs(types, node) {
    const type = typeMap(types).get(node?.type);

    if (!type) {
        return [];
    }

    if (!type.branches) {
        return type.outputs ?? [];
    }

    const fixed = type.outputs ?? [];
    const values = Array.isArray(node.config?.[type.branches]) ? node.config[type.branches] : [];
    const branches = [];

    for (const value of values) {
        if (branches.length < MAX_BRANCHES && typeof value === 'string' && PORT.test(value)
            && !fixed.some((output) => output.key === value) && !branches.some((output) => output.key === value)) {
            branches.push({ key: value, label: '' });
        }
    }

    return [...branches, ...fixed];
}

/** Hoe hoog een stap is: met veel uitgangen hoger, zodat ze niet op elkaar staan. */
export function nodeHeight(types, node) {
    const count = outputs(types, node).length;

    return count > 3 ? 24 * (count + 1) : NODE_HEIGHT;
}

/** Verbindingen van uitgangen die een stap niet meer heeft (een tak die wegviel), weg. */
export function prunePorts(graph, types, id) {
    const keys = new Set(outputs(types, findNode(graph, id)).map((output) => output.key));
    const edges = graph.edges.filter((edge) => edge.from !== id || keys.has(edge.port));

    return edges.length === graph.edges.length ? graph : { ...graph, edges };
}

export function isStart(types, node) {
    return Boolean(typeMap(types).get(node?.type)?.start);
}

export function startOf(graph, types) {
    const map = typeMap(types);

    return graph.nodes.find((node) => map.get(node.type)?.start) ?? null;
}

export function target(graph, from, port) {
    return graph.edges.find((edge) => edge.from === from && edge.port === port)?.to ?? null;
}

/** De verbindingen uit een stap, in de volgorde van de uitgangen van haar soort. */
export function outgoing(graph, types, id) {
    const node = findNode(graph, id);

    return outputs(types, node)
        .map((output) => graph.edges.find((edge) => edge.from === id && edge.port === output.key))
        .filter(Boolean);
}

export function incoming(graph, id) {
    return graph.edges.filter((edge) => edge.to === id);
}

/** Een wandeling vanaf een stap: eerst de eerste uitgang helemaal af. */
function walk(graph, types, id, order) {
    const stack = [id];

    while (stack.length) {
        const current = stack.pop();

        if (order.has(current)) {
            continue;
        }

        order.add(current);

        for (const edge of outgoing(graph, types, current).reverse()) {
            if (!order.has(edge.to)) {
                stack.push(edge.to);
            }
        }
    }
}

/** De ids vanaf de start, zoals Graph::reachable(). */
export function reachable(graph, types) {
    const start = startOf(graph, types);
    const order = new Set();

    if (start) {
        walk(graph, types, start.id, order);
    }

    return [...order];
}

/** Alle stappen in leesvolgorde, zoals Graph::ordered(). */
export function ordered(graph, types) {
    const start = startOf(graph, types);
    const order = new Set();

    if (start) {
        walk(graph, types, start.id, order);
    }

    for (const node of graph.nodes) {
        if (!order.has(node.id) && incoming(graph, node.id).length === 0) {
            walk(graph, types, node.id, order);
        }
    }

    // Een lus (die er niet hoort te zijn) laat niets weg.
    for (const node of graph.nodes) {
        order.add(node.id);
    }

    return [...order].map((id) => findNode(graph, id));
}

/** De ids die je vanaf een stap bereikt, die stap erbij. */
export function descendants(graph, types, id) {
    const order = new Set();
    walk(graph, types, id, order);

    return order;
}

/** Of `to` vanaf `from` bereikt wordt. */
export function leadsTo(graph, types, from, to) {
    return descendants(graph, types, from).has(to);
}

/**
 * Of een uitgang naar een stap mag: null als het mag, anders waarom niet
 * ('missing', 'port', 'self', 'start', 'cycle').
 */
export function canConnect(graph, types, from, port, to) {
    const map = typeMap(types);
    const source = findNode(graph, from);
    const destination = findNode(graph, to);

    if (!source || !destination) {
        return 'missing';
    }

    if (!outputs(map, source).some((output) => output.key === port)) {
        return 'port';
    }

    if (from === to) {
        return 'self';
    }

    if (isStart(map, destination)) {
        return 'start';
    }

    // Wat al vanaf de bestemming bereikt wordt, mag er niet naartoe wijzen.
    if (leadsTo(graph, map, to, from)) {
        return 'cycle';
    }

    return null;
}

/** Een uitgang verbinden; wat er al aan hing, laat los. */
export function connect(graph, from, port, to) {
    return {
        ...graph,
        edges: [...graph.edges.filter((edge) => !(edge.from === from && edge.port === port)), { from, port, to }],
    };
}

export function disconnect(graph, from, port) {
    return { ...graph, edges: graph.edges.filter((edge) => !(edge.from === from && edge.port === port)) };
}

export function addNode(graph, node) {
    return { ...graph, nodes: [...graph.nodes, { id: node.id, type: node.type, config: node.config ?? {}, x: node.x ?? null, y: node.y ?? null }] };
}

export function updateNode(graph, id, changes) {
    return { ...graph, nodes: graph.nodes.map((node) => (node.id === id ? { ...node, ...changes } : node)) };
}

/**
 * Een stap weghalen. Hing ze tussen twee stappen (een verbinding erin, een
 * op haar eerste uitgang), dan sluiten die weer aan, zoals in Graph.php.
 */
export function removeNode(graph, types, id, bridge = true) {
    const node = findNode(graph, id);

    if (!node) {
        return graph;
    }

    const into = incoming(graph, id);
    const first = outputs(types, node)[0]?.key ?? null;
    const after = first === null ? null : target(graph, id, first);

    let next = {
        ...graph,
        nodes: graph.nodes.filter((other) => other.id !== id),
        edges: graph.edges.filter((edge) => edge.from !== id && edge.to !== id),
    };

    if (bridge && into.length === 1 && after !== null) {
        next = connect(next, into[0].from, into[0].port, after);
    }

    return next;
}

/**
 * Een nieuwe stap na een uitgang zetten. Hing er al iets aan die uitgang,
 * dan komt dat na de nieuwe stap (aan haar eerste uitgang): ertussen.
 */
export function insertAfter(graph, types, from, port, node) {
    const after = target(graph, from, port);
    let next = connect(addNode(graph, node), from, port, node.id);
    const first = outputs(types, node)[0]?.key ?? null;

    if (after !== null && first !== null) {
        next = connect(next, node.id, first, after);
    }

    return next;
}

/**
 * Een id dat nog vrij is: n1, n2, ... (een id van de server, zoals "12", zit
 * nooit in de weg). Met `after` nooit lager dan dat: de editor onthoudt het
 * hoogste nummer, zodat een id van een weggehaalde stap niet terugkomt.
 */
export function nextId(graph, prefix = 'n', after = 0) {
    const used = new Set(graph.nodes.map((node) => node.id));
    let number = Math.max(graph.nodes.length, after);

    for (const node of graph.nodes) {
        const match = node.id.match(new RegExp(`^${prefix}(\\d+)$`));

        if (match) {
            number = Math.max(number, Number(match[1]));
        }
    }

    let id;

    do {
        id = `${prefix}${++number}`;
    } while (used.has(id));

    return id;
}

/** De plaatsen die layout() geeft, als Map id => [x, y]. Zie src/Flows/Layout.php. */
export function layout(graph, types) {
    const map = typeMap(types);
    const ids = graph.nodes.map((node) => node.id);
    const into = new Map(ids.map((id) => [id, 0]));

    for (const edge of graph.edges) {
        into.set(edge.to, (into.get(edge.to) ?? 0) + 1);
    }

    const indegree = new Map(into);
    const columns = new Map(ids.map((id) => [id, 0]));
    const queue = ids.filter((id) => indegree.get(id) === 0);

    while (queue.length) {
        const id = queue.shift();

        for (const edge of outgoing(graph, map, id)) {
            columns.set(edge.to, Math.max(columns.get(edge.to), columns.get(id) + 1));
            indegree.set(edge.to, indegree.get(edge.to) - 1);

            if (indegree.get(edge.to) === 0) {
                queue.push(edge.to);
            }
        }
    }

    const start = startOf(graph, map)?.id ?? null;
    const roots = start === null ? [] : [start];

    for (const id of ids) {
        if (id !== start && into.get(id) === 0) {
            roots.push(id);
        }
    }

    const rows = new Map();
    let next = 0;

    const visit = (id) => {
        rows.set(id, -1);
        let row = null;

        for (const edge of outgoing(graph, map, id)) {
            if (!rows.has(edge.to)) {
                visit(edge.to);
                row ??= rows.get(edge.to);
            }
        }

        rows.set(id, row ?? next++);
    };

    for (const id of [...roots, ...ids]) {
        if (!rows.has(id)) {
            visit(id);
        }
    }

    return new Map(ids.map((id) => [id, [columns.get(id) * COLUMN, rows.get(id) * ROW]]));
}

/** De graaf met de plaatsen van layout(); met all false alleen voor wie er nog geen had. */
export function arrange(graph, types, all = true) {
    const positions = layout(graph, types);

    return {
        ...graph,
        nodes: graph.nodes.map((node) => {
            if (!all && node.x !== null && node.y !== null) {
                return node;
            }

            const [x, y] = positions.get(node.id);

            return { ...node, x, y };
        }),
    };
}

/**
 * Wat nog niet af is, per stap, in dezelfde volgorde als Graph::issues():
 * een start zonder vervolg, een stap die nergens aan hangt, en wat
 * required(node) zegt (de editor leest dat uit het formulier).
 */
export function issues(graph, types, { required = () => [], strings = {} } = {}) {
    const map = typeMap(types);
    const start = startOf(graph, map);
    const reach = new Set(reachable(graph, map));
    const found = new Map();
    const add = (id, message) => {
        if (!found.has(id)) {
            found.set(id, []);
        }

        found.get(id).push(message);
    };

    for (const node of ordered(graph, map)) {
        const type = map.get(node.type);

        if (start && node.id === start.id && outgoing(graph, map, node.id).length === 0 && outputs(map, node).length > 0) {
            add(node.id, strings.startAlone ?? 'Nog geen volgende stap.');
        }

        if (start && !reach.has(node.id)) {
            add(node.id, strings.unreachable ?? 'Deze stap hangt aan geen enkele andere stap.');
        }

        for (const message of required(node)) {
            add(node.id, message);
        }
    }

    return found;
}

// ---- Meetkunde ---------------------------------------------------------------

/** Waar een verbinding binnenkomt: links, halverwege. */
export function inPoint(node, types = null) {
    return { x: node.x, y: node.y + (types ? nodeHeight(types, node) : NODE_HEIGHT) / 2 };
}

/** Waar een uitgang vertrekt: rechts, de uitgangen gelijk verdeeld over de hoogte. */
export function outPoint(types, node, port) {
    const list = outputs(types, node);
    const index = Math.max(0, list.findIndex((output) => output.key === port));

    return { x: node.x + NODE_WIDTH, y: node.y + (nodeHeight(types, node) * (index + 1)) / (list.length + 1) };
}

/** Een zachte bocht van een uitgang naar een ingang, ook als die links ligt. */
export function edgePath(from, to) {
    const dx = Math.max(48, Math.abs(to.x - from.x) / 2);

    return `M ${from.x} ${from.y} C ${from.x + dx} ${from.y}, ${to.x - dx} ${to.y}, ${to.x} ${to.y}`;
}

/** Het midden van die bocht (t = 0,5), voor de knoppen op een verbinding. */
export function edgeMiddle(from, to) {
    const dx = Math.max(48, Math.abs(to.x - from.x) / 2);

    return {
        x: (from.x + 3 * (from.x + dx) + 3 * (to.x - dx) + to.x) / 8,
        y: (from.y + 3 * from.y + 3 * to.y + to.y) / 8,
    };
}

/** De rechthoek rond alle stappen en notities (een notitie telt als een stap breed en hoog). */
export function bounds(graph, types = null) {
    const boxes = [
        ...graph.nodes.filter((node) => node.x !== null && node.y !== null)
            .map((node) => [node.x, node.y, node.x + NODE_WIDTH, node.y + (types ? nodeHeight(types, node) : NODE_HEIGHT)]),
        ...(graph.stickies ?? []).filter((sticky) => sticky.x !== null && sticky.y !== null)
            .map((sticky) => [sticky.x, sticky.y, sticky.x + STICKY_WIDTH, sticky.y + NODE_HEIGHT]),
    ];

    if (!boxes.length) {
        return null;
    }

    return {
        minX: Math.min(...boxes.map((box) => box[0])),
        minY: Math.min(...boxes.map((box) => box[1])),
        maxX: Math.max(...boxes.map((box) => box[2])),
        maxY: Math.max(...boxes.map((box) => box[3])),
    };
}

/** Een plaats die vrij is, vanaf (x, y) telkens een halve rij lager. */
export function freeSpot(graph, x, y, ignore = null) {
    const overlaps = (top) => graph.nodes.some((node) => node.id !== ignore && node.x !== null
        && Math.abs(node.x - x) < NODE_WIDTH + 20 && Math.abs(node.y - top) < NODE_HEIGHT + 20);

    let top = y;

    for (let tries = 0; tries < 50 && overlaps(top); tries++) {
        top += ROW / 2;
    }

    return { x, y: top };
}

export function snap(value, grid = 10) {
    return Math.round(value / grid) * grid;
}

// ---- Notities ----------------------------------------------------------------

/** Een id voor een notitie dat nog vrij is: s1, s2, ... */
export function nextStickyId(graph) {
    const used = new Set((graph.stickies ?? []).map((sticky) => sticky.id));
    let number = 0;
    let id;

    do {
        id = `s${++number}`;
    } while (used.has(id));

    return id;
}

export function addSticky(graph, sticky) {
    return { ...graph, stickies: [...(graph.stickies ?? []), { id: sticky.id, text: sticky.text ?? '', x: sticky.x ?? null, y: sticky.y ?? null }] };
}

export function updateSticky(graph, id, changes) {
    return { ...graph, stickies: (graph.stickies ?? []).map((sticky) => (sticky.id === id ? { ...sticky, ...changes } : sticky)) };
}

export function removeSticky(graph, id) {
    return { ...graph, stickies: (graph.stickies ?? []).filter((sticky) => sticky.id !== id) };
}

// ---- Kopiëren en plakken -------------------------------------------------------

/**
 * Wat gekozen is, om te kopiëren: de stappen (de start niet: die is er maar
 * een), de verbindingen tussen hen, en de notities.
 */
export function extract(graph, types, nodeIds, stickyIds = []) {
    const map = typeMap(types);
    const ids = new Set(nodeIds.filter((id) => {
        const node = findNode(graph, id);

        return node && !map.get(node.type)?.start;
    }));
    const notes = new Set(stickyIds);

    return {
        nodes: graph.nodes.filter((node) => ids.has(node.id)).map((node) => structuredClone(node)),
        edges: graph.edges.filter((edge) => ids.has(edge.from) && ids.has(edge.to)).map((edge) => ({ ...edge })),
        stickies: (graph.stickies ?? []).filter((sticky) => notes.has(sticky.id)).map((sticky) => ({ ...sticky })),
    };
}

/**
 * Een kopie in een graaf zetten: nieuwe ids, een eind verschoven, de
 * verbindingen ertussen mee. Een soort die deze flow niet kent, of waarvan
 * er al genoeg zijn, valt weg. Geeft de nieuwe graaf en de nieuwe ids.
 */
export function paste(graph, types, clip, { dx = 40, dy = 40, after = 0 } = {}) {
    const map = typeMap(types);
    const counts = new Map();

    for (const node of graph.nodes) {
        counts.set(node.type, (counts.get(node.type) ?? 0) + 1);
    }

    let next = graph;
    let highest = after;
    const renamed = new Map();
    const nodes = [];
    const stickies = [];

    for (const node of clip?.nodes ?? []) {
        const type = map.get(node?.type);
        const count = counts.get(node?.type) ?? 0;

        if (!type || type.start || (type.max && count >= type.max)) {
            continue;
        }

        const id = nextId(next, 'n', highest);
        highest = Number(id.slice(1));
        counts.set(node.type, count + 1);
        renamed.set(node.id, id);
        next = addNode(next, { id, type: node.type, config: isObject(node.config) ? structuredClone(node.config) : {}, x: coordinate(node.x) === null ? null : coordinate(node.x) + dx, y: coordinate(node.y) === null ? null : coordinate(node.y) + dy });
        nodes.push(id);
    }

    for (const edge of clip?.edges ?? []) {
        const from = renamed.get(edge?.from);
        const to = renamed.get(edge?.to);

        if (from && to && outputs(map, findNode(next, from)).some((output) => output.key === edge.port) && target(next, from, edge.port) === null) {
            next = connect(next, from, edge.port, to);
        }
    }

    for (const sticky of clip?.stickies ?? []) {
        if ((next.stickies ?? []).length >= MAX_STICKIES || !isObject(sticky)) {
            continue;
        }

        const id = nextStickyId(next);
        next = addSticky(next, { id, text: typeof sticky.text === 'string' ? sticky.text.slice(0, 1000) : '', x: coordinate(sticky.x) === null ? null : coordinate(sticky.x) + dx, y: coordinate(sticky.y) === null ? null : coordinate(sticky.y) + dy });
        stickies.push(id);
    }

    return { graph: next, nodes, stickies, highest };
}
