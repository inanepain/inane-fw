// ==UserScript==
// @name         YTS Helper
// @namespace    https://www.cathedral.co.za/tm/yts
// @version      2026-02-14-11-42
// @description  YTS keyboard shortcuts
// @author       Philip Michael Raab<philip@cathedral.co.za>
// @match        https://yts.mx/*
// @match        https://yts.bz/*
// @match        https://www.yts-official.cc/*
// @icon         data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==
// @grant        GM_setValue
// @grant        GM_getValue
// @grant        GM.setValue
// @grant        GM.getValue
// @grant        GM_setClipboard
// @grant        unsafeWindow
// @grant        window.close
// @grant        window.focus
// @grant        window.onurlchange
// @sandbox      raw
// @run-at       document-start
// ==/UserScript==

//#region Initialise
unsafeWindow.iq = unsafeWindow.iq || document.iq;
unsafeWindow.iqs = unsafeWindow.iqs || document.iqs;
unsafeWindow.iqsa = unsafeWindow.iqsa || document.iqsa;
unsafeWindow.shortcut = unsafeWindow.shortcut || document.shortcut;
//#endregion Initialise

//#region Libraries
((window) => {
    if (!Array.prototype.unique) { Array.prototype.unique = function () { const unique = []; for (let i = 0; i < this.length; i++)if (!unique.includes(this[i])) unique.push(this[i]); return unique } }
    if (!Array.prototype.searchObject) { Array.prototype.searchObject = function (nameKey, keyValue, fuzzy = !1) { return this.filter(item => { if (item.hasOwnProperty(nameKey) && keyValue !== undefined) { if (fuzzy && typeof item[nameKey] == 'string') return item[nameKey].toLowerCase().includes(keyValue.toLowerCase()); return item[nameKey] == keyValue } }) } }
    if (!Array.prototype.groupByProperty) { Array.prototype.groupByProperty = function (key) { return this.reduce((rv, x) => { (rv[x[key]] = rv[x[key]] || []).push(x); return rv }, {}) } }
    if (!Array.prototype.sortByProperty) { Array.prototype.sortByProperty = function (propName, sortNumerically = !1) { if (sortNumerically == !0) { this.sort(function (a, b) { return a[propName] - b[propName] }) } else { this.sort(function (a, b) { var nameA = `${(a[propName] ?? '')}`.toUpperCase(); var nameB = `${(b[propName] ?? '')}`.toUpperCase(); if (nameA < nameB) return -1; if (nameA > nameB) return 1; return 0 }) } } }
    if (!Array.prototype.log) { Array.prototype.log = function () { console.log(JSON.stringify(this)) } }; if (!Date.prototype.getWeekNumber) { Date.prototype.getWeekNumber = function () { var d = new Date(+this); d.setHours(0, 0, 0); d.setDate(d.getDate() + 4 - (d.getDay() || 7)); return Math.ceil((((d - new Date(d.getFullYear(), 0, 1)) / 8.64e7) + 1) / 7) } }
    if (!Date.prototype.nextYear) { Date.prototype.nextYear = function () { return new Date(this.getTime() + 31536000000) } }
    if (!Date.prototype.nextYearGMTString) { Date.prototype.nextYearGMTString = function () { return this.nextYear().toGMTString() } }
    if (!Date.prototype.unixZero) { Date.prototype.unixZero = function () { return new Date(0) } }
    if (!Date.prototype.unixZeroGMTString) { Date.prototype.unixZeroGMTString = function () { return (new Date(0)).toGMTString() } }
    if (!Date.prototype.log) { Date.prototype.log = function () { console.log(this.constructor.toString().split(' ')[1].replace('()', '').toLowerCase() + '(' + this.toLocaleString().length + '): ' + this.toLocaleString()) } }; for (let element of [HTMLCollection, NodeList]) { if (!element.prototype.toArray) { element.prototype.toArray = function () { return Array.from(this) } } }
    for (let element of [Document, HTMLElement, ShadowRoot, HTMLDocument]) {
        if (!element.prototype.iqs) { element.prototype.iqs = function (selectors) { const el = this?.querySelector ? this : window.document; return el.querySelector(selectors) } }
        if (!window.iqs && window.document.iqs) window.iqs = window.document.iqs; if (!element.prototype.iqsa) { element.prototype.iqsa = function (selectors) { const el = this?.querySelectorAll ? this : window.document; return Array.from(el.querySelectorAll(selectors)) } }
        if (!window.iqsa && window.document.iqsa) window.iqsa = window.document.iqsa; if (!element.prototype.iq) { element.prototype.iq = function (selectors) { const dynamic = selectors.startsWith('@@'); if (dynamic) selectors = selectors.substring(2); const cmd = selectors.startsWith('@') || selectors.split(' ').pop().charAt(0) === "#" && !selectors.includes(',') ? 'querySelector' : 'querySelectorAll'; if (selectors.startsWith('@')) selectors = selectors.substring(1); const el = this?.[cmd] ? this : window.document; result = el[cmd](selectors); result = cmd == 'querySelector' ? result : Array.from(result); if (dynamic) return Array.isArray(result) ? (result.length == 1 ? result.pop() : result) : result; return result } }
        if (!window.iq && window.document.iq) window.iq = window.document.iq
    }; if (!Number.getRandom) { Number.getRandom = function (min, max) { min = Math.ceil(min || 0); max = Math.floor(max || min * min); return Math.floor(Math.random() * (max - min + 1)) + min } }
    if (!Number.prototype.log) { Number.prototype.log = function () { console.log(this.constructor.toString().split(' ')[1].replace('()', '').toLowerCase() + '(' + this.toString().length + '): ' + this.toString()) } }; if (!Object.prototype.watch) { Object.defineProperty(Object.prototype, 'watch', { enumerable: !1, configurable: !1, writable: !0, value: function (prop, handler) { var getter, setter, change = { property: prop, value: this[prop], previous: undefined, set update(v) { if (this.value == v) return !1; this.previous = this.value; this.value = v; return !0 } }, getter = function () { return change.value }, setter = function (val) { if (change.update = val) handler.call(this, change); return val }; if (delete this[prop]) { Object.defineProperty(this, prop, { get: getter, set: setter, configurable: !0 }) } } }) }
    if (!Object.prototype.unwatch) { Object.defineProperty(Object.prototype, 'unwatch', { enumerable: !1, configurable: !1, writable: !0, value: function (prop) { var val = this[prop]; delete this[prop]; this[prop] = val } }) }
    if (!Object.prototype.jsonString) { Object.defineProperty(Object.prototype, 'jsonString', { enumerable: !1, configurable: !1, writable: !0, value: function () { return JSON.stringify(this) } }) }
    if (!Object.prototype.pick) { Object.defineProperty(Object.prototype, 'pick', { enumerable: !1, configurable: !1, writable: !0, value: function (propsArray) { if (!propsArray) return; if (!Array.isArray(propsArray) && (typeof propsArray == "string")) propsArray = [propsArray]; propsArray = propsArray.unique(); const picked = {}; propsArray.forEach(prop => { if (this.hasOwnProperty(prop)) picked[prop] = this[prop] }); return picked } }) }
    if (!Object.prototype.readPath) {
        Object.defineProperty(Object.prototype, 'readPath', {
            enumerable: !1, configurable: !1, writable: !0, value: function (path, delimiter = '.') {
                if (!path) return this; const eP = typeof path == 'string' ? path.split(delimiter) : path; let t = Object.assign({}, this); for (let i = 0; i < eP.length; i++)
                    if (t && t.hasOwnProperty(eP[i])) t = t[eP[i]]; else t = undefined; return t
            }
        })
    }
    if (!Object.prototype.sorted) { Object.defineProperty(Object.prototype, 'sorted', { enumerable: !1, configurable: !1, writable: !0, value: function () { return this.pick(this.keys().sort()) } }) }
    if (!Object.prototype.propertyRename) {
        Object.defineProperty(Object.prototype, 'propertyRename', {
            enumerable: !1, configurable: !1, writable: !0, value: function (old_key, new_key, force = !1) {
                if (!old_key || !new_key) { console.error('Object.propertyRename: old_key and new_key are required.'); return this }
                if (typeof old_key !== 'string' || typeof new_key !== 'string') { console.error('Object.propertyRename: old_key and new_key must be strings.'); return this }
                if (old_key === new_key) { console.warn('Object.propertyRename: old_key and new_key are the same, no action taken.'); return this }
                if (!this.hasOwnProperty(old_key)) { console.warn(`Object.propertyRename: old_key "${old_key}" does not exist on this object.`); return this }
                if (this.hasOwnProperty(new_key) && !force) { console.warn(`Object.propertyRename: new_key "${new_key}" already exists on this object, no action taken.`); return this }
                if (this.hasOwnProperty(new_key) && force) { delete this[new_key] }
                Object.defineProperty(this, new_key, Object.getOwnPropertyDescriptor(this, old_key)); delete this[old_key]; return this
            }
        })
    }
    if (!Object.prototype.renameProperty) { Object.defineProperty(Object.prototype, 'renameProperty', { enumerable: !1, configurable: !1, writable: !0, value: function (old_key, new_key, force = !1) { return this.propertyRename(old_key, new_key, force) } }) }
    if (!Object.prototype.groupByProperty) { Object.defineProperty(Object.prototype, 'groupByProperty', { enumerable: !1, configurable: !1, writable: !0, value: function (key) { try { let target = Array.isArray(this) ? this : this.values(); return target.reduce((rv, x) => { (rv[x[key]] = rv[x[key]] || []).push(x); return rv }, {}) } catch (error) { console.error('Unable to group object.') } } }) }
    if (!Object.prototype.keys) { Object.defineProperty(Object.prototype, 'keys', { enumerable: !1, configurable: !1, writable: !0, value: function () { return Object.keys(this) } }) }
    if (!Object.prototype.values) { Object.defineProperty(Object.prototype, 'values', { enumerable: !1, configurable: !1, writable: !0, value: function () { return Object.values(this) } }) }; if (!String.prototype.toTitleCase) { String.prototype.toTitleCase = function (lowerAsWell = !1, isName = !1) { let string = lowerAsWell === !0 ? this.toLowerCase() : this; if (isName) return string.replace(/\b[a-z]/g, txt => { return txt.charAt(0).toUpperCase() + txt.substring(1).toLowerCase() }); else return string.replace(/(?:^|\s)\w/g, function (match) { return match.toUpperCase() }) } }
    if (!String.prototype.replaceAll) { String.prototype.replaceAll = function (find, replace) { return this.split(find).join(replace) } }
    if (!String.prototype.trimChars) { String.prototype.trimChars = function (chars) { return this.replace(new RegExp('^(' + chars + ')+|(' + chars + ')+$', 'gm'), '') } }
    if (!String.prototype.trimCharsLeft) { String.prototype.trimCharsLeft = function (chars) { return this.replace(new RegExp('^(' + chars + ')+', 'gm'), '') } }
    if (!String.prototype.trimCharsRight) { String.prototype.trimCharsRight = function (chars) { return this.replace(new RegExp('(' + chars + ')+$', 'gm'), '') } }
    if (!String.prototype.camelCaseToHyphen) { String.prototype.camelCaseToHyphen = function () { let str = this; str = str.replace(/[^\w\s\-]/gi, ''); str = str.replace(/([A-Z])/g, function ($1) { return '-' + $1.toLowerCase() }); return str.replace(/\s/g, '-').replace(/^-+/g, '') } }
    if (!String.prototype.hyphenToCamelCase) { String.prototype.hyphenToCamelCase = function () { return this.replace(/-([a-z])/g, (m, w) => w.toUpperCase()) } }
    if (!String.prototype.splice) { String.prototype.splice = function (start, delCount, newSubStr) { return this.slice(0, start) + newSubStr + this.slice(start + Math.abs(delCount)) } }
    if (!String.prototype.parseJSON) { String.prototype.parseJSON = function () { try { return JSON.parse(this) } catch (error) { return null } } }
    if (!String.prototype.log) { String.prototype.log = function (label = !1) { let args = [this.toString()]; if (label?.constructor?.name == 'String') args.unshift(label); else if (label === !0) args.unshift(this.constructor.name, this.length); console.log(...args) } }
})(unsafeWindow);

