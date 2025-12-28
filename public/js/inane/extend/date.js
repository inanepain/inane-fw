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
