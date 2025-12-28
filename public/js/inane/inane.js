/**
 * InaneJS
 *
 * @link https://git.inane.co.za:3000/Inane/inane-js/wiki/Inane-Inane
 *
 * @version 0.17.2
 * @author Philip Michael Raab <philip@cathedral.co.za>
 * @copyright 2020 Philip Michael Raab <philip@cathedral.co.za>
 *
 * @license MIT
 *
 * @see {@link https://git.inane.co.za:3000/Inane/inane-js/raw/master/LICENSE MIT license}
 *
 * Public Domain.
 * NO WARRANTY EXPRESSED OR IMPLIED. USE AT YOUR OWN RISK.
 */
/* vscode: vscode-fold=2 */
/**
 * 0.17.2 (2022 Feb 16)
 * + throttle: options.context add to specify the `this` object
 * + sortByLength: sort an array of string by their length
 * - longestCommonSubstring: makes use of `sortByLength` to improve result
 *
 * 0.17.1 (2021 May 17)
 * + objectToQuery: fix variable assignment in strict mode
 *
 * 0.17.0 (2021 Mar 05)
 * + commonSubsequence: get the longest common substring in a string array
 *
 * 0.16.2 (2020 Dec 15)
 * - parseValue: fix numeric strings with spaces
 *
 * 0.16.1 (2020 Oct 13)
 * - parseValue: fix for dates
 *
 * 0.16.0 (2020 Sep 07)
 * - pick: New function to create object with selected properties from source object
 *
 * 0.15.0 (2020 Aug 06)
 * - pick: New function to create object with selected properties from source object
 *
 * 0.14.1 (2020 Jul 29)
 * - queryToObject: Fix: Empty string now returns a empty object
 *
 * 0.14.0 (2020 Jul 27)
 * - addFeatureFallback: checks object prototype for function and assign custom function if not found
 * - browserData: added basic isMobile property
 * - queryToObject: Fix: Empty string now returns a empty object
 *
 * 0.13.0 (2020 Jun 26)
 * - objectToQuery: turns object to url query string
 * - queryToObject: turns url query string to object
 *
 * 0.12.0 (2020 Jun 14)
 * - mergeOptions can now take any amount of source objects in decreasing order of priority
 *
 * 0.11.2 (2020 Jun 10)
 * - Fix md5 function, variable not init
 * - Shuffle code around a little
 *
 * 0.11.1 (2020 Jun 07)
 * - Updated animate classes to use new version
 *
 * 0.11.0 (2020 Jun 01)
 * - converted to class
 * - throttle take optional options argument
 *
 * 0.10.0 (2020 May 20)
 * - new     : modern setTimeout that returns a promise which resolves at timeout
 * - update  : mergeOptions: null & undefined properties on obj1 will now be updated
 *
 * 0.9.3 (2020 Apr 27)
 * - new     : throttle function that returns a restricted version of passed in function
 * - new     : isElementInView returns true if element is in viewport
 *
 * 0.9.2 (2020 Apr 20)
 * - fix     : mergeOptions was not handling objects in objects correctly
 *
 * 0.9.0 (2020 Apr 01)
 *  - new    : mergeOptions function add
 *
 * 0.8.0 (2019 Dec 03)
 *  - new    : browserData function added
 *
 * 0.7.0 (2019 May 28)
 *  - new    : DataText function added
 *
 * 0.6.0 (2019 May 13)
 *  - new    : md5 function added
 *
 * 0.5.1 (2019 Mar 04)
 *  - fix    : animateCss works with array of elements
 *
 * 0.5.0 (2019 Feb 22)
 *  - new    : animateCss function that works with animateCss
 *
 * 0.4.0 (2019 Feb 21)
 *  - new    : allowTracking - return the Do Not Track status
 *  - upd    : VERSION now readonly
 *
 * 0.3.0 (2018 Dec 06)
 *  - new    : all underscore like stuff removed, Inane will now focus on its own functions
 *
 * 0.2.0 (2016 May 18)
 *  - added  : propertyOf, allKeys, pick
 *  - added  : isEqual, isObject, isFunction, isBoolean
 *  - added  : flatten
 *
 * 0.1.0 (2016 Apr 08)
 *  - Initial: each
 */

