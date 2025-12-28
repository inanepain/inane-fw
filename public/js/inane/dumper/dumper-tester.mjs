/**
 * DumperTester is a utility class for testing and demonstrating the functionality of Dumper logging instances.
 * It allows for dynamic switching between different Dumper instances, adjusting log levels, and testing log bubbling options.
 *
 * @class
 * @classdesc
 * Provides methods to:
 * - Initialize and switch between Dumper instances.
 * - Log Dumper version and log level information.
 * - Test logging at various levels (trace, debug, info, warn, error).
 * - Set log levels and bubbling options for parent/child Dumper relationships.
 * - Run automated test scripts to validate Dumper behaviors.
 *
 * @example
 * const tester = new DumperTester();
 * tester.runAutomatedTestScript();
 */
class DumperTester {
    /**
     * @type {Dumper}
     * @private
     * Holds the reference to the dumper instance or value.
     */
    #dumper = undefined;

    /**
     * Indicates whether the instance has been initialised.
     * @type {boolean}
     * @private
     */
    #initialised = false;

    /**
     * Initializes the dumper instance for the class.
     *
     * If a dumper is not provided, it attempts to use the existing instance or falls back to `globalThis.Dumper`.
     * Logs the dumper name if a new dumper is set, and updates version and log level information.
     *
     * @param {Dumper} [dumper=undefined] - The dumper instance to initialize with. If not provided, uses the existing or global dumper.
     * @param {boolean} [showVersion=false] - If true, forces the version log.
     * @private
     */
    #initialise(dumper = undefined, showVersion = false) {
        if (dumper === undefined) {
            if (this.#dumper !== undefined) {
                dumper = this.#dumper;
            } else {
                dumper = globalThis.Dumper;
            }
        }

