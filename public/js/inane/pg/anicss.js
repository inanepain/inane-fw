/**
 * Options
 * @typedef {Object} AniCSS~Options
 * @property {boolean} autoremove - Remove animation on completion.
 * @memberof AniCSS
 */
const defaults = {
    autoremove: true,
};

/**
 * AnimateCss
 *
 * @export
//  * @class AniCSS
 * @extends {iObject}
 */
export default class AniCSS {
    /**
     * Creates an instance of AniCSS.
     * 
     * @constructor
     * @param {string} selector
     * @param {AniCSS~Options} [options={}]
     * @memberof AniCSS
     */
    constructor(selector, options = {}) {
        this._options = I.margeOptions(options, defaults);

        this._selector = selector;
        this._nodes = undefined;
        // if (typeof selector !== 'undefined' && document.querySelectorAll(selector).length  > 0) this._nodes = Array.from(document.querySelectorAll(selector));
        // I need to know if no selector but if it selects nothing... mmm...
        // TODO: Maybe store selector and re-evaluate later if opportunity arises
        if (typeof selector !== 'undefined') {
            this._nodes = Array.from(document.querySelectorAll(selector));
            for (let index = 0; index < this._nodes.length; index++) {
                const element = this._nodes[index];
                element.addEventListener('animationend', this.animationEnd);
            }
        }
    }

    animate(animation) {
        for (let index = 0; index < this._nodes.length; index++) {
            const element = this._nodes[index];
            element.dataset.animation = animation;
            element.classList.add(animation);
        }
    }

    animationEnd(event) {
        console.log('animationEnd', event, this);
    }

}
