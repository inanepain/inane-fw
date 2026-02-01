/**
 * Extend Array
 *
 * @author Philip Michael Raab<philip@inane.co.za>
 * @version 1.3.0
 */

/**
 * 1.3.0 (2025 May 22)
 *  +/- groupByProperty/groupBy : `groupBy` renamed to `groupByProperty` no to clash with official `Object.groupBy`
 */

/**
 * Removes all duplicates from an array.
 */
if (!Array.prototype.unique) {
    /**
     * Removes all duplicates from an array
     *
     * @return {array}
     */
    Array.prototype.unique = function() {
        const unique = [];
        for (let i = 0; i < this.length; i++) if (!unique.includes(this[i])) unique.push(this[i]);
        return unique;
    };
}

/**
 * Search items for key ?and value and returns matches.
 */
if (!Array.prototype.searchObject) {
    /**
     * Search items for key ?and value and returns matches
     *
     * @param {string} nameKey property name
     * @param {string|number|Array|object} keyValue search term
     * @param {boolean} [fuzzy=false] case insensitive, keyValue partial matches (N.B.: only for string values) @since 1.2.1
     *
     * @return {Array} matching search results
     */
    Array.prototype.searchObject = function(nameKey, keyValue, fuzzy = false) {
        return this.filter(item => {
            if (item.hasOwnProperty(nameKey) && keyValue !== undefined) {
                if (fuzzy && typeof item[nameKey] == 'string') return item[nameKey].toLowerCase().includes(keyValue.toLowerCase());
                return item[nameKey] == keyValue;
            }
        });
    };
}

/**
 * Returns object grouped by property.
 */
if (!Array.prototype.groupByProperty) {
    /**
     * Returns object grouped by property
     *
     * @return Object
     */
    Array.prototype.groupByProperty = function(key) {
        return this.reduce((rv, x) => {
            (rv[x[key]] = rv[x[key]] || []).push(x);
            return rv;
        }, {});
    };
}

/**
 * Sort array of objects by a property of theirs.
 */
if (!Array.prototype.sortByProperty) {
    /**
     * Sort an array of objects by a property of those objects
     *
     * sortNumerically: if values are numbers set this to true, much faster
     *  - the sort will fail if any value is not a number
     *
     * @param propName property to sort by
     * @param sortNumerically sort number values
     */
    Array.prototype.sortByProperty = function(propName, sortNumerically = false) {
        if (sortNumerically == true) {
            this.sort(function(a, b) {
                return a[propName] - b[propName];
            });
        } else {
            this.sort(function(a, b) {
                // Watchout for numbers, they don't support toUpperCase, so we wrap it in text
                var nameA = `${(a[propName] ?? '')}`.toUpperCase(); // ignore upper and lowercase
                var nameB = `${(b[propName] ?? '')}`.toUpperCase(); // ignore upper and lowercase

                if (nameA < nameB) return -1;
                if (nameA > nameB) return 1;

                // names must be equal
                return 0;
            });
        }
    }
}

/**
 * Logs Debug output
 */
if (!Array.prototype.log) {
    /**
     * Logs Debug output
     */
    Array.prototype.log = function() {
        console.log(JSON.stringify(this));
    };
}

/**
 * Extend Date
 *
 * @author Philip Michael Raab<philip@cathedral.co.za>
 * @version 1.1.0
 */

/**
 * 1.1.0 (2020 Aug 06)
 *  + nextYear/nextYearGMTString: return a year from date as date or GMT string
 *  + unixZero/unixZeroGMTString: return timestamp 0 as date or GMT string
 */

if (!Date.prototype.getWeekNumber) {
    /**
     * Adds getWeekNumber to date objects.
     * The ISO-8601 week of year number if the date.
     *
     * @return {number}
     */
    Date.prototype.getWeekNumber = function() {
        var d = new Date(+this);
        d.setHours(0, 0, 0);
        d.setDate(d.getDate() + 4 - (d.getDay() || 7));
        return Math.ceil((((d - new Date(d.getFullYear(), 0, 1)) / 8.64e7) + 1) / 7);
    };
}

if (!Date.prototype.nextYear) {
    /**
     * Adds a year to date.<br />
     * 31536000000 = 1 year (365 * 24 * 60 * 60 * 1000)
     *
     * @return {Date}
     */
    Date.prototype.nextYear = function() {
        return new Date(this.getTime() + 31536000000);
    };
}

