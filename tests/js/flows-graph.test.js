/*
| De graaf in de browser (resources/js/flows/graph.js) en de hulpjes van de editor.
|
| WAT DIT BEWAAKT: layout(), reachable() en ordered() komen uit wat
| tests/fixtures/flows-layout.json zegt, net als src/Flows/Layout.php; een
| verbinding die een lus zou maken of naar de start gaat, wordt geweigerd
| voor ze getekend is; een stap weghalen sluit de reeks; een stap ertussen
| zetten schuift wat erna kwam erachter.
*/

import { test } from 'node:test';
import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';

import * as G from '../../resources/js/flows/graph.js';
import { format, summarize, whenMatches } from '../../resources/js/flows/editor.js';

const fixture = JSON.parse(readFileSync(new URL('../fixtures/flows-layout.json', import.meta.url), 'utf8'));

const asTypes = (types) => types.map((type) => ({ key: type.key, label: type.key, start: type.start, branches: type.branches ?? null, outputs: type.outputs.map((key) => ({ key, label: '' })) }));

const graphOf = (nodes, edges = []) => G.parse({
    nodes: nodes.map(([id, type, config]) => ({ id, type, config: config ?? {} })),
    edges: edges.map(([from, port, to]) => ({ from, port, to })),
});

const types = asTypes(fixture.types);

for (const item of fixture.cases) {
    test(`schikt zoals de server: ${item.name}`, () => {
        const graph = graphOf(item.nodes, item.edges);

        assert.deepEqual(Object.fromEntries(G.layout(graph, types)), item.positions);
        assert.deepEqual(G.reachable(graph, types), item.reachable);
        assert.deepEqual(G.ordered(graph, types).map((node) => node.id), item.ordered);
    });
}

const branching = () => graphOf(
    [['start', 'trigger'], ['c', 'condition'], ['a', 'task'], ['b', 'wait'], ['m', 'task']],
    [['start', 'out', 'c'], ['c', 'yes', 'a'], ['c', 'no', 'b'], ['a', 'out', 'm'], ['b', 'out', 'm']],
);

test('leest wat de server gaf, en laat weg wat er niet in hoort', () => {
    const graph = G.parse(JSON.stringify({
        nodes: [
            { id: 'start', type: 'trigger', config: { event: 'deal.won' }, x: 10.4, y: '20' },
            { id: 'start', type: 'task' },
            { id: 'a', type: 'task', config: ['geen', 'object'] },
            { type: 'task' },
        ],
        edges: [
            { from: 'start', port: 'out', to: 'a' },
            { from: 'start', port: 'out', to: 'ghost' },
            { from: 'a', port: 'out', to: 'a' },
            { from: 'start', port: 'out', to: 'a' },
        ],
    }));

    assert.deepEqual(graph.nodes, [
        { id: 'start', type: 'trigger', config: { event: 'deal.won' }, x: 10, y: null },
        { id: 'a', type: 'task', config: {}, x: null, y: null },
    ]);
    assert.deepEqual(graph.edges, [{ from: 'start', port: 'out', to: 'a' }]);
    assert.deepEqual(G.parse('kapot').nodes, []);
    assert.deepEqual(G.parse(G.serialize(graph)), graph);
});

test('weigert wat de server ook zou weigeren', () => {
    const graph = branching();

    assert.equal(G.canConnect(graph, types, 'm', 'out', 'c'), 'cycle');
    assert.equal(G.canConnect(graph, types, 'm', 'out', 'start'), 'start');
    assert.equal(G.canConnect(graph, types, 'm', 'out', 'm'), 'self');
    assert.equal(G.canConnect(graph, types, 'm', 'maybe', 'a'), 'port');
    assert.equal(G.canConnect(graph, types, 'm', 'out', 'ghost'), 'missing');
    assert.equal(G.canConnect(graph, types, 'c', 'yes', 'm'), null);
});

