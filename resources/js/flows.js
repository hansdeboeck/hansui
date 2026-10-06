/*
| De flow-editor van HansUI (<x-flow-editor>) starten: elk element met
| data-flow-editor op de pagina.
|
| Apart van hansui.js, en dus apart op te nemen, want de meeste schermen
| tekenen geen flow:
|
|   import '../../vendor/hansdeboeck/hansui/resources/js/flows.js';
|
| Een stuk pagina dat later binnenkomt (een venster dat apart laadt), start
| ze met initFlows(element). Wie iets aan een formulier van een stap wil
| doen (opties wegfilteren naargelang de start), luistert op `flows:form`;
| elke wijziging is een `flows:change` (allebei op het element van de editor,
| en ze borrelen op naar document).
*/

import { initFlows } from './flows/editor.js';

export { FlowEditor, editorFor, initFlows } from './flows/editor.js';
export * as graph from './flows/graph.js';

if (typeof document !== 'undefined') {
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => initFlows());
    } else {
        initFlows();
    }
}