if (!Date.prototype.nextYearGMTString) {
    /**
     * Adds a year to date and return GMT string.
     *
     * @return {string}
     */
    Date.prototype.nextYearGMTString = function() {
        return this.nextYear().toGMTString();
    };
}

if (!Date.prototype.unixZero) {
    /**
     * Date for timestamp 0.
     *
     * @return {string}
     */
    Date.prototype.unixZero = function() {
        return new Date(0);
    };
}

if (!Date.prototype.unixZeroGMTString) {
    /**
     * GMT String for timestamp 0.
     *
     * @return {string}
     */
    Date.prototype.unixZeroGMTString = function() {
        return (new Date(0)).toGMTString();
    };
}

if (!Date.prototype.log) {
    /**
     * Logs Debug output
     */
    Date.prototype.log = function() {
        console.log(this.constructor.toString().split(' ')[1].replace('()', '').toLowerCase() + '(' + this.toLocaleString().length + '): ' + this.toLocaleString());
    };
}

/**
 * Extend HTML
 *
 * @author Philip Michael Raab<philip@cathedral.co.za>
 * @version 1.4.0
 */

/**
 * HISTORY
 *
 * 1.4.0: 2025 May 22
 *  - iq        : gets *@@* to return element if only single match
 *
 * 1.3.2: 2022 Apr 08
 *  - iqs, iqsa : re-worked for simplicity
 *  - iq        : no longer uses iqs or iqsa but it's own optimised code
 *  - jsdoc     : updated
 *
 * 1.3.1: 2022 Jan 14
 *  - iqsa also now returns an array instead of HTMLCollection or NodeList
 *
 * 1.3.0: 2021 Nov 12
 *  - iq acting as `iqsa` now returns an array instead of HTMLCollection or NodeList
 *
 * 1.2.0: 2021 Oct 13
 *  - Added ShadowRoot to items that get Inane Query Shortcuts
 */

/**
 * Collections
 */
for (let element of [HTMLCollection, NodeList]) {
    // test for toArray
    if (!element.prototype.toArray) {
        /**
         * Converts the collection to an array.
         *
         * @since 1.0.0
         *
         * @returns {Array}
         */
        element.prototype.toArray = function() {
            return Array.from(this);
        }
    }
}

/**
 * Items
 */
for (let element of [Document, HTMLElement, ShadowRoot, HTMLDocument]) {
    // test for iqs
    if (!element.prototype.iqs) {
        /**
         * modified querySelector
         *
         * @since 1.0.0
         *
         * @param {DOMString} selectors A DOMString containing one or more selectors to match
         *
         * @returns {null|Element} An Element representing the first match or null if no match
         */
        element.prototype.iqs = function(selectors) {
            const el = this?.querySelector ? this : window.document;
            return el.querySelector(selectors);
        }
    }

    // assign global iqs
    if (!window.iqs && window.document.iqs) window.iqs = window.document.iqs;

    // test for iqsa
    if (!element.prototype.iqsa) {
        /**
         * modified querySelectorAll
         *
         * @since 1.1.0
         *
         * @param {DOMString} selectors A DOMString containing one or more selectors to match
         *
         * @returns {Element[]} An Element array containing all matches
         */
        element.prototype.iqsa = function(selectors) {
            const el = this?.querySelectorAll ? this : window.document;
            return Array.from(el.querySelectorAll(selectors));
        }
    }

    // assign global iqsa
    if (!window.iqsa && window.document.iqsa) window.iqsa = window.document.iqsa;

    // test for iq
    if (!element.prototype.iq) {
        /**
         * Unified Query method
         *
         * Using either call, apply or bind to set this to an HTMLElement
         *  will restrict the query to it's children.
         *
         * Prefixing the selectors string with:
         *  `@` uses `querySelector` (return first element if multipule matches).
         *  `@@` uses `querySelectorAll` but if only one match a single item is returned.
         *
         * @since 1.1.1
         *
         * @param {DOMString|string} selectors A DOMString containing one or more selectors to match
         *
         * @returns {null|Element|Element[]} An Element representing the first match, an Element array containing all matches or null if nothing matched
         */
        element.prototype.iq = function(selectors) {
            const dynamic = selectors.startsWith('@@');
            if (dynamic) selectors = selectors.substring(2);

            const cmd = selectors.startsWith('@') || selectors.split(' ').pop().charAt(0) === "#" && !selectors.includes(',') ? 'querySelector' : 'querySelectorAll';
            if (selectors.startsWith('@')) selectors = selectors.substring(1);
            const el = this?.[cmd] ? this : window.document;

            result = el[cmd](selectors);
            result = cmd == 'querySelector' ? result : Array.from(result);
            if (dynamic) return Array.isArray(result) ? (result.length == 1 ? result.pop() : result) : result;
            return result;
        }
    }

    // assign global iq
    if (!window.iq && window.document.iq) window.iq = window.document.iq;
}

