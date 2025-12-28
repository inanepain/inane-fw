(function () {
    /**
     * @type {string} The custom tag's name
     */
    const tagName = 'toggle-switch';

    /**
     * Version
     */
    const version = '0.3.0';

    /**
     * @type {Object} defaults
     */
    const _defaults = {
        text: '',
        colour: 'white',
        chkColour: 'colour',
        background: '#CCC',
        chkBackground: '#2196F3',
        outline: 'transparent',
        labelOn: '',
        labelOnColour: 'initial',
        labelOff: '',
        labelOffColour: 'initial',
        labelSwitch: false,
    };

    /**
     * Update Style
     */
    function updateStyle() {
        this.shadow.querySelector('style').textContent = `
:host {
    all: initial;
    contain: content;
}
.switch {
    position: relative;
    display: inline-block;
    height: 2em;
}
.switch input {
    opacity: 0;
    width: 0;
    height: 0;
}
.slider {
    position: absolute;
    cursor: pointer;
    width: ${this.ctrlSize.width};
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: ${this.background};
    box-shadow: 0 0 1px 1px ${this.outline}, 0 0 1px 1px ${this.outline}, 0 0 1px 1px ${this.outline};
    -webkit-transition: .4s;
    transition: .4s;
}
:host-context(.shadow) .slider {
    box-shadow: 1px 1px 4px -1px ${this.outline};
}
.slider:after,
.slider:before {
    position: absolute;
    overflow: hidden;
    line-height: 1.5em;
    text-align: center;
    height: 1.5em;
    width: 1.5em;
    bottom: 0.25em;
    -webkit-transition: .4s;
    transition: .4s;
}
.slider:before {
    left: 0.35em;
    content: "${!this.labelSwitch ? this.labelOff : _defaults.labelOff}";
    color: ${!this.labelSwitch ? this.labelOffColour : _defaults.labelOffColour};
    background-color: ${this.colour};
}
.slider:after {
    right: .35em;
    content: "${this.labelSwitch ? this.labelOn : _defaults.labelOn}";
    color: ${this.labelSwitch ? this.labelOnColour : _defaults.labelOnColour};
    bottom: 0.15em;
    padding: 1px;
}
:host([checked]) .slider {
    background-color: ${this.chkBackground};
}
:host:focus .slider {
    box-shadow: 0 0 1px ${this.chkBackground};
}
:host([checked]) .slider:before {
    content: "${!this.labelSwitch ? this.labelOn : _defaults.labelOn}";
    color: ${!this.labelSwitch ? this.labelOnColour : _defaults.labelOnColour};
    background-color: ${this.chkColour};
    -webkit-transform: translateX(${this.ctrlSize.swap});
    -ms-transform: translateX(${this.ctrlSize.swap});
    transform: translateX(${this.ctrlSize.swap});
}
:host([checked]) .slider:after {
    content: "${this.labelSwitch ? this.labelOff : _defaults.labelOff}";
    color: ${this.labelSwitch ? this.labelOffColour : _defaults.labelOffColour};
    -webkit-transform: translateX(-${this.ctrlSize.swap});
    -ms-transform: translateX(-${this.ctrlSize.swap});
    transform: translateX(-${this.ctrlSize.swap});
}
.slider.round {
    border-radius: 2.125em;
}
.slider.round:before {
    border-radius: 50%;
}
.switch .text {
    right: -100%;
    left: 50%;
    top: 50%;
    font-size: 150%;
    margin: 0;
    cursor: pointer;
    margin-left: ${this.ctrlSize.margin};
    white-space: nowrap;
    -webkit-user-select: none;
    -ms-user-select: none;
    user-select: none;
    transform: translate(50%, -50%);
}
:host(toggle-switch) {
    font-family: inherit;
    padding: 2px;
}`;
    }

    /**
     * Update Text
     */
    function updateText() {
        if (this.textContent != this.text) this.textContent = this.text;
        this.shadow.querySelector('.text').textContent = this.text;
    }

    function upgradeProperty(prop) {
        if (this.hasOwnProperty(prop)) {
            let value = this[prop];
            delete this[prop];
            this[prop] = value;
        }
    }

    /**
     * mutationObserver
     */
    const mutationObserver = new MutationObserver(mutations => {
        mutations.forEach(mutation => {
            if (mutation.type == `childList`) {
                console.log(`MATCH:mutationObserver`, mutation);
                mutation.addedNodes[0].parentNode.setAttribute('text', Array.from(mutation.addedNodes).map(el => el.textContent).join());
            }
            // console.log(mutation);
        });
    });

    /**
     * ToggleSwitch
     *
     * @attribute size - font size
     *
     * PS: label wins over img if both set
     *
     * @extends {HTMLElement}
     * @version 0.2.0
     */
    class ToggleSwitch extends HTMLElement {
        /**
         * Attributes that trigger an update
         *
         * @static
         * @property
         * @readonly
         * @returns {String[]}
         */
        static get observedAttributes() {
            return ['checked', 'round', 'colour', 'disabled', 'chkColour', 'background', 'chkbackground', 'outline', 'text', `label-on`, 'label-on-colour', `label-off`, 'label-off-colour', 'label-switch'];
        }

        /**
         * Creates an instance of ToggleSwitch
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

            const label = document.createElement('label');
            const input = document.createElement('input');
            const span = document.createElement('span');
            const text = document.createElement('span');
            const style = document.createElement('style');
            input.type = 'checked';
            label.classList.add('switch');
            span.classList.add('slider');
            text.classList.add('text');

            label.appendChild(input);
            label.appendChild(span);
            label.appendChild(text);

            shadow.appendChild(style);
            shadow.appendChild(label);
        }

        /**
         * colour<br/>
         *
         * colour may specify both unchecked and checked colours by using a comma `,`
         * in this case it chkcolour is not used
         *
         * @property
         * @readonly
         * @returns {string}
         */
        get colour() {
            let colour = this.getAttribute('colour') || _defaults.colour;
            if (colour.includes(`,`)) return colour.split(`,`).map(col => col = col.trim()).shift();
            return colour;
        }

        /**
         * chkColour<br/>
         *
         * if colour uses a comma `,` to set unchecked and checked colours
         * chkColour is not used
         *
         * @property
         * @readonly
         * @returns {string}
         */
        get chkColour() {
            let colour = this.getAttribute('colour') || _defaults.colour;
            if (colour.includes(`,`)) return colour.split(`,`).map(col => col = col.trim()).pop();

            colour = this.getAttribute('chkcolour') || _defaults.chkColour;
            if (colour == 'colour') colour = this.colour;
            return colour;
        }

        /**
         * background<br/>
         *
         * background may specify both unchecked and checked colours by using a comma `,`
         * in this case it chkbackground is not used
         *
         * @property
         * @readonly
         * @returns {string}
         */
        get background() {
            let colour = this.getAttribute('background') || _defaults.background;
            if (colour.includes(`,`)) return colour.split(`,`).map(col => col = col.trim()).shift();
            return colour;
        }

        /**
         * chkBackground<br/>
         *
         * if background uses a comma `,` to set unchecked and checked colours
         * chkBackground is not used
         *
         * @property
         * @readonly
         * @returns {string}
         */
        get chkBackground() {
            let colour = this.getAttribute('background') || _defaults.background;
            if (colour.includes(`,`)) return colour.split(`,`).map(col => col = col.trim()).pop();

            return this.getAttribute('chkbackground') || _defaults.chkBackground;
        }

        /**
         * outline colour
         *
         * @property
         * @readonly
         * @returns {string}
         */
        get outline() {
            return this.getAttribute('outline') || _defaults.outline;
        }

        /**
         * text description right of toggle
         *
         * @property
         * @readonly
         * @returns {string}
         */
        get text() {
            return this.getAttribute('text') || this.textContent || _defaults.text;
        }

        /**
         * state on: label text
         *
         * @property
         * @readonly
         * @returns {string}
         */
        get labelOn() {
            return this.getAttribute('label-on') || _defaults.labelOn;
        }

        /**
         * state on: label colour
         *
         * @property
         * @readonly
         * @returns {string}
         */
        get labelOnColour() {
            return this.getAttribute('label-on-colour') || _defaults.labelOnColour;
        }

        /**
         * state off: label text
         *
         * @property
         * @readonly
         * @returns {string}
         */
        get labelOff() {
            return this.getAttribute('label-off') || _defaults.labelOff;
        }

        /**
         * state off: label colour
         *
         * @property
         * @readonly
         * @returns {string}
         */
        get labelOffColour() {
            return this.getAttribute('label-off-colour') || _defaults.labelOffColour;
        }

        /**
         * label-switch: label switch on/off position<br/>
         *
         * Default: false - label shows current state on the slider<br/>
         * true - labels shows on/off position
         *
         * @property
         * @readonly
         * @returns {boolean}
         */
        get labelSwitch() {
            return this.hasAttribute('label-switch');
        }

        /**
         * disabled state
         *
         * @property
         * @type {boolean}
         */
        get disabled() {
            return this.hasAttribute('disabled');
        }

        set disabled(value) {
            if (Boolean(value)) this.setAttribute('disabled', '');
            else this.removeAttribute('disabled');
        }

        /**
         * Checked state
         *
         * @property
         * @type {boolean}
         */
        get checked() {
            return this.hasAttribute('checked');
        }

        set checked(value) {
            if (Boolean(value)) this.setAttribute('checked', '');
            else this.removeAttribute('checked');
        }

        get ctrlSize() {
            if (true) {
                return {
                    width: '3.75em',
                    margin: '3em',
                    swap: '1.625em',
                };
            } else {
                return {
                    width: '4.5em',
                    swap: '2.5em',
                };
            }
        }

        /**
         * Toggled the checked value
         *
         * @fires ToggleSwitch#change
         *
         * @return {boolean} the checked state
         */
        toggle() {
            if (this.disabled) return;

            this.checked = !this.checked;
            /**
             * Change event<br/>
             *
             * Fired when the toggle checked status changes.
             *
             * @event ToggleSwitch#change
             *
             * @type {object}
             * @property {boolean} checked - Indicates whether toggle is checked or not.
             */
            this.dispatchEvent(new CustomEvent('change', {
                detail: {
                    checked: this.checked,
                },
                bubbles: true,
            }));

            return this.checked;
        }

        /**
         * Custom element added to page
         */
        connectedCallback() {
            window.document.head.querySelector(`#${tagName}-preloader`)?.remove();

            if (this.hasAttribute('round')) this.shadow.querySelector('.slider').classList.add('round');

            updateStyle.apply(this);
            updateText.apply(this);
            upgradeProperty.apply(this, ['checked']);
            upgradeProperty.apply(this, ['disabled']);

            this.addEventListener('click', event => {
                event.preventDefault();
                this.toggle();
            });

            mutationObserver.observe(this, {
                attributes: true,
                characterData: true,
                childList: true,
                subtree: false,
                attributeOldValue: false,
                characterDataOldValue: false
            });
        }

        /**
         * Custom element removed from page
         */
        disconnectedCallback() {
        }

        /**
         * Custom element moved to new page
         */
        adoptedCallback() {
        }

        /**
         * Attribute changed
         */
        attributeChangedCallback(name, oldValue, newValue) {
            const hasValue = newValue !== null;
            switch (name) {
                case 'checked':
                    this.setAttribute('aria-checked', hasValue);
                    break;
                case 'round':
                    if (hasValue) this.shadow.querySelector('.slider').classList.add('round');
                    else this.shadow.querySelector('.slider').classList.remove('round');
                    break;
                case 'text':
                    updateText.apply(this);
                    break;
                default:
                    updateStyle.apply(this);
            }
        }
    }

    /**
     * Pre-loader: Hides the tag untill is defined
     */
    (() => {
        const styleHide = document.createElement('style');
        styleHide.id = `${tagName}-preloader`;
        styleHide.textContent = `${tagName}:not(:defined){visibility:hidden;}`;
        window.document.head.appendChild(styleHide);
    })();

    // Define the new element
    customElements.define(tagName, ToggleSwitch);
})();
