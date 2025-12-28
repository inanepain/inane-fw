/**
 * Extend HTML
 *
 * @author Philip Michael Raab<philip@cathedral.co.za>
 * @version 1.4.0
 */

/**
 * HISTORY
 *
 * 1.4.0: 2025 May 22
 *  - iq        : gets *@@* to return element if only single match
 *
 * 1.3.2: 2022 Apr 08
 *  - iqs, iqsa : re-worked for simplicity
 *  - iq        : no longer uses iqs or iqsa but it's own optimised code
 *  - jsdoc     : updated
 *
 * 1.3.1: 2022 Jan 14
 *  - iqsa also now returns an array instead of HTMLCollection or NodeList
 *
 * 1.3.0: 2021 Nov 12
 *  - iq acting as `iqsa` now returns an array instead of HTMLCollection or NodeList
 *
 * 1.2.0: 2021 Oct 13
 *  - Added ShadowRoot to items that get Inane Query Shortcuts
 */

/**
 * Collections
 */
for (let element of [HTMLCollection, NodeList]) {
    // test for toArray
    if (!element.prototype.toArray) {
        /**
         * Converts the collection to an array.
         *
         * @since 1.0.0
         *
         * @returns {Array}
         */
        element.prototype.toArray = function() {
            return Array.from(this);
        }
    }
}

/**
 * Items
 */
for (let element of [Document, HTMLElement, ShadowRoot, HTMLDocument]) {
    // test for iqs
    if (!element.prototype.iqs) {
        /**
         * modified querySelector
         *
         * @since 1.0.0
         *
         * @param {DOMString} selectors A DOMString containing one or more selectors to match
         *
         * @returns {null|Element} An Element representing the first match or null if no match
         */
        element.prototype.iqs = function(selectors) {
            const el = this?.querySelector ? this : window.document;
            return el.querySelector(selectors);
        }
    }

    // assign global iqs
    if (!window.iqs && window.document.iqs) window.iqs = window.document.iqs;

    // test for iqsa
    if (!element.prototype.iqsa) {
        /**
         * modified querySelectorAll
         *
         * @since 1.1.0
         *
         * @param {DOMString} selectors A DOMString containing one or more selectors to match
         *
         * @returns {Element[]} An Element array containing all matches
         */
        element.prototype.iqsa = function(selectors) {
            const el = this?.querySelectorAll ? this : window.document;
            return Array.from(el.querySelectorAll(selectors));
        }
    }

    // assign global iqsa
    if (!window.iqsa && window.document.iqsa) window.iqsa = window.document.iqsa;

    // test for iq
    if (!element.prototype.iq) {
        /**
         * Unified Query method
         *
         * Using either call, apply or bind to set this to an HTMLElement
         *  will restrict the query to it's children.
         *
         * Prefixing the selectors string with:
         *  `@` uses `querySelector` (return first element if multipule matches).
         *  `@@` uses `querySelectorAll` but if only one match a single item is returned.
         *
         * @since 1.1.1
         *
         * @param {DOMString|string} selectors A DOMString containing one or more selectors to match
         *
         * @returns {null|Element|Element[]} An Element representing the first match, an Element array containing all matches or null if nothing matched
         */
        element.prototype.iq = function(selectors) {
            const dynamic = selectors.startsWith('@@');
            if (dynamic) selectors = selectors.substring(2);

            const cmd = selectors.startsWith('@') || selectors.split(' ').pop().charAt(0) === "#" && !selectors.includes(',') ? 'querySelector' : 'querySelectorAll';
            if (selectors.startsWith('@')) selectors = selectors.substring(1);
            const el = this?.[cmd] ? this : window.document;

            result = el[cmd](selectors);
            result = cmd == 'querySelector' ? result : Array.from(result);
            if (dynamic) return Array.isArray(result) ? (result.length == 1 ? result.pop() : result) : result;
            return result;
        }
    }

    // assign global iq
    if (!window.iq && window.document.iq) window.iq = window.document.iq;
}
