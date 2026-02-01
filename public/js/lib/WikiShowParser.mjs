class WikiShowParser {
    #data;

    get data() {
        return this.#data;
    }

    constructor() {
        this.reset();
    }

    static createParser() {
        return new WikiShowParser();
    }

    static #createObject() {
        return Object.create(null);
    }

    #parseTable(base, el) {
        for (const show of el.iq('tbody tr')) {
            console.log(show);
            let infos = show.iq('td');
            if (infos.length > 0) {
                let info = WikiShowParser.#createObject();
                info['name'] = infos[0].iqs('a, i').textContent;
                info['Genre'] = (infos[1].iqs('a') || infos[1]).textContent;
                base[info['name']] = info;
            }
        }
    }

    #parseH4(base, el) {
        let label = el.iqs('h'.concat(Array.from(el.classList).pop().slice(-1))).textContent;
        base[label] = WikiShowParser.#createObject();

        let next = el.nextElementSibling;
        if (next.nodeName === 'TABLE') this.#parseTable(base[label], next);
    }

    #parseH3(base, el) {
        let label = el.iqs('h'.concat(Array.from(el.classList).pop().slice(-1))).textContent;
        base[label] = WikiShowParser.#createObject();

        let next = el.nextElementSibling;
        if (next.nodeName === 'P') next = next.nextElementSibling;
        if (next.classList.contains('mw-heading4')) this.#parseH4(base[label], next);
        else if (next.nodeName === 'TABLE') this.#parseTable(base[label], next);
    }

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

    reset() {
        this.#data = WikiShowParser.#createObject();
    }
}

export {WikiShowParser};