        if (this.#dumper !== dumper) {
            this.#message(`==================== ${dumper.name}: ${dumper.level.name} (${dumper.level.value}) ====================`, true);

            this.#dumper = dumper;
            this.#logVersion(showVersion);
        }
    }

    /**
     * Returns a string representation of the current dumper.
     * If the dumper's name is 'Dumper', returns 'Global Dumper'.
     * Otherwise, returns 'Named Dumper: (<dumper name>)'.
     *
     * @private
     * @returns {string} Description of the current dumper.
     */
    #getDumperDescription() {
        if (this.#dumper === globalThis.Dumper) {
            return 'Global Dumper';
        } else {
            return `Named Dumper: ${this.#dumper.name}`;
        }
    }

    /**
     * Logs a message with the current dumper description.
     * @param {string} message - The message to be logged.
     * @private
     *
     * @returns {this} Returns the current instance for method chaining.
     */
    #message(message, plain = false) {
        if (plain === true) {
            globalThis.Dumper.log(message);
        } else {
            globalThis.Dumper.log(`${this.#getDumperDescription()}: ${message}`);
        }

        return this;
    }

    /**
     * Logs the current version of the dumper to the console or output.
     * Utilizes the private #message method to display the version information.
     * @private
     *
     * @param {boolean} [force=false] - If true, forces the version log.
     *
     * @returns {this} Returns the current instance for method chaining.
     */
    #logVersion(force = false) {
        if (!this.#initialised || force) {
            this.#initialised = true;
            this.#message(`version: ${this.#dumper.VERSION}`);
        }

        return this;
    }

    /**
     * Logs test messages at various log levels using the Dumper utility.
     * This method demonstrates logging at trace, debug, info, warn, and error levels.
     * Intended for verifying that all logging levels are functioning as expected.
     *
     * @private
     */
    #testLogging() {
        this.#message('-------------------- TEST LOGGING --------------------', true);
        this.#dumper.trace('trace');
        this.#dumper.debug('debug');
        this.#dumper.info('info');
        this.#dumper.warn('warn');
        this.#dumper.error('error');
    }

    /**
     * Sets the dumper instance for the current object.
     *
     * @param {*} [dumper=undefined] - The dumper instance to set. If not provided, defaults to undefined.
     * @param {boolean} [showVersion=false] - If true, forces the version log.
     *
     * @returns {this} Returns the current instance for method chaining.
     */
    setDumper(dumper = undefined, showVersion = false) {
        this.#initialise(dumper, showVersion);

        return this;
    }

    /**
     * Sets the logging level and optionally configures bubbling options for parent and children.
     *
     * @param {Object} level - The new logging level to set. Should have a `name` property.
     * @param {boolean|null|undefined} [optionBubbleFromParent=undefined] -
     *        If `true`, enables bubbling from parent.
     *        If `false`, disables bubbling from parent.
     *        If `null`, logs the current bubbling from parent state.
     *        If `undefined`, does not change the current state.
     * @param {boolean|null|undefined} [optionBubbleToChildren=undefined] -
     *        If `true`, enables bubbling to children.
     *        If `false`, disables bubbling to children.
     *        If `null`, logs the current bubbling to children state.
     *        If `undefined`, does not change the current state.
     * @returns {this} Returns the current instance for chaining.
     */
    setLevel(level, optionBubbleFromParent = undefined, optionBubbleToChildren = undefined) {
        if (optionBubbleToChildren === true) {
            this.#dumper.optionBubbleToChildren = true;
            this.#message('Bubble to children: enabled');
        } else if (optionBubbleToChildren === false) {
            this.#dumper.optionBubbleToChildren = false;
            this.#message('Bubble to children: disabled');
        } else if (optionBubbleToChildren === null) {
            this.#message(`Bubble to children: ${this.#dumper.optionBubbleToChildren ? 'enabled' : 'disabled'}`);
        }

        const previous = this.#dumper.level;
        this.#dumper.level = level;

        if (previous !== this.#dumper.level) {
            this.#message(`LogLevel: ${previous.name} => ${this.#dumper.level.name}`);
        }

        if (optionBubbleFromParent === true) {
            this.#dumper.optionBubbleFromParent = true;
            this.#message('Bubble from parent: enabled');
        } else if (optionBubbleFromParent === false) {
            this.#dumper.optionBubbleFromParent = false;
            this.#message('Bubble from parent: disabled');
        } else if (optionBubbleFromParent === null) {
            this.#message(`Bubble from parent: ${this.#dumper.optionBubbleFromParent ? 'enabled' : 'disabled'}`);
        }

        return this;
    }

    /**
     * Tests the dumper functionality by setting the provided dumper and performing a test log.
     *
     * @param {*} [dumper=undefined] - The dumper instance to set. If not provided, defaults to undefined.
     *
     * @returns {this} Returns the current instance for method chaining.
     */
    testDumper(dumper = undefined) {
        this.setDumper(dumper);
        this.#testLogging();

        return this;
    }

    /**
     * Runs a series of automated tests for the Dumper logging system.
     *
     * This method exercises various Dumper configurations and behaviors, including:
     * - Basic Dumper usage with default and custom levels.
     * - Creating and testing sub-dumpers with different log levels.
     * - Setting log levels and testing log bubbling from parent to child dumpers.
     * - Disabling bubbling and verifying child dumper behavior.
     * - Testing log level propagation with bubbling to children disabled.
     *
     * The tests are intended to verify correct Dumper instantiation, log level management,
     * and parent-child bubbling logic.
     *
     * @returns {this} Returns the current instance for method chaining.
     */
    runAutomatedTestScript() {
        this.testDumper();
        this.testDumper(Dumper.get('Tester', {level: 'debug'}));

        this
            .testDumper(Dumper.get('Tester').get('SubTester1', {level: 'trace'}))
            .setLevel(Dumper.WARN)
            .testDumper()
            ;

        this
            .testDumper(Dumper.get('Tester').get('SubTester2', {level: 'debug'}))
            .setLevel(Dumper.WARN)
            .testDumper()
            ;

        // Test bubbling from parent
        this
            .setDumper(Dumper.get('Tester'))
            .setLevel(Dumper.DEBUG)
            .setDumper(Dumper.get('Tester').get('SubTester1'))
            .testDumper()
            ;

        // Test bubbling from parent with child not listening
        this
            .setLevel(Dumper.ERROR, false)
            .setDumper(Dumper.get('Tester'))
            .setLevel(Dumper.INFO)
            .setDumper(Dumper.get('Tester').get('SubTester1'))
            .setDumper(Dumper.get('Tester').get('SubTester2'))
            ;

        // Testing setting level on a dumper with bubbling to children disabled
        this
            .setDumper(Dumper.get('Tester'))
            .setLevel(Dumper.WARN, undefined, false)
            .setDumper(Dumper.get('Tester').get('SubTester2'), true)
            ;

        return this;
    }
}

export { DumperTester };