test('een uitgang heeft een verbinding: wie opnieuw verbindt, vervangt', () => {
    const graph = G.connect(branching(), 'c', 'yes', 'm');

    assert.equal(G.target(graph, 'c', 'yes'), 'm');
    assert.equal(graph.edges.filter((edge) => edge.from === 'c' && edge.port === 'yes').length, 1);
    assert.equal(G.target(G.disconnect(graph, 'c', 'yes'), 'c', 'yes'), null);
});

test('een stap weghalen sluit de reeks, zoals Graph::withoutNode', () => {
    const line = graphOf([['start', 'trigger'], ['a', 'task'], ['b', 'wait'], ['c', 'task']], [['start', 'out', 'a'], ['a', 'out', 'b'], ['b', 'out', 'c']]);

    assert.equal(G.target(G.removeNode(line, types, 'b'), 'a', 'out'), 'c');
    assert.equal(G.target(G.removeNode(line, types, 'b', false), 'a', 'out'), null);
    assert.equal(G.target(G.removeNode(branching(), types, 'c'), 'start', 'out'), 'a');
    assert.equal(G.removeNode(line, types, 'ghost'), line);
});

test('een nieuwe stap na een uitgang die al verbonden is, komt ertussen', () => {
    const graph = G.insertAfter(branching(), types, 'c', 'yes', { id: 'n9', type: 'wait' });

    assert.equal(G.target(graph, 'c', 'yes'), 'n9');
    assert.equal(G.target(graph, 'n9', 'out'), 'a');

    const end = G.insertAfter(branching(), types, 'm', 'out', { id: 'n9', type: 'stop' });

    assert.equal(G.target(end, 'm', 'out'), 'n9');
    assert.deepEqual(G.outgoing(end, types, 'n9'), []);
});

test('een id dat nog vrij is', () => {
    assert.equal(G.nextId(branching()), 'n6');
    assert.equal(G.nextId(graphOf([['start', 'trigger'], ['n7', 'task'], ['12', 'task']])), 'n8');
    assert.equal(G.nextId(branching(), 'n', 9), 'n10', 'nooit lager dan het hoogste nummer dat er al was');
});

test('wat nog niet af is, in dezelfde volgorde als Graph::issues', () => {
    const graph = graphOf([['start', 'trigger'], ['a', 'task', { title: '' }], ['loose', 'wait']], [['start', 'out', 'a']]);
    const issues = G.issues(graph, types, {
        required: (node) => (node.type === 'task' && !node.config.title ? ['Nog in te vullen: Titel'] : []),
        strings: { unreachable: 'Hangt nergens aan.' },
    });

    assert.deepEqual(Object.fromEntries(issues), { a: ['Nog in te vullen: Titel'], loose: ['Hangt nergens aan.'] });

    const alone = G.issues(graphOf([['start', 'trigger']]), types);

    assert.deepEqual(Object.fromEntries(alone), { start: ['Nog geen volgende stap.'] });
});

test('schikt alles, of alleen wat nog geen plaats had', () => {
    const graph = G.updateNode(branching(), 'c', { x: 5, y: 5 });

    assert.deepEqual(G.findNode(G.arrange(graph, types), 'c'), { id: 'c', type: 'condition', config: {}, x: G.COLUMN, y: 0 });
    assert.deepEqual(G.findNode(G.arrange(graph, types, false), 'c'), { id: 'c', type: 'condition', config: {}, x: 5, y: 5 });
});

test('tekent een bocht van een uitgang naar een ingang', () => {
    const graph = G.arrange(branching(), types);
    const from = G.outPoint(types, G.findNode(graph, 'c'), 'no');
    const to = G.inPoint(G.findNode(graph, 'b'));

    assert.deepEqual(from, { x: G.COLUMN + G.NODE_WIDTH, y: (G.NODE_HEIGHT * 2) / 3 });
    assert.deepEqual(to, { x: 2 * G.COLUMN, y: 150 + G.NODE_HEIGHT / 2 });
    assert.equal(G.edgePath({ x: 0, y: 0 }, { x: 100, y: 50 }), 'M 0 0 C 50 0, 50 50, 100 50');
    assert.deepEqual(G.edgeMiddle({ x: 0, y: 0 }, { x: 100, y: 50 }), { x: 50, y: 25 });
    assert.deepEqual(G.bounds(graph), { minX: 0, minY: 0, maxX: 3 * G.COLUMN + G.NODE_WIDTH, maxY: 150 + G.NODE_HEIGHT });
    assert.deepEqual(G.freeSpot(graph, 2 * G.COLUMN, 0), { x: 2 * G.COLUMN, y: 300 });
    assert.equal(G.snap(17), 20);
});

