/**
 * iShortcut
 * @module icroot/iShortcut
 *
 * Description
 * @see https://git.inane.co.za:3000/Inane/inane-js/wiki/Inane_icRoot-iShortcut
 *
 * @author Philip Michael Raab <philip@cathedral.co.za>
 * @version 0.4.0
 */

/**
 * KeyMapping
 * @namespace
 * @property {Object} code - keys by code
 * @property {Object} char - keys by character
 * @property {Object} mod - modifier key save character
 * @property {string} [mod.SHIFT='S'] - shift key code
 * @property {string} [mod.ALT='A'] - alt key code
 * @property {string} [mod.CTRL='C'] - control key code
 */
const kbKeys = {
    code: {
        33: '!',
        35: '#',
        36: '$',
        37: '%',
        38: '&',
        39: '\'',
        40: '(',
        41: ')',
        42: '*',
        43: '+',
        44: ',',
        45: '"',
        46: '.',
        47: '/',
        48: '0',
        49: '1',
        50: '2',
        51: '3',
        52: '4',
        53: '5',
        54: '6',
        55: '7',
        56: '8',
        57: '9',
        63: '?',
        64: '@',
        65: 'A',
        66: 'B',
        67: 'C',
        68: 'D',
        69: 'E',
        70: 'F',
        71: 'G',
        72: 'H',
        73: 'I',
        74: 'J',
        75: 'K',
        76: 'L',
        77: 'M',
        78: 'N',
        79: 'O',
        80: 'P',
        81: 'Q',
        82: 'R',
        83: 'S',
        84: 'T',
        85: 'U',
        86: 'V',
        87: 'W',
        88: 'X',
        89: 'Y',
        90: 'Z',
        91: '[',
        93: ']',
        94: '^',
        95: '_',
        96: '`',
        123: '{',
        125: '}',
        126: '~',
        191: '?'
    },
    char: {
        '!': 33,
        '#': 35,
        '$': 36,
        '%': 37,
        '&': 38,
        '\'': 39,
        '(': 40,
        ')': 41,
        '*': 42,
        '+': 43,
        ',': 44,
        '"': 45,
        '.': 46,
        '/': 47,
        '0': 48,
        '1': 49,
        '2': 50,
        '3': 51,
        '4': 52,
        '5': 53,
        '6': 54,
        '7': 55,
        '8': 56,
        '9': 57,
        '?': 63,
        '@': 64,
        'A': 65,
        'B': 66,
        'C': 67,
        'D': 68,
        'E': 69,
        'F': 70,
        'G': 71,
        'H': 72,
        'I': 73,
        'J': 74,
        'K': 75,
        'L': 76,
        'M': 77,
        'N': 78,
        'O': 79,
        'P': 80,
        'Q': 81,
        'R': 82,
        'S': 83,
        'T': 84,
        'U': 85,
        'V': 86,
        'W': 87,
        'X': 88,
        'Y': 89,
        'Z': 90,
        '[': 91,
        ']': 93,
        '^': 94,
        '_': 95,
        '`': 96,
        '{': 123,
        '}': 125,
        '~': 126,
        '?': 191
    },
    mod: {
        SHIFT: 'S',
        ALT: 'A',
        CTRL: 'C',
        // metaKey: 'meta',
    }
};

/**
 * @type {Map} - stores the key combination code and callback method
 */
const scDb = new Map();

/**
 * Keyboard Shortcuts
 *
 * @author Philip Michael Raab <philip@cathedral.co.za>
 * @copyright 2020 Philip Michael Raab <philip@cathedral.co.za>
 *
 * @version 0.4.0
 *
 * @license MIT
 * @see {@link https://git.inane.co.za:3000/Inane/inane-js/raw/master/LICENSE MIT license}
 */
class iShortcut {
    /**
     * Version
     *
     * @readonly
     * @type {string} Version
     * @ignore
     */
    static get VERSION() {
        return '0.4.0';
    }
    get VERSION() {
        return this.constructor.VERSION;
    }

    /**
     * Creates an instance of iShortcut.
     */
    constructor() {
        document.addEventListener('keyup', (event) => {
            this.onKeyup.call(this, event);
        });

        this.add = this.addShortcut.bind(this);
        this.add('alt + ?', () => {
            this.help();
        }, 'Show registered shortcuts');
    }

    /**
     * Creates a code from shortcut text
     *
     * @param {string} shortcut text shortcut
     *
     * @returns {string}
     */
    #parseShortcut(shortcut) {
        let cmd = shortcut.toUpperCase().replaceAll(' ', '').replaceAll('CONTROL', 'CTRL').split('+');
        let cmdMod = Object.keys(kbKeys.mod).map(key => {
            if (cmd.includes(key)) return kbKeys.mod[cmd.splice(cmd.indexOf(key), 1)[0]]; return;
        });
        return cmdMod.concat([':']).concat(cmd).join('');
    }

    /**
     * Add keyboard shortcut and callback
     *
     * @example
     * // adds a shortcut
     * iShortcut.addShortcut('shift + alt + h', myFunc, 'Does Something');
     *
     * @param {string} shortcut the keyboard shortcut as text
     * @param {function} callback callback to run when shortcut is used
     * @param {string} [description=''] a description of shortcut for help
     *
     * @returns {iShortcut}
     */
    addShortcut(shortcut, callback, description = '') {
        const code = this.#parseShortcut(shortcut);
        if (!scDb.has(code)) scDb.set(code, {
            code: code,
            shortcut: shortcut.split(' + ').join('+').replaceAll('+', ' + '),
            description: description,
            listeners: []
        });

        scDb.get(code).listeners.push(callback);
        return this;
    }

    /**
     * Show registered shortcuts and descriptions
     *
     * @param {boolean} [alert=true] show alert of registered shortcuts
     *
     * @returns {string[]} shortcuts list
     */
    help(alert = true) {
        const shortcutDescriptions = [];
        shortcutDescriptions.push('SHORTCUT DESCRIPTIONS');

        for (const [key, element] of scDb) {
            let parts = element.shortcut.replaceAll(' ', '').split('+');
            for (let index = 0; index < parts.length; index++) parts[index] = parts[index].length > 1 ? parts[index].toUpperCase().padEnd(6, ' ') : parts[index].toUpperCase();
            const shortcut = parts.join(' + ');
            const tab = shortcut.length < 12 ? "\t\t\t" : "\t";
            shortcutDescriptions.push(`${shortcut}${tab}: ${element.description}`);
        }

        window.alert(shortcutDescriptions.join("\n"));
        return shortcutDescriptions;
    }

    /**
     * Keyup Event Handler
     *
     * @param {KeyboardEvent} event event to evaluate for keys
     *
     * @returns {iShortcut}
     */
    onKeyup(event) {
        // if (!event.key) return;
        if (!event.which) return;

        let code = '';
        code += event.shiftKey ? 'S' : '';
        code += event.altKey ? 'A' : '';
        code += event.ctrlKey ? 'C' : '';
        code += ':';
        // code += event.key;
        // code += kbKeys.code[event.key];
        code += kbKeys.code[event.which];

        // if (scDb.has(code)) scDb.get(code).listeners.forEach(callback => callback(event, {
        //     code: event.key,
        //     char: event.key
        // }));
        if (scDb.has(code)) scDb.get(code).listeners.forEach(callback => callback(event, {
            code: event.which,
            char: kbKeys.code[event.which]
        }));
        return this;
    }
}

/**
 * @type {iShortcut}
 * @instance
 */
const shortcut = new iShortcut();

export default shortcut;
