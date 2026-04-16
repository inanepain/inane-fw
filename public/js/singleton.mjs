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
     * Constructs an instance of the class and initializes its properties.
     * Ensures the class follows a singleton pattern by returning the existing instance
     * if already created.
     *
     * @param {string} [name='Unknown'] - The name of the instance.
     * @param {Object} [options={age: 0, gender: 'Male'}] - An object containing optional configuration parameters.
     * @param {number} [options.age=0] - The age value to initialize the instance with.
     * @param {string} [options.gender='Male'] - The gender value to initialize the instance with.
     *
     * @return {Object} An instance of the class.
     */
    constructor(name = 'Unknown', options = {age: 0, gender: 'Male'}) {
        const {
            age = 0,
            gender = 'Male',
        } = options;

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
