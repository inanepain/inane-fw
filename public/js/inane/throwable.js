/**
 * Adds ONLY missing properties from source objects to target in decreasing priority
 *
 * @example
 * // copy values from defaults missing in options to options
 * mergeOptions(options, defaults);
 *
 * @param {Object} target the target object
 * @param {...Object} source the source objects in decreasing order of priority
 *
 * @ignore
 */
const mergeOptions = (target, ...source) => {
    var key, i;
    for (i = 0; i < source.length; i++)
        for (key in source[i])
            if (!(key in target) && source[i].hasOwnProperty(key)) target[key] = source[i][key];
            else
                try { // If we are dealing with child objects here we simple dive into them to process the whole object
                    if (target[key].constructor === Object && source[i][key].constructor === Object) mergeOptions(target[key], source[i][key]);
                } catch (error) { // If target has undefined or null we catch the error and set the value
                    if (error.message.includes('target[key].constructor')) target[key] = source[i][key];
                }
};

/**
 * Throwable
 */
class Throwable extends Error {
    /**
     * Type
     */
    #name = 'Throwable';

    /**
     * Namespace
     */
    #namespace = '';

    /**
     * Date
     */
    #date = new Date();

    /**
     * Custom details
     */
    #detail = {};

    /**
     * Throwable
     *
     * @param {string} message error message
     * @param {object} options
     * @param {string} [options.type=Throwable] error type
     * @param {string} [options.namespace] namespace
     * @param {Date}   [options.date=now] date
     * @param {object} [options.detail] custom information to be added here
     */
    constructor(message, { type, namespace, date, detail } = {}) {
        super(message);

        if (type) this.#name = type;
        if (namespace) this.#namespace = namespace;
        if (date) this.#date = date;

        if (detail) mergeOptions(this.#detail, detail);
    }

    /**
     * Name
     *
     * @type {string}
     * @readonly
     */
    get name() {
        return this.#name;
    }

    /**
     * Namespace
     *
     * @type {string}
     * @readonly
     */
    get namespace() {
        return this.#namespace;
    }

    /**
     * Date
     *
     * @type {Date}
     * @readonly
     */
    get date() {
        return this.#date;
    }

    /**
     * TimeStamp
     *
     * @type {number}
     * @readonly
     */
    get timeStamp() {
        return this.date.getTime();
    }

    /**
     * Detail
     *
     * @type {object}
     * @readonly
     */
    get detail() {
        return this.#detail;
    }
}

// /**\/
try {
    if (true) throw new Throwable('Throwing custom error!', { namespace: 'doSomething', type:'Crash', detail: {bob: true, colour: 'purple'} });
} catch (e) {
    const detailString = JSON.stringify(e.detail);

    console.log(`Error: ${e.name}
Namespace: ${e.namespace}
Message: ${e.message}
Date: ${e.date}
timeStamp: ${e.timeStamp}
Detail: ${detailString}
`);
}
// /\**/

// export default Throwable;
export { Throwable };
