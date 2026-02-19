class One {
    /**
     * Singleton instance holder (private static field).
     * @type {One|undefined}
     */
    static #instance;

    /**
     * Internal name storage.
     * @type {string}
     */
    #name;

    /**
     * Returns the name of this instance.
     *
     * @returns {string}
     */
    get name() {
        return this.#name;
    }

    /**
     * Create a One instance or return the already-created singleton.
     *
     * If an instance already exists the constructor returns that instance.
     * Note: returning an object from a constructor will replace the constructed
     * object with the returned one; this is used intentionally here to support
     * the singleton pattern when `new One(name)` is called multiple times.
     *
     * @param {string} name - One's name
     * 
     * @returns {One} The singleton instance
     */
    constructor(name) {
        if (this.constructor.#instance) {
            console.log(`${this.constructor.name} already created. Loading: ${this.constructor.#instance.name}`);
            return this.constructor.#instance;
        }

        this.#name = name;
        console.log(`${this.constructor.name} created as: ${this.name}`);

        if (!this.constructor.#instance) {
            this.constructor.#instance = this;
        }
    }

    /**
     * Return the singleton instance, creating it if necessary.
     *
     * @param {string} [name] - Optional name for the instance when it is first created
     * 
     * @returns {One}
     */
    static getInstance(name) {
        if (!this.#instance) {
            this.#instance = new this(name);
        }

        return this.#instance;
    }
}

let bob = new One('Bob');
let john = new One('John');
let max = One.getInstance('Max');

console.debug(`Bob's name: ${bob.name}`);
console.debug(`John's name: ${john.name}`);
console.debug(`Max's name: ${max.name}`);

if (bob === john) {
    console.log('Bob === John');
} else {
    console.log('Bob != John');
}
