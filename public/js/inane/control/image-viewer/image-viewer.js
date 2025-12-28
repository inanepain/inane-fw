import { Dumper } from '../../dumper.js';

/**
 * HISTORY
 *
 * 1.4.0: 2021 11 15
 *  - Added - Options.placeholder: loads when no images found
 *
 * 1.3.0: 2021 11 14
 *  - simplified options internally
 *  - added `options.info.formatter` for customising the filename display in the info box. It's passed the filename and returns the display string
 *
 * 1.2.0: 2021 11 05
 *  - added option `showName` to show file name in info box. default: false
 *
 * 1.1.0: 2021 10 27
 *  - added option for word search `wholeWord`, default: false
 */

/**
 * Return object with only specified properties
 *
 * @since 1.3.0
 *
 * @param obj Source Object
 * @param properties Property or Properties to return
 * @returns {object}
 */
const pick = (obj, properties) => {
    if (!properties) return;
    if (!Array.isArray(properties) && (typeof properties == "string")) properties = [properties];

    const picked = {};
    properties.forEach(prop => {
        if (obj.hasOwnProperty(prop)) picked[prop] = obj[prop];
    });

    return picked;
}

/**
 * Adds ONLY missing properties from source objects to target in decreasing priority
 *
 * @since 1.3.0
 *
 * @example
 * // copy values from defaults missing in options to options
 * mergeOptions(options, defaults);
 *
 * @param {Object} target the target object
 * @param {...Object} source the source objects in decreasing order of priority
 *
 * @ignore
 */
const mergeOptions = (target, ...source) => {
    var key, i;
    for (i = 0; i < source.length; i++)
        for (key in source[i])
            if (!(key in target) && source[i].hasOwnProperty(key)) target[key] = source[i][key];
            else
                try { // If we are dealing with child objects here we simple dive into them to process the whole object
                    if (target[key].constructor === Object && source[i][key].constructor === Object) mergeOptions(target[key], source[i][key]);
                } catch (error) { // If target has undefined or null we catch the error and set the value
                    if (error.message.includes('target[key].constructor')) target[key] = source[i][key];
                }
}

/**
 * Viewer Options
 * @typedef ViewerOptions
 * @property {string} [path=/img] image path
 * @property {string} [viewer=#viewer] image tag selector
 * @property {string} [placeholder] default image on load  or empty
 * @property {object} [info] info block options
 * @property {string} [info.el] info element selector
 * @property {Boolean} [info.showName=false] Show file name in info box
 * @property {function} [info.formatter] Takes file name so it can be formatted for display
 * @property {string} [filter] filter images
 * @property {Boolean} [wholeWord] match whole words (broken by _-.)
 * @property {string|number} [debugLevel] debug level
 */

/**
 * ImageViewer
 *
 * @version 1.5.0
 */
class ImageViewer {
    #images = [];
    #imgIndex = 0;

    #con = new XMLHttpRequest();

    #logger = Dumper.get('ImageViewer');