test('toont een veld alleen als de config het zegt', () => {
    assert.equal(whenMatches('operator:empty,filled', { operator: 'filled' }), true);
    assert.equal(whenMatches('operator!:empty,filled', { operator: 'filled' }), false);
    assert.equal(whenMatches('operator!:empty,filled', { operator: '>' }), true);
    assert.equal(whenMatches('workdays', { workdays: true }), true);
    assert.equal(whenMatches('workdays', { workdays: '0' }), false);
    assert.equal(whenMatches('users:3', { users: ['1', '3'] }), true);
    assert.equal(whenMatches('kapot ding', {}), true);
});

test('vult :namen in, de langste eerst', () => {
    assert.equal(format('Stap toevoegen na :stap (:stapnaam)', { stap: 'Taak', stapnaam: 'yes' }), 'Stap toevoegen na Taak (yes)');
    assert.equal(format(':aantal stappen', { aantal: 3 }), '3 stappen');
});

test('vat een stap samen uit een patroon, en laat een leeg deel weg', () => {
    const values = { amount: '2', unit: 'dagen', workdays: '', title: 'Bellen {naam}' };
    const display = (key) => values[key] ?? '';

    assert.equal(summarize('{amount} {unit}[, {workdays}]', display), '2 dagen');
    values.workdays = 'op werkdagen';
    assert.equal(summarize('{amount} {unit}[, {workdays}]', display), '2 dagen, op werkdagen');
    assert.equal(summarize('{title} · {to}', display), 'Bellen {naam} · ', 'wat een veld toont, wordt niet opnieuw ingevuld');
});

test('haalt de takken van een splitsing uit haar config, voor de vaste uitgangen', () => {
    const split = (values) => ({ id: 's', type: 'split', config: { values }, x: 0, y: 0 });

    assert.deepEqual(G.outputs(types, split(['high', 'urgent'])).map((output) => output.key), ['high', 'urgent', 'other']);
    assert.deepEqual(G.outputs(types, split([])).map((output) => output.key), ['other']);
    assert.deepEqual(
        G.outputs(types, split(['a', '12', 'Hoog', 'a', 'other', 'b-2', 'c', 'd', 'e'])).map((output) => output.key),
        ['a', 'b-2', 'c', 'd', 'other'],
        'geen naam, dubbel, de vaste of een vijfde tak telt niet',
    );

    // Met veel uitgangen is een stap hoger, en de uitgangen staan verdeeld over die hoogte.
    assert.equal(G.nodeHeight(types, split(['a'])), G.NODE_HEIGHT);
    assert.equal(G.nodeHeight(types, split(['a', 'b'])), 24 * 4, 'drie uitgangen: elk 24px, zodat hun namen niet op elkaar staan');
    assert.equal(G.nodeHeight(types, split(['a', 'b', 'c', 'd'])), 24 * 6);
    assert.deepEqual(G.outPoint(types, split(['a', 'b', 'c', 'd']), 'other'), { x: G.NODE_WIDTH, y: 24 * 5 });
    assert.deepEqual(G.inPoint(split(['a', 'b', 'c', 'd']), types), { x: 0, y: 72 });
});

test('een tak die wegvalt, verliest zijn verbinding', () => {
    let graph = graphOf(
        [['start', 'trigger'], ['s', 'split', { values: ['high', 'urgent'] }], ['a', 'task'], ['b', 'task'], ['c', 'task']],
        [['start', 'out', 's'], ['s', 'high', 'a'], ['s', 'urgent', 'b'], ['s', 'other', 'c']],
    );

    assert.equal(G.canConnect(graph, types, 's', 'low', 'c'), 'port');
    assert.equal(G.prunePorts(graph, types, 's'), graph, 'niets weg te halen: dezelfde graaf');

    graph = G.prunePorts(G.updateNode(graph, 's', { config: { values: ['high'] } }), types, 's');

    assert.equal(G.target(graph, 's', 'urgent'), null);
    assert.equal(G.target(graph, 's', 'high'), 'a');
    assert.equal(G.target(graph, 's', 'other'), 'c');
});

