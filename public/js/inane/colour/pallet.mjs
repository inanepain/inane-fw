const colourData = {
    paletteName : 'Inane Colours v4',
    colors : [
            '#a22c16',
            '#c6291c',
            '#eb4030',
            '#c74844',
            '#ef7c37',
            '#f19737',
            '#f4b13e',
            '#f5bb41',
            '#ead146',
            '#f9d348',
            '#fff99f',
            '#fffa54',
            '#d5e056',
            '#d7fc51',
            '#c8f2a5',
            '#73f44a',
            '#67c94d',
            '#4eab31',
            '#58a02e',
            '#49a259',
            '#c1e7fc',
            '#72f9fd',
            '#73ddd0',
            '#3e94d8',
            '#53bef9',
            '#003ef5',
            '#2b61b7',
            '#7a84f7',
            '#cccdfb',
            '#978cb7',
            '#534a7a',
            '#561682',
            '#882f8d',
            '#812df5',
            '#9055a9',
            '#cba0ef',
            '#eb58f7',
            '#f3afbb',
            '#f6c6a6',
            '#ab682f',
            '#a47a4b',
            '#8d4e1a',
            '#080808',
            '#303030',
            '#585858',
            '#808080',
            '#919191',
            '#c0c0c0',
            '#dadada',
            '#f5f5f5',
            '#ffffff',
            '#1b1b1b',
            '#000000'
        ]
}

/**
 * ColourTool
 */
class ColourTool {
    /**
     * Version
     *
     * @readonly
     * @static
     * @type {string}
     */
    static get VERSION() {
        return '1.0.0-dev';
    }
    /**
     * Version
     *
     * @readonly
     * @type {string}
     * @ignore
     */
    get VERSION() {
        return this.constructor.VERSION;
    }

    /**
     * Colour Pallets
     */
    #pallets = new Map();

    /**
     * ColourTool
     */
    constructor() {

    }

    /**
     * Loads a pallet
     *
     * @param {object} pallet colour pallet
     */
    loadPallet({ paletteName, colors}) {
        if (!this.#pallets.has(paletteName)) {
            let d = {};
            for (const p of colors.map(c => {
                return { dec: parseInt(c.replace('#', ''), 16), hex: c };
            })) d[p.dec] = p;

            d = Object.values(d);
            this.#pallets.set(paletteName, d);
        }
    }

    pallet(paletteName) {
        if (this.#pallets.has(paletteName)) return {
            paletteName: paletteName,
            colours: this.#pallets.get(paletteName),
        };

        return null;
    }
}

const ct = new ColourTool();
ct.loadPallet(colourData);

const p = ct.pallet('Inane Colours v4');

console.log(p);
