/**
 * iOptions
 *
 * Merging options
 *
 * @link https://git.inane.co.za:3000/Inane/inane-js/wiki/Inane_icRoot-iOptions
 *
 * @author Philip Michael Raab <philip@cathedral.co.za>
 */

 /**
 * moduleName
 *
 * @constant
 * @type {String}
 * @memberof iOptions
 */
const moduleName = 'iOptions';

/**
 * Version
 *
 * @constant
 * @type {String}
 * @memberof iOptions
 */
const VERSION = '0.3.1';

if (window.Dumper) Dumper.dump('MODULE', moduleName.concat(' v').concat(VERSION), 'LOAD');

/**
 * iOptions
 *
 * Tools for working with options/defaults
 *
 * @version 0.3.1
 */
class iOptions {
    /**
     * Creates an instance of iOptions
     *
     * @param options the options object
     * @constructor
     */
    constructor(options = {}) {
        this._options = options;
    }

    /**
     * Version
     *
     * @readonly
     * @static
     * @returns {String}
     * @memberof iOptions
     */
    static get VERSION() {
        return '0.3.1';
    }

    /**
     * Version
     *
     * @readonly
     * @returns {String}
     * @memberof iOptions
     */
    get VERSION() {
        return this.constructor.VERSION;
    }

    /**
     * Adds ONLY missing properties from source objects to target in decreasing priority
     *
     * @example
     * // copy values from defaults missing in options to options
     * iOptions.complete(options, defaults);
     *
     * @param {Object} target the target object
     * @param {Object[]} source the source objects in decreasing order of priority
     *
     * @returns {Object} target with missing properties from source
     *
     * @static
     *
     * @memberof iOptions
     */
    static complete(target, ...source) {
        var key, i;
		for (i = 0; i < source.length; i++) {
			for (key in source[i]) {
				if (!(key in target) && source[i].hasOwnProperty(key)) {
					target[key] = source[i][key];
				} else {
                    try { // If we are dealing with child objects here we simple dive into them to process the whole object
                        if (target[key].constructor === Object && source[i][key].constructor === Object) iOptions.complete(target[key], source[i][key]);
                    } catch (error) { // If target has undefined or null we catch the error and set the value
                        if (error.message.includes('target[key].constructor')) target[key] = source[i][key];
                    }
                }
			}
		}
        return target;
    }

    /**
     * Adds ONLY missing properties from objs to this.options in decreasing priority
     *
     * @example
     * // copy values from defaults missing in options to options
     * const iopts = new iOptions(options);
     * iopts.complete(moreOptions);
     *
     * @param {Object[]} objs the source objects in decreasing order of priority
     *
     * @returns {iOptions}
     *
     * @memberof iOptions
     */
    complete(...objs) {
        this.constructor.complete(this._options, objs);
        return this;
    }
}

export default iOptions;
