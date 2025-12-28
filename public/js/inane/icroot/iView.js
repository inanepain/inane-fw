/**
 * iView
 *
 * Control and viewport functionality
 *
 * @see https://git.inane.co.za:3000/Inane/inane-js/wiki/Inane_icRoot-iView
 *
 * @author Philip Michael Raab <philip@cathedral.co.za>
 */

import iOptions from './iOptions.js';

/**
 * Version
 *
 * @constant
 * @type {String}
 * @memberof iView
 */
const VERSION = '0.5.2';

/**
* moduleName
*
* @constant
* @type {String}
*/
const moduleName = 'iView';

if (window.Dumper) Dumper.dump('MODULE', moduleName.concat(' v').concat(VERSION), 'LOAD');

/**
 * iView
 *
 * Tools for working with viewport
 *
 * @version 0.5.2
 * @extends iOptions
//  * @class iView
 */
class iView extends iOptions {
    /**
     * Creates an instance of iView
     *
     * @param element the element or selector
     * @constructor
     */
    constructor(scroller, selector, options = {}) {
        super(options);
        this._options = options;

        this.constructor.complete(this._options, {
            throttle: 100,
        });

        this._holder = typeof scroller == 'string' ? document.querySelector(scroller) : scroller;
        if (!selector) this._viewQueue = Array.from(this.holder.children);
        else this._viewQueue = typeof scroller == 'string' ? Array.from(document.querySelectorAll(selector)) : selector;

        this.viewQueue.map(el => el.ariaHidden = !this.isEndInView(el));

        // TODO: Move out of constructor and have options for automatic event handling
        this.holder.addEventListener('scroll', I.throttle(event => {
            this.viewQueue.map(el => el.ariaHidden = !this.isEndInView(el));

            this.viewQueue.map(el => {
                // prevent last item not being displayed
                if (this.viewQueue.length > 2 && el == this.viewQueue[this.viewQueue.length - 1])
                    el.ariaHidden = this.viewQueue[this.viewQueue.length - 2].ariaHidden;
                else el.ariaHidden = !this.isEndInView(el)
            });

            if (this.viewQueue.length > 2 && !this.viewQueue[this.viewQueue.length - 2].ariaHidden)
                this.viewQueue[this.viewQueue.length - 1].ariaHidden = false;
        }, this._options.throttle), false);
    }

    /**
     * Version
     *
     * @readonly
     * @static
     * @returns {String}
     * @memberof iView
     */
    static get VERSION() {
        return VERSION;
    }

    /**
     * Version
     *
     * @readonly
     * @returns {String}
     * @memberof iView
     */
    get VERSION() {
        return VERSION;
    }

    /**
     * The Element
     *
     * @readonly
     * @memberof iView
     */
    get holder() {
        return this._holder;
    }

    /**
     * The Elements monitored for view
     *
     * @readonly
     * @memberof iView
     * @type {Array}
     */
    get viewQueue() {
        return this._viewQueue;
    }

    /**
     * Is Element in Viewport
     *
     * @static
     * @param {node} element
     * @returns {boolean}
     * @memberof iView
     */
    static isInView(element) {
        const bounding = typeof element == 'string' ? document.querySelector(element).getBoundingClientRect() : element.getBoundingClientRect();
        return (
            bounding.top >= 0 &&
            bounding.left >= 0 &&
            bounding.bottom <= (window.innerHeight || document.documentElement.clientHeight) &&
            bounding.right <= (window.innerWidth || document.documentElement.clientWidth)
        );
    }

    /**
     * Is Element's end in Viewport
     *
     * @param {element|string} selector
     * @param {element|string} holder
     * @returns {boolean}
     * @memberof iView
     */
    isEndInView(selector, holder) {
        if (!holder) holder = this.holder;
        else holder = typeof holder == 'string' ? document.querySelector(holder) : holder;
        const element = typeof selector == 'string' ? document.querySelector(selector) : selector;

        const docViewTop = holder.getBoundingClientRect().top;
        const docViewBottom = docViewTop + holder.offsetHeight;
        const elementTop = element.getBoundingClientRect().top;
        const elementBottom = elementTop + element.offsetHeight;
        return elementBottom <= docViewBottom;
    }

    /**
     * Is Element in Viewport
     *
     * @returns {boolean}
     * @memberof iView
     */
    get isInView() {
        const inView = this.constructor.isInView(this.element);
        if (this.element.dataset.inview !== inView.toString()) this.element.dataset.inview = inView;
        return inView;
    }
}

export default iView;