    /**
     * @since 1.3.0
     *
     * @type {ViewerOptions}
     */
    #options = {
        path: '/img',
        viewer: '#viewer',
        placeholder: 'data:image/svg+xml;base64,PHN2ZyB2ZXJzaW9uPSIxLjIiIGJhc2VQcm9maWxlPSJ0aW55IiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAxMTEwIDExMTAiIHByZXNlcnZlQXNwZWN0UmF0aW89Im5vbmUiPgogICAgPHJlY3Qgd2lkdGg9IjEwMCUiIGhlaWdodD0iMTAwJSIgZmlsbD0iI2YwZjBmMCIgLz4KICAgIDxnIHN0cm9rZT0iIzg0MDBmZiIgc3Ryb2tlLWxpbmVjYXA9InJvdW5kIiBzdHJva2UtbWl0ZXJsaW1pdD0iMTAiIGZpbGw9Im5vbmUiIHN0cm9rZS13aWR0aD0iMTEwIj4KICAgICAgICA8cGF0aCBkPSJNNTYuODQ4IDEwMzcuMTljMTUuMzk5LTQyMy45NiAxMjMuNzMxLTcwMC4zMTQgNC40NTctOTgyLjE0bS02LjI1NS41MjljMjgxLjI0NiAyMy40NCA1MjUuODcxIDQ5OS43MyA1MTUuNDE0IDQ5OS40MzIuMTM0IDUuOTI1IDIyMS4zNjggNDI5LjYyMyA0NzQuNDM3IDQ5Mi41NThtLTkuMzUzLTk5MS43OTFjMTAuMzQ5IDIzNS4zMTMtMTI5Ljg5NyA2NDIuNTQgOC45ODggOTg5Ljg1IiAvPgogICAgICAgIDxwYXRoIGQ9Ik01NzIuNDUyIDEwNi45MzZzMy43NTYgNjMuODQzIDQuNTY0IDExNC41ODRjMS40ODMgOTMuMDMyLTguMzAxIDE3Ni4zNDEtOS4xNyAyOTQuNjYzLS44NzUgMTE4Ljk4Ny0xNC43ODcgMjEyLjg1NC0uNDY1IDQ4NC40NTciIHN0cm9rZS1taXRlcmxpbWl0PSIxIiAvPgogICAgPC9nPgo8L3N2Zz4K',
        filter: undefined,
        wholeWord: false,
        info: {
            el: undefined,
            showName: false,
            formatter: (name) => {
                return name;
            }
        }
    }

    /**
     * Version
     *
     * @readonly
     * @static
     * @type {string}
     */
    static get VERSION() {
        return '1.4.0';
    }
    /**
     * Version
     *
     * @readonly
     * @type {string}
     */
    get VERSION() {
        return this.constructor.VERSION;
    }

    /**
     * Document Query shortcut based in inane's iq
     *
     * Using either call, apply or bind to set this to an HTMLElement
     *  will restrict the query to it's children.
     *
     * Prefixing the selectors string with an `@` sign denotes the use of `querySelector`
     *
     * @param {string} selectors A DOMString containing one or more selectors to match
     *
     * @returns {null|Element|Element[]} An Element representing the first match, an Element array containing all matches or null if nothing matched
     */
    #iq(selectors) {
        const cmd = selectors.startsWith('@') || selectors.split(' ').pop().charAt(0) === "#" && !selectors.includes(',') ? 'querySelector' : 'querySelectorAll';
        if (selectors.startsWith('@')) selectors = selectors.replace('@', '');
        const el = this?.[cmd] ? this : window.document;

        result = el[cmd](selectors);
        return cmd == 'querySelector' ? result : Array.from(result);
    }

    /**
     * ImageViewer
     *
     * @param {ViewerOptions} param0
     */
    constructor({ path, viewer, filter, wholeWord = false, info = { showName: false }, debugLevel } = {}) {
        this.#options = this.#parseOptions((arguments[0] || {}));

        if (debugLevel) this.#logger.level = debugLevel;

        this.#con.onload = this.#onLoad.bind(this);
        this.#con.onerror = this.#onError.bind(this);

        document.addEventListener(`keydown`, event => {
            const left = -1, right = 1;

            if (event.code == `ArrowLeft`) this.changeImage(left);
            else if (event.code == `ArrowRight`) this.changeImage(right);

            return false;
        });
    }

    /**
     * Parse options
     *
     * @since 1.3.0
     *
     * @param {object} options options
     * @returns {object}
     */
    #parseOptions(options) {
        let tmp;
        tmp = pick(options, Object.keys(this.#options));
        mergeOptions(tmp, this.#options);

        if (tmp.filter) tmp.filter = tmp.filter.toLowerCase();
        if (!tmp.path.endsWith('/')) tmp.path += '/';

        return tmp;
    }

    /**
     * Fetch Images
     *
     * @param {ViewerOptions} param0
     */
    fetchImages({ filter, wholeWord = false } = { filter: undefined, wholeWord: false }) {
        this.#options = this.#parseOptions((arguments[0] || {}));

        this.#images = [];
        this.#imgIndex = 0;

        this.#con.open(`GET`, this.#options.path);

        this.#con.send();
    }

    /**
     * Change to image at index
     *
     * @param {number} indexOffset image index
     */
    changeImage(indexOffset) {
        this.#imgIndex += indexOffset;

        if (this.#imgIndex >= this.#images.length) this.#imgIndex = 0;
        else if (this.#imgIndex < 0) this.#imgIndex = this.#images.length - 1;

        const name = this.#images[this.#imgIndex];
        const title = this.#options.info.showName ? this.#options.info.formatter(name) : '';

        iqs(this.#options.viewer).src = this.#options.path + name;
        if (this.#options?.info?.el) iqs(this.#options.info.el).textContent = (this.#options.info.showName === true ? `${title}: ` : '') + `${(this.#imgIndex + 1)} / ${this.#images.length}`;
    }

    /**
     * Load Handler
     */
    #onLoad() {
        if (this.#con.status == 200) {
            const data = document.createElement(`div`);
            data.innerHTML = this.#con.response;
            data.iqsa(`a[href]`).forEach(img => {
                let href = img.href.split(`/`).pop();
                let testValue = href.toLowerCase();
                if (testValue.indexOf(`.jpg`) > 0 || testValue.indexOf(`.png`) > 0 || testValue.indexOf(`.jpeg`) > 0) {
                    if (this.#options.filter == undefined || testValue.indexOf(this.#options.filter) >= 0) {
                        if (this.#options.wholeWord) {
                            if (testValue.indexOf(this.#options.filter.concat('.')) >= 0 || testValue.indexOf(this.#options.filter.concat('_')) >= 0) this.#images.push(href);
                        } else this.#images.push(href);
                    }
                }
            });
        } else {
            this.#logger.log(`error`, `response`, this.#con.statusText);
        }

        if (this.#images.length > 0) {
            this.#logger.debug(`${this.#images.length} images loaded!`);
            this.changeImage(0);

            this.#logger.debug(`Image Count:`, this.#images.length);
        } else iqs(this.#options.viewer).src = this.#options.placeholder;
    }

    /**
     * Error Handler
     */
    #onError() {
        this.#logger.error(`error`, `connection`);
    }
}

/*
Example

let iv = new ImageViewer({filter: 'logo'});
let iv = new ImageViewer({info: '#info', filter: 'logo'});
let iv = new ImageViewer({path: `/img/special`, info: '#info', filter: 'logo'});
iv.fetchImages();

*/

export default ImageViewer;
