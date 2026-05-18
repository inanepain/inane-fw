import {MergeOptions} from './class-lib/MergeOptions.mjs';


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
     * @param {object} [options.detail] custom information to be added here
     */
    constructor(message, { type, namespace, detail } = {}) {
        super(message);

        if (type) this.#name = type;
        if (namespace) this.#namespace = namespace;

        if (detail) MergeOptions.mergeOptionsWithAddAndUpdate(this.#detail, detail);
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
