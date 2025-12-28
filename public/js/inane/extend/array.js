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
