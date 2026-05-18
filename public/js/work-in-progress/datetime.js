/**
 * Represents a Datetime utility class for handling and converting between Unix timestamps
 * and Mac absolute time, as well as formatting dates.
 */
class Datetime {
    /**
     * Retrieves the CFAbsoluteTime offset, which represents the number of seconds
     * between January 1, 2001, 00:00:00 GMT (the reference date for Core Foundation),
     * and the Unix epoch (January 1, 1970, 00:00:00 GMT).
     *
     * @return {number} The constant offset value in seconds, 978307200.
     */
    static get CFAbsoluteTimeOffset() {
        return 978_307_200;
    }

    /**
     * Represents configuration settings for formatting options.
     *
     * @property {number} formatted - Determines the format type.
     *                                2 indicates a specific custom format,
     *                                while 1 represents the ISO standard format.
     */
    #options = {
        'formatted': 2 // 1 = ISO
    };

    /**
     * An array containing the abbreviated names of the months in a year.
     * Each element corresponds to a specific month in chronological order,
     * starting from January ('Jan') and ending with December ('Dec').
     */
    static #months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

    /**
     * Specifies the format in which time is represented.
     *
     * The value assigned to this variable defines the time format
     * as a Unix timestamp, which is the number of seconds that have
     * elapsed since January 1, 1970 (midnight UTC/GMT).
     *
     * Possible uses include time conversion, timestamp validation,
     * and compatibility with systems requiring Unix time formatting.
     */
    #timeFormat = 'UnixTimestamp';
    /**
     * Represents a data object that can be used to store and manage key-value pairs.
     * This object is initialized as empty but can be populated with dynamic properties as needed.
     * Commonly used as a generic container for temporary or structured data in an application.
     */
    #data = {};

    /**
     * Constructs an instance of the class with a timestamp and its format.
     *
     * @param {number|null} [eitherTimestamp=null] - The timestamp value to initialize. If null, the current timestamp is used.
     * @param {boolean} [isUnixTimestamp=true] - A flag indicating whether the given timestamp is in Unix Timestamp format (true) or Mac Absolute Time format (false).
     *
     * @return {void} Initializes the instance with appropriate timestamp formats and precomputes formatted datetime.
     */
    constructor(eitherTimestamp = null, isUnixTimestamp = true) {
        this.#timeFormat = isUnixTimestamp ? 'UnixTimestamp' : 'MacAbsoluteTime';

        if (eitherTimestamp === null) {
            eitherTimestamp = Math.floor(Date.now() / 1000);
            if (this.#timeFormat === 'MacAbsoluteTime') {
                eitherTimestamp = Datetime.unixTimestamp2macAbsoluteTime(eitherTimestamp);
            }
        }

        this.#data[this.#timeFormat] = eitherTimestamp;
        this.#data[isUnixTimestamp ? 'MacAbsoluteTime' : 'UnixTimestamp'] = isUnixTimestamp ? Datetime.unixTimestamp2macAbsoluteTime(eitherTimestamp) : Datetime.macAbsoluteTime2UnixTimestamp(eitherTimestamp);
        this.#formattedDatetime();
    }

    /**
     * Converts a given Unix timestamp into an instance of the current class.
     *
     * @param {number} unixTimestamp - The Unix timestamp to be converted.
     * @return {Object} An instance of the current class initialized with the specified Unix timestamp.
     */
    static fromUnixTimestamp(unixTimestamp) {
        return new this(unixTimestamp);
    }

    /**
     * Converts a Mac Absolute Time value into an instance of this class.
     *
     * Mac Absolute Time represents the number of seconds since midnight
     * on January 1, 2001, in the Gregorian calendar.
     *
     * @param {number} macAbsoluteTime - The Mac Absolute Time value to convert.
     * @return {Object} An instance of this class representing the given time.
     */
    static fromMacAbsoluteTime(macAbsoluteTime) {
        return new this(macAbsoluteTime, false);
    }

    /**
     * Converts Mac absolute time (seconds since 2001-01-01) to Unix timestamp (milliseconds since 1970-01-01).
     *
     * @param {Number} macAbsoluteTime - The Mac absolute time in seconds
     *
     * @returns {Number} The Unix timestamp in milliseconds
     */
    static macAbsoluteTime2UnixTimestamp(macAbsoluteTime) {
        return this.CFAbsoluteTimeOffset + Number.parseInt(macAbsoluteTime);
    }

    /**
     * Converts Unix timestamp (milliseconds since 1970-01-01) to Mac absolute time (seconds since 2001-01-01).
     *
     * @param {Number} unixTimestamp - The Unix timestamp in milliseconds
     *
     * @returns {Number} The Mac absolute time in seconds
     */
    static unixTimestamp2macAbsoluteTime(unixTimestamp) {
        return unixTimestamp - this.CFAbsoluteTimeOffset;
    }

    #formattedDatetime() {
        const d = new Date(this.#data.UnixTimestamp * 1_000);

        if (this.#options.formatted === 1) this.#data['Formatted'] = d.toISOString()
            .replace('T', ' ')
            .substring(0, 19)
            .replace(/-/g, '-');
        else this.#data['Formatted'] = `${d.getFullYear()}-${this.constructor.#months[d.getMonth()]}-${String(d.getDate()).padStart(2, '0')} ` +
            `${String(d.getHours()).padStart(2, '0')}:` +
            `${String(d.getMinutes()).padStart(2, '0')}:` +
            `${String(d.getSeconds()).padStart(2, '0')}`;
    }

    dump() {
        console.debug(this.#timeFormat, this.#data);
    }
}

let dt1 = Datetime.fromMacAbsoluteTime(771_958_361.043_517);
dt1.dump();

let dt2 = Datetime.fromUnixTimestamp(1_750_265_561);
dt2.dump();

let dt3 = new Datetime();
dt3.dump();

let dt4 = new Datetime(null, false);
dt4.dump();