test('leest en schrijft notities, en schrijft er geen als er geen zijn', () => {
    const graph = G.parse({
        nodes: [{ id: 'start', type: 'trigger' }],
        stickies: [
            { id: 's1', text: 'Eerst bellen', x: 10.4, y: 300 },
            { id: 's1', text: 'dubbel' },
            { id: 'een notitie', text: 'geen geldig id' },
            { text: 'zonder id' },
            { id: 's2', text: ['geen tekst'] },
        ],
    });

    assert.deepEqual(graph.stickies, [{ id: 's1', text: 'Eerst bellen', x: 10, y: 300 }, { id: 's2', text: '', x: null, y: null }]);
    assert.deepEqual(JSON.parse(G.serialize(graph)).stickies, graph.stickies);
    assert.equal('stickies' in JSON.parse(G.serialize(G.parse({ nodes: [] }))), false);

    let next = G.addSticky(graph, { id: G.nextStickyId(graph), text: 'Nieuw', x: 0, y: 0 });
    assert.deepEqual(next.stickies.map((sticky) => sticky.id), ['s1', 's2', 's3']);

    next = G.removeSticky(G.updateSticky(next, 's3', { text: 'Anders' }), 's1');
    assert.deepEqual(next.stickies.map((sticky) => `${sticky.id}:${sticky.text}`), ['s2:', 's3:Anders']);
    assert.deepEqual(G.bounds(G.parse({ nodes: [], stickies: [{ id: 's1', x: 100, y: 50 }] })), { minX: 100, minY: 50, maxX: 100 + G.STICKY_WIDTH, maxY: 50 + G.NODE_HEIGHT });
});

test('kopieert stappen met hun verbindingen, en plakt ze met nieuwe ids', () => {
    const graph = G.arrange(G.addSticky(branching(), { id: 's1', text: 'Notitie', x: 0, y: 400 }), types);
    const clip = G.extract(graph, types, ['start', 'c', 'a', 'm'], ['s1']);

    assert.deepEqual(clip.nodes.map((node) => node.id), ['c', 'a', 'm'], 'de start gaat niet mee');
    assert.deepEqual(clip.edges.map((edge) => `${edge.from}:${edge.port}:${edge.to}`), ['c:yes:a', 'a:out:m'], 'alleen wat tussen de gekozen stappen ligt');

    const pasted = G.paste(graph, types, clip, { dx: 40, dy: 40, after: 5 });

    assert.deepEqual(pasted.nodes, ['n6', 'n7', 'n8']);
    assert.deepEqual(pasted.stickies, ['s2']);
    assert.equal(pasted.highest, 8);
    assert.equal(G.target(pasted.graph, 'n6', 'yes'), 'n7');
    assert.equal(G.target(pasted.graph, 'n7', 'out'), 'n8');
    assert.equal(G.target(pasted.graph, 'n6', 'no'), null);
    assert.deepEqual([G.findNode(pasted.graph, 'n6').x, G.findNode(pasted.graph, 'n6').y], [G.findNode(graph, 'c').x + 40, G.findNode(graph, 'c').y + 40]);
    assert.equal(pasted.graph.nodes.length, graph.nodes.length + 3);

    // Een soort die deze flow niet kent of waarvan er al genoeg zijn, valt weg; een start ook.
    const foreign = G.paste(graph, types, { nodes: [{ id: 'x', type: 'robot' }, { id: 'y', type: 'trigger' }, { id: 'z', type: 'task', config: ['geen object'] }], edges: [{ from: 'x', port: 'out', to: 'z' }] });

    assert.deepEqual(foreign.nodes, ['n6']);
    assert.deepEqual(G.findNode(foreign.graph, 'n6').config, {});
    assert.deepEqual(G.paste(graph, types, null).nodes, []);
});
