/** Parses programming-section show tables from the current MediaWiki document. */
class WikiShowParser {
    #data;

    /**
     * Parsed data indexed by programming section, heading, and show name.
     *
     * @readonly
     * @type {Object}
     */
    get data() {
        return this.#data;
    }

    /** Creates a parser with an empty result. */
    constructor() {
        this.reset();
    }

    /**
     * Creates a new parser instance.
     *
     * @static
     * @returns {WikiShowParser}
     */
    static createParser() {
        return new WikiShowParser();
    }

    /**
     * Creates a dictionary without a prototype for parsed values.
     *
     * @static
     * @returns {Object}
     */
    static #createObject() {
        return Object.create(null);
    }

    /**
     * Adds each table row to the supplied dictionary, using its first cell's
     * linked or italicised text as the show name.
     *
     * @param {Object} base Dictionary that receives the parsed shows.
     * @param {Element} el Table whose body rows contain show information.
     */
    #parseTable(base, el) {
        for (const show of el.iq('tbody tr')) {
            let infos = show.iq('td');
            if (infos.length > 0) {
                let info = WikiShowParser.#createObject();
                info['name'] = infos[0].iqs('a, i').textContent;
                info['Genre'] = (infos[1].iqs('a') || infos[1]).textContent;
                base[info['name']] = info;
            }
        }
    }

    /** Parses a level-four heading and its immediately following table. */
    #parseH4(base, el) {
        let label = el.iqs('h'.concat(Array.from(el.classList).pop().slice(-1))).textContent;
        base[label] = WikiShowParser.#createObject();

        let next = el.nextElementSibling;
        if (next.nodeName === 'TABLE') this.#parseTable(base[label], next);
    }

    /**
     * Parses a level-three heading and its next level-four heading or table.
     *
     * @param {Object} base Dictionary that receives the heading's contents.
     * @param {Element} el Heading wrapper and its associated content.
     */
    #parseH3(base, el) {
        let label = el.iqs('h'.concat(Array.from(el.classList).pop().slice(-1))).textContent;
        base[label] = WikiShowParser.#createObject();

        let next = el.nextElementSibling;
        if (next.nodeName === 'P') next = next.nextElementSibling;
        if (next.classList.contains('mw-heading4')) this.#parseH4(base[label], next);
        else if (next.nodeName === 'TABLE') this.#parseTable(base[label], next);
    }

    /**
     * Parses programming sections in the current document into {@link data}.
     *
     * @returns {this}
     */
    parseWiki() {
        for (const state of iq('.mw-heading.mw-heading2').filter(el => el.iqs('h2').textContent.endsWith('programming'))) {
            let label = state.iqs('h'.concat(Array.from(state.classList).pop().slice(-1))).textContent;
            this.#data[label] = WikiShowParser.#createObject();

            let sibling = state.nextElementSibling;
            while (sibling && !sibling.classList.contains('mw-heading2')) {
                sibling = sibling.nextElementSibling;
                if (sibling.classList.contains('mw-heading3')) this.#parseH3(this.#data[label], sibling);
            }
        }

        return this;
    }

    /** Clears all previously parsed data. */
    reset() {
        this.#data = WikiShowParser.#createObject();
    }
}

export {WikiShowParser};
