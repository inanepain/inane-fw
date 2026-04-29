class Datetime {
    static get CFAbsoluteTimeOffset() {
        return 978_307_200;
    }

    #options = {
        'formatted': 2 // 1 = ISO
    };

    static #months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

    #timeFormat = 'UnixTimestamp';
    #data = {};

    constructor(eitherTimestamp = null, isUnixTimestamp = true) {
        this.#timeFormat = isUnixTimestamp ? 'UnixTimestamp' : 'MacAbsoluteTime';

        this.#data[this.#timeFormat] = eitherTimestamp;
        this.#data[!isUnixTimestamp ? 'UnixTimestamp' : 'MacAbsoluteTime'] = isUnixTimestamp ? Datetime.unixTimestamp2macAbsoluteTime(eitherTimestamp) : Datetime.macAbsoluteTime2UnixTimestamp(eitherTimestamp);
        this.#formattedDatetime();
    }

    static fromUnixTimestamp(unixTimestamp) {
        return new this(unixTimestamp);
    }

    static fromMacAbsoluteTime(macAbsoluteTime) {
        return new this(macAbsoluteTime, false);
    }

    /**
     * Converts Mac absolute time (seconds since 2001-01-01) to Unix timestamp (milliseconds since 1970-01-01).
     *
     * @param {number} macAbsoluteTime - The Mac absolute time in seconds
     *
     * @returns {number} The Unix timestamp in milliseconds
     */
    static macAbsoluteTime2UnixTimestamp(macAbsoluteTime) {
        return Number.parseInt((this.CFAbsoluteTimeOffset + macAbsoluteTime) * 1);
        // return Number.parseInt((this.CFAbsoluteTimeOffset + macAbsoluteTime) * 1_000);
    }

    /**
     * Converts Unix timestamp (milliseconds since 1970-01-01) to Mac absolute time (seconds since 2001-01-01).
     *
     * @param {number} unixTimestamp - The Unix timestamp in milliseconds
     *
     * @returns {number} The Mac absolute time in seconds
     */
    static unixTimestamp2macAbsoluteTime(unixTimestamp) {
        return this.CFAbsoluteTimeOffset + (unixTimestamp / 1);
        // return this.CFAbsoluteTimeOffset + (unixTimestamp / 1_000);
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
