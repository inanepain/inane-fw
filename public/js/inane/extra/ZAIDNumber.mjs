
/**
 * ZAIDNumber class provides utilities for generating and manipulating South African ID numbers.
 *
 * The class includes methods for generating random components of an ID number such as birthday, gender, citizenship, and race.
 * It also provides methods to set these components and calculate the checksum for the ID number.
 *
 * @class
 * @example
 * // Create a new ZAIDNumber instance with random values
 * const specificId = new ZAIDNumber();
 * console.log(specificId.IDNumber); // Outputs a valid ZAID number
 *
 * // Create a new ZAIDNumber instance with a specific ID number
 * const id = new ZAIDNumber('8001015009087');
 * console.log(id.IDNumber); // Outputs '8001015009087'
 *
 * // Set specific components
 * id.setBirthday('900101').setGender('Male').setCitizenship('SouthAfricanCitizen').setRace('White');
 * console.log(id.IDNumber); // Outputs a ZAID number with the specified components '9001019019081'
 *
 * @version 1.0.0
 */
class ZAIDNumber {
    /**
     * Gets the version of the ZAIDNumber class.
     *
     * @returns {string} The current version of the ZAIDNumber class.
     */
    static get VERSION() {
        return '1.0.0';
    }

    /**
     * Gets the version of the current instance.
     *
     * @returns {string} The version of the current instance.
     */
    get VERSION() {
        return this.constructor.VERSION;
    }

    /**
     * Generates a random birthday date string in the format 'YYMMDD'.
     *
     * @param {string} [minDate='1950-01-01'] - The minimum date range in 'YYYY-MM-DD' format.
     * @param {string} [maxDate='2010-12-31'] - The maximum date range in 'YYYY-MM-DD' format.
     *
     * @returns {string} A random date string in the format 'YYMMDD'.
     */
    static Birthday = {
        Random(minDate = '1950-01-01', maxDate = '2010-12-31') {
            const startDate = new Date(minDate);
            const endDate = new Date(maxDate);

            const timeDiff = endDate.getTime() - startDate.getTime();
            const randomTime = Math.random() * timeDiff;
            const randomDate = new Date(startDate.getTime() + randomTime);
            return randomDate.toISOString().slice(2, 10).replaceAll('-', '');
        }
    }

    /**
     * Gender utility for generating random gender-specific or random 4-digit numbers.
     *
     * @namespace Gender
     * @property {string} Female - Generates a random 4-digit number between 0000 and 4999.
     * @property {string} Male - Generates a random 4-digit number between 5000 and 9999.
     * @method Random - Generates a random 4-digit number between 0000 and 9999.
     */
    static Gender = {
        /**
         * Generates a random 4-digit string representing a female identifier.
         * The number ranges from 0000 to 4999.
         *
         * @returns {string} A 4-digit string padded with leading zeros if necessary.
         */
        get Female() {
            return (Math.floor(Math.random() * (4999 - 0 + 1)) + 0).toString().padStart(4, '0');
        },
        /**
         * Generates a random 4-digit number representing a male identifier.
         * The number will be between 5000 and 9999, inclusive.
         *
         * @returns {string} A 4-digit string representing a male identifier.
         */
        get Male() {
            return (Math.floor(Math.random() * (9999 - 5000 + 1)) + 5000).toString().padStart(4, '0');
        },
        /**
         * Generates a random 4-digit string.
         *
         * @returns {string} A string representing a random 4-digit number, padded with leading zeros if necessary.
         */
        Random() {
            return (Math.floor(Math.random() * (9999 - 0 + 1)) + 0).toString().padStart(4, '0');
        }
    }

    /**
     * Citizenship class provides static properties and methods to handle different types of citizenship.
     *
     * @property {string} SouthAfricanCitizen - Represents a South African citizen with a value of '0'.
     * @property {string} PermanentResident - Represents a permanent resident with a value of '1'.
     * @property {string} Refugee - Represents a refugee with a value of '2'.
     *
     * @method Random - Returns a random citizenship value as a string ('0', '1', or '2').
     * @returns {string} A random citizenship value.
     */
    static Citizenship = {
        get SouthAfricanCitizen() {
            return '0';
        },
        get PermanentResident() {
            return '1';
        },
        get Refugee() {
            return '2';
        },
        Random() {
            return (Math.floor(Math.random() * (2 - 0 + 1)) + 0).toString();
        }
    }

