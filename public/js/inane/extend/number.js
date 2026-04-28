/**
 * Extend Number
 *
 * @author Philip Michael Raab<philip@cathedral.co.za>
 * @version 1.1.0
 */

if (!Number.getRandom) {
    /**
     * Generates a random integer within a specified range.
     *
     * @param {number} min - The minimum value of the range (inclusive). Defaults to 0 if not provided.
     * @param {number} max - The maximum value of the range (inclusive). Defaults to the square of `min` if not provided.
     * @return {number} A random integer between `min` and `max` (both inclusive).
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