/**
 * Extend Number
 *
 * @author Philip Michael Raab<philip@cathedral.co.za>
 * @version 1.1.0
 */

if (!Number.getRandom) {
    /**
     * Random number between to values.
     */
    Number.getRandom = function(min, max) {
        min = Math.ceil(min || 0);
        max = Math.floor(max || min * min);
        return Math.floor(Math.random() * (max - min + 1)) + min; //The maximum is inclusive and the minimum is inclusive
    };
}

if (!Number.prototype.log) {
    /**
     * Logs Debug output
     */
    Number.prototype.log = function() {
        console.log(this.constructor.toString().split(' ')[1].replace('()', '').toLowerCase() + '(' + this.toString().length + '): ' + this.toString());
    };
}

/**
 * Extend Object
 *
 * @version 1.10.0
 * @author Philip Michael Raab<philip@cathedral.co.za>
 *
 * Public Domain.
 * NO WARRANTY EXPRESSED OR IMPLIED. USE AT YOUR OWN RISK.
 */

/**
 * 1.10.0 (2026 Jan 31)
 *  * jsonString: Update - supports two params: `replacer` and `space`.
 *
 * 1.9.0 (2025 Jun 08)
 *  * propertyRename: Update - now allows replacing existing property if `force` is true
 *  * renameProperty: Update - now allows replacing existing property if `force` is true
 *
 * 1.8.0 (2025 May 22)
 *  +/- groupByProperty/groupBy : `groupBy` renamed to `groupByProperty` no to clash with official `Object.groupBy`
 *  + keys                      : `Object.keys` alias
 *  + values                    : `Object.values` alias
 *  + renameProperty            : `Object.propertyRename` alias
 *
 * 1.7.0 (2022 Jan 12)
 *  + groupBy: Group by a property
 *
 * 1.6.0 (2022 Jan 12)
 *  + propertyRename: Rename a property
 *
 * 1.5.0 (2021 Nov 10)
 *  + sorted   : Get a sorted copy of object
 *  - pick     : Update - can also take a string if only one property is required
 *  - pick     : Fix - returns undefined for invalid properties
 *
 * 1.4.0 (2021 Oct 28)
 *  + readWithPath : returns property value using a string as a path
 *
 * 1.3.0 (2020 Aug 06)
 *  + pick     : return a new object with only the properties requested in an array
 *
 * 1.2.0 (2020 Jul 08)
 *  - New      : watch now returns a change object with properties: property, value, previous
 *  - Upd      : watch now returns a change object with properties: property, value, previous
 *
 * 1.1.0 (2018 Nov 01)
 *  - New      : handler now only gets call if oldVal !== newVal
 *
 * 1.0.1 (2016 Apr 08)
 *  - Fixed    : oldVal returns undefined after the 1st change
 */

/*
let o = {p: 'yyyy'};
o.watch('p', change=>console.log(change));
o.p = 'la de da';
*/

// object.watch
if (!Object.prototype.watch) {
    Object.defineProperty(Object.prototype, 'watch', {
        enumerable: false,
        configurable: false,
        writable: true,
        value: function(prop, handler) {
            var change = {
                    property: prop,
                    value: this[prop],
                    previous: undefined,
                    set update(v) {
                        if (this.value === v) return false;
                        this.previous = this.value;
                        this.value = v;
                        return true;
                    }
                },
                getter = function() {
                    return change.value;
                },
                setter = function(val) {
                    if (change.update = val) handler.call(this, change);
                    return val;
                };
            if (delete this[prop]) { // can't watch constants
                Object.defineProperty(this, prop, {
                    get: getter,
                    set: setter,
                    // enumerable: true,
                    configurable: true
                });
            }
        }
    });
}

// object.unwatch
if (!Object.prototype.unwatch) {
    Object.defineProperty(Object.prototype, 'unwatch', {
        enumerable: false,
        configurable: false,
        writable: true,
        value: function(prop) {
            var val = this[prop];
            delete this[prop]; // remove accessors
            this[prop] = val;
        }
    });
}

