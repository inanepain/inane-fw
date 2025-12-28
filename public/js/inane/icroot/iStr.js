/**
 * iStr
 *
 * Description
 * @see https://git.inane.co.za:3000/Inane/inane-js/wiki/Inane_icRoot-iStr
 *
 * @author Philip Michael Raab <philip@cathedral.co.za>
 */

/**
 * Version
 *
 * @constant
 * @type {String}
 * @memberof iStr
 */
const VERSION = '0.5.0';

/**
* moduleName
*
* @constant
* @type {String}
*/
const moduleName = 'iStr';

if (window.Dumper) Dumper.dump('MODULE', moduleName.concat(' v').concat(VERSION), 'LOAD');

/**
 * iStr
 *
 * Quick string functions
 *
 * Prefs (on: CAP / off: small)
 *  - s: use joiner on/off
 *
 * _icroot.iHelper.importModule(_icroot.iHelper.icModules.iStr)
 * iStr=_icroot.iStr
 *
 * @example
 * // returns Hello World
 * iStr.c('Hello')._().a('world').ep('s').a('example').s
 *
 * @version 0.5.0
//  * @class iStr
 */
class iStr {
    /**
     * @type {String} the string
     */
    #string;

    /**
     * @type {String} italic
     */
    #i = `*`;

    /**
    * @type {String} bold
    */
    #b = `__`;

    /**
     * @type {String} bolditalic
     */
    #bi = `***`;

    /**
     * @type {String} join string
     */
    #j = ` `;

    /**
     * @type {String} preferences
     */
    #prefs = `s`;

    /**
     * Creates an instance of iStr
     *
     * @constructor
     * @param {string} string
    //  * @memberof iStr
     */
    constructor(string, prefs) {
        this.#string = string || ``;
        if (prefs) this.#prefs = prefs;
    }

    /**
     * Version
     *
     * @readonly
     * @static
     * @returns {String}
     */
    static get VERSION() {
        return VERSION;
    }

    /**
     * Version
     *
     * @readonly
     * @returns {String}
     */
    get VERSION() {
        return VERSION;
    }

    /**
     * Create a new instance of iStr
     *
    //  * @constructs
     * @static
     * @param {String} string - starting string
     * @param {String} prefs - set prefs
     * @returns {String}
     */
    static c(string, prefs = null) {
        return (new this(string, prefs));
    }

    /**
     * Check Preferences
     *
     * @param {string} pref
     * @returns
     */
    cp(pref) {
        return this.#prefs.includes(pref.toUpperCase());
    }

    /**
     * Enable Preferences
     *
     * @param {string} pref
     * @returns
     */
    ep(pref) {
        if (!this.cp(pref))
            this.#prefs = this.#prefs.replace(pref.toLowerCase(), pref.toUpperCase());
        return this;
    }

    /**
     * Disable Preferences
     *
     * @param {string} pref
     * @returns
     */
    dp(pref) {
        if (this.cp(pref))
            this.#prefs = this.#prefs.replace(pref.toUpperCase(), pref.toLowerCase());
        return this;
    }

    /**
     * Sets joiner
     *
     * default string between string
     *
     * @param {string} string
     * @returns {iStr}
     */
    sj(joiner) {
        this.#j = joiner;
        return this;
    }

    /**
     * output string
     *
     * @static
     * @returns {String}
     */
    get s() {
        return this.#string;
    }

    /**
     * Add string
     *
     * @param {string} string
     * @returns {iStr}
     */
    a(string) {
        if (this.cp('s')) this.#string += this.#j;
        this.#string += string;
        return this;
    }

    /**
     * Add bold string
     *
     * @param {string} string
     * @returns {iStr}
     */
    b(string) {
        return this.a(this.#b + string + this.#b);
    }

    /**
     * Add italic string
     *
     * @param {string} string
     * @returns {iStr}
     */
    i(string) {
        return this.a(this.#i + string + this.#i);
    }

    /**
     * Add bold italic string
     *
     * @param {string} string
     * @returns {iStr}
     */
    bi(string) {
        return this.a(this.#bi + string + this.#bi);
    }

    /**
     * Add Space
     *
     * @returns {iStr}
     */
    _() {
        return this.a(` `);
    }
}

export default iStr;
