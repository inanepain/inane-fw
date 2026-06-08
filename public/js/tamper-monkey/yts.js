// ==UserScript==
// @name         YTS Helper
// @namespace    https://www.cathedral.co.za/tm/yts
// @version      1780951168
// @description  YTS keyboard shortcuts.
// @author       Philip Michael Raab<philip@cathedral.co.za>
// @match        https://yts.mx/*
// @match        https://yts.bz/*
// @match        https://www.yts-official.cc/*
// @match        https://en.yts-official.biz/*
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

/*
 * YTS keyboard shortcuts.
 *
 * @date 08 June 2026 10:39:28 PM SAST
 *
 * Execution order: >
 */

//#region Libraries
//#region Libraries Dumper
((window) => {
  if (window.Dumper) return window.Dumper;

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
      else if (typeof level === `string` && Dumper[level.toUpperCase()]) return Dumper[level.toUpperCase()];
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
      if (level === undefined) return true;
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
  const copyObject = (original) => {
    return JSON.parse(JSON.stringify(original));
  }

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
        value: new LogLevel({value: 1, name: 'TRACE'}),
        enumerable: true,
      },
      DEBUG: {
        value: new LogLevel({value: 2, name: 'DEBUG'}),
        enumerable: true,
      },
      INFO: {
        value: new LogLevel({value: 3, name: 'INFO'}),
        enumerable: true,
      },
      TIME: {
        value: new LogLevel({value: 4, name: 'TIME'}),
        enumerable: true,
      },
      WARN: {
        value: new LogLevel({value: 5, name: 'WARN'}),
        enumerable: true,
      },
      ERROR: {
        value: new LogLevel({value: 8, name: 'ERROR'}),
        enumerable: true,
      },
      OFF: {
        value: new LogLevel({value: 99, name: 'OFF'}),
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

      this.trickle = throttle(this.debug, this.#options.trickle, {context: this});
      if (!this.dump) this.dump = () => {
      };

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
      const children = (this === Dumper || this.#children === undefined) ? Children : this.#children;

      if (!children.has(name)) {
        // Any unset child options are copied from parent
        if (this !== Dumper) mergeOptions(options, this.#options);
        if (options.level && options.level.name) options.level = options.level.name;

        options = copyObject(options);
        options.level = LogLevel.from(options.level);

        let context = this === Dumper ? {name: []} : copyObject(this.#context);
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
      for (let [name, child] of (this !== Dumper && this.#children || Children)) kids[name] = child; // this.log(child.name, child);

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
      let options = (this === Dumper || this.#options === undefined) ? defaults : this.#options;

      if (!(level instanceof LogLevel)) level = LogLevel.from(level);

      // If initial setLevel OR updateChain is true: update options
      if (!bubbled || (this === Dumper || options.bubbling.listen)) options.level = level;

      // Global Dumper & LogLevel.OFF: We don't chain Level since Global.OFF stops all logging as is.
      // OR if the level chain set to stop bubbling
      if ((this === Dumper && level === this.OFF) || (bubbled && options.bubbling.trigger === false)) return;

      // Single Instance Dumper: Has no children so we stop here to prevent a Global setLevel call.
      if (this !== Dumper && this.#children === undefined) return this;

      // Update level of children
      if (options.bubbling.trigger) for (let child of (this !== Dumper && this.#children || Children).values()) child.setLevel(level, true);
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
      formatMessage(messages, (this === Dumper ? undefined : this.#context), {color: 'DarkBlue'});
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
      formatMessage(messages, (this === Dumper ? undefined : this.#context), {color: 'LightBlue'});
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
      formatMessage(messages, (this === Dumper ? undefined : this.#context), {color: 'Blue'});
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
      formatMessage(messages, (this === Dumper ? undefined : this.#context), {color: 'Orange'});
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
      formatMessage(messages, (this === Dumper ? undefined : this.#context), {color: 'DarkRed'});
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
      formatMessage(messages, (this === Dumper ? undefined : this.#context), {color: 'Black'});
      if (this.getLevel().allows()) return out.log.apply(this, messages);
    }

    /**
     * Log a message and stack trace to the console if the first argument is `false`
     *
     * @since 2.4.3 assert available on Dumper class not only instances
     *
     * @param {boolean} assertion boolean assertion
     * @param {...any} messages log messages
     *
     * @returns {boolean} if assertion took place or not @since 2.4.1
     */
    static assert(assertion, ...messages) {
      formatMessage(messages, (this === Dumper ? undefined : this.#context), {color: 'Crimson'});
      const new_ts = Date.now();
      const old_ts = this._last_assert || 0;

      const options = this === Dumper ? defaults : this.#options;

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
      formatMessage(messages, (this === Dumper ? undefined : this.#context));
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
      formatMessage(messages, (this === Dumper ? undefined : this.#context));
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
      formatMessage(messages, (this === Dumper ? undefined : this.#context));
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
      formatMessage(messages, (this === Dumper ? undefined : this.#context));
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
      const counters = (this === Dumper ? Counters : this.#counters);
      if (!counters.hasOwnProperty(label)) counters[label] = 0;
      counters[label] += 1;
      let messages = [`${label}: ` + counters[label]];
      formatMessage(messages, (this === Dumper ? undefined : this.#context));
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
      const counters = (this === Dumper ? Counters : this.#counters);
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
      this.#options.assert.limit = assertLimit.toString() === 'NaN' ? 0 : assertLimit;
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
      return this.optionBubbleFromParent === this.optionBubbleToChildren && this.optionBubbleToChildren || null;
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
//#endregion Libraries Dumper

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

            if (typeof el === `string`) {
                this.#options.selector = el;
                this.#options.el = document.querySelector(el);
            } else if (el instanceof HTMLElement) {
                this.#options.selector = null;
                this.#options.el = el;
            }

            if (this.#options.el === null && this.#options.config.fail_gracefully === false) throw `Parameter is not a valid HTMLElement or selector string!`;

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
            if (first[id].textContent.includes('First') || first[id].textContent.includes('1.')) {
                dumper.debug("    Shortcut: First Page");
                const fp = id;
                shortcut.add('shift + alt + f', () => {
                    first[fp].iqs('a').click();
                }, 'First page.');
                id++;

                if (first[id].nextElementSibling.textContent.includes('Previous')) {
                    dumper.debug("    Shortcut: Previous Page");
                    shortcut.add('shift + alt + p', () => {
                        first[fp].nextElementSibling.iqs('a').click();
                    }, 'Previous page.');
                }
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
            dumper.debug("    Shortcut: Last Page");
            shortcut.add('shift + alt + l', () => {
                last.iqs('a').click();
            }, 'Last page.');

            if (last.previousElementSibling.textContent.includes('Next')) {
                dumper.debug("    Shortcut: Next Page");
                shortcut.add('shift + alt + n', () => {
                    last.previousElementSibling.iqs('a').click();
                }, 'Next page.');
            }
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
        // .add('shift + alt + u', () => {
        //     ScrollTo.element((iq('@header')), 0, scrollDuration);
        // }, 'Scroll up.')
        // .add('shift + alt + d', () => {
        //     ScrollTo.element((iq('@footer')), 0, scrollDuration);
        // }, 'Scroll down.');
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