const shortcut = ((window) => {
    const moduleName = 'iShortcut'; const VERSION = '0.3.3'; if (window.Dumper) Dumper.dump('MODULE', moduleName.concat(' v').concat(VERSION), 'LOAD'); const kbKeys = { code: { 33: '!', 35: '#', 36: '$', 37: '%', 38: '&', 39: '\'', 40: '(', 41: ')', 42: '*', 43: '+', 44: ',', 45: '"', 46: '.', 47: '/', 48: '0', 49: '1', 50: '2', 51: '3', 52: '4', 53: '5', 54: '6', 55: '7', 56: '8', 57: '9', 63: '?', 64: '@', 65: 'A', 66: 'B', 67: 'C', 68: 'D', 69: 'E', 70: 'F', 71: 'G', 72: 'H', 73: 'I', 74: 'J', 75: 'K', 76: 'L', 77: 'M', 78: 'N', 79: 'O', 80: 'P', 81: 'Q', 82: 'R', 83: 'S', 84: 'T', 85: 'U', 86: 'V', 87: 'W', 88: 'X', 89: 'Y', 90: 'Z', 91: '[', 93: ']', 94: '^', 95: '_', 96: '`', 123: '{', 125: '}', 126: '~', 191: '?' }, char: { '!': 33, '#': 35, '$': 36, '%': 37, '&': 38, '\'': 39, '(': 40, ')': 41, '*': 42, '+': 43, ',': 44, '"': 45, '.': 46, '/': 47, '0': 48, '1': 49, '2': 50, '3': 51, '4': 52, '5': 53, '6': 54, '7': 55, '8': 56, '9': 57, '?': 63, '@': 64, 'A': 65, 'B': 66, 'C': 67, 'D': 68, 'E': 69, 'F': 70, 'G': 71, 'H': 72, 'I': 73, 'J': 74, 'K': 75, 'L': 76, 'M': 77, 'N': 78, 'O': 79, 'P': 80, 'Q': 81, 'R': 82, 'S': 83, 'T': 84, 'U': 85, 'V': 86, 'W': 87, 'X': 88, 'Y': 89, 'Z': 90, '[': 91, ']': 93, '^': 94, '_': 95, '`': 96, '{': 123, '}': 125, '~': 126, '?': 191 }, mod: { SHIFT: 'S', ALT: 'A', CTRL: 'C', } }; const scDb = new Map(); class iShortcut {
        constructor() { document.addEventListener('keyup', (event) => { this.onKeyup.call(this, event); }); this.add = this.addShortcut.bind(this); this.add('alt + ?', () => { this.help(); }, 'Show registered shortcuts'); }
        static get VERSION() { return VERSION; }
        get VERSION() { return this.constructor.VERSION; }
        addShortcut(shortcut, callback, description = '') { let code = this.parseShortcut(shortcut); if (!scDb.has(code)) scDb.set(code, { code: code, shortcut: shortcut.split(' + ').join('+').replaceAll('+', ' + '), description: description, listeners: [] }); scDb.get(code).listeners.push(callback); return this; }
        help() {
            let shortcutDescriptions = []; shortcutDescriptions.push('SHORTCUT DESCRIPTIONS'); for (const [key, element] of scDb) { let parts = element.shortcut.replaceAll(' ', '').split('+'); for (let index = 0; index < parts.length; index++)parts[index] = parts[index].length > 1 ? parts[index].toUpperCase().padEnd(6, ' ') : parts[index].toUpperCase(); const shortcut = parts.join(' + '); const tab = shortcut.length < 12 ? "\t\t\t" : "\t"; shortcutDescriptions.push(`${shortcut}${tab}: ${element.description}`); }
            const message = shortcutDescriptions.join("\n"); window.alert(message); return this;
        }
        onKeyup(event) { if (!event.which) return; let code = ''; code += event.shiftKey ? 'S' : ''; code += event.altKey ? 'A' : ''; code += event.ctrlKey ? 'C' : ''; code += ':'; code += kbKeys.code[event.which]; if (scDb.has(code)) scDb.get(code).listeners.forEach(callback => callback(event, { code: event.which, char: kbKeys.code[event.which] })); return this; }
        parseShortcut(shortcut) { let cmd = shortcut.toUpperCase().replaceAll(' ', '').replaceAll('CONTROL', 'CTRL').split('+'); let cmdMod = Object.keys(kbKeys.mod).map(key => { if (cmd.includes(key)) return kbKeys.mod[cmd.splice(cmd.indexOf(key), 1)[0]]; return; }); return cmdMod.concat([':']).concat(cmd).join(''); }
    }
    const shortcut = new iShortcut();
    window.shortcut = shortcut;
    return shortcut;
})(unsafeWindow);

