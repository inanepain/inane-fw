/**
 * Safari debug inspector BootScript
 *
 * For all your debug fun & site automation.
 */
((root, _log, level) => {
    /**
     * @var {String} id unique id for module
     */
    const id = `za.co.inane.inspector.bootstrap`;

    /**
     * @var {number} VERSION interceptor bootstrap version
     */
    const VERSION = 26,
        logLevel = level.TRACE,
        enableAutomation = true,
        enableAugmentation = true,
        enableRemoteScripts = true;

    /*
    VERSIONS:
    25 (2021 Nov 15)
        - parseLocation: parameter made optional by defaulting to current location
    24 (2021 Nov 15)
        - Added Automation disabled property. Disables that levels automations ONLY
    23 (2021 Nov 3)
        - Added keyboard shortcuts for Torn
    22 (2021 Oct 31)
        - Torn links active style
        - added more custom links
    21 (2021 Oct 30)
        - Added torn customisations
    18 (2021 Oct 21)
        - Renaming of variables to make the names self explanatory
        - Minor cleanup and fixes
    19 (> 2021 Aug 31)
        - Unsure of changes
    18 (2021 Aug 31)
        - YTS: Scroll bottom navigation panel into view (Shift + Ctrl + D)
    */

    /**
     * @var {Object} msgThemes Colour themes for debug messages
     */
    const msgThemes = {
        system: [`LavenderBlush`, `Crimson`],
        first: [`red`, `cornflowerblue`],
        second: [`Red`, `AliceBlue`],
        third: [`MediumPurple`, `AliceBlue`],
    };

    /**
     * Torn Extra
     */
    const extraTorn = {
        highlight: ['City', 'Crimes', 'Missions'],
        data: [
            { id: 'inane', title: 'Inane', icon: 'https://inane.co.za/img/infinite.png', url: 'header'},
            { id: 'bob', title: 'Attack: Bob', icon: '💥', key: 'b', url: 'https://www.torn.com/page.php?sid=UserList&playername=bob'},
            { id: 'roulette', title: 'Casino: Roulette', icon: '🎰', key: 'r', url: 'https://www.torn.com/loader.php?sid=roulette'},
            { id: 'wheel', title: 'Casino: Wheel', icon: '🎰', url: 'https://www.torn.com/loader.php?sid=spinTheWheel'},
            { id: 'upkeep', title: 'Property: Upkeep', icon: '🏠', key: 'u', url: 'https://www.torn.com/properties.php#/p=options&ID=4010889&tab=upkeep'},
            // { title: 'Property: Upkeep', icon: '🏠', url: 'https://www.torn.com/properties.php#/p=options&ID=3992695&tab=upkeep'},
            { id: 'market', title: 'City: Item Market', icon: '🏙', key: 'm', url: 'https://www.torn.com/imarket.php'},
            { id: 'church', title: 'City: Church', icon: '⛪️', url: 'https://www.torn.com/church.php'},
            { id: 'armoury', title: 'Faction: Armoury', icon: '⚔️', url: 'https://www.torn.com/factions.php?step=your#/tab=armoury'},
        ],
        createLink: ({ id, url, icon = '🔗', hint, key, title = 'Link' }) => {
            let el;
            if (url == 'header') {
                el = document.createElement('div');
                title = `<span><img src="${icon}" /></span> ${title}`;
            } else {
                el = document.createElement('a');
                el.href = url;
                title = `<span>${icon}</span> ${title}`;

                if (url.toUpperCase() == window.location.href.toUpperCase()) el.classList.add('inane-active');
            }

            if (key) {
                el.accessKey = key;
                el.title = `CTRL + OPT + ${key}`;

                title += `<kbd>${key}</kbd>`;
            }

            el.id = `inane-${id}`;
            el.innerHTML = title;

            return el;
        },
        addStyle: () => {
            if (!iq('#inane-style')) {
                const style = document.createElement('style');
                style.id = 'inane-style';

                style.textContent = `
.inane-active {
    font-weight: bold!important;
    background: lavender!important;
}

#inane-quick-links div {
    -webkit-user-select: none;
    background: purple;
    border-bottom-right-radius: 5px;
    border-top-right-radius: 5px;
    color: var(--sidebar-titles-font-color);
    display: flex;
    font-size: 12px;
    font-weight: 700;
    padding-left: 5px;
    text-shadow: 0 1px 0 #333;
    line-height: 24px;
}

#inane-quick-links a {
    -webkit-user-select: none;
    background: var(--default-bg-panel-color);
    border-bottom-right-radius: 5px;
    border-top-right-radius: 5px;
    color: var(--default-color);
    cursor: pointer;
    display: flex;
    line-height: 23px;
    margin: 1px 0px;
    padding-left: 10px;
    padding-top: 1px;
    text-decoration: none;
    font-weight: 400;
    transition: background .3s;
}

#inane-quick-links a:hover {
    background: white;
    font-weight: 600;
}

#inane-quick-links a span {
    margin-right: 10px;
}

#inane-quick-links a kbd {
    margin-left: auto;
    font-weight: bold;
    width: 15px;
    text-align: center;
}
`;

                if (!iq('#inane-style'))
                    iq('head')[0].appendChild(style);
            }
        },
    };

    /**
     * Object with custom actions for host & page combinations
     */
    const AutomationConfiguration = {
        action: function () {
            /**
             * Augment Prototypes if enabled
             */
            if (enableAugmentation) augmentPrototypes();

            /**
             * Add Inane Scripts if enabled
             */
            if (enableRemoteScripts) setupRemoteScripts();

        },
        local: {
            digitalcabinet: {
                'mailbox.php': {
                    action: function () {
                        dump(`AUTOMATION`, `Sharehub`, `mailboxphp`);
                        iqs(`input[name="email"]`).value = `jse@local`;
                        iqs(`input[name="password"]`).value = `password`;
                    },
                },
                digitalcabinet: {
                    'login.php': {
                        action: function () {
                            dump(`AUTOMATION`, `digitalcabinet`, `loginphp`);
                            iqs('input[name="email"]').value = 'dcadmin@claremart.co.za';
                            iqs('input[name="password"]').value = 'aP@ssword1';
                        }
                    },
                },
            },
            raid: {
                action: function () {
                    const data = document.querySelector('#primary article .entry-content');
                    if (data) {
                        document.body.replaceChildren(data);
                        let videos = headings = document.evaluate("//h2[contains(., ' Videos')]", document, null, XPathResult.ANY_TYPE, null).iterateNext();
                        if (videos != null) {
                            while (videos?.nextElementSibling?.tagName == 'P') videos.nextElementSibling.remove();
                            videos.remove();
                        }
                    }
                },
            },
        },
        za: {
            co: {

            },
        },
        mx: {
            yts: {
                action: function () {
                    _i.irs('ishortcut').then(iShortcut => iShortcut.add(`shift + ctrl + b`, () => iqs(`a[href="https://yts.mx/browse-movies"]`).click(), `Browse Movies`));
                },
                'browse-movies': {
                    action: function () {
                        _i.irs('ishortcut').then(iShortcut => iShortcut.add(`shift + ctrl + n`, () => Array.from(iqsa(`li a`)).filter(a => a.innerText == "Next »").pop().click(), `Next Page`)
                            .add(`shift + ctrl + d`, () => Array.from(iqsa(`li a`)).filter(a => a.innerText == "Next »")[2].scrollIntoView(), `Page Down`));
                    },
                }
            }
        },
        tv: {
            trakt: {
                shows: {
                    action: function () {
                        function ratePage() {
                            const delay = 1000;
                            const rating = prompt(`Rating: `, 7) || 5;
                            // const episodes = document.querySelectorAll('.percentage');
                            const episodes = iqsa('.percentage');

                            episodes.forEach(episode => {
                                // if (episode.parentNode.parentNode.parentNode.querySelector('.corner-rating .text') == null) episode.click();
                                if (episode.parentNode.parentNode.parentNode.iqs('.corner-rating .text') == null) episode.click();
                            });

                            setTimeout(() => {
                                // document.querySelectorAll('label.rating-'.concat(rating)).forEach(label => label.click());
                                iqsa('label.rating-'.concat(rating)).forEach(label => label.click());
                            }, delay);
                        }
                        _i.irs('ishortcut').then(iShortcut => {
                            iShortcut.add(`shift + ctrl + r`, ratePage, 'Rate Page');
                        });
                    },
                }
            }
        },
        com: {
            torn: {
                www: {
                    disabled: true,
                    action: function () {
                        const areas = iq('.areasWrapper')[0]?.parentElement?.parentNode;
                        if (areas) {
                            extraTorn.addStyle();

                            const div = document.createElement('div');
                            div.id = 'inane-quick-links';

                            for (let i = 0; i < extraTorn.data.length; i++) div.appendChild(extraTorn.createLink(extraTorn.data[i]));

                            areas.prepend(div);

                            for (let i = 0; i < extraTorn.highlight.length; i++) {
                                let search = "//span[contains(., '" + extraTorn.highlight[i] + "')]";
                                let el = document.evaluate(search, document, null, XPathResult.ANY_TYPE, null)?.iterateNext();
                                if (el) el.parentElement.style = 'color: violet;font-weight:bolder;';
                            }

                            // _i.irs('ishortcut').then(iShortcut => {
                            //     iShortcut.add(`shift + ctrl + a`, iq('#btn-inane-bob')?.click(), 'Attack: Bob');
                            // });
                        }
                    },
                }
            }
        }
    }

    /**
     * Uses the location to build the config path
     *
     * @param {Location} loc
     *
     * @returns {Array} location array
     */
    const parseLocation = loc => (loc || window.location).host.replace(/:[0-9]+/, ``).split(`.`).reverse().concat((loc || window.location).pathname.replace(/^\//, ``).replace(/\/$/, ``).split(`/`));

    const irsModules = {
        dumper: { name: `dumper`, description: `logging`, url: `https://inane.co.za/js/inane/dumper.js`, depends: [], module: null },
        underscore: { name: `underscore`, description: `utility functions`, url: `https://inane.co.za/lib/underscore/1.13.1/underscore-esm.js`, depends: [], module: null },
        backbone: { name: `backbone`, description: `rest, collections & models`, url: `https://inane.co.za/lib/backbone/1.4.0/backbone.js`, depends: [`underscore`], module: null },
        ishortcut: { name: `ishortcut`, description: `keyboard bindings`, url: `https://inane.co.za/js/inane/icroot/iShortcut.js`, depends: [], module: null },
        itemplate: { name: `itemplate`, description: `keyboard bindings`, url: `https://inane.co.za/js/inane/icroot/iTemplate.js`, depends: [], module: null },
        jmespath: { name: `jmespath`, description: `json path`, url: `https://inane.co.za/lib/jmespath/0.15.0/jmespath.js`, depends: [], module: null },
        ajv2019: { name: `ajv2019`, description: `json schema`, url: `https://inane.co.za/lib/ajv/7.1.1/ajv2019.bundle.js`, depends: [], module: null },
        extarray: { name: `extarray`, description: `extend array`, url: `https://inane.co.za/js/inane/extend/array.js`, depends: [], module: null },
        extdate: { name: `extdate`, description: `extend date`, url: `https://inane.co.za/js/inane/extend/date.js`, depends: [], module: null },
        exthtml: { name: `exthtml`, description: `extend html`, url: `https://inane.co.za/js/inane/extend/html.js`, depends: [], module: null },
        extnumber: { name: `extnumber`, description: `extend number`, url: `https://inane.co.za/js/inane/extend/number.js`, depends: [], module: null },
        extobject: { name: `extobject`, description: `extend object`, url: `https://inane.co.za/js/inane/extend/object.js`, depends: [], module: null },
        extstring: { name: `extstring`, description: `extend string`, url: `https://inane.co.za/js/inane/extend/string.js`, depends: [], module: null },
    };

    /**
     * Load script from remote
     *
     * @param {String} irsName name to check for load state
     * @param {String} url url to scriipt file
     *
     * @returns {Promise}
     */
    function inaneRemoteScript(irsName, url) {
        const name = `iRemoteScript`,
            root = window,
            logger = root?.Dumper?.get(name, { level: `debug` }) || console;

        const mdl = irsModules[irsName] || {
            name: irsName,
            description: irsName,
            url: url,
            depends: [],
            module: null,
            default: null,
        };

        if (!mdl.name || !mdl.url) return logger.log(`Some options:`, Object.keys(irsModules), irsModules);
        if (!irsModules[mdl.name]) irsModules[mdl.name] = mdl;

        // If module already loaded, we return it
        if (mdl.module) {
            logger.info(mdl.name, `Returned from cache: ${mdl.name}`);
            return new Promise(module => module(mdl.module));
        }

        return new Promise(resolve => {
            if (mdl.module) {
                logger.info(mdl.name, `Returned from cache (catch two): ${mdl.name}`);
                resolve(mdl.module);
            } else {
                import(mdl.url).then((module) => {
                    if (module.default) mdl.default = module.default;
                    mdl.module = module;

                    logger.debug(name, 'Loaded: '.concat(mdl.name));

                    // And finally bring it all back
                    resolve(mdl.module);
                });
            }
        });
    }

    /**
     * Loading of remote scripts
     */
    function setupRemoteScripts() {
        if (!root._i) root._i = {};

        if (!root._i.irs) root._i.irs = inaneRemoteScript;

        if (!root._i.m) root._i.m = irsModules;
    }

    /**
     * Prototype enhancements
     */
    function augmentPrototypes() {
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

        if (!root.iqs) root.iqs = HTMLElement.prototype.iqs;
        if (!root.document.iqs) root.document.iqs = HTMLElement.prototype.iqs;

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
                let result;
                if (this && this.querySelectorAll) result = this.querySelectorAll(selectors);
                else result = root.document.querySelectorAll(selectors);

                return Array.from(result);
            }
        }


        if (!root.iqsa) root.iqsa = HTMLElement.prototype.iqsa;
        if (!root.document.iqsa) root.document.iqsa = HTMLElement.prototype.iqsa;

        /**
         * Add iq to HTMLElement prototype
         */
        if (!HTMLElement.prototype.iq) {
            /**
             * iq<br />
             * Shorter version of QuerySelector(All)
             *
             * @param {string} selectors
             */
            HTMLElement.prototype.iq = function (selectors) {
                let cmd = 'iqsa';
                if (selectors.charAt(0) === "#") cmd = 'iqs';

                if (this && this[cmd]) return this[cmd](selectors);
                return root.document[cmd](selectors);
            }
        }

        if (!root.iq) root.iq = HTMLElement.prototype.iq;
        if (!root.document.iq) root.document.iq = HTMLElement.prototype.iq;
    }

    /**
     * Create css for logging output
     *
     * @param {string} colour text colour
     * @param {string} background background colour
     */
    const createStyle = (colour = `unset`, background = `unset`) => `color: ${colour}; font-weight: 500; background: ${background};padding: 2px; border-radius:5px;`;

    /**
     * Logging method
     *
     * @param  {...any} msgs log message(s)
     */
    function dump(...msgs) {
        const messageCount = msgs.length;
        let msg1, msg2, msgType = 4;

        if (msgs[0] === id) {
            msgs.shift();
            msgType = 1;
        } else if (typeof msgs[0] == `string` && msgs[0].toUpperCase().startsWith(`MODULE`)) msgType = 2;

        msg1 = `%cDUMP:${VERSION}%c %c${msgs.shift()}`;
        if (messageCount > 1 && typeof msgs[0] == `string`) {
            msg2 = `${msg1}%c %c${msgs.shift()}`;
            msgs.unshift(createStyle(`Blue`, `Azure`));
            msgs.unshift(createStyle());
        }

        if (msgType == 2) msgs.unshift(createStyle.apply(null, msgThemes.third));
        else msgs.unshift(createStyle.apply(null, msgThemes.second));

        msgs.unshift(createStyle());
        if (msgType == 1) msgs.unshift(createStyle.apply(null, msgThemes.system));
        else msgs.unshift(createStyle.apply(null, msgThemes.first));

        if (msg2) msgs.unshift(msg2);
        else msgs.unshift(msg1);

        return _log.log.apply(_log, msgs);
    }

    if (logLevel !== level.OFF) {
        root.debug = true;
        root.logLevel = logLevel;
        root.dump = dump;
        _log.dump = dump;
    }

    /**
     * Matches the current url against the automation configuration looking for actions to run
     *
     * @param {Location} loc
     */
    function dispatchAutomatons(loc) {
        let path = parseLocation(loc);
        let step = path.shift();
        let c = AutomationConfiguration;
        while (c) {
            if (c?.disabled !== true) c?.action?.();
            c = c[step];
            step = path.shift();
        }
    }

    /**
     * Listen for when dom loaded
     */
    root.document.addEventListener(`readystatechange`, event => {
        switch (event.target.readyState) {
            case `loading`:
                dump(`Inspector:Bootstrap:${VERSION}`, `${enableAugmentation ? 'augmented:' : ''}${enableRemoteScripts ? 'InaneScripts:' : ''}document:state`, `LOADING`);
                break;
            case `interactive`:
                dump(id, `Inspector:Bootstrap:${VERSION}`, `${enableAugmentation ? 'augmented:' : ''}${enableRemoteScripts ? 'InaneScripts:' : ''}document:state`, `INTERACTIVE`);
                if (enableAutomation) dispatchAutomatons(root.location);
                break;
            case `complete`:
                dump(id, `Inspector:Bootstrap:${VERSION}`, `${enableAugmentation ? 'augmented:' : ''}${enableRemoteScripts ? 'InaneScripts:' : ''}document:state`, `READY`);
                break;
        }
    }, false);

    if (enableAutomation && window.navigator.userAgent.includes('Chrome')) dispatchAutomatons(root.location);
})(window, console, {
    OFF: `OFF`,
    TRACE: `TRACE`,
    DEBUG: `DEBUG`,
    INFO: `INFO`,
    TIME: `TIME`,
    WARN: `WARN`,
    ERROR: `ERROR`
});