/**
 * Returns Object as JSON string, ala stringify.
 *
 * @method jsonString
 *
 * @param {function|(string|number)[]} [replacer] A function that alters the behavior of the stringification process or an array of strings and numbers that specifies properties of value to be included in the output. If replacer is an array, all elements in this array that are not strings or numbers (either primitives or wrapper objects), including Symbol values, are completely ignored. If replacer is anything other than a function or an array (e.g., null or not provided), all string-keyed properties of the object are included in the resulting JSON string.
 * @param {string|number} [space] A string or number used to insert white space (including indentation, line break characters, etc.) into the output JSON string for readability purposes.
 *  - If this is a number, it indicates the number of space characters to be used as indentation, clamped to 10 (that is, any number greater than 10 is treated as if it were 10). Values less than 1 indicate that no space should be used.
 *  - If this is a string, the string (or the first 10 characters of the string, if it's longer than that) is inserted before every nested object or array.
 *  - If space is anything other than a string or number (can be either a primitive or a wrapper object) — for example, is null or not provided — no white space is used.
 *
 * @return {string}
 */
if (!Object.prototype.jsonString) {
    /**
     * Returns Object as JSON string, ala stringify.
     *
     * @param {function|(string|number)[]} [replacer] A function that alters the behavior of the stringification process or an array of strings and numbers that specifies properties of value to be included in the output. If replacer is an array, all elements in this array that are not strings or numbers (either primitives or wrapper objects), including Symbol values, are completely ignored. If replacer is anything other than a function or an array (e.g., null or not provided), all string-keyed properties of the object are included in the resulting JSON string.
     * @param {string|number} [space] A string or number used to insert white space (including indentation, line break characters, etc.) into the output JSON string for readability purposes.
     *  - If this is a number, it indicates the number of space characters to be used as indentation, clamped to 10 (that is, any number greater than 10 is treated as if it were 10). Values less than 1 indicate that no space should be used.
     *  - If this is a string, the string (or the first 10 characters of the string, if it's longer than that) is inserted before every nested object or array.
     *  - If space is anything other than a string or number (can be either a primitive or a wrapper object) — for example, is null or not provided — no white space is used.
     *
     * @return {string}
     */
    Object.defineProperty(Object.prototype, 'jsonString', {
        enumerable: false,
        configurable: false,
        writable: true,
        value: function(replacer = null, space = null) {
            return JSON.stringify(this, replacer, space);
        }
    });
}

/**
 * Returns Object with only propsArray properties of the original
 */
if (!Object.prototype.pick) {
    /**
     * Returns Object with only propsArray properties of the original
     *
     * @since 1.3.0
     * @since 1.5.0 can also take a string if only one property required
     *
     * @param propsArray Array of properties to pick or string of a single property
     *
     * @return {object}
     */
    Object.defineProperty(Object.prototype, 'pick', {
        enumerable: false,
        configurable: false,
        writable: true,
        value: function(propsArray) {
            if (!propsArray) return;
            if (!Array.isArray(propsArray) && (typeof propsArray == "string")) propsArray = [propsArray];
            propsArray = propsArray.unique();

            const picked = {};
            propsArray.forEach(prop => {
                if (this.hasOwnProperty(prop)) picked[prop] = this[prop];
            });

            return picked;
        }
    });
}

/**
 * Read property using string path
 */
if (!Object.prototype.readPath) {
    /**
     * Get the value of a property using a string for the path
     *
     * @since 1.4.0
     *
     * @param path string as path
     * @param delimiter path delimiter if not period (.)
     *
     * @return {any} property value
     */
    Object.defineProperty(Object.prototype, 'readPath', {
        enumerable: false,
        configurable: false,
        writable: true,
        value: function(path, delimiter = '.') {
            if (!path) return this;

            const eP = typeof path == 'string' ? path.split(delimiter) : path;
            let t = Object.assign({}, this);

            for (let i = 0; i < eP.length; i++)
                if (t && t.hasOwnProperty(eP[i])) t = t[eP[i]];
                else t = undefined;

            return t;
        }
    });
}

/**
 * Get a sorted copy of an object.
 */
if (!Object.prototype.sorted) {
    /**
     * Get a sorted copy of object
     *
     * @since 1.5.0
     *
     * @return {Object} sorted object
     */
    Object.defineProperty(Object.prototype, 'sorted', {
        enumerable: false,
        configurable: false,
        writable: true,
        value: function() {
            return this.pick(this.keys().sort());
        }
    });
}

/**
 * Rename property
 */
