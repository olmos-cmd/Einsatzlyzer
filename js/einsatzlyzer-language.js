(() => {
    'use strict';
    const config = window.fflEinsatzlyzerLanguage || {};
    if (config.language !== 'en' || !config.translations) return;
    const dict = config.translations;
    const protectedSelector = [
        '#title', '#content', '.editor-post-title', '.block-editor-rich-text__editable',
        'textarea[name="content"]', 'input[name="post_title"]',
        '[data-ffl-user-content]', '.ffl-user-content'
    ].join(',');

    const translateExact = (value) => {
        if (!value || !value.trim()) return value;
        const leading = value.match(/^\s*/)[0];
        const trailing = value.match(/\s*$/)[0];
        const core = value.trim();
        if (Object.prototype.hasOwnProperty.call(dict, core)) {
            return leading + dict[core] + trailing;
        }
        const countMatch = core.match(/^(Alle|Veröffentlicht|Entwurf|Entwürfe|Ausstehend|Privat|Papierkorb)\s*(\(\d+\))$/);
        if (countMatch) {
            const labels = {Alle:'All',Veröffentlicht:'Published',Entwurf:'Draft',Entwürfe:'Drafts',Ausstehend:'Pending',Privat:'Private',Papierkorb:'Trash'};
            return leading + labels[countMatch[1]] + ' ' + countMatch[2] + trailing;
        }
        return value;
    };

    const isProtected = (element) => element && element.closest && element.closest(protectedSelector);

    const translateElement = (root) => {
        if (!root || root.nodeType !== 1 || isProtected(root)) return;
        const walker = document.createTreeWalker(root, NodeFilter.SHOW_TEXT, {
            acceptNode(node) {
                const parent = node.parentElement;
                if (!parent || isProtected(parent) || ['SCRIPT','STYLE','TEXTAREA','CODE'].includes(parent.tagName)) {
                    return NodeFilter.FILTER_REJECT;
                }
                return NodeFilter.FILTER_ACCEPT;
            }
        });
        let node;
        while ((node = walker.nextNode())) {
            const next = translateExact(node.nodeValue);
            if (next !== node.nodeValue) node.nodeValue = next;
        }
        root.querySelectorAll('[aria-label],[title],[placeholder],[value]').forEach((el) => {
            if (isProtected(el)) return;
            ['aria-label','title','placeholder'].forEach((attr) => {
                if (el.hasAttribute(attr)) el.setAttribute(attr, translateExact(el.getAttribute(attr)));
            });
            if (['BUTTON','INPUT'].includes(el.tagName) && el.value) el.value = translateExact(el.value);
        });
    };

    const run = () => translateElement(document.body);
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', run); else run();

    const observer = new MutationObserver((mutations) => {
        mutations.forEach((mutation) => {
            mutation.addedNodes.forEach((node) => {
                if (node.nodeType === 1) translateElement(node);
            });
        });
    });
    const start = () => document.body && observer.observe(document.body, {subtree:true, childList:true});
    if (document.body) start(); else document.addEventListener('DOMContentLoaded', start);
})();
