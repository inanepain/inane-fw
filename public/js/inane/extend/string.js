/**
 * Extend String
 *
 * @author Philip Michael Raab<peep@inane.co.za>
 * @version 1.7.2
 *
 * Changes
 * 1.7.2 @2025 Sep 14
 *  - update: `log` -> streamlined this somewhat
 *
 * 1.7.1 @2022 Feb 06
 *  - Upd: toTitleCase -> replaced `substr` with `substring`
 *
 * 1.7.0 @2021 Apr 11
 *  - Upd: log -> added label param to customise output a little
 *
 * 1.6.1 @2021 Mar 09
 *  - Fix: toTitleCase -> added isName option to switch how the apostrophe is handled
 *
 * 1.6.0 @2020 Apr 20
 *  - New: parseJSON -> returns object from a valid json string else null
 *
 * 1.5.1 @2020 Apr 18
 *  - upd: replaceAll function updated to increase speed
 *  - fix: log works again
 *  - fix: Only add function if undefined
 */

if (!String.prototype.toTitleCase) {
    /**
     * Capitalises first letter of each word
     *
     * @param {boolean} lowerAsWell true: first lowers string
     *  note: if lowerAsWell is false it will preserve upper case letters
     * @param {boolean} isName sets how apostrophes are handled.<br/>
     *  false - letter after ' is lower case as in: It's, They've
     *  true - letter after ' is UPPER case as in: James O'Mally,
     *
     * @return string
     */
    String.prototype.toTitleCase = function (lowerAsWell = false, isName = false) {
        let string = lowerAsWell === true ? this.toLowerCase() : this;

        if (isName) return string.replace(/\b[a-z]/g, txt => {
            return txt.charAt(0).toUpperCase() + txt.substring(1).toLowerCase();
        });
        else return string.replace(/(?:^|\s)\w/g, function (match) {
            return match.toUpperCase();
        });
    };
}

if (!String.prototype.replaceAll) {
    /**
     * Replace all find with replace
     *
     * @return string
     */
    String.prototype.replaceAll = function (find, replace) {
        return this.split(find).join(replace);
    };
}

/***************************************************
 * TRIM
 ***************************************************/

if (!String.prototype.trimChars) {
    /**
     * Trims chars from front and back of string
     *
     * @param chars characters to trim of string's ends
     *
     * @return string
     */
    String.prototype.trimChars = function (chars) {
        return this.replace(new RegExp('^(' + chars + ')+|(' + chars + ')+$', 'gm'), '');
    };
}

if (!String.prototype.trimCharsLeft) {
    /**
     * Trims chars from front of string
     *
     * @param chars characters to trim of string's start
     *
     * @return string
     */
    String.prototype.trimCharsLeft = function (chars) {
        return this.replace(new RegExp('^(' + chars + ')+', 'gm'), '');
    };
}

if (!String.prototype.trimCharsRight) {
    /**
     * Trims chars from back of string
     *
     * @param chars characters to trim of string's ends
     *
     * @return string
     */
    String.prototype.trimCharsRight = function (chars) {
        return this.replace(new RegExp('(' + chars + ')+$', 'gm'), '');
    };
}

if (!String.prototype.camelCaseToHyphen) {
    /**
     * Convert strings into lowercase-hyphen
     *
     * @param  {String} str
     * @return {String}
     */
    String.prototype.camelCaseToHyphen = function () {
        let str = this;
        str = str.replace(/[^\w\s\-]/gi, '');
        str = str.replace(/([A-Z])/g, function ($1) {
            return '-' + $1.toLowerCase();
        });

        return str.replace(/\s/g, '-').replace(/^-+/g, '');
    };
}

if (!String.prototype.hyphenToCamelCase) {
    /**
     * convert a hyphenated string to camelCase
     *
     * @param  {String} str
     * @return {String}
     */
    String.prototype.hyphenToCamelCase = function () {
        return this.replace(/-([a-z])/g, (m, w) => w.toUpperCase());
    }
}

if (!String.prototype.splice) {
    /**
     * {JSDoc}
     *
     * The splice() method changes the content of a string by removing a range of
     * characters and/or adding new characters.
     *
     * @this {String}
     * @param {number} start Index at which to start changing the string.
     * @param {number} delCount An integer indicating the number of old chars to remove.
     * @param {string} newSubStr The String that is spliced in.
     * @return {string} A new string with the spliced substring.
     */
    String.prototype.splice = function (start, delCount, newSubStr) {
        return this.slice(0, start) + newSubStr + this.slice(start + Math.abs(delCount));
    };
}

if (!String.prototype.parseJSON) {
    /**
     * Returns Object from valid string
     *
     * @return Object
     */
    String.prototype.parseJSON = function () {
        try {
            return JSON.parse(this);
        } catch (error) {
            return null;
        }
    };
}

/***************************************************
 * LOG/DEBUG
 ***************************************************/
if (!String.prototype.log) {
    /**
     * Logs the string to console but still returns unchanged string
     *
     * @param {bool} [label=false] false no label, true: default label, string: custom label
     */
    String.prototype.log = function (label = false) {
        let args = [this.toString()];
        if (label?.constructor?.name == 'String') args.unshift(label);
        else if (label === true) args.unshift(this.constructor.name, this.length);
        console.log(...args);
    };
}
