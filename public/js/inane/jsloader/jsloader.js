/**
 * JSLoader
 *
 * Load scripts/css lazy
 * Setup deps between scripts
 * @link https://git.inane.co.za:3000/Inane/inane-js/wiki/Inane-JSLoader
 *
 * @version 0.11.1
 * @author Philip <philip@cathedral.co.za>
 *
 * Public Domain.
 * NO WARRANTY EXPRESSED OR IMPLIED. USE AT YOUR OWN RISK.
 */
/* vscode: vscode-fold=2 */

/**
 * EVENTS
 * add
 *
 * start
 *
 * queueing
 * queued
 *
 * skipped
 * loaded
 * error
 * ignore
 *
 * done
 * finished
 */

/**
 * Change Log
 *
 * 0.11.1 (2020 Jul 06)
 * - update      : script params use data attributes.
 * - new         : data-basepath script attribute added to set base path.
 *
 * 0.11.0 (2020 May 24)
 * update        : manualTrigger: when set to true JSLoader does not trigger on load event but waits for manual call of start function.
 *
 * 0.10.0 (2020 May 21)
 * update        : new attachment functions that are uniform and cleaner then before.
 *
 * 0.9.0 (2018 Oct 04)
 *  - loadCheck  : About time, validate module name right up front and ignore invalid ones and triggers an ignore event
 *  - dotRequire : For some items it make sense to put to requirement onto the module name. IE a custom jquiery select => myselect could be called jqueryui.myselect. This looks for jqueryui as a requirement.
 *  - events     : Replaced onreadystatechange with addEvent...
 *
 * 0.8.3 (2018 Sep 19)
 *  - logging    : Totally quiet when off
 *  - Inane      : No longer used
 *
 * 0.8.2 (2018 Jul 3)
 *  - ready      : Fixed: Ready scripts not run if no modules loaded
 *  - arg check  : Fix: Error if loadModules called with no arguments
 *
 * 0.8.1 (2017 Nov 23)
 *  - baseURL    : Add option for a base url to prepend urls
 *  - fix spinner: Spinner fades out based on transTime
 *
 * 0.8.0 (2016 May 26)
 *  ? Fix Loop   : Multi load required modules
 *  - Ready      : Register callbacks for done
 *
 * 0.7.0 (2016 May 18)
 *  - history    : Keep history of loaded files, prevent double loading
 *  - event & log: Fixed a problem with css files sent as empty string to events
 *  - other      : Little tweaks to speed things up
 *
 * 0.6.0 (2016 Apr 29)
 *  - noCache    : uses a query string to stop browser caching of stylesheets
 *  - Spinner    : optional loading spinner while scripts loading (only handy for large queues)
 *  - Underscore : removed dependence (next backbone?)
 */

