import {MergeOptions} from '../inane/class-lib/MergeOptions.mjs';
import {Clamp} from '../inane/class-lib/Clamp.mjs';
import {Dumper} from '../inane/dumper.mjs';

/**
 * ActivityPicker - Randomly selects sexual activities from a predefined list.
 * Starts with range 0-9, expands max to 99, then contracts min until complete.
 * Returns "RESET" and restarts after finishing the full progression.
 */
class ActivityPicker extends MergeOptions {
    /**
     * A list of intimate activities.
     *
     * This array contains a collection of intimate or sexual activities
     * represented as strings. Each entry in the array describes a specific
     * activity, which can be utilized in contexts related to adult content,
     * relationship education, or similar purposes.
     */
    #activities = [
        'Breast caressing',
        'Grinding while clothed',
        '69 position oral',
        'Anal rimming (him on her)',
        'Clitoral fingering',
        'Handjob techniques',
        'Shallow penetration',
    ];

    /**
     * Configuration options for the application behavior.
     *
     * @property {number} rangeSize - The size of the range to be used.
     * @property {number} step - The incremental step value for processing.
     * @property {number} retryDuplicates - The number of retry attempts allowed for duplicate handling.
     * @property {string} logLevel - The log level for system output.
     * @property {Object} debug - Debugging configuration options.
     * @property {boolean} debug.pick - Flag to indicate if pick actions should be logged.
     * @property {boolean} debug.lastActivity - Flag to indicate if the last activity should be logged.
     * @property {boolean} debug.options - Flag to indicate if options activity should be logged.
     * @property {boolean} debug.duplicate - Flag to indicate if duplicate actions should be logged.
     */
    #options = {
        rangeSize: 9,
        step: 5,
        retryDuplicates: 3,
        logLevel: 'WARN',
        debug: {
            pick: false,
            lastActivity: false,
            options: false,
            duplicate: false,
            numberOfPicks: false,
        }
    };

    #dumper;

    /**
     * Represents a cache object used to store data related to indices.
     *
     * @property {number} minIndex - The smallest index currently stored in the cache.
     */
    #cache = {
        minIndex: 0,
    };

    /**
     * Represents the current index in a sequence or collection.
     * Used to track or reference the position of an element within a structure.
     * The value is typically a number or null when no position is selected.
     * Initialized to null, indicating that no index is currently set.
     */
    #currentIndex = null;
    /**
     * A boolean flag indicating whether a certain process, task, or operation
     * has been completed.
     *
     * When `true`, it signifies that the operation is finished.
     * When `false`, it signifies that the operation is still in progress or incomplete.
     */
    #isComplete = false;

    /**
     * Retrieves the minimum index from the cache.
     *
     * @return {number} The minimum index stored in the cache.
     */
    get #minIndex() {
        return this.#cache.minIndex;
    }

    /**
     * Sets the minimum index while ensuring it does not exceed the last activity index.
     * If the provided minimum index exceeds the last activity index, it is set to the last activity index,
     * and the completion state is updated.
     *
     * @param {number} minIndex - The proposed minimum index to be set.
     */
    set #minIndex(minIndex) {
        if ((this.#cache.minIndex = this.clamp(minIndex, 0, this.#lastActivity)) === this.#lastActivity)
            this.#isComplete = true;
    }

    /**
     * Calculates the maximum index based on the minimum index, range size,
     * and the last recorded activity.
     * Adjusts the maximum index if it exceeds the last activity value.
     *
     * @return {number} The calculated maximum index value.
     */
    get #maxIndex() {
        let maxIndex = this.#minIndex + this.#options.rangeSize;
        if (maxIndex > this.#lastActivity) maxIndex = this.#lastActivity;
        return maxIndex;
    }

    /**
     * Retrieves the index of the last activity in the activity list.
     *
     * @return {number} The index of the most recent activity in the #activities array.
     */
    get #lastActivity() {
        return this.#activities.length - 1;
    }

    // mergeMethod = ActivityPicker.mergeMethodUpdateOnly;
    // mergeMethod = ActivityPicker.mergeMethodAddOnly;
    // mergeMethod = ActivityPicker.mergeMethodAddAndUpdate;

    /**
     * Determines whether the starting index is the minimum index.
     *
     * @return {boolean} True if the starting index equals the minimum index, otherwise false.
     */
    get start() {
        return this.#minIndex === 0;
    }

    /**
     * Retrieves the completion status of the current instance.
     *
     * @return {boolean} Indicates whether the current instance is complete.
     */
    get end() {
        return this.#isComplete;
    }

    /**
     * Retrieves the current activity from the activities list.
     *
     * The activity is determined by the current index. If the index is not set,
     * the method defaults to the first activity in the list.
     *
     * @return {Object} The current activity object from the activities list.
     */
    get activity() {
        return this.#activities[this.#currentIndex || 0];
    }

    /**
     * Constructs a new instance of the class and initializes its state.
     *
     * @param {Array} activities - The list of activities to be managed. If undefined or null, no activities will be initialized.
     * @param {Object} [options={rangeSize: 9, step: 5}] - Configuration options for initializing the instance.
     * @param {number} [options.rangeSize=9] - The range size to be used in the configuration.
     * @param {number} [options.step=5] - The step value to be used in the configuration.
     * @return {void} This constructor does not return a value but initializes the class instance.
     */
    constructor(activities, options = {rangeSize: 9, step: 5}) {
        super();
        if (activities !== undefined && activities !== null) {
            this.#activities = activities;
        }

        this.clamp = Clamp.prototype.clamp.bind(this);
        this.mergeOptions(this.#options, options);

        this.#dumper = Dumper.get('ActivityPicker', {level: this.#options.logLevel});

        this.#logDebug('lastActivity', 'lastActivity', this.#lastActivity);

        this.#logDebug('options', 'mergeMethod', this.mergeMethod);
        this.#logDebug('options', '#options', this.#options);

        this.reset();
    }

    /**
     * Logs debug messages to the console for a specific context if debugging is enabled.
     *
     * @param {string} logLevel
     * @param {string} context - The context or category for the debug message.
     * @param {...any} messages - The messages or data to log to the console.
     * @return {void} This method does not return a value.
     */
    #log(logLevel, context, ...messages) {
        if (this.#options.debug[context]) this.#dumper[logLevel](...messages);
    }

    /**
     * Logs debug messages to the console for a specific context if debugging is enabled.
     *
     * @param {string} context - The context or category for the debug message.
     * @param {...any} messages - The messages or data to log to the console.
     * @return {void} This method does not return a value.
     */
    #logDebug(context, ...messages) {
        if (this.#options.debug[context]) this.#log('debug', context, ...messages);
    }

    /**
     * Logs debug messages to the console for a specific context if debugging is enabled.
     *
     * @param {string} context - The context or category for the debug message.
     * @param {...any} messages - The messages or data to log to the console.
     * @return {void} This method does not return a value.
     */
    #logInfo(context, ...messages) {
        if (this.#options.debug[context]) this.#log('info', context, ...messages);
    }

    /**
     * Logs debug messages to the console for a specific context if debugging is enabled.
     *
     * @param {string} context - The context or category for the debug message.
     * @param {...any} messages - The messages or data to log to the console.
     * @return {void} This method does not return a value.
     */
    #logWarn(context, ...messages) {
        if (this.#options.debug[context]) this.#log('warn', context, ...messages);
    }

    /**
     * Logs debug messages to the console for a specific context if debugging is enabled.
     *
     * @param {string} context - The context or category for the debug message.
     * @param {...any} messages - The messages or data to log to the console.
     * @return {void} This method does not return a value.
     */
    #logError(context, ...messages) {
        if (this.#options.debug[context]) this.#log('error', context, ...messages);
    }

    /**
     * Generates a random integer between the specified minimum and maximum values, inclusive.
     *
     * @param {number} min - The minimum value (inclusive).
     * @param {number} max - The maximum value (inclusive).
     * @return {number} A random integer between min and max.
     */
    #randomNumber(min, max) {
        return Math.floor(Math.random() * (max - min + 1)) + min;
    }

    /**
     * Sets the number of picks and optionally adjusts the range optimization.
     *
     * @param {number} numberOfPicks - The desired number of picks to be set.
     * @param {boolean} [optimiseRange=false] - Indicates whether to optimize the range size based on the step value. A number can be used to set it manually.
     *
     * @return {this} The instance of the class to allow method chaining.
     */
    setNumberOfPicks(numberOfPicks, optimiseRange = false) {
        this.#options.step = Math.ceil((this.#lastActivity + 1) / numberOfPicks);

        const newValues = {};
        newValues.step = this.#options.step;

        if (optimiseRange) {
            if (typeof optimiseRange === 'number') {
                this.#options.rangeSize = optimiseRange;
            } else {
                this.#options.rangeSize = Math.ceil((this.#lastActivity + 1) / this.#options.step);
            }
            newValues.rangeSize = this.#options.rangeSize;
        }

        this.#logDebug('numberOfPicks', 'newValues:', newValues);

        return this;
    }

    /**
     * Resets the internal state of the object by setting the minimum index to zero
     * and marking the completion status as false.
     *
     * @return {this} The instance of the class to allow method chaining.
     */
    reset() {
        this.#minIndex = 0;
        this.#isComplete = false;

        return this;
    }

    /**
     * Selects an item based on the defined logic and updates internal state accordingly.
     *
     * If the internal state indicates completion, the method will reset the state and return 'RESET'.
     * Otherwise, it generates a random index within a defined range, ensuring no immediate duplicates
     * up to a specified retry limit. The selected item's index is then logged and the range is updated
     * for the next call.
     *
     * @return {string} The activity associated with the picked index or 'RESET' if the state was reset.
     */
    pick() {
        if (this.#isComplete) {
            this.reset();
            return 'RESET';
        }

        let i = 0, index;
        do {
            if (i > 0) this.#logDebug('duplicate', '\t', 'attempt:', i, 'of:', this.#options.retryDuplicates, 'duplicateIndex:', index);
            index = this.#randomNumber(this.#minIndex, this.#maxIndex);
        } while (index === this.#currentIndex && this.#options.retryDuplicates > i++);
        this.#currentIndex = index;

        this.#logDebug('pick', '\t', 'minIndex:', this.#minIndex, 'maxIndex:', this.#maxIndex, 'currentIndex:', this.#currentIndex);

        this.#minIndex += this.#options.step;
        return this.activity;
    }
}

export {ActivityPicker};
