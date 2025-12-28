/**
 * Switch
 */
import { Dumper } from '../dumper.js';

Dumper.dump('MODULE', 'Switch', 'Load');

/**
 * Assert Switch. P.S. Assert prints on fail so ON=true
 * @typedef {Object} SwitchData
 * @property {boolean} [ON=false] - Assert On
 * @property {boolean} [OFF=true] - Assert Off
 * @property {boolean} [DEFAULT=true] - Can be set on/off
 * @memberof Switch
 */
/**
 * @type {SwitchData}
 * @memberof Switch
 */
const SwitchData = {
    ON: false,
    OFF: true,
    DEFAULT: undefined,
};

/**
 * @description Switch
 * @author Philip Michael Raab<philip@cathedral.co.za>
 * @version 1.0.0
 */
class Switch {
    /**
     * Creates an instance of Switch.
     *
     * @constructor
     * @param {boolean} defaultValue
     */
    constructor(defaultValue) {
        if (typeof defaultValue === 'boolean') {
            this._DEFAULT = defaultValue;
            if (typeof SwitchData.DEFAULT === 'undefined') SwitchData.DEFAULT = defaultValue;
        }
    }

    /**
     * @description ON
     * @readonly
     * @static
     * @memberof Switch
     */
    static get ON() {
        return false;
    }

    /**
     * @description OFF
     * @readonly
     * @static
     * @memberof Switch
     */
    static get OFF() {
        return true;
    }

    /**
     * @description Global DEFAULT. Can only be set once.
     * @readonly
     * @static
     * @memberof Switch
     */
    static get DEFAULT() {
        return typeof SwitchData.DEFAULT === 'undefined' ? this.OFF : SwitchData.DEFAULT;
    }

    /**
     * @description ON
     * @readonly
     * @memberof Switch
     */
    get ON() {
        return false;
    }

    /**
     * @description OFF
     * @readonly
     * @memberof Switch
     */
    get OFF() {
        return true;
    }

    /**
     * @description Instance DEFAULT. Set on instance creation.
     * @readonly
     * @memberof Switch
     */
    get DEFAULT() {
        return this.hasOwnProperty('_DEFAULT') ? this._DEFAULT : Switch.DEFAULT;
    }

    /**
     * @description Global DEFAULT.
     * @readonly
     * @memberof Switch
     */
    get DEFAULT_GLOBAL() {
        return Switch.DEFAULT;
    }
}

// export default Switch;
export {Switch as default};