(function (factory) {
	/**
	 * @type {Object}
	 */
    var root = (typeof self == 'object' && self.self == self && self) || (typeof global == 'object' && global.global ==
        global && global);

    if (typeof root.JSLoader !== 'undefined') {
        window.console.log('JSLoader already loaded!');
        return;
    }

	/**
	 * @class JSLoader
	 * @type {Object}
	 */
    var JSLoader = {};
    _.extend(JSLoader, Backbone.Events);

	/**
	 * Create JSLoader
	 * @function factory
	 * @param {Object} root
     * @memberof JSLoader
    //  * @class JSLoader
	 */
    root.JSLoader = factory(root, JSLoader, root.Backbone, (root.jQuery || root.Zepto || root.ender || root.$));
}(function (root, JSLoader, Backbone, $) {
	/**
	 * A LoaderModule
	 * @typedef {Object} LoaderModule
	 * @property {string} script - The javascript file
	 * @property {string} style - The stylesheet file
	 * @property {string[]} require - array listing the id's of required modules
     * @memberof JSLoader
 	 */

	/**
	 * @type {Object} JSLoader
	 * @property {LoaderModule} moduleList - Available modules
     * @memberof JSLoader
	 */
    JSLoader.VERSION = '0.11.1';
    JSLoader.verbose = document.currentScript.getAttribute('data-verbose') == 1 ? true : false;

	/**
	 * @type {LoaderModule[]} - Holds the modules defined in jsloader-modules.js
     * @memberof JSLoader
	 */
    JSLoader.moduleList = {};

	/**
	 * @type {boolean} - When true (default: false) adds ?ts=timestamp to style to avoid caching
     * @memberof JSLoader
	 */
    JSLoader.noCache = false;

    /**
	 * @type {boolean} - When true (default: false) start not called automatically
     * @memberof JSLoader
	 */
    // JSLoader.manualTrigger = false;
    JSLoader.manualTrigger = document.currentScript.getAttribute('data-manual') == 1 ? true : false;

	/**
	 * @type {boolean} - When true JSLoader will call stop spinner when done
	 */
    JSLoader.autospinner = true;

    var processQueue = [];
    var scriptCollection = [];

    var transTime = 2000;

    var history = [];

    var baseURL = document.currentScript.getAttribute('data-basepath') || '';

    $.holdReady(true);

	/**
	 * FUNCTIONS
	 */

	/**
	 * Set a base url
	 *
	 * @param {string} url
     * @memberof JSLoader
	 */
    JSLoader.setBaseUrl = function (url) {
        baseURL = url;
        return JSLoader;
    }; // setBaseUrl

    /**
	 * Get a base url
	 *
	 * @returns {string} url
     * @memberof JSLoader
	 */
    JSLoader.getBaseUrl = function () {
        return baseURL;
    }; // getBaseUrl

	/**
	 * Removes all instances of values from array and returns new array
	 *
	 * @param array		Source array to remove values from
	 * @param values	String or Array with values to remove
	 *
	 * @return array
	 */
    function without(array, values) {
        if (!Array.isArray(values)) values = [values];
        return array.filter(function (element) {
            return values.indexOf(element) < 0;
        });
    } // without

	/**
	 * Each
	 */
    var each = Array.prototype.forEach;

	/**
	 * LOGGING
	 */

	/**
	 * Turn logging to console on or off
	 *
	 * @param {boolean} enabled
     * @memberof JSLoader
	 */
    JSLoader.enableVerbosity = function (enabled) {
        if (enabled === false)
            JSLoader.verbose = false;
        else
            JSLoader.verbose = true;

        return JSLoader;
    }; // enableVerbosity

	/**
	 * Logs msg if verbosity OR show true
	 *
	 * @param {string} msg
	 * @param {boolean} show
	 * @returns {string}
	 */
    function logger(msg, show) {
        if ((JSLoader.verbose === true) || (show === true)) {
            window.console.debug('JSLoader: ' + msg);
        }
        return JSLoader;
    } // logger

	/**
	 * About JSLoader
	 *
	 * Show some basic JSLoader info
     *
     * @memberof JSLoader
	 */
    JSLoader.about = function () {
        logger(
            `
Author: Philip Michael Raab<philip@cathedral.co.za>
Version: ${JSLoader.VERSION}
Script verbosity attribute: 1`);
    }; // about

	/**
	 * @type {function[]} - Functions to run once everything loaded and system ready
     * @memberof JSLoader
	 */
    this._ready = [];

	/**
	 * Adds a function to be called when the system is ready
	 *
	 * @function ready
	 * @param {function} listener
     * @memberof JSLoader
	 */
    JSLoader.ready = function (listener) {
        if (typeof JSLoader._ready == 'undefined') {
            JSLoader._ready = [];
        }

        JSLoader._ready.push(listener);
    };

    let removeReady = function (listener) {
        if (JSLoader._ready instanceof Array) {
            var listeners = JSLoader._ready;
            for (var i = 0, len = listeners.length; i < len; i++) {
                if (listeners[i] === listener) {
                    listeners.splice(i, 1);
                    break;
                }
            }
        }
    };

    let runReady = function () {
        logger('running => ready');
        if (JSLoader._ready instanceof Array) {
            var listeners = JSLoader._ready;
            var listener;
            //for (var i = 0, len = listeners.length; i < len; i++) {
            while (listener = listeners.shift()) {
                listener.call(this);
                removeReady(listener);
            }
        }
        logger('finished');
        JSLoader.trigger('finished');
    };

	/**
	 * @type {boolean}
     * @memberof JSLoader
	 */
    JSLoader.spinner = false;

	/**
	 * Set autospinner on|off
	 *
	 * @param enabled	bool
     * @memberof JSLoader
	 */
    JSLoader.setAutospinner = function (enabled) {
        if (enabled === false) {
            JSLoader.autospinner = false;
        } else {
            JSLoader.autospinner = true;
        }
        return JSLoader;
    }; // setAutospinner

	/**
	 * Adds spinner to body and starts it
	 *
	 * @returns JSLoader
     * @memberof JSLoader
	 */
    JSLoader.spinnerStart = function () {
        if (JSLoader.spinner === false) {
            JSLoader.spinner = document.createElement('div');
            JSLoader.spinner.classList.add('jsl-fading-circle');

            JSLoader.spinner.innerHTML =
                `
<div class="jsl-circle1 jsl-circle"></div>
<div class="jsl-circle2 jsl-circle"></div>
<div class="jsl-circle3 jsl-circle"></div>
<div class="jsl-circle4 jsl-circle"></div>
<div class="jsl-circle5 jsl-circle"></div>
<div class="jsl-circle6 jsl-circle"></div>
<div class="jsl-circle7 jsl-circle"></div>
<div class="jsl-circle8 jsl-circle"></div>
<div class="jsl-circle9 jsl-circle"></div>
<div class="jsl-circle10 jsl-circle"></div>
<div class="jsl-circle11 jsl-circle"></div>
<div class="jsl-circle12 jsl-circle"></div>`;
        }

        JSLoader.spinner.style.width = window.innerWidth + 'px';
        JSLoader.spinner.style.height = window.innerHeight + 'px';
        //document.body.prepend(JSLoader.spinner);
        document.body.appendChild(JSLoader.spinner);
        JSLoader.spinner.style.opacity = 1;
        logger('spinner => start');

        return JSLoader;
    }; // spinnerStart

	/**
	 * Adds spinner to body and starts it
	 *
	 * @returns JSLoader
     * @memberof JSLoader
	 */
    JSLoader.spinnerStop = function () {
        if (JSLoader.spinner !== false) {
            JSLoader.spinner.style.opacity = 0;
            setTimeout(function () {
                JSLoader.spinner.remove();
                logger('spinner => stop');
            }, transTime);
        }
        return JSLoader;
    }; // spinnerStop

	/**
	 * HISTORY
	 */

	/**
	 * Return array of loaded scripts
     *
     * @memberof JSLoader
	 */
    JSLoader.getHistory = function () {
        return history;
    };

	/**
	 * Returns true if script already loaded
     *
     * @memberof JSLoader
	 */
    JSLoader.hasHistory = function (script) {
        return history.indexOf(script) >= 0 ? true : false;
    };

	/**
	 * Adds script to history if no recorded exists
	 */
    let addHistory = function (script) {
        JSLoader.hasHistory(script) || history.push(script);
        return JSLoader;
    };

	/**
	 * dependency
     *
     * @memberof JSLoader
	 */
    JSLoader.checkDependents = _.once(function () {
        var keys = _(JSLoader.moduleList).allKeys();
        keys.forEach(function (module) {
            var requs = JSLoader.moduleList[module].require || [];
            requs.forEach(function (mod) {
                var deps = JSLoader.moduleList[mod].dependents || [];
                deps.push(module);
                deps.sort();
                JSLoader.moduleList[mod].dependents = deps;
            });
        });
    });

	/**
	 * LOADING SCRIPTS
	 */

	/**
	 * Add script urls to queue
	 *
	 * @deprecated
	 * @param scripts	array
	 * @param priority	int
     *
     * @memberof JSLoader
	 */
    JSLoader.addScripts = function (scripts, priority) {
        logger('DEPRECATED => addScripts', true);
        return addToCollection(scripts, priority);
    };

	/**
	 * Add script urls to collection
	 *
	 * @param scripts	array
	 * @param priority	int
	 */
    function addToCollection(scripts, priority) {
        if (scripts.length > 0) {
            if (typeof priority === 'undefined') priority = 10;

            if (!Array.isArray(scriptCollection[priority])) scriptCollection[priority] = [];

            scriptCollection[priority] = scriptCollection[priority].concat(scripts);
            JSLoader.trigger('add', scripts, priority);
            logger('added => ' + scripts.length);
        }
        return JSLoader;
    }

	/**
	 * Check module name for requirements
	 *
	 * @param {string} modules
	 */
    function parseModuleName(moduleName) {
        logger('parsing => ' + moduleName + ': for requirements');
        let moduleNameArray = moduleName.split('.');
        moduleNameArray.pop();
        JSLoader.moduleList[moduleName]['require'] = JSLoader.moduleList[moduleName].require || [];
        each.call(moduleNameArray, function (item) {
            JSLoader.moduleList[moduleName].require = JSLoader.moduleList[moduleName].require.concat(item);
        });
    }

	/**
	 * Adds a module to the queue for loading
	 *
	 * @param {Array} modules
	 * @param {number} priority
	 *
	 * @returns {JSLoader}
     * @memberof JSLoader
	 */
    JSLoader.loadModules = function (modules, priority) {
        modules = modules || [];
        if (modules.length > 0) {
            var scripts = [];

            each.call(modules, function (module) {
                if (!_(JSLoader.moduleList).has(module)) {
                    JSLoader.trigger('ignore', {
                        module: module,
                        error: 'Not found in moduleList',
                        event: 'ignore'
                    });
                    return;
                }
                if (module.split('.').length > 0) parseModuleName(module);
                if (JSLoader.moduleList[module].loaded !== true) {
                    if (Array.isArray(JSLoader.moduleList[module].require)) {
                        logger('require => ' + JSLoader.moduleList[module].require.join(', '));
                        JSLoader.loadModules(JSLoader.moduleList[module].require, priority);
                    }
                    let sFiles = [JSLoader.moduleList[module].style, JSLoader.moduleList[module].script];

                    scripts = scripts.concat(without(sFiles, undefined));
                    JSLoader.moduleList[module].loaded = true;
                }
            });

            addToCollection(scripts, priority);
        }
        return JSLoader;
    };

	/**
	 * Loading scripts into dom
	 */

	/**
	 * Loading a module failed
	 *
	 * @param _callback	function
     * @memberof JSLoader
	 */
    JSLoader.fail = function (_callback) {
        JSLoader.trigger('error', _callback);
        return JSLoader;
    };

    /**
     * Attach Script
     *
	 * Attaches a script to document header
	 *
	 * @param {string} src	property for script element
     * @param {boolean} isModule
     * @param {boolean} async
     * @param {boolean} defer
     * @returns {Promise}
     * @memberof JSLoader
	 */
    function attachScript(src, isModule, async, defer) {
        const script = document.createElement('script');
        script.url = src;

        if (isModule) script.type = 'module';
        else script.type = 'application/javascript';
        if (async) script.setAttribute('async', '');
        if (defer) script.setAttribute('defer', '');

        document.head.appendChild(script);

        return new Promise((success, error) => {
            script.onload = success;
            script.onerror = error;
            script.src = src;// start loading the script
        });
    }

    /**
     * Attach Style
     *
	 * Attaches a style link to document header
	 *
	 * @param {string} href	property for link element
     * @returns {Promise}
     * @memberof JSLoader
	 */
    function attachStyle(href) {
        const style = document.createElement('link');
        style.url = href;
        style.media = 'screen';
        style.rel = 'stylesheet';
        style.type = 'text/css';

        document.head.appendChild(style);

        return new Promise((success, error) => {
            style.onload = success;
            style.onerror = error;
            style.href = href;// start loading the style
        });
    }

    /**
     * attachHandler
     *
	 * Send file to relevant function for attaching and that is a Promise
	 *
	 * @param {string} file	link of file to attach
     * @returns {Promise}
     * @memberof JSLoader
	 */
    function attachHandler(file) {
        const type = file.split('.').pop();
        file += JSLoader.noCache ? '?ts=' + Date.now() : '';

        if (type === 'js') return attachScript(file);
        else if (type === 'css') return attachStyle(file);

        return false;
    }

	/**
	 * Processing requests
	 */

	/**
	 * Adds scripts to queue by priority and unique
	 * Returning true if there are items to process
	 *
	 * @returns {bool}
	 */
    function createQueue() {
        logger('loading => queue');
        JSLoader.trigger('queueing');

        let merged = processQueue;

        let collection = scriptCollection;
        scriptCollection = [];

        each.call(collection, function (scripts) {
            if (typeof scripts !== 'undefined') {
                merged = merged.concat(scripts);
            }
        });
        processQueue = [...new Set(merged)];

        if (processQueue.length > 0) {
            logger('queued => ' + processQueue.length);
            JSLoader.trigger('queued', processQueue.length);
            return true;
        }

        runReady();

        if (JSLoader.autospinner) JSLoader.spinnerStop();
        return false;
    }

	/**
	 * Processes a batch of modules to add to dom
	 *
	 * @param scripts	array
	 */
    function process(scripts) {
        if (scripts.length > 0) {
            $.holdReady(true);
            let aScript = scripts.shift(); // + '?' + Date.now();
            aScript = baseURL.concat(aScript);

            if (JSLoader.hasHistory(aScript)) {
                logger('duplicate => ' + aScript);
                JSLoader.trigger('skipped', aScript);
                process(scripts);
                $.holdReady(false);
            } else {
                attachHandler(aScript).then(event => {
                    const script = event.target.url.split('?').shift();
                    addHistory(script);
                    logger('success => ' + script);
                    JSLoader.trigger('loaded', script);
                }).catch(event => {
                    const script = event.target.url.split('?').shift();
                    logger('Failed => ' + script + ' => ERROR', true);
                    JSLoader.trigger('error', {
                        script: script,
                        error: event
                    });
                }).finally(() => {
                    process(scripts);
                    $.holdReady(false);
                });
            }
        } else {
            if (createQueue()) process(processQueue);

            $.holdReady(false);
            logger('loading => done');
            JSLoader.trigger('done');
        }
    }

	/**
	 * Start JSLoader loading the added scripts
	 * events: add, start, queueing, queued, loaded, error, done
	 *
	 * @returns {String}
     * @memberof JSLoader
	 */
    JSLoader.start = function () {
        logger('loading => start');
        JSLoader.trigger('start');

        JSLoader.checkDependents();
        if (createQueue()) process(processQueue);

        $.holdReady(false);
        return JSLoader;
    };

    /**
     * Listen for when dom loaded
     */
    root.document.addEventListener('readystatechange', function (evt) {
        switch (evt.target.readyState) {
            case 'loading':
                // Loading Stuff
                break;
            case 'interactive':
                if (JSLoader.manualTrigger == false) JSLoader.start();
                break;
            case 'complete':
                logger('root.document.readyState == \'complete\'');
                break;
        }
    }, false);

    /* Show about if script verbosity attribute set to 1 */
    JSLoader.about();

    return JSLoader;
}));
