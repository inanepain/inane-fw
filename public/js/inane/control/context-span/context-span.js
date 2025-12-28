import { Dumper } from '../../dumper.js';

(function (options) {
    /**
     * @type {string} The custom tag's name
     */
    const tagName = 'context-span';

    /**
     * Version
     */
    const version = '0.1.0';

    /**
     * see: inane's config.js
     */
    const GlobalConfig = window?._icroot?.config?.ContextSpan || {};
    const DumperOptions = {};
    if (window.location.hostname.split('.').pop() == 'local') DumperOptions['level'] = 'INFO';
    if (GlobalConfig?.dumper?.level) DumperOptions['level'] = GlobalConfig?.dumper?.level;

    /**
     * ContextSpan
     *
     * PS: label wins over img if both set
     *
     * @extends {HTMLElement}
     * @version 0.1.0
     */
    class ContextSpan extends HTMLElement {
        /**
         * Attributes that trigger an update
         *
         * @static
         * @property
         * @readonly
         * @returns {String[]}
         */
        // static get observedAttributes() {
        //     return [];
        // }

        #logger;

        /**
         * Creates an instance of QuickHeader
         * @constructor
         */
        constructor() {
            // Always call super first in constructor
            super();

            /**
             * Version
             */
            Object.defineProperty(this, 'VERSION', {
                value: version,
                writable: false
            });

            /**
             * Version
             * @static
             */
            Object.defineProperty(this.constructor, 'VERSION', {
                value: version,
                writable: false
            });

            // Create a shadow root
            let shadow = this.attachShadow({ mode: 'closed' });
            this.shadow = shadow;

            const style = document.createElement('style');
            const span = document.createElement('span');

            span.textContent = this.textContent;

            const styles = {
                original: '',
                default: '',
                playground: '',
            };

            styles.original = `
:host {
    all: initial;
    font-family: inherit;
    contain: content;
}

span:hover {
    text-decoration: underline;
}
:host-context(h1) {
    font-style: italic;
}
:host-context(h1):after {
    content: " - no links in headers!"
}
:host-context(article, aside) {
    color: gray;
}
:host(.footer) {
    color : red;
}
:host {
    background: rgba(0,0,0,0.1);
    padding: 2px 5px;
}
`;

            styles.default = `
:host {
    all: initial;
    font-family: inherit;
    contain: content;
}

span:hover {
    text-decoration: underline;
    color: purple;
}
:host-context(h1) {
    font-style: italic;
    font-size: 24px;
}
:host-context(h1):after {
    display: block;
    content: " - no links in headers!";
    width: 100px;
}
:host(.footer1) {
    all:inherit;
}
:host(.footer) {
    color : red;
}
:host-context(div) {
    background: rgba(0,0,0,0.1);
    padding: 2px 5px;
}
:host(.theme) {
    background: white;
    padding: 2px 5px;
    color: pink;
}
`;

            style.playground = `
:host {
    all: initial;
    font-family: inherit;
    contain: content;
}
:host(.footer) {
    color : red;
}

:host-context(article) span {
    font-weight: light;
    color: red;
    font-size: 48px;
    text-shadow: 10px 10px 10px pink solid;
    background: pink;
}
:host-context(.theme) {
    font-weight: bold;
    color: green;
    font-size: 24px;
    text-shadow: 10px 10px 10px pink solid;
    background: yellow;
}
`;

            style.textContent = styles.default;

            shadow.appendChild(style);
            shadow.appendChild(span);

            this.#logger = Dumper.get(this.constructor.name, DumperOptions);
            this.#logger.info('version', version);

            this.watch('textContent', (data) => {
                // {property: "textContent", value: "xxx", previous: "ggg"}
                // this.setAttribute('text', data.value);
                this.#logger.debug('textContent', data, this.textContent);
                this.updateText();
            });
        }

        /**
         * Custom element added to page
         */
        connectedCallback() {
            window.document.head.querySelector(`#${tagName}-preloader`)?.remove();

            this.updateText();
        }

        /**
         * Custom element removed from page
         */
        disconnectedCallback() {
            // this.#logger.debug('disconnectedCallback');
        }

        /**
         * Custom element moved to new page
         */
        adoptedCallback() {
            // this.#logger.debug('adoptedCallback');
        }

        /**
         * Attribute changed
         */
        attributeChangedCallback(name, oldValue, newValue) {
            // this.#logger.debug('attributeChangedCallback', name, oldValue, newValue);
            // if (name == 'text') this.updateText();
            // else this.updateStyle();
        }

        /**
         * Update Text
         */
        updateText() {
            this.#logger.debug('updateText', {
                'thisTextContent': this.textContent,
                'shadowSpanTextContent': this.shadow.querySelector('span').textContent,
            });
            this.shadow.querySelector('span').textContent = this.textContent;
        }
    }

    /**
     * Pre-loader: Hides the tag until is defined
     */
    if (GlobalConfig?.preloader !== false && (options?.preloader === true || GlobalConfig?.preloader == true)) (() => {
        const styleHide = document.createElement('style');
        styleHide.id = `${tagName}-preloader`;
        styleHide.textContent = `${tagName}:not(:defined){visibility:hidden;}`;
        window.document.head.appendChild(styleHide);
    })();

    // Define the new element
    customElements.define(tagName, ContextSpan);
})({
    preloader: true,
});