((factory) => {
    /**
     * Inane Automatic Startup
     */
    let root = (typeof self == 'object' && self.self == self && self) || (typeof global == 'object' && global.global == global && global);

    if (typeof root.I !== 'undefined') {
        console.log('Inane already loaded!');
        return;
    }

    /**
     * Inane I
     *
     * @class I
     */
    root.I = factory(root, class I {
        constructor() {
            // console.log(`Inane v${VERSION}`);
        }
    });
})(function (root, Inane) {
    const VERSION = '0.17.2';

    const getCache = [];

    /**
     * MD5: Convert a 32-bit number to a hex string with ls-byte first
     */
    const hex_chr = '0123456789abcdef';
    function rhex(num) {
        let str = '';
        for (let j = 0; j <= 3; j++) str += hex_chr.charAt((num >> (j * 8 + 4)) & 0x0F) + hex_chr.charAt((num >> (j * 8)) & 0x0F);
        return str;
    }

    /**
     * Convert a string to a sequence of 16-word blocks, stored as an array.
     * Append padding bits and the length, as described in the MD5 standard.
     */
    function str2blks_MD5(str) {
        let nblk = ((str.length + 8) >> 6) + 1;
        let blks = new Array(nblk * 16);
        for (let i = 0; i < nblk * 16; i++) blks[i] = 0;
        for (i = 0; i < str.length; i++) blks[i >> 2] |= str.charCodeAt(i) << ((i % 4) * 8);
        blks[i >> 2] |= 0x80 << ((i % 4) * 8);
        blks[nblk * 16 - 2] = str.length * 8;
        return blks;
    }

    /**
     * Add integers, wrapping at 2^32. This uses 16-bit operations internally
     * to work around bugs in some JS interpreters.
     */
    function add(x, y) {
        let lsw = (x & 0xFFFF) + (y & 0xFFFF);
        let msw = (x >> 16) + (y >> 16) + (lsw >> 16);
        return (msw << 16) | (lsw & 0xFFFF);
    }

    /**
     * Bitwise rotate a 32-bit number to the left
     */
    function rol(num, cnt) {
        return (num << cnt) | (num >>> (32 - cnt));
    }

    /**
     * These functions implement the basic operation for each round of the algorithm.
     */
    function cmn(q, a, b, x, s, t) {
        return add(rol(add(add(a, q), add(x, t)), s), b);
    }
    function ff(a, b, c, d, x, s, t) {
        return cmn((b & c) | ((~b) & d), a, b, x, s, t);
    }
    function gg(a, b, c, d, x, s, t) {
        return cmn((b & d) | (c & (~d)), a, b, x, s, t);
    }
    function hh(a, b, c, d, x, s, t) {
        return cmn(b ^ c ^ d, a, b, x, s, t);
    }
    function ii(a, b, c, d, x, s, t) {
        return cmn(c ^ (b | (~d)), a, b, x, s, t);
    }

    /**
     * I
     *
     * @version 0.17.2
     */
    class I extends Inane {
        /**
         * Creates an instance of Inane.
         */
        constructor() {
            super();
        }

        /**
         * VERSION
         *
         * @readonly
         *
         * @returns version
         */
        static get VERSION() {
            return VERSION;
        }

        /**
         * VERSION
         *
         * @readonly
         *
         * @returns version
         */
        get VERSION() {
            return I.VERSION;
        }

        /**
         * allowTracking (private)
         *
         * true if Do Not Track not enabled
         *
         * @returns {boolean}
         */
        get allowTracking() {
            return root.doNotTrack || navigator.doNotTrack == 1;
        }

        /**
         * Parses value to int|float|string
         *
         * @param {number|string|null} value - object to parse
         * @returns {number|string|null} the value cast to best matching type
         */
        parseValue = value => value === "0" ? 0 : ((typeof value != 'number' && value?.includes(' ')) ? value : Number.isNaN(value) == false && Number.isInteger(value) && Number.parseInt(value) || Number.parseFloat(value) || value);

        /**
         * Adds ONLY missing properties from source objects to target in decreasing priority
         *
         * @example
         * // copy values from defaults missing in options to options
         * I.mergeOptions(options, defaults);
         *
         * @param {Object} target the target object
         * @param {Object[]} source the source objects in decreasing order of priority
         *
         * @returns {Object} target with missing properties from objs
         */
        mergeOptions(target, ...source) {
            let key, i;
            for (i = 0; i < source.length; i++) {
                for (key in source[i]) {
                    if (!(key in target) && source[i].hasOwnProperty(key)) {
                        target[key] = source[i][key];
                    } else {
                        try { // If we are dealing with child objects here we simple dive into them to process the whole object
                            if (target[key].constructor === Object && source[i][key].constructor === Object) this.mergeOptions(target[key], source[i][key]);
                        } catch (error) { // If target has undefined or null we catch the error and set the value
                            if (error.message.includes('target[key].constructor')) target[key] = source[i][key];
                        }
                    }
                }
            }
            return target;
        }

        /**
         * Creates a new object with propsArray properties from obj
         *
         * @param {object} obj - the source object
         * @param {string[]} propsArray - array of properties you want in new object
         */
        pick(obj, propsArray) {
            // Make sure object and properties are provided
            if (!obj || !propsArray) return;

            if (obj.pick) return obj.pick(propsArray);

            // Create new object
            const picked = {};

            // Loop through props and push to new object
            propsArray.forEach(prop => picked[prop] = obj[prop]);

            // Return new object
            return picked;
        }

        /**
         * Sort array by string length
         *
         * @since 0.17.2
         *
         * @param {string[]} list string array
         *
         * @returns array sorted by string length
         */
        sortByLength(list) {
            // const buffer = '1'.padEnd(`${list.length}`.length, '0') * 1;
            const buffer = Math.pow(10, `${list.length}`.length);

            const cache = {};
            list.sort().forEach(str => {
                let len = str.length * buffer;
                while (cache.hasOwnProperty(len)) len++;
                cache[len] = str;
            });

            return Object.values(cache);
        }

        /**
         * Returns the longest substring contained in a list of strings
         *
         * @param {string[]} list list of strings to search
         * @param {boolean} showCandidates return object with longest substring and candidates used in process
         *
         * @returns {string|object}
         */
        longestCommonSubstring(list, showCandidates = false) {
            // sort by string length to make the shortest string our base of comparison.
            list = this.sortByLength(list);

            let tw = list.shift();
            let tl = tw.split(``);
            let t = ``;
            let l = ``;
            let lc = [];
            tl.forEach(c => {
                let p = t.concat(c);
                // check that all words have current substring.
                if (!list.some(w => {
                    return w.includes(p) == false;
                })) {
                    // substring found in every word, store it in tmp
                    t = p;
                    // if its longer than the last longest sub, record it for return
                    if (p.length >= l.length) {
                        lc.push(p);
                        l = p;
                    }
                } else t = t.length > 1 ? p[p.length - 1] : ``; // if not every word
            });

            if (showCandidates) return { longest: l, longestCandidates: lc };
            return l;
        }

        /**
         * Throttle Options
         *
         * @typedef {Object} ThrottleOptions
         * @property {boolean} [skipfirst=false] - False: first call is instant, True: first call delayed
         * @property {object} context - the object to use as `this` @since 0.17.2
         */
        /**
         * Throttles and debounces a function
         *
         * @param {Function} func the function to restrict
         * @param {number} limitDelay the milliseconds between calls and delay to wait for last call
         * @param {ThrottleOptions} options extra options for throttle
         *
         * @returns {Function} the restricted function
         */
        throttle(func, limitDelay = 1000, options = {}) {
            this.mergeOptions(options, {
                skipfirst: false,
                context: this,
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
        }

        // const getUrlCache = new WeakMap();

        /**
         * getUrl
         *
         * Uses a promise to get the url contents
         *
         * @example
         * getUrl('http://some.url/to/fetch').then(result=>{
         *  console.log('result', result);
         * }).catch(error=>{
         *  console.log('Error:', error);
         * })
         *
         * @param {string} url the url to request
         * @param {boolean} useCache cache the request and return cached version on repeated calls
         *
         * @returns {Promise}
         */
        getUrl(url, useCache = false) {

            let cache = getCache.find((uc, index) => {
                if (uc.url == url) {
                    uc.index = index;
                    return uc;
                }
            });

            if (cache != undefined) {
                if (useCache) {
                    cache.hits++;

                    return new Promise((resolve, reject) => {
                        resolve(cache.data);
                    });
                }
            } else cache = {
                url: url,
                data: undefined,
                hits: 0,
                index: getCache.length
            };

            return new Promise((resolve, reject) => {
                let req = new XMLHttpRequest();
                req.open('GET', url);
                req.onload = () => {
                    if (req.status == 200) {
                        if (cache.data === undefined) {
                            cache.data = req.response;
                            getCache.push(cache);
                        } else getCache[cache.index] = cache;

                        resolve(req.response);
                    } else {
                        reject(req.statusText);
                    }
                };

                req.onerror = () => {
                    reject('Network Error');
                };

                req.send();
            });
        }

        /**
         * Simple function to add/remove animateCss classes
         *
         * @example: animateCssOnEvent('.arrow-back', 'animate__pulse');
         *
         * @param {string} element
         * @param {string} animation
         * @param {function} callback
         */
        animateCss(element, animation, callback) {
            if (typeof element == 'string') element = document.querySelector(element);

            element.classList.add('animate__animated', animation);

            function handleAnimationEnd(event) {
                element.classList.remove('animate__animated', animation);
                element.removeEventListener('animationend', handleAnimationEnd);

                if (typeof callback === 'function') callback(event);
            }

            element.addEventListener('animationend', handleAnimationEnd);
        }

        /**
         * Simple function to add/remove animateCss classes on a specific event
         *
         * @example: I.animateCssOnEvent('mouseenter', '.arrow-back', 'animate__pulse');
         *
         * @param {string} event
         * @param {string} elementSelector
         * @param {string} animation
         * @param {function} callback
         */
        animateCssOnEvent(event, elementSelector, animation, callback) {
            document.querySelectorAll(elementSelector).forEach(element => {
                element.addEventListener(event, () => {
                    this.animateCss(element, animation, callback);
                }, false);
            });
        }

        /**
         * Get browser name and version data
         *
         * @returns {object} browser info
         */
        browserData() {
            let browserCode, browserName, isMobile, sUsrAg = navigator.userAgent;
            let browserVersion = 0;

            // The order matters here, and this may report false positives for unlisted browsers.
            if (sUsrAg.indexOf('Firefox') > -1) {
                browserCode = 'Firefox'
                browserName = 'Mozilla Firefox';
                browserVersion = sUsrAg.split(browserCode + '/')[1].split(' ')[0];
            } else if (sUsrAg.indexOf('SamsungBrowser') > -1) {
                browserCode = 'SamsungBrowser'
                browserName = 'Samsung Internet';
                browserVersion = sUsrAg.split(browserCode + '/')[1].split(' ')[0];
            } else if (sUsrAg.indexOf('Opera') > -1) {
                browserCode = 'Opera'
                browserName = 'Opera';
                browserVersion = sUsrAg.split(browserCode + '/')[1].split(' ')[0];
            } else if (sUsrAg.indexOf('OPR') > -1) {
                browserCode = 'OPR'
                browserName = 'Opera';
                browserVersion = sUsrAg.split(browserCode + '/')[1].split(' ')[0];
            } else if (sUsrAg.indexOf('Trident') > -1) {
                browserCode = 'Trident'
                browserName = 'Microsoft Internet Explorer';
                browserVersion = sUsrAg.split(browserCode + '/')[1].split(' ')[0];
            } else if (sUsrAg.indexOf('Edg') > -1) {
                browserCode = 'Edg'
                browserName = 'Microsoft Edge';
                browserVersion = sUsrAg.split(browserCode + '/')[1].split(' ')[0];
            } else if (sUsrAg.indexOf('Chrome') > -1) {
                browserCode = 'Chrome'
                browserName = 'Google Chrome or Chromium';
                browserVersion = sUsrAg.split(browserCode + '/')[1].split(' ')[0];
            } else if (sUsrAg.indexOf('Safari') > -1) {
                browserCode = 'Safari'
                browserName = 'Apple Safari';
                browserVersion = sUsrAg.split(browserCode + '/')[1].split(' ')[0];
                if (sUsrAg.indexOf('Version') > -1) browserVersion = sUsrAg.split('Version/')[1].split(' ')[0];
            }

            isMobile = sUsrAg.toLowerCase().includes('mobile');

            return {
                code: browserCode,
                name: browserName,
                version: browserVersion,
                useragent: sUsrAg,
                isMobile: isMobile
            };
        }

        /**
         * Convert Object to URL Query String
         *
         * @param {Object} obj - object to convert
         * @param {string} prefix - uses prefix as an array instead of property name
         *
         * @returns {string} - the url query string
         */
        objectToQuery(obj, prefix) {
            let queryParts = []
            for (let param in obj) {
                if (obj.hasOwnProperty(param)) {
                    let key = prefix ? prefix + "[]" : param;
                    let value = obj[param];

                    queryParts.push(
                        (value !== null && typeof value === "object") ?
                            this.objectToQuery(value, key) :
                            key + "=" + value
                    );
                }
            }

            return queryParts.join("&");
        }

        /**
         * Convert URL Query String to Object
         *
         * @param {string} urlParams - url query string
         *
         * @returns {Object} - JSON object of query params
         */
        queryToObject(urlParams) {
            return urlParams
                .replace(/\[\d?\]=/gi, '=')
                .split('&')
                .reduce((result, param) => {
                    if (param === '') return result;
                    let [key, value] = param.split('=');
                    value = decodeURIComponent(value || '');

                    if (!result.hasOwnProperty(key)) {
                        result[key] = value;
                        return result;
                    }

                    result[key] = [...[].concat(result[key]), value]
                    return result
                }, {});
        }

        /**
         * addFeatureFallback
         *
         * @param {*} obj - object to test for feature
         * @param {string} feature - required function
         * @param {*} fallback - function to emulate feature
         *
         * @returns {I}
         */
        addFeatureFallback(obj, feature, fallback) {
            if (!obj.prototype[feature]) obj.prototype[feature] = fallback;
            return this;
        }

        /**
         * Promise version of setTimeout
         *
         * @example I.onTimeout(10*1000).then(() => saySomething("10 seconds")).catch(failureCallback);
         *
         * @param {number} ms milliseconds to wait
         *
         * @returns {Promise} on timeout
         */
        onTimeout = ms => new Promise(resolve => setTimeout(resolve, ms));

        /**
         * Is Element in Viewport
         *
         * @param {node} element
         *
         * @returns {boolean}
         */
        isElementInView(element) {
            const bounding = element.getBoundingClientRect();
            return (
                bounding.top >= 0 &&
                bounding.left >= 0 &&
                bounding.bottom <= (root.innerHeight || document.documentElement.clientHeight) &&
                bounding.right <= (root.innerWidth || document.documentElement.clientWidth)
            );
        }

        /**
         * Sets the text to a random data property of elements with .datatext class
         */
        DataText() {
            document.querySelectorAll('.datatext').forEach((datatextElement) => {
                let datatextText = datatextElement.textContent;
                do {
                    let datatextSet = datatextElement.dataset;
                    let datatextKeys = _(datatextElement.dataset).allKeys();
                    let datatextKey = _.random(0, datatextKeys.length - 1);
                    datatextText = datatextSet[datatextKeys[datatextKey]];
                } while (datatextElement.textContent == datatextText);
                datatextElement.textContent = datatextText;
            });
        }

        /**
         * uuidv4
         *
         * Creates unique id
         *
         * crypto.getRandomValues(new Uint32Array(4)).join('-')
         *
         * @returns {string}
         */
        uuidv4() {
            return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, c => {
                let r = Math.random() * 16 | 0, v = c == 'x' ? r : (r & 0x3 | 0x8);
                return v.toString(16);
            });
        }

        /**
         * Take a string and return the hex representation of its MD5.
         *
         * @param {string} str
         * @returns {string}
         */
        md5(str) {
            let x = str2blks_MD5(str);
            let a = 1732584193;
            let b = -271733879;
            let c = -1732584194;
            let d = 271733878;

            for (let i = 0; i < x.length; i += 16) {
                let olda = a;
                let oldb = b;
                let oldc = c;
                let oldd = d;

                a = ff(a, b, c, d, x[i + 0], 7, -680876936);
                d = ff(d, a, b, c, x[i + 1], 12, -389564586);
                c = ff(c, d, a, b, x[i + 2], 17, 606105819);
                b = ff(b, c, d, a, x[i + 3], 22, -1044525330);
                a = ff(a, b, c, d, x[i + 4], 7, -176418897);
                d = ff(d, a, b, c, x[i + 5], 12, 1200080426);
                c = ff(c, d, a, b, x[i + 6], 17, -1473231341);
                b = ff(b, c, d, a, x[i + 7], 22, -45705983);
                a = ff(a, b, c, d, x[i + 8], 7, 1770035416);
                d = ff(d, a, b, c, x[i + 9], 12, -1958414417);
                c = ff(c, d, a, b, x[i + 10], 17, -42063);
                b = ff(b, c, d, a, x[i + 11], 22, -1990404162);
                a = ff(a, b, c, d, x[i + 12], 7, 1804603682);
                d = ff(d, a, b, c, x[i + 13], 12, -40341101);
                c = ff(c, d, a, b, x[i + 14], 17, -1502002290);
                b = ff(b, c, d, a, x[i + 15], 22, 1236535329);

                a = gg(a, b, c, d, x[i + 1], 5, -165796510);
                d = gg(d, a, b, c, x[i + 6], 9, -1069501632);
                c = gg(c, d, a, b, x[i + 11], 14, 643717713);
                b = gg(b, c, d, a, x[i + 0], 20, -373897302);
                a = gg(a, b, c, d, x[i + 5], 5, -701558691);
                d = gg(d, a, b, c, x[i + 10], 9, 38016083);
                c = gg(c, d, a, b, x[i + 15], 14, -660478335);
                b = gg(b, c, d, a, x[i + 4], 20, -405537848);
                a = gg(a, b, c, d, x[i + 9], 5, 568446438);
                d = gg(d, a, b, c, x[i + 14], 9, -1019803690);
                c = gg(c, d, a, b, x[i + 3], 14, -187363961);
                b = gg(b, c, d, a, x[i + 8], 20, 1163531501);
                a = gg(a, b, c, d, x[i + 13], 5, -1444681467);
                d = gg(d, a, b, c, x[i + 2], 9, -51403784);
                c = gg(c, d, a, b, x[i + 7], 14, 1735328473);
                b = gg(b, c, d, a, x[i + 12], 20, -1926607734);

                a = hh(a, b, c, d, x[i + 5], 4, -378558);
                d = hh(d, a, b, c, x[i + 8], 11, -2022574463);
                c = hh(c, d, a, b, x[i + 11], 16, 1839030562);
                b = hh(b, c, d, a, x[i + 14], 23, -35309556);
                a = hh(a, b, c, d, x[i + 1], 4, -1530992060);
                d = hh(d, a, b, c, x[i + 4], 11, 1272893353);
                c = hh(c, d, a, b, x[i + 7], 16, -155497632);
                b = hh(b, c, d, a, x[i + 10], 23, -1094730640);
                a = hh(a, b, c, d, x[i + 13], 4, 681279174);
                d = hh(d, a, b, c, x[i + 0], 11, -358537222);
                c = hh(c, d, a, b, x[i + 3], 16, -722521979);
                b = hh(b, c, d, a, x[i + 6], 23, 76029189);
                a = hh(a, b, c, d, x[i + 9], 4, -640364487);
                d = hh(d, a, b, c, x[i + 12], 11, -421815835);
                c = hh(c, d, a, b, x[i + 15], 16, 530742520);
                b = hh(b, c, d, a, x[i + 2], 23, -995338651);

                a = ii(a, b, c, d, x[i + 0], 6, -198630844);
                d = ii(d, a, b, c, x[i + 7], 10, 1126891415);
                c = ii(c, d, a, b, x[i + 14], 15, -1416354905);
                b = ii(b, c, d, a, x[i + 5], 21, -57434055);
                a = ii(a, b, c, d, x[i + 12], 6, 1700485571);
                d = ii(d, a, b, c, x[i + 3], 10, -1894986606);
                c = ii(c, d, a, b, x[i + 10], 15, -1051523);
                b = ii(b, c, d, a, x[i + 1], 21, -2054922799);
                a = ii(a, b, c, d, x[i + 8], 6, 1873313359);
                d = ii(d, a, b, c, x[i + 15], 10, -30611744);
                c = ii(c, d, a, b, x[i + 6], 15, -1560198380);
                b = ii(b, c, d, a, x[i + 13], 21, 1309151649);
                a = ii(a, b, c, d, x[i + 4], 6, -145523070);
                d = ii(d, a, b, c, x[i + 11], 10, -1120210379);
                c = ii(c, d, a, b, x[i + 2], 15, 718787259);
                b = ii(b, c, d, a, x[i + 9], 21, -343485551);

                a = add(a, olda);
                b = add(b, oldb);
                c = add(c, oldc);
                d = add(d, oldd);
            }
            return rhex(a) + rhex(b) + rhex(c) + rhex(d);
        }
    }

    return new I;
});
