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
