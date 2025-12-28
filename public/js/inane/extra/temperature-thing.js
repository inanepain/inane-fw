/**
 * TemperatureThing is a utility class for converting temperatures between Kelvin, Celsius, and Fahrenheit.
 * It supports setting and getting temperature values in any of the three units, and automatically updates the internal state.
 * The class also allows configuration of decimal precision and debug logging.
 *
 * @version 0.5.0
 *
 * @class
 * @example
 * const tc = new TemperatureThing({ celsius: 25 });
 * console.log(tc.fahrenheit); // 77
 *
 * @property {number} kelvin - The temperature in Kelvin.
 * @property {number} celsius - The temperature in Celsius.
 * @property {number} fahrenheit - The temperature in Fahrenheit.
 *
 * @param {Object} [options] - Initial temperature values.
 * @param {number} [options.kelvin] - Initial temperature in Kelvin.
 * @param {number} [options.celsius] - Initial temperature in Celsius.
 * @param {number} [options.fahrenheit] - Initial temperature in Fahrenheit.
 * @param {Object} [extra] - Additional configuration options.
 * @param {boolean} [extra.debug=false] - Enable debug logging.
 * @param {number} [extra.decimals=0] - Number of decimal places for output.
 *
 * @method static fromKelvin(degrees, [extra]) - Create a TemperatureThing from Kelvin.
 * @method static fromCelsius(degrees, [extra]) - Create a TemperatureThing from Celsius.
 * @method static fromFahrenheit(degrees, [extra]) - Create a TemperatureThing from Fahrenheit.
 */
class TemperatureThing {
    //#region version
    /**
     * Gets the current version of the TemperatureThing class.
     *
     * @returns {string} The version string, e.g., '0.5.0'.
     * @readonly
     * @static
     */
    static get VERSION() {
        return '0.5.0';
    }
    /**
     * Gets the static VERSION property of the class.
     *
     * @type {string}
     * @readonly
     */
    get VERSION() {
        return this.constructor.VERSION;
    }
    //#endregion version

    /**
     * @private
     * @type {number}
     * Stores the temperature value in Kelvin.
     */
    #kelvin = 0;

