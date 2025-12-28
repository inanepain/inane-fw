const matches = ['HIGHER', 'EQUAL', 'LOWER'];

/**
 * VersionMatch
 *
 * Returned by Version.compare
 *
 * HIGHER = 1
 * EQUAL = 0
 * LOWER = -1
 *
 * @version 1.0.0
 */
class VersionMatch {
    #name;
    #value;
    constructor({ name, value }) {
        // for (const func of matches) Object.defineProperty(this, func, {
        //     value: VersionMatch[func],
        // });

        if (VersionMatch[name]) return VersionMatch[name];
        else if (!matches.includes(name)) return VersionMatch.EQUAL;

        this.#name = name;
        this.#value = value;

        Object.defineProperties(this, {
            name: {
                enumerable: true,
                value: this.#name,
            },
            value: {
                enumerable: true,
                value: this.#value,
            },
        });
    }

    /**
     * Get VersionMatch by value
     *
     * @param {number} value VersionMatch value
     *
     * @returns {VersionMatch} matching value
     */
    static from(value) {
        const t = {
            '1': 'HIGHER',
            '0': 'EQUAL',
            '-1': 'LOWER',
        };
        return this[t[value]] || null;
    }
}

/**
 * Define VersionMatch values
 */
Object.defineProperties(VersionMatch, {
    HIGHER: {
        value: new VersionMatch({ value: 1, name: 'HIGHER' }),
        enumerable: true,
    },
    EQUAL: {
        value: new VersionMatch({ value: 0, name: 'EQUAL' }),
        enumerable: true,
    },
    LOWER: {
        value: new VersionMatch({ value: -1, name: 'LOWER' }),
        enumerable: true,
    },
});

/**
 * Version
 *
 * @version 1.0.0
 */
class Version {
    /**
     * Target version number
     */
    #version;

    /**
     * lexicographical<br />
     *
     * compares each part of the version strings lexicographically instead of naturally;
     *  this allows suffixes such as "b" or "dev"
     *  but will cause "1.10" to be considered smaller than "1.2".
     *
     * @default false
     *
     * @type {bool}
     */
    #lexicographical;

    /**
     * zeroExtend<br />
     *
     * changes the result if one version string has less parts than the other.
     *  In this case the shorter string will be padded with "zero" parts instead of being considered smaller.
     *
     * @default true
     *
     * @type {bool}
     */
    #zeroExtend;

    /**
     * Optional flags that affect comparison behavior:
     * @typedef {Object} Options
     * @property {bool} [lexicographical] - Optional flag compares each part of the version strings lexicographically
     * @property {bool} [zeroExtend=true] - Optional flag changes the result if one version string has less parts than the other
     */
    /**
     *
     * @param {string} version Version requierment.
     * @param {Options} options Optional flags that affect comparison behavior:
     */
    constructor(version, { lexicographical, zeroExtend = true } = {}) {
        if (!version) throw new SyntaxError('Missing parameter: version');
        if (!version.split('.').every(this.#isValidPart)) throw new SyntaxError('Invalid parameter `version` must be a semver version string');

        this.#version = version;
        this.#lexicographical = lexicographical;
        this.#zeroExtend = zeroExtend;
    }

    get version() {
        return this.#version;
    }

    /**
     * Validate version segmant
     *
     * @param {bool} part version segmant
     *
     * @returns is valid
     */
    #isValidPart(part) {
        return (this?.#lexicographical ? /^\d+[A-Za-z]*$/ : /^\d+$/).test(part);
    }


    /**
     * Compares a software version numbers
     *
     * @param {string} version Version to be compared.
     * @returns {VersionMatch|NaN}
     * <ul>
     *    <li>VersionMatch::EQUAL (0) if the versions are equal</li>
     *    <li>VersionMatch::LOWER (-1) iff version < this</li>
     *    <li>VersionMatch::HIGHER (1) iff version > this</li>
     *    <li>NaN if version string is in the wrong format</li>
     * </ul>
     */
    compare(version) {
        let v1parts = version.split('.'),
            v2parts = this.#version.split('.');

        if (!v1parts.every(this.#isValidPart)) return NaN;

        if (this.#zeroExtend) {
            while (v1parts.length < v2parts.length) v1parts.push('0');
            while (v2parts.length < v1parts.length) v2parts.push('0');
        }

        if (!this.#lexicographical) {
            v1parts = v1parts.map(Number);
            v2parts = v2parts.map(Number);
        }

        for (var i = 0; i < v1parts.length; ++i) {
            if (v2parts.length == i) return VersionMatch.HIGHER;

            if (v1parts[i] == v2parts[i]) continue;
            else if (v1parts[i] > v2parts[i]) return VersionMatch.HIGHER;
            else return VersionMatch.LOWER;
        }

        if (v1parts.length != v2parts.length) return VersionMatch.LOWER;

        return VersionMatch.EQUAL;
    }

    /**
     * Require higher version
     *
     * @param {string} version to compare
     *
     * @returns {bool} is higher
     */
    isHigher(version) {
        return this.compare(version) == VersionMatch.HIGHER;
    }

    /**
     * Require higher or equal version
     *
     * @param {string} version to compare
     *
     * @returns {bool} is higher or equal
     */
    isHigherOrEqual(version) {
        return this.compare(version)?.value >= VersionMatch.EQUAL.value;
    }

    /**
     * Require equal version
     *
     * @param {string} version to compare
     *
     * @returns {bool} is equal
     */
    isEqual(version) {
        return this.compare(version) == VersionMatch.EQUAL;
    }

    /**
     * Require lower or equal version
     *
     * @param {string} version to compare
     *
     * @returns {bool} is lower or equal
     */
    isLowerOrEqual(version) {
        return this.compare(version)?.value <= VersionMatch.EQUAL.value;
    }

    /**
     * Require lower version
     *
     * @param {string} version to compare
     *
     * @returns {bool} is lower
     */
    isLower(version) {
        return this.compare(version) == VersionMatch.LOWER;
    }
}

export { Version, VersionMatch };

//////////// EXAMPLES
// const log = console.log;
// const Backbone = {VERSION: '1.2'};

// const bbv = new Version('1.2.0');
// if (!bbv.isHigherOrEqual(Backbone.VERSION)) log('Please update Backbone'); // no error: versions match
// else log('LOADING Backbone...');

// const bbv2 = new Version('1.2.0', { zeroExtend: false });
// if (!bbv2.isHigherOrEqual(Backbone.VERSION)) log('Please update Backbone'); // no error: versions match
// else log('LOADING Backbone...');
// log('');
// if (bbv.isLower(Backbone.VERSION)) log('Please update Backbone'); // no error: versions match
// else log('LOADING Backbone...');

// log('');
// log('TEST: VersionMatch');
// log(VersionMatch.from(-1));

// const $v = new Version('1.0');
// log(`Target Version:`, $v.version);

// let r = $v.compare('1.0');

// log('r == VersionMatch.EQUAL', r, r == VersionMatch.EQUAL);
// log('isHigher 0.9', $v.isHigher('0.9'));
// log()
// const iv = new VersionMatch({ name: 'bob', value: 10 });
// log('new VersionMatch({name: \'bob\', value: 10})', iv, iv.name, iv.value);
// log('iv.EQUAL')
// log(iv.EQUAL);
// log('AAA')
// log(iv == iv.EQUAL, iv.EQUAL  == VersionMatch.EQUAL);
// log('BBB')
// log(iv == VersionMatch.EQUAL);