if (!Object.prototype.propertyRename) {
    /**
     * Rename property
     *
     * - if new_key exists, nothing is done
     *
     * @since 1.6.0
     * @since 1.9.0 updated to allow replacing existing property if `force` is true
     *
     * @param {string} old_key - property to rename
     * @param {string} new_key - new name for property
     * @param {boolean} [force=false] - if true, will force new_key if it exists
     *
     * @return {Object} this object
     */
    Object.defineProperty(Object.prototype, 'propertyRename', {
        enumerable: false,
        configurable: false,
        writable: true,
        value: function(old_key, new_key, force = false) {
            // Validate inputs
            if (!old_key || !new_key) {
                console.error('Object.propertyRename: old_key and new_key are required.');
                return this;
            }
            if (typeof old_key !== 'string' || typeof new_key !== 'string') {
                console.error('Object.propertyRename: old_key and new_key must be strings.');
                return this;
            }
            if (old_key === new_key) {
                console.warn('Object.propertyRename: old_key and new_key are the same, no action taken.');
                return this;
            }
            if (!this.hasOwnProperty(old_key)) {
                console.warn(`Object.propertyRename: old_key "${old_key}" does not exist on this object.`);
                return this;
            }
            if (this.hasOwnProperty(new_key) && !force) {
                // If new_key already exists and force is false, do nothing
                console.warn(`Object.propertyRename: new_key "${new_key}" already exists on this object, no action taken.`);
                return this;
            }
            // If old_key exists and new_key does not (or force), rename the property
            if (this.hasOwnProperty(new_key) && force) {
                // If force is true, delete the new_key if it exists
                delete this[new_key];
            }
            // Define the new property with the same descriptor as the old one
            Object.defineProperty(this, new_key, Object.getOwnPropertyDescriptor(this, old_key));
            delete this[old_key];

            return this;
        }
    });
}

/**
 * Rename property
 */
if (!Object.prototype.renameProperty) {
    /**
     * Rename property
     *
     * - if new_key exists, nothing is done
     *
     * @see Object.propertyRename
     *
     * @since 1.8.0 alias of propertyRename
     * @since 1.9.0 updated to allow replacing existing property if `force` is true
     *
     * @param {string} old_key - property to rename
     * @param {string} new_key - new name for property
     * @param {boolean} [force=false] - if true, will force new_key if it exists
     *
     * @return {Object} this object
     */
    Object.defineProperty(Object.prototype, 'renameProperty', {
        enumerable: false,
        configurable: false,
        writable: true,
        value: function(old_key, new_key, force = false) {
            // Call the propertyRename method with the same parameters
            return this.propertyRename(old_key, new_key, force);
        }
    });
}

/**
 * Returns object grouped by property.
 */
if (!Object.prototype.groupByProperty) {
    /**
     * Group by property
     *
     * @since 1.7.0
     * @since 1.8.0 renamed to `groupByProperty` in 2024
     *
     * @param {string} key - property to group by
     *
     * @return {Object} object with a values group by key
     */
    Object.defineProperty(Object.prototype, 'groupByProperty', {
        enumerable: false,
        configurable: false,
        writable: true,
        value: function(key) {
            try {
                let target = Array.isArray(this) ? this : this.values();
                return target.reduce((rv, x) => {
                    (rv[x[key]] = rv[x[key]] || []).push(x);
                    return rv;
                }, {});
            } catch (error) {
                console.error('Unable to group object.');
            }
        }
    });
}

/**
 * Returns the object's keys
 */
if (!Object.prototype.keys) {
    /**
     * Returns the object's keys
     *
     * @since 1.8.0
     *
     * @return {string[]} the object's keys
     */
    Object.defineProperty(Object.prototype, 'keys', {
        enumerable: false,
        configurable: false,
        writable: true,
        value: function() {
            return Object.keys(this);
        }
    });
}

/**
 * Returns the object's values
 */
if (!Object.prototype.values) {
    /**
     * Returns the object's values
     *
     * @since 1.8.0
     *
     * @return {Array} the object's values
     */
    Object.defineProperty(Object.prototype, 'values', {
        enumerable: false,
        configurable: false,
        writable: true,
        value: function() {
            return Object.values(this);
        }
    });
}

// const obj = {
//     "A": "Aye,",
//     "B": "Bee",
//     "C": {
//         "A": "CAye,",
//         "B": "CBee",
//         "C": "CCee",
//     },
// };

// console.log(obj.readPath('C.B'));
// console.log(obj.readPath(['C', 'A']));

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

