/**
 * ScrollTo
 * @version 0.5.1
 * @author Philip Michael Raab<philip@cathedral.co.za>
 */

/**
 * VERSION
 */
const VERSION = `0.5.1`;

const defaults = {
    offset: 0,
    duration: 1000,
    fail_gracefully: false
};

/**
 * ScrollTo
 */
export default class ScrollTo {
    #options = {
        selector: null,
        el: null,
        config: {}
    }

    /**
     * ScrollTo Element
     *
     * @param {HTMLElement|string} el Element or selector string
     * @param {object} param1 configuration options
     */
    constructor(el, {offset = 0, duration = 1000, fail_gracefully = false} = {}) {
        Object.assign(this.#options.config, defaults, arguments[1]);

        if (typeof el == `string`) {
            this.#options.selector = el;
            this.#options.el = document.querySelector(el);
        } else if (el instanceof HTMLElement) {
            this.#options.selector = null;
            this.#options.el = el;
        }

        if (this.#options.el == null && this.#options.config.fail_gracefully == false) throw `Parameter is not a valid HTMLElement or selector string!`;

        this.animateScrolling = ScrollTo.animateScrolling.bind(this);
        this.scrollTo = ScrollTo.element.bind(this, this.#options.el);
    }

    /**
     * Version
     *
     * @property
     * @readonly
     * @type {string}
     */
    static get VERSION() {
        return VERSION;
    }

    /**
     * Animate Scrolling
     *
     * @param {number} endingY final Y position after scroll
     * @param {number} duration millisecond scroll duration
     */
    static animateScrolling(endingY, duration) {
        let startingY = window.pageYOffset,
            diff = endingY - startingY,
            start;

        /**
         * Animate scroll
         */
        window.requestAnimationFrame(function step(timestamp) {
            if (!start) start = timestamp;

            const time = timestamp - start,
                percent = Math.min(time / duration, 1);

            window.scrollTo(0, startingY + diff * percent);

            if (time < duration) window.requestAnimationFrame(step);
        });
    }

    /**
     * Scroll el into view pad with offset in duration
     *
     * @param {HTMLElement} el element to scroll
     * @param {number} offset padding for el
     * @param {number} duration scroll animation duration
     */
    static element(el, offset, duration) {
        const opts = ScrollTo === this ? defaults : this.#options.config;

        const targetPosition = el.offsetTop - (offset ?? opts.offset);

        this.animateScrolling(targetPosition, duration ?? opts.duration);
    }
}