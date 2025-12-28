/**
 * iHelper
 *
 * Ease of use for icRoot
 * @see https://git.inane.co.za:3000/Inane/inane-js/wiki/Inane_icRoot-iHelper
 *
 * @author Philip Michael Raab <philip@cathedral.co.za>
 * @version 0.6.2
 * @class iHelper
 */
/** vscode-fold=2 */
(function (root) {
    /**
     * Version
     *
     * @constant
     * @type {String}
     * @memberof iHelper
     */
    const VERSION = '0.6.2';

    /**
     * moduleName
     *
     * @constant
     * @type {String}
     * @memberof iHelper
     */
    const moduleName = 'iHelper';

    if (window.Dumper) Dumper.dump('MODULE', moduleName.concat(' v').concat(VERSION), 'LOAD');
    else if (window.dump) window.dump('SCRIPT', moduleName.concat(' v').concat(VERSION), 'LOAD');

    /**
     * iHelper
     *
     * @constant
     * @type {Object}
     * @memberof iHelper
     */
    const iHelper = {
        VERSION: VERSION,
        importModule: undefined,
        module: undefined,
        icModules: undefined,
    };

    /**
     * DevMode
     *
     * Development Mode
     *
     * @type {boolean}
     * @memberof iHelper
     */
    let devMode = false;

    /**
     * icPath
     *
     * path of icModules
     *
     * @constant
     * @type {String}
     * @memberof iHelper
     */
    // const icPath = '/js/inane/icroot/__IC__.js?e=' + _.now();
    const icPath = '/js/inane/icroot/__IC__.js';

    /**
     * icModules
     *
     * Valid icModules collection
     *
     * @constant
     * @type {Object}
     * @memberof iHelper
     */
    const icModules = {
        imVersionMixin: 'imVersionMixin',
        iFile: 'iFile',
        iOptions: 'iOptions',
        iShortcut: 'iShortcut',
        iStr: 'iStr',
        iTemplate: 'iTemplate',
        iView: 'iView',
    };
    iHelper.icModules = icModules;

    /**
     * rootModules
     *
     * icModules that get loaded into root as well as icRoot
     *
     * @constant
     * @type {string[]}
     * @memberof iHelper
     */
    const rootModules = ['iShortcut'];

    let logger = root['Dumper'] ? root.Dumper.get('Inane', {
        level: Dumper.WARN,
    }).get(`iHelper`) : console;

    /**
     * Loads an ic module
     *
     * @param {string} icModule
     * @memberof iHelper
     */
    const importModule = (icModule, callback = null) => {
        // Return if the module is invalid
        if (!icModules[icModule]) return logger.warn('importModule', 'Invalid IC Module: '.concat(Object.keys(icModules).join(': ')));

        // If rootModule found in root but not icroot we copy it to icroot
        if (rootModules.includes(icModule) && root[icModule]) if (!root._icroot[icModule]) root._icroot[icModule] = root[icModule];

        // If module already loaded, we return it
        if (root._icroot[icModule]) return new Promise(module => module(root._icroot[icModule]));

        // Build the file path
        const icFile = icPath.replace('__IC__', icModule).concat(devMode ? '?e=' + _.now() : '');

        return new Promise(resolve => {
            import(icFile).then((module) => {
                logger.debug('importModule', 'Loaded: '.concat(icModule));

                // Also and it to the _icroot and root if a rootModule
                root._icroot[icModule] = module.default;
                if (rootModules.includes(icModule)) root[icModule] = module.default;

                // And finally bring it all back
                resolve(module.default);
            }).then(callback);
        });
    }
    iHelper.importModule = importModule;


    /**
     * Add iqs to HTMLElement prototype
     */
    if (!HTMLElement.prototype.iqs) {
        /**
         * iqs<br />
         * Shorter version of QuerySelector
         *
         * @param {string} selectors
         */
        HTMLElement.prototype.iqs = function (selectors) {
            if (this && this.querySelector) return this.querySelector(selectors);
            return root.document.querySelector(selectors);
        }
    }

    /**
     * Add iqsa to HTMLElement prototype
     */
    if (!HTMLElement.prototype.iqsa) {
        /**
         * iqsa<br />
         * Shorter version of QuerySelectorAll
         *
         * @param {string} selectors
         */
        HTMLElement.prototype.iqsa = function (selectors) {
            if (this && this.querySelectorAll) return this.querySelectorAll(selectors);
            return root.document.querySelectorAll(selectors);
        }
    }

    if (!root.iqs) {
        root.iqs = function (selectors) {
            return root.document.querySelector(selectors);
        }
    }

    if (!root.iqsa) {
        root.iqsa = function (selectors) {
            return root.document.querySelectorAll(selectors);
        }
    }

    if (!root.document.iqs) {
        root.document.iqs = function (selectors) {
            return root.document.querySelector(selectors);
        }
    }

    if (!root.document.iqsa) {
        root.document.iqsa = function (selectors) {
            return root.document.querySelectorAll(selectors);
        }
    }

    // Check for _icroot or create
    if (root['_icroot'] === undefined) root['_icroot'] = {};
    // Check for module in _icroot or create
    if (!root._icroot[moduleName]) root._icroot[moduleName] = iHelper;
}(window));