((window) => {
    /**
     * Dumper
     *
     * Logging with filters, Named loggers, enhanced assert and much more.
     *
     * @author Philip Michael Raab <philip@cathedral.co.za>
     * @copyright 2020 Philip Michael Raab <philip@cathedral.co.za>
     *
     * @license UNLICENSE
     *
     * @see {@link https://inanepain.gitbook.io/dumperjs Documentation}
     * @see {@link https://unlicense.org/UNLICENSE UNLICENSE}
     */

    /**
     * @ignore
     */
    const out = console;

    /**
     * LogLevel Options
     * @typedef {Object} LogLevelOptions
     * @property {string} name LogLevel name
     * @property {number} value LogLevel number
     */

    /**
     * LogLevel
     * @version 1.2.1
     */
    class LogLevel {
        /**
         * LogLevel Options
         * @type {LogLevelOptions}
         */
        #options;

        /**
         * Creates an instance of LogLevel.
         *
         * @param {LogLevelOptions} options LogLevel values
         */
        constructor({
            name,
            value
        }) {
            this.#options = arguments[0];

            Object.defineProperties(this, {
                name: {
                    enumerable: true,
                    value: this.#options.name,
                },
                value: {
                    enumerable: true,
                    value: this.#options.value,
                },
            });
        }

        /**
         * Return LogLevel from a variety of input
         *
         * If no matching level found based on the input then the default level in returned.
         *
         * @param {LogLevel|string|number|Object} level name, value (as number or string), object with only name or value (as object or string)
         *
         * @returns {LogLevel} The LogLevel matching parameter.
         */
        static from(level) {
            if (level instanceof LogLevel && Dumper[level.name]) return Dumper[level.name];
            else if (typeof level == `string` && Dumper[level.toUpperCase()]) return Dumper[level.toUpperCase()];
            else if (Dumper[level]) return Dumper[level];
            else if (Dumper[level?.value]) return Dumper[level?.value];
            else if (Dumper[level?.name?.toUpperCase()]) return Dumper[level?.name?.toUpperCase()];
            return defaults.level;
        }

        /**
         * allows<br>
         * If this LogLevel blocks or passes LogLevel level.<br />
         * Omit level to validate as highest. Only OFF fails.
         *
         * @param {LogLevel} [level] LogLevel to test.
         *
         * @returns {boolean} block or pass
         */
        allows(level) {
            if ([defaults.level, level].includes(Dumper.OFF)) return false;
            if (level == undefined) return true;
            return this.value - level.value <= 0;
        }
    }

    /**
     * Test if obj is static or an instance of Dumper
     *
     * @param {Dumper} obj dumper to test
     *
     * @returns {boolean} true if static Dumper
     *
     * @ignore
     */
    const isStatic = (obj) => obj === Dumper;

    /**
     * copyObject<br/>
     *
     * Creates 100% new object using JSON to stringify the object.<br/>
     *
     * N.B.: The to/from string conversion!!! Use for data/options.
     *
     * @param {Object} original object to create a copy from
     *
     * @returns {Object} a new unref copy of original
     *
     * @ignore
     */
    const copyObject = (original) => { return JSON.parse(JSON.stringify(original)); }

    /**
     * Adds ONLY missing properties from source objects to target in decreasing priority
     *
     * @example
     * // copy values from defaults missing in options to options
     * mergeOptions(options, defaults);
     *
     * @param {Object} target the target object
     * @param {...Object} source the source objects in decreasing order of priority
     *
     * @ignore
     */
    const mergeOptions = (target, ...source) => {
        var key, i;
        for (i = 0; i < source.length; i++)
            for (key in source[i])
                if (!(key in target) && source[i].hasOwnProperty(key)) target[key] = source[i][key];
                else
                    try { // If we are dealing with child objects here we simple dive into them to process the whole object
                        if (target[key].constructor === Object && source[i][key].constructor === Object) mergeOptions(target[key], source[i][key]);
                    } catch (error) { // If target has undefined or null we catch the error and set the value
                        if (error.message.includes('target[key].constructor')) target[key] = source[i][key];
                    }
    };

    /**
     * Throttle Options
     *
     * @typedef {Object} ThrottleOptions
     * @property {boolean} skipfirst - False: first call is instant, True: first call delayed
     * @ignore
     */
    /**
     * Throttles and debounce a function
     *
     * @param {Function} func the function to restrict
     * @param {number} limitDelay the milliseconds between calls and delay to wait for last call
     * @param {ThrottleOptions} options extra options for throttle
     *
     * @returns {Function} the restricted function
     *
     * @ignore
     */
    const throttle = (func, limitDelay = 1000, options = {}) => {
        mergeOptions(options, {
            skipfirst: false,
            context: func,
        });
        let inThrottle = options.skipfirst;
        let inDebounce;
        return function () {
            const args = arguments;
            const context = options.context;
            if (!inThrottle) {
                inThrottle = true;
                func.apply(context, args);
                inThrottle = setTimeout(() => inThrottle = undefined, limitDelay);
            } else {
                clearTimeout(inDebounce);
                inDebounce = setTimeout(() => func.apply(context, args), limitDelay);
            }
        }
    };

    /**
     * formatMessage</br>
     * Create context string and prepend it to messages with applied style.<br>
     * The original messages object is modified, thus nothing is returned.
     *
     * @param {string[]} messages - the messages to apply context too
     * @param {Object} [context] - the dumper's context
     * @param {string[]} [context.name=[]] - context name array
     * @param {Dumper} [context.parent] - context parent Dumper
     * @param {Object|boolean} [style=false] - the style properties to apply to the context
     *
     * @ignore
     */
    const formatMessage = (messages, context, style = false) => {
        if (typeof context !== `undefined` && Array.isArray(context.name) && context.name.length > 0) {
            const name = '['.concat(context.name.join(' - ')).concat(']');
            if (style) messages.unshift(Object.entries(style).join('; ').replaceAll(',', ': ').concat(';'));
            messages.unshift((style ? '%c' : '').concat(name));
        }
    };

    /**
     * Static & instance functions
     * @ignore
     */
    const funcs = ['log', 'trace', 'debug', 'info', 'warn', 'error', 'time', 'timeEnd', 'timeLog', 'timeStamp', 'count', 'countReset', 'getLevel', 'setLevel', 'children', 'assert'];

    /**
     * Children of the Global Dumper
     *
     * @type {Map}
     *
     * @ignore
     */
    const Children = new Map();

    /**
     * Counters
     *
     * @type {object}
     *
     * @ignore
     */
    const Counters = {
        default: 0
    };

    /**
     * LogLevel Definitions
     */
    const LogLevels = (() => {
        const LogLevels = Object.create(null);

        /**
         * Create Dumper's log levels
         */
        Object.defineProperties(LogLevels, {
            TRACE: {
                value: new LogLevel({ value: 1, name: 'TRACE' }),
                enumerable: true,
            },
            DEBUG: {
                value: new LogLevel({ value: 2, name: 'DEBUG' }),
                enumerable: true,
            },
            INFO: {
                value: new LogLevel({ value: 3, name: 'INFO' }),
                enumerable: true,
            },
            TIME: {
                value: new LogLevel({ value: 4, name: 'TIME' }),
                enumerable: true,
            },
            WARN: {
                value: new LogLevel({ value: 5, name: 'WARN' }),
                enumerable: true,
            },
            ERROR: {
                value: new LogLevel({ value: 8, name: 'ERROR' }),
                enumerable: true,
            },
            OFF: {
                value: new LogLevel({ value: 99, name: 'OFF' }),
                enumerable: true,
            },
        });

        Object.freeze(LogLevels);
        return LogLevels;
    })();

    /**
     * Dumper</br>
     * Logging with filters, Named loggers, enhanced assert, ...
     *
     * @version 2.5.0
     * @author Philip Michael Raab <philip@cathedral.co.za>
     * @copyright 2020 Philip Michael Raab <philip@cathedral.co.za>
     *
     * @property {LogLevel} TRACE - trace
     * @property {LogLevel} DEBUG - debug
     * @property {LogLevel} INFO - info
     * @property {LogLevel} TIME - time
     * @property {LogLevel} WARN - warn
     * @property {LogLevel} ERROR - error
     * @property {LogLevel} OFF - off
     *
     * @license UNLICENSE
     *
     * @see {@link https://inanepain.gitbook.io/dumperjs Documentation}
     * @see {@link https://unlicense.org/UNLICENSE UNLICENSE}
     */
    class Dumper {
        /**
         * Version
         *
         * @readonly
         * @static
         * @type {string}
         */
        static get VERSION() {
            return '2.5.0';
        }
        /**
         * Version
         *
         * @readonly
         * @type {string}
         * @ignore
         */
        get VERSION() {
            return this.constructor.VERSION;
        }

        /**
         * options
         *
         * @type {Dumper#Options}
         */
        #options;

        /**
         * Children of this dumper
         *
         * @type {Map<string,Dumper>}
         */
        #children;

        /**
         * Gets Dumper's children
         *
         * Gets the children of the current object.
         * This is an alias for the `children()` method.
         *
         * @since 2.5.0
         *
         * @type {Array} The children of the current object.
         */
        static get kids() {
            return this.children();
        }
        /**
         * Gets this instance of Dumper's childern
         *
         * Getter for retrieving the children of the current object.
         * This is an alias for the `children()` method.
         *
         * @since 2.5.0
         *
         * @type {Array|Object} The children of the current object.
         */
        get kids() {
            return this.children();
        }

        /**
         * Context
         *
         * @type {Object}
         * @ignore
         */
        #context = {
            name: []
        };

        /**
         * Counters
         */
        #counters = {
            default: 0
        };

        /**
         * Creates an instance of Dumper.
         *
         * @param {Dumper#Options} options - customisations for instance logger
         */
        constructor({
            clear = false,
            level = Dumper.WARN,
            bubbling = {
                listen: true,
                trigger: true,
            },
            trickle = 1000,
            assert = {
                time: false,
                hhmmss: false,
                limit: 0,
            },
        } = defaults) {
            this.#options = this.#parseOptions((arguments[0] || defaults));
            if (!(this.#options.level instanceof LogLevel)) this.#options.level = LogLevel.from(this.#options.level);

            for (const func of funcs) this[func] = Dumper[func].bind(this);

            this.group = out.group.bind(this);
            this.groupCollapsed = out.groupCollapsed.bind(this);
            this.groupEnd = out.groupEnd.bind(this);
            this.clear = out.clear.bind(this);

            this.trickle = throttle(this.debug, this.#options.trickle, { context: this });
            if (!this.dump) this.dump = () => { };

            // Object.defineProperty(this, Dumper.TRACE.name, {
            //     value: Dumper.TRACE,
            // });
            // Object.defineProperty(this, Dumper.DEBUG.name, {
            //     value: Dumper.DEBUG,
            // });
            // Object.defineProperty(this, Dumper.INFO.name, {
            //     value: Dumper.INFO,
            // });
            // Object.defineProperty(this, Dumper.TIME.name, {
            //     value: Dumper.TIME,
            // });
            // Object.defineProperty(this, Dumper.WARN.name, {
            //     value: Dumper.WARN,
            // });
            // Object.defineProperty(this, Dumper.ERROR.name, {
            //     value: Dumper.ERROR,
            // });
            // Object.defineProperty(this, Dumper.OFF.name, {
            //     value: Dumper.OFF,
            // });

            if (this.#options.clear) this.clear();
        }

        /**
         * Parse options
         *
         * @since 1.3.0
         *
         * @param {object} options options
         * @returns {object}
         */
        #parseOptions(options) {
            const picked = {};
            Object.keys(defaults).forEach(key => {
                if (options.hasOwnProperty(key)) picked[key] = options[key];
            });

            mergeOptions(picked, defaults);

            picked.id = Date.now();
            if (options.linked === true) {
                this.get = Dumper.get.bind(this);
                this.#children = new Map();
                this.#context = options.context;
            } else this.get = Dumper.get.bind(Dumper);

            return picked;
        }

        /**
         * Gets a Named Dumper instance</br>
         * - If an instance by name exist it will be returned and NOT a new instance created.
         *
         * @static
         * @param {string} name Uniquely identify the dumper.
         * @param {Dumper#Options} [options={}] Custom settings for the dumper.
         *
         * @returns {Dumper} A new Dumper directly under the Global Dumper
         */
        static get(name, {
            clear = false,
            level = Dumper.WARN,
            bubbling = {
                listen: true,
                trigger: true,
            },
            trickle = 1000,
            assert = {
                time: false,
                hhmmss: false,
                limit: 0,
            },
        } = defaults) {
            let options = arguments[1] ?? {};
            const children = (this == Dumper || this.#children == undefined) ? Children : this.#children;

            if (!children.has(name)) {
                // Any unset child options are copied from parent
                if (this != Dumper) mergeOptions(options, this.#options);
                if (options.level && options.level.name) options.level = options.level.name;

                options = copyObject(options);
                options.level = LogLevel.from(options.level);

                let context = this == Dumper ? { name: [] } : copyObject(this.#context);
                context.parent = this;
                context.name.push(name);

                options.linked = true;
                options.context = context;
                const child = new Dumper(options);
                child.name = name;

                children.set(name, child);
            }
            return children.get(name);
        }

        /**
         * Retrieves the children of the current Dumper or if Class object it gets the root childen
         *
         * @returns {Object} An object containing the children, where each key is the child's name
         *                   and the value is the corresponding child object.
         */
        static children() {
            const kids = Object.create(null);
            for (let [name, child] of (this != Dumper && this.#children || Children)) kids[name] = child; // this.log(child.name, child);

            return kids;
        }

        /**
         * TRACE
         *
         * @type {LogLevel}
         * @ignore
         */
        static get TRACE() {
            return LogLevels.TRACE;
        }
        /**
         * DEBUG
         *
         * @type {LogLevel}
         * @ignore
         */
        static get DEBUG() {
            return LogLevels.DEBUG;
        }
        /**
         * INFO
         *
         * @type {LogLevel}
         * @ignore
         */
        static get INFO() {
            return LogLevels.INFO;
        }
        /**
         * TIME
         *
         * @type {LogLevel}
         * @ignore
         */
        static get TIME() {
            return LogLevels.TIME;
        }
        /**
         * WARN
         *
         * @type {LogLevel}
         * @ignore
         */
        static get WARN() {
            return LogLevels.WARN;
        }
        /**
         * ERROR
         *
         * @type {LogLevel}
         * @ignore
         */
        static get ERROR() {
            return LogLevels.ERROR;
        }
        /**
         * OFF
         *
         * @type {LogLevel}
         * @ignore
         */
        static get OFF() {
            return LogLevels.OFF;
        }

        /**
         * TRACE
         *
         * @type {number}
         * @ignore
         */
        static get 1() {
            return this.TRACE;
        }
        /**
         * DEBUG
         *
         * @type {number}
         * @ignore
         */
        static get 2() {
            return this.DEBUG;
        }
        /**
         * INFO
         *
         * @type {number}
         * @ignore
         */
        static get 3() {
            return this.INFO;
        }
        /**
         * TIME
         *
         * @type {number}
         * @ignore
         */
        static get 4() {
            return this.TIME;
        }
        /**
         * WARN
         *
         * @type {number}
         * @ignore
         */
        static get 5() {
            return this.WARN;
        }
        /**
         * ERROR
         *
         * @type {number}
         * @ignore
         */
        static get 8() {
            return this.ERROR;
        }
        /**
         * OFF
         *
         * @type {number}
         * @ignore
         */
        static get 99() {
            return this.OFF;
        }

        /**
         * Level
         *
         * @static
         * @returns {LogLevel} current LogLevel
         */
        static getLevel() {
            return (isStatic(this) ? defaults : this.#options).level;
        }
        /**
         * Set Level</br>
         * - Accepts: name, value (as number or string), object with only name or value (as object or string)
         * @example
         * // All valid: All the same
         * dumper.setLevel(Dumper.INFO);
         * dumper.setLevel('info');
         * dumper.setLevel(3);
         * dumper.setLevel('3');
         * dumper.setLevel({ name: 'info' });
         * dumper.setLevel({ value: 3 });
         * dumper.setLevel({ value: '3' });
         *
         * @static
         * @param {LogLevel|string|number} level name, value (as number or string), object with only name or value (as object or string)
         * @param {boolean} [bubbled=false] should not be set manually, true if update called from parent
         */
        static setLevel(level, bubbled = false) {
            let options = (this == Dumper || this.#options == undefined) ? defaults : this.#options;

            if (!(level instanceof LogLevel)) level = LogLevel.from(level);

            // If initial setLevel OR updateChain is true: update options
            if (!bubbled || (this == Dumper || options.bubbling.listen)) options.level = level;

            // Global Dumper & LogLevel.OFF: We don't chain Level since Global.OFF stops all logging as is.
            // OR if the level chain set to stop bubbling
            if ((this == Dumper && level == this.OFF) || (bubbled && options.bubbling.trigger == false)) return;

            // Single Instance Dumper: Has no children so we stop here to prevent a Global setLevel call.
            if (this != Dumper && this.#children == undefined) return this;

            // Update level of children
            if (options.bubbling.trigger) for (let child of (this != Dumper && this.#children || Children).values()) child.setLevel(level, true);
            return this;
        }

        /**
         * get: level
         *
         * @static
         * @type {LogLevel}
         */
        static get level() {
            return this.getLevel();
        }

        /**
         * @ignore
         */
        static set level(level) {
            return this.setLevel(level);
        }

        /**
         * get: level
         *
         * @type {LogLevel}
         */
        get level() {
            return this.getLevel();
        }

        set level(level) {
            return this.setLevel(level);
        }

        /**
         * Writes to console regardless of levels and shouldn't be used in ust about any circumstance
         *
         * @static
         * @param {...any} msgs items to dump
         *
         * @returns {Dumper} Dumper
         */
        static dump(...msgs) {
            if (out.dump) return out.dump.apply(this, msgs);
        }

        /**
         * Outputs a stack trace
         *
         * @static
         * @param {...any} messages log messages
         */
        static trace(...messages) {
            formatMessage(messages, (this == Dumper ? undefined : this.#context), { color: 'DarkBlue' });
            if (this.getLevel().allows(Dumper.TRACE)) return out.trace.apply(this, messages);
        }
        /**
         * Outputs a message to the console with the log level `debug`
         *
         * @static
         * @param {...any} messages log messages
         *
         * @returns {Dumper} Dumper
         */
        static debug(...messages) {
            formatMessage(messages, (this == Dumper ? undefined : this.#context), { color: 'LightBlue' });
            if (this.getLevel().allows(Dumper.DEBUG)) return out.debug.apply(this, messages);
        }
        /**
         * Informative logging of information
         *
         * @static
         * @param {...any} messages log messages
         *
         * @returns {Dumper} Dumper
         */
        static info(...messages) {
            formatMessage(messages, (this == Dumper ? undefined : this.#context), { color: 'Blue' });
            if (this.getLevel().allows(Dumper.INFO)) return out.info.apply(this, messages);
        }
        /**
         * Outputs a warning message
         *
         * @static
         * @param {...any} messages log messages
         *
         * @returns {Dumper} Dumper
         */
        static warn(...messages) {
            formatMessage(messages, (this == Dumper ? undefined : this.#context), { color: 'Orange' });
            if (this.getLevel().allows(Dumper.WARN)) return out.warn.apply(this, messages);
        }
        /**
         * Outputs an error message
         *
         * @static
         * @param {...any} messages log messages
         *
         * @returns {Dumper} Dumper
         */
        static error(...messages) {
            formatMessage(messages, (this == Dumper ? undefined : this.#context), { color: 'DarkRed' });
            if (this.getLevel().allows(Dumper.ERROR)) return out.error.apply(this, messages);
        }
        /**
         * For general output of logging information
         *
         * @static
         * @param {...any} messages log messages
         *
         * @returns {Dumper} Dumper
         */
        static log(...messages) {
            formatMessage(messages, (this == Dumper ? undefined : this.#context), { color: 'Black' });
            if (this.getLevel().allows()) return out.log.apply(this, messages);
        }

        /**
         * Log a message and stack trace to console if the first argument is `false`
         *
         * @since 2.4.3 assert available on Dumper class not only instances
         *
         * @param {...any} messages log messages
         *
         * @returns {boolean} if assertion took place or not @since 2.4.1
         */
        static assert(assertion, ...messages) {
            formatMessage(messages, (this == Dumper ? undefined : this.#context), { color: 'Crimson' });
            const new_ts = Date.now();
            const old_ts = this._last_assert || 0;

            const options = this == Dumper ? defaults : this.#options;

            // Stop here if limit not reached
            if (options.assert.limit && old_ts && new_ts - old_ts < options.assert.limit) return false;

            if (options.assert.time) {
                messages.push(new_ts);
                const gap_ts = new_ts - old_ts;
                // Add date string to message if option set
                if (options.assert.hhmmss) messages.push((new Date(gap_ts).toISOString().substring(11, 8)).replace(new RegExp('^(00:)+', 'gm'), ''));
                else messages.push(gap_ts); // Add timestamp to message
            }

            this._last_assert = new_ts;
            messages.unshift(assertion);
            if (this.getLevel().allows()) {
                out.assert.apply(this, messages);
                return !assertion;
            }

            return false;
        }

        /**
         * Starts a timer with a name specified as an input parameter
         *
         * @static
         * @param {string} [label=default] The name to give the new timer. This will identify the timer.
         *
         * @returns {Dumper} Dumper
         */
        static time(label = 'default') {
            let messages = [label];
            formatMessage(messages, (this == Dumper ? undefined : this.#context));
            if (this.getLevel().allows(Dumper.TIME)) return out.time.call(this, messages.join(' - '));
        }

        /**
         * Stops the specified timer and logs the elapsed time in milliseconds since it started
         *
         * @static
         * @param {string} [label=default] The name to give the new timer. This will identify the timer.
         *
         * @returns {Dumper} Dumper
         */
        static timeEnd(label = 'default') {
            let messages = [label];
            formatMessage(messages, (this == Dumper ? undefined : this.#context));
            if (this.getLevel().allows(Dumper.TIME)) return out.timeEnd.call(this, messages.join(' - '));
        }

        /**
         * Logs the value of the specified timer to the console
         *
         * @static
         * @param {string} [label=default] The name to give the new timer. This will identify the timer.
         *
         * @returns {Dumper} Dumper
         */
        static timeLog(label = 'default') {
            let messages = [label];
            formatMessage(messages, (this == Dumper ? undefined : this.#context));
            if (this.getLevel().allows(Dumper.TIME)) return out.timeLog.call(this, messages.join(' - '));
        }

        /**
         * Creates a marker in the timeline and a timestamp in the console.
         *
         * The timeline marker is level agnostic and always happens.
         * But the console timestamp's level is TIME
         *
         * @static
         *
         * @returns {Dumper} Dumper
         */
        static timeStamp() {
            let messages = [Date.now()];
            formatMessage(messages, (this == Dumper ? undefined : this.#context));
            if (this.getLevel().allows(Dumper.TIME)) out.log.call(this, messages.join(' - '));

            out.timeStamp.call(this);
            return this;
        }

        /**
         * Log the number of times this line has been called with the given label
         *
         * It's worth noting that each Dumper has it's own counters.
         * So the count value for a label will differ from counter to counter.
         *
         * @static
         * @param {string} [label=default] If supplied, `count()` outputs the number of times it has been called with that label.
         * @param {boolean} [returnCount=false] returns the counter value as int rather than the dumper.
         *
         * @returns {Dumper|number} Dumper or counter value
         */
        static count(label = 'default', returnCount = false) {
            const counters = (this == Dumper ? Counters : this.#counters);
            if (!counters.hasOwnProperty(label)) counters[label] = 0;
            counters[label] += 1;
            let messages = [`${label}: ` + counters[label]];
            formatMessage(messages, (this == Dumper ? undefined : this.#context));
            if (this.getLevel().allows(Dumper.TIME)) out.log.call(this, messages.join(' - '));
            return returnCount ? counters[label] : this;
        }

        /**
         * Resets the value of the counter with the given label
         *
         * @static
         * @param {string} [label=default] If supplied, `countReset()` resets the count for that label to 0.
         *
         * @returns {Dumper} Dumper
         */
        static countReset(label = 'default') {
            const counters = (this == Dumper ? Counters : this.#counters);
            if (counters.hasOwnProperty(label)) counters[label] = 0;
            return this;
        }

        /**
         * Assert Time logging option
         *
         * @type {boolean}
         */
        get optionAssertTime() {
            return this.#options.assert.time;
        }

        set optionAssertTime(assertTime) {
            this.#options.assert.time = Boolean(assertTime);
        }

        /**
         * Assert Limit option: ms between assert calls
         *
         * @type {number}
         */
        get optionAssertLimit() {
            return this.#options.assert.limit;
        }

        set optionAssertLimit(assertLimit) {
            assertLimit = assertLimit * 1;
            this.#options.assert.limit = assertLimit.toString() == 'NaN' ? 0 : assertLimit;
        }

        /**
         * Accept level changes bubbled from parent
         *
         * @type {boolean}
         *
         * @since 2.3.0
         */
        get optionBubbleFromParent() {
            return this.#options.bubbling.listen;
        }

        set optionBubbleFromParent(bubble) {
            this.#options.bubbling.listen = Boolean(bubble);
            return this;
        }

        /**
         * Bubble level changes to children
         *
         * @type {boolean}
         *
         * @since 2.3.0
         */
        get optionBubbleToChildren() {
            return this.#options.bubbling.trigger;
        }

        set optionBubbleToChildren(bubble) {
            this.#options.bubbling.trigger = Boolean(bubble);
            return this;
        }

        /**
         * Bubble
         *
         * @type {boolean}
         *
         * @since 2.3.0
         */
        get optionBubble() {
            return this.optionBubbleFromParent == this.optionBubbleToChildren && this.optionBubbleToChildren || null;
        }

        set optionBubble(bubble) {
            this.optionBubbleFromParent = bubble;
            this.optionBubbleToChildren = bubble;
            return this;
        }
    }

    /**
     * Dumper Options
     * @typedef {Object} Dumper#Options
     * @property {boolean} [clear=false] - clears the log
     * @property {LogLevel} [level={value: 5, name: 'WARN'}] - default log level
     * @property {Object} bubbling - how to handle option changes bubbling in and out
     * @property {boolean} [bubbling.listen=true] - listen for bubbled changes from parent
     * @property {boolean} [bubbling.trigger=true] - bubble changes to children
     * @property {number} trickle - throttle ms time
     * @property {Object} assert - options for assert
     * @property {boolean} [assert.time=false] - adds timestamp to assert log
     * @property {boolean} [assert.hhmmss=false] - changes timestamp to time string
     * @property {number} [assert.limit=0] - limits 1 assert log per limit period
     * @memberof Dumper
     */
    /**
     * defaults
     *
     * @type {Dumper#Options}
     */
    const defaults = {
        clear: false,
        level: Dumper.WARN,
        bubbling: {
            listen: true,
            trigger: true,
        },
        trickle: 1000,
        assert: {
            time: false,
            hhmmss: false,
            limit: 0,
        }
    };

    // Putting Dumper into global scope, where it's need when you want to debug.
    // If multiple Dumper are loaded, the first registered is always used
    if (!window.Dumper) {
        window.Dumper = Dumper;
    } else if (window.Dumper !== Dumper) {
        Dumper = window.Dumper;
    }

})(unsafeWindow);

//#region Query String
((window) => {
    const params = new URLSearchParams(window.location.search);
    console.log(params, Dumper);
    if (params.has('dumperLevel')) {
        console.log('params has dumperLevel');
        const dl = params.get('dumperLevel').toUpperCase();

        if (Dumper[dl]) {
            console.log('VALID: params has dumperLevel');
            window.dumperLevel = Dumper[dl];
        }
    } else if (window.dumperLevel === undefined) {
        window.dumperLevel = Dumper.INFO;
    } else if (typeof window.dumperLevel === 'string') {
        const dl = window.dumperLevel.toUpperCase();

        if (Dumper[dl]) {
            console.log('VALID: params has dumperLevel');
            window.dumperLevel = Dumper[dl];
        }
    }
})(unsafeWindow);
//#endregion Query String
//#endregion Libraries

//#region YTS
((window, dumper) => {
    'use strict';
    dumper.info('YTS Helper');

    /**
     * ScrollTo
     */
    class ScrollTo {
        /**
         * Version
         *
         * @readonly
         * @type {string} Version
         * @ignore
         */
        static get VERSION() {
            return '0.5.1';
        }
        get VERSION() {
            return this.constructor.VERSION;
        }

        static get defaults() {
            return {
                offset: 0,
                duration: 1000,
                fail_gracefully: false
            };
        }

        #options = {
            selector: null,
            el: null,
            config: {}
        }

        /**
         * ScrollTo Element
         *
         * @param {HTMLElement|string} el Element or selector string
         * @param {object} param1 configuration options
         */
        constructor(el, { offset = 0, duration = 1000, fail_gracefully = false } = {}) {
            Object.assign(this.#options.config, this.constructor.defaults, arguments[1]);

            if (typeof el == `string`) {
                this.#options.selector = el;
                this.#options.el = document.querySelector(el);
            } else if (el instanceof HTMLElement) {
                this.#options.selector = null;
                this.#options.el = el;
            }

            if (this.#options.el == null && this.#options.config.fail_gracefully == false) throw `Parameter is not a valid HTMLElement or selector string!`;

            this.animateScrolling = this.constructor.animateScrolling.bind(this);
            this.scrollTo = this.constructor.element.bind(this, this.#options.el);
        }

        /**
         * Animate Scrolling
         *
         * @param {number} endingY final Y position after scroll
         * @param {number} duration millisecond scroll duration
         */
        static animateScrolling(endingY, duration) {
            let startingY = window.pageYOffset,
                diff = endingY - startingY,
                start;

            /**
             * Animate scroll
             */
            window.requestAnimationFrame(function step(timestamp) {
                if (!start) start = timestamp;

                const time = timestamp - start,
                    percent = Math.min(time / duration, 1);

                window.scrollTo(0, startingY + diff * percent);

                if (time < duration) window.requestAnimationFrame(step);
            });
        }

        /**
         * Scroll el into view pad with offset in duration
         *
         * @param {HTMLElement} el element to scroll
         * @param {number} offset padding for el
         * @param {number} duration scroll animation duration
         */
        static element(el, offset, duration) {
            const opts = ScrollTo === this ? this.defaults : this.#options.config;
            const targetPosition = el.offsetTop - (offset ?? opts.offset);

            this.animateScrolling(targetPosition, duration ?? opts.duration);
        }
    }

    const shortcut = window.shortcut;
    const path = window.location.pathname;

    dumper.debug('Register Shortcuts:');

    //#region Menu
    const btnBrowse = iq('.nav-links li').filter(el => el.textContent.includes('Browse Movies'))?.pop()?.iq('@a');
    if (btnBrowse) {
        dumper.debug("    Shortcut: Browse Movies");
        shortcut.add('shift + alt + b', () => {
            btnBrowse.click();
        }, 'Goto Browse page.');
    }
    //#endregion Menu

    //#region Navigation
    if (path.startsWith('/browse-movies')) {
        const first = window.iq('ul.tsc_pagination li:first-child');
        if (!first[0].classList.contains('hidden')) {
            let id = 0;
            if (first[id].textContent.includes('First')) {
                dumper.debug("    Shortcut: First Page");
                const fp = id;
                shortcut.add('shift + alt + f', () => {
                    first[fp].iqs('a').click();
                }, 'First page.');
                id++;
            }

            if (first[id].textContent.includes('Previous')) {
                dumper.debug("    Shortcut: Previous Page");
                const pp = id;
                shortcut.add('shift + alt + p', () => {
                    first[pp].iqs('a').click();
                }, 'Previous page.');
            }
        }

        const next = iq('@ul.tsc_pagination li:last-child');
        if (next.innerText.startsWith('Next')) {
            dumper.debug("    Shortcut: Next Page");
            shortcut.add('shift + alt + n', () => {
                next.iqs('a').click();
            }, 'Next page.');
        }

        const last = iq('@ul.tsc_pagination li:last-child');
        if (!last.innerText.startsWith('Next')) {
            dumper.debug("    Shortcut: Next Page");
            shortcut.add('shift + alt + l', () => {
                last.iqs('a').click();
            }, 'Next page.');
        }
    }
    //#endregion Navigation

    //#region Scrolling
    const scrollDuration = (() => {
        let h = (document.documentElement.scrollHeight - document.documentElement.clientHeight) * 2;
        if (h < 1000) h = 1000;
        if (h > 10000) h = 10000;
        return h;
    })();

    dumper.debug("    Shortcut: Scroll up");
    dumper.debug("    Shortcut: Scroll down");
    shortcut
        .add('shift + alt + u', () => {
            ScrollTo.element((iq('@header')), 0, scrollDuration);
        }, 'Scroll up.')
        .add('shift + alt + d', () => {
            ScrollTo.element((iq('@footer')), 0, scrollDuration);
        }, 'Scroll down.');
    //#endregion Scrolling

    //#region RemoveBlocker
    /**
     * showNotice(message, options)
     *
     * options:
     *  - title: string (optional)
     *  - icon: string (emoji, optional)
     *  - timeout (ms) default: 3000
     *  - type: 'info' | 'success' | 'warning' | 'error'
     *  - position: 'top-right' | 'top-left' | 'bottom-right' | 'bottom-left'
     */
    const showNotice = function (message, options = {}) {
        const {
            title = '',
            icon = '',
            timeout = 3000,
            type = 'info',
            position = 'top-right',
        } = options;

        // Ensure container exists (singleton per position)
        const containerId = `notice-container-${position}`;
        let container = document.getElementById(containerId);

        if (!container) {
            container = document.createElement('div');
            container.id = containerId;
            container.style.position = 'fixed';
            container.style.zIndex = '9999';
            container.style.display = 'flex';
            container.style.flexDirection = 'column';
            container.style.gap = '8px';

            const [vertical, horizontal] = position.split('-');
            container.style[vertical] = '16px';
            container.style[horizontal] = '16px';

            document.body.appendChild(container);
        }

        function createNotice(message, config = {title: '', icon: ''}) {
            const {
                title = '',
                icon = '',
            } = config;

            // Create notice
            const notice = document.createElement('div');
            notice.id = `notice-${Number.getRandom(1, 10000)}`;
            notice.style.display = 'flex';
            notice.style.alignItems = 'flex-start';
            notice.style.gap = '10px';
            notice.style.padding = '12px 14px';
            notice.style.borderRadius = '6px';
            notice.style.color = '#fff';
            notice.style.fontFamily = 'system-ui, sans-serif';
            notice.style.fontSize = '14px';
            notice.style.boxShadow = '0 4px 12px rgba(0,0,0,0.15)';
            notice.style.opacity = '0';
            notice.style.transform = 'translateY(10px)';
            notice.style.transition = 'all 200ms ease';
            notice.style.cursor = 'pointer';
            notice.style.maxWidth = '25vw';

            // Type styling
            const colors = {
                info: '#2b7cff',
                success: '#2ecc71',
                warning: '#f39c12',
                error: '#e74c3c',
            };
            notice.style.background = colors[type] || colors.info;

            // Icon (optional)
            if (icon) {
                const iconEl = document.createElement('div');
                iconEl.textContent = icon;
                iconEl.style.fontSize = '18px';
                iconEl.style.lineHeight = '1';
                iconEl.style.marginTop = '2px';
                iconEl.style.alignSelf = 'center';
                iconEl.style.zoom = 3;
                notice.appendChild(iconEl);
            }

            // Content wrapper
            const content = document.createElement('div');
            content.style.display = 'flex';
            content.style.flexDirection = 'column';
            content.style.borderLeft = '1px dashed lightgrey';
            content.style.paddingLeft = '1rem';
            content.style.userSelect = 'none';

            // Title (optional)
            if (title) {
                const titleEl = document.createElement('div');
                titleEl.textContent = title;
                titleEl.style.fontWeight = '600';
                titleEl.style.fontSize = '110%';
                titleEl.style.marginBottom = '4px';
                titleEl.style.borderBottom = '1px dashed lightgrey';
                titleEl.style.textShadow = '3px 3px 4px darkslategrey';
                titleEl.style.paddingBottom = '5px';
                content.appendChild(titleEl);
            }

            // Message
            const messageEl = document.createElement('div');
            // messageEl.textContent = message;
            messageEl.innerHTML = message;
            messageEl.style.opacity = '0.95';
            messageEl.style.borderRadius = '10px';
            messageEl.style.padding = '1rem';
            messageEl.style.backgroundColor = '#C0C0C050';
            content.appendChild(messageEl);

            notice.appendChild(content);

            return notice;
        }

        const notice = createNotice(message, {title: title, icon: icon});

        container.appendChild(notice);

        // Animate in
        requestAnimationFrame(() => {
            notice.style.opacity = '1';
            notice.style.transform = 'translateY(0)';
        });

        // Dismiss logic
        const remove = (targetNotice = notice) => {
            if (!targetNotice || targetNotice.dataset.removing === 'true') return;

            targetNotice.dataset.removing = 'true';
            targetNotice.style.opacity = '0';
            targetNotice.style.transform = 'translateY(10px)';
            setTimeout(() => targetNotice.remove(), 400);
        };

        const timer = setTimeout(() => remove(notice), timeout);
        notice.dataset.timer = `${timer}`;

        notice.addEventListener('click', () => {
            clearTimeout(Number(notice.dataset.timer));
            remove(notice);
        });

        return { remove };
    }

    dumper.debug("    Shortcut: Remove Blocker");
    shortcut
        .add('shift + alt + r', () => {
            const title = 'Block: Remover';
            let b = 0;

            window.stop();

            if (iq('#dontfoid')) {
                b++;
                iq('#dontfoid')?.remove();
            }

            if (iq('script').length > 0) {
                b += iq('script').length;
                iq('script').forEach(spt => spt.remove());
            }

            if (iq('iframe').length > 0) {
                b += iq('iframe').length;
                iq('iframe').forEach(spt => spt.remove());
            }

            if (b === 0) showNotice('No blockers found.', { title: title, type: 'warning', icon: '⚠️', timeout: 4000 });
            else showNotice(`Possible blockers removed: <strong>${b}</strong>`, { title: title, type: 'success', icon: '❎', timeout: 6000 });
        }, 'Remove Blocker.')
    //#endregion RemoveBlocker


    //#region Dumper Debug
    dumper.debug('    Shortcut: Dumper => DEBUG');
    shortcut.add('shift + alt + x', () => {
        const params = new URLSearchParams(window.location.search);

        if (params.has('dumperLevel')) {
            params.delete('dumperLevel');
        } else {
            params.set('dumperLevel', 'DEBUG');
        }
        window.location.search = params.toString();

    }, 'Dumper Toggle: DEBUG.');
    //#endregion Dumper Debug
})(unsafeWindow, Dumper.get('YTS Helper', { level: unsafeWindow.dumperLevel }));
//#endregion YTS