    /**
     * @typedef {Object} Options
     * @property {boolean} debug - Enables or disables debug mode.
     * @property {number} decimals - Number of decimal places to use.
     */
    #options = {
        debug: false,
        decimals: 0,
    }

    static get ZERO_KELVIN() {
        return 0;
    }
    static get ZERO_CELSIUS() {
        return -273;
    }
    static get ZERO_FAHRENHEIT() {
        return -459;
    }

    get ZERO_KELVIN() {
        return this.constructor.ZERO_KELVIN;
    }
    get ZERO_CELSIUS() {
        return this.constructor.ZERO_CELSIUS;
    }
    get ZERO_FAHRENHEIT() {
        return this.constructor.ZERO_FAHRENHEIT;
    }

    //#region temperature unites

    /**
     * Gets the temperature in Kelvin.
     *
     * @returns {number} The temperature in Kelvin.
     */
    get kelvin() {
        return this.#kelvin.toFixed(this.#options.decimals) * 1;
    }
    /**
     * Sets the temperature in Kelvin.
     *
     * @param {number} degrees - The temperature value in Kelvin to set.
     */
    set kelvin(degrees) {
        this.#kelvin = degrees < 0 ? 0 : degrees;
        this.#log();
    }

    /**
     * Gets the temperature in Celsius.
     *
     * @returns {number} The temperature in Celsius.
     */
    get celsius() {
        return (this.#kelvin - 273.15).toFixed(this.#options.decimals) * 1;
    }
    /**
     * Sets the temperature in Celsius.
     *
     * @param {number} degrees - The temperature value in Celsius to set.
     */
    set celsius(degrees) {
        this.kelvin = degrees + 273.15;
        // this.#log();
    }

    /**
     * Gets the temperature in Fahrenheit.
     *
     * @returns {number} The temperature in Fahrenheit.
     */
    get fahrenheit() {
        return (1.8 * (this.#kelvin - 273.15) + 32).toFixed(this.#options.decimals) * 1;
    }
    /**
     * Sets the temperature in Fahrenheit.
     *
     * @param {number} degrees - The temperature value in Fahrenheit to set.
     */
    set fahrenheit(degrees) {
        this.kelvin = (degrees - 32) * 5 / 9 + 273.15;
        // this.#log();
    }

    //#endregion templature unites

    //#region factories

    /**
     * Creates an instance of the class from a temperature in Kelvin.
     *
     * @param {number} degrees - The temperature in Kelvin.
     * @param {Object} [extra={}] - Optional extra parameters for initialization.
     *
     * @returns {this} A new instance of the class initialized with the given Kelvin temperature.
     */
    static fromKelvin(degrees, extra = {}) {
        return new this({kelvin: degrees}, extra);
    }

    /**
     * Creates an instance of the class from a temperature in Celsius.
     *
     * @param {number} degrees - The temperature in Celsius.
     * @param {Object} [extra={}] - Optional extra parameters for initialization.
     *
     * @returns {this} A new instance of the class initialized with the given Celsius value.
     */
    static fromCelsius(degrees, extra = {}) {
        return new this({celsius: degrees}, extra);
    }

    /**
     * Creates an instance of the class from a temperature in Fahrenheit.
     *
     * @param {number} degrees - The temperature in Fahrenheit.
     * @param {Object} [extra={}] - Optional extra parameters for initialization.
     *
     * @returns {this} A new instance of the class initialized with the given Fahrenheit value.
     */
    static fromFahrenheit(degrees, extra = {}) {
        return new this({fahrenheit: degrees}, extra);
    }

    //#endregion factories

    //#region constructor & initialise

    /**
     * Creates an instance of the class with a temperature value.
     * Accepts an object with one of the temperature units: kelvin, celsius, or fahrenheit.
     * Only the first defined value (in the order: kelvin, celsius, fahrenheit) will be used to set the temperature.
     * An optional `extra` object can be provided for additional configuration.
     *
     * @param {Object} [params] - The temperature values.
     * @param {number} [params.kelvin] - Temperature in Kelvin.
     * @param {number} [params.celsius] - Temperature in Celsius.
     * @param {number} [params.fahrenheit] - Temperature in Fahrenheit.
     * @param {Object} [extra] - Additional configuration options.
     * @param {boolean} [extra.debug=false] - Enable debug logging.
     * @param {number} [extra.decimals=0] - Number of decimal places for output.
     */
    constructor({kelvin, celsius, fahrenheit} = {kelvin: undefined, celsius: undefined, fahrenheit: undefined}, extra = {}) {
        this.#configure(extra);

        if (kelvin || Number.isFinite(kelvin)) {
            this.kelvin = kelvin;
        } else if (celsius || Number.isFinite(celsius)) {
            this.celsius = celsius;
        } else if (fahrenheit || Number.isFinite(fahrenheit)) {
            this.fahrenheit = fahrenheit;
        }
    }

    /**
     * Configures the instance with the provided settings.
     *
     * @param {Object} config - Configuration options.
     * @param {boolean} [config.debug=false] - Enables debug mode if true.
     * @param {number} [config.decimals=0] - Sets the number of decimal places.
     * @private
     */
    #configure(config) {
        if (config?.debug) {
            this.#options.debug = true;
        }

        if (config?.decimals) {
            if (Number.isInteger(config.decimals)) {
                this.#options.decimals = config.decimals;
            }
        }
    }

    //#endregion constructor & initialise

    /**
     * Logs the current temperature values in Kelvin, Celsius, and Fahrenheit to the console
     * if debugging is enabled.
     * @private
     */
    #log() {
        if (this.#options.debug)
            console.log({kelvin: this.kelvin, celsius: this.celsius, fahrenheit: this.fahrenheit});
    }
}

// const config = {
//     debug: true,
//     decimals: 2,
// };

// const tc = new TemperatureThing({kelvin: undefined, celsius: undefined, fahrenheit: undefined}, config);
// tc.celsius = 28;
// const tcc = TemperatureThing.fromCelsius(28, config);
// const tcf = TemperatureThing.fromFahrenheit(40, config);
// console.log(TemperatureThing.fromKelvin(300, config).celsius);
// tc.celsius = 0;
// tc.kelvin = 0;