    /**
     * Represents a Race with predefined values and a method to get a random race.
     *
     * @namespace Race
     * @property {string} White - Returns the string '8' representing the White race.
     * @property {string} Black - Returns the string '9' representing the Black race.
     * @method Random - Returns a random race value as a string ('8' or '9').
     */
    static Race = {
        /**
         * Gets the value representing the color white.
         * @returns {string} The value '8' representing white.
         */
        get White() {
            return '8';
        },
        /**
         * Gets the value representing the color black.
         * @returns {string} The string '9' representing the color black.
         */
        get Black() {
            return '9';
        },
        /**
         * Generates a random number between 8 and 9 (inclusive) and returns it as a string.
         *
         * @returns {string} A random number between 8 and 9 as a string.
         */
        Random() {
            return (Math.floor(Math.random() * (9 - 8 + 1)) + 8).toString();
        }
    }

    /**
     * Stores to the components of the ID number and provides a method to get the full ID number.
     *
     * @private
     * @type {Object}
     * @property {string} Birthday - A randomly generated birthday (digit: 1 - 6).
     * @property {string} Gender - A randomly generated gender (digit: 7 - 10).
     * @property {string} Citizenship - A randomly generated citizenship (digit: 11).
     * @property {string} Race - A randomly generated race (digit: 12).
     * @property {Function} getIDNumber - Method to get the ID number composed of Birthday, Gender, Citizenship, and Race (digit: 13).
     */
    #componets = {
        Birthday: this.constructor.Birthday.Random(),
        Gender: this.constructor.Gender.Random(),
        Citizenship: this.constructor.Citizenship.Random(),
        Race: this.constructor.Race.Random(),
        /**
         * Constructs and returns the ID number by concatenating the Birthday, Gender, Citizenship, and Race properties.
         *
         * @returns {string} The concatenated ID number.
         */
        getIDNumber() {
            return `${this.Birthday}${this.Gender}${this.Citizenship}${this.Race}`;
        }
    };

    /**
     * Calculates the checksum for a given ID number.
     *
     * The checksum is calculated using the Luhn algorithm, which involves reversing the first 12 digits of the ID number,
     * doubling every second digit, subtracting 9 from any results greater than 9, summing all the digits, and then finding
     * the difference between the sum and the next multiple of 10.
     *
     * @param {string|number} idNum - The ID number to calculate the checksum for. It should be at least 12 digits long.
     *
     * @returns {string} The calculated checksum as a single digit string.
     */
    #calculateChecksum(idNum) {
        let _idn = idNum.toString().substring(0, 12).split('').reverse().join('');
        let _s = 0;
        for (let i = 0; i < _idn.length; i++) {
            if (i % 2 !== 0) {
                _s += parseInt(_idn[i]);
            } else {
                let _d = parseInt(_idn[i]) * 2;
                _s += _d > 9 ? _d - 9 : _d;
            }
        }

        return (10-(_s % 10)).toString();
    }

    /**
     * Gets the birthday component.
     *
     * @returns {Date} The birthday component.
     */
    get birthday() {
        return this.#componets.Birthday;
    }

    /**
     * Gets the gender based on the ZAID number components.
     *
     * @returns {string} Returns 'Female' if the Gender component is less than or equal to 4999, otherwise 'Male'.
     */
    get gender() {
        return this.#componets.Gender <= 4999 ? 'Female' : 'Male';
    }

    /**
     * Gets the citizenship status based on the components' citizenship value.
     * Iterates through the Citizenship enumeration to find the matching key.
     *
     * @returns {string} The key representing the citizenship status.
     */
    get citizenship() {
        let k;
        for (const [key, value] of Object.entries(this.constructor.Citizenship)) {
            k = key;
            if (value == this.#componets.Citizenship) break;
        }

        return k;
    }

    /**
     * Gets the race based on the Race component.
     * Iterates through the Race entries of the constructor to find the matching race key.
     *
     * @returns {string} The key corresponding to the race value.
     */
    get race() {
        let k;
        for (const [key, value] of Object.entries(this.constructor.Race)) {
            k = key;
            if (value == this.#componets.Race) break;
        }

        return k;
    }

    /**
     * Constructs a ZAIDNumber instance.
     *
     * @param {string} idNumber - The South African ID number. Must be 13 characters long. If not provided, random values will be assigned.
     * @throws {Error} Throws an error if the idNumber is not 13 characters long.
     *
     * @property {string} #componets.Birthday - The birthdate component of the ID number (first 6 characters).
     * @property {string} #componets.Gender - The gender component of the ID number (characters 7 to 10).
     * @property {string} #componets.Citizenship - The citizenship component of the ID number (character 11).
     * @property {string} #componets.Race - The race component of the ID number (character 12).
     */
    constructor(idNumber) {
        if (idNumber?.length == 13) {
            this.#componets.Birthday = idNumber.substring(0, 6);
            this.#componets.Gender = idNumber.substring(6, 10);
            this.#componets.Citizenship = idNumber.substring(10, 11);
            this.#componets.Race = idNumber.substring(11, 12);
        } else if (idNumber) {
            throw new Error('The ID number must be 13 characters long.');
        }
    }

    /**
     * Sets the birthday for the current instance.
     *
     * @param {string} birthday - The birthday string to set formatted as `YYMMDD`. It can contain '-' or '/' as separators.
     *
     * @returns {ZAIDNumber} The current instance of ZAIDNumber for method chaining.
     */
    setBirthday(birthday) {
        this.#componets.Birthday = birthday?.replaceAll('-', '')?.replaceAll('/', '') || ZAIDNumber.Birthday.Random();
        return this;
    }

    /**
     * Sets the gender for the current instance.
     *
     * @param {string} gender - The gender to set. If not provided, a random gender will be assigned.
     *
     * @returns {ZAIDNumber} The current instance for chaining.
     */
    setGender(gender) {
        if (/^\d+$/.test(gender)) {
            this.#componets.Gender = gender;
            return this;
        }

        if (Object.keys(this.constructor.Gender).slice(0, -1).includes(gender))
            this.#componets.Gender = this.constructor.Gender[gender];
        else
            this.#componets.Gender = this.constructor.Gender.Random();
        return this;
    }

    /**
     * Sets the citizenship for the current instance.
     *
     * @param {string} citizenship - The citizenship to set. If not provided, a random citizenship will be assigned.
     *
     * @returns {ZAIDNumber} The current instance for chaining.
     */
    setCitizenship(citizenship) {
        if (Object.keys(this.constructor.Citizenship).slice(0, -1).includes(citizenship))
            citizenship = this.constructor.Citizenship[citizenship];

        this.#componets.Citizenship = Object.values(this.constructor.Citizenship).slice(0, -1).includes(citizenship) && citizenship || this.constructor.Citizenship.Random();
        return this;
    }

    /**
     * Sets the race for the current instance.
     *
     * @param {string} race - The race to set. If not provided, a random race will be assigned.
     *
     * @returns {ZAIDNumber} The current instance of ZAIDNumber for method chaining.
     */
    setRace(race) {
        if (Object.keys(this.constructor.Race).slice(0, -1).includes(race))
            race = this.constructor.Race[race];

        this.#componets.Race = Object.values(this.constructor.Race).slice(0, -1).includes(race) && race || this.constructor.Race.Random();
        return this;
    }

    /**
     * Gets the ID number with a checksum.
     *
     * @returns {string} The ID number concatenated with its checksum.
     */
    get IDNumber() {
        const idNum = this.#componets.getIDNumber();
        return idNum + this.#calculateChecksum(idNum);
    }
}

Object.freeze(ZAIDNumber.Birthday);
Object.freeze(ZAIDNumber.Gender);
Object.freeze(ZAIDNumber.Citizenship);
Object.freeze(ZAIDNumber.Race);

// const num = new ZAIDNumber();
// console.log(num.citizenship);
// console.log(num.race);
// num.setCitizenship(ZAIDNumber.Citizenship.Refugee).setRace('Black');
// console.log(num.IDNumber);
// console.log(num.citizenship);
// console.log(num.race);

const id = new ZAIDNumber('8001015009087');
id.setBirthday(ZAIDNumber.Birthday.Random()).setGender('Male').setCitizenship('SouthAfricanCitizen').setRace('White');
console.log(id.IDNumber);

export {ZAIDNumber};
