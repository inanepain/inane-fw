(function (window) {
    /**
     * Version
     */
    const version = '0.4.1';
    const template = document.createElement('template');

    /**
     * PopUpInfo
     * 
     * @attribute text - the popup text
     * @attribute label - label to use as popup trigger
     * @attribute img - url to img file to use as popup trigger
     * @attribute side - DEFAULT: left, right
     * 
     * PS: label wins over img if both set
     *
    //  * @class PopUpInfo
     * @extends {HTMLElement}
     * @version 0.5.0
     */
    class PopUpInfo extends HTMLElement {
        /**
         * Creates an instance of PopUpInfo.
         * @constructor
         * @memberof PopUpInfo
         */
        constructor() {
            // Always call super first in constructor
            super();

            /**
             * Version
             * @memberof PopUpInfo
             */
            Object.defineProperty(this, 'VERSION', {
                value: version,
                writable: false
            });

            /**
             * Version
             * @memberof PopUpInfo
             * @static
             */
            Object.defineProperty(this.constructor, 'VERSION', {
                value: version,
                writable: false
            });

            // Create a shadow root
            let shadow = this.attachShadow({ mode: 'closed' });

            // Take attribute content and put it inside the info span
            const text = this.getAttribute('text');

            // Insert label
            let trigger;
            let triggerClass = '';
            if (this.hasAttribute('label')) {
                trigger = this.getAttribute('label');
                triggerClass = ' text';
            } else if (this.hasAttribute('img')) {
                trigger = '<img src="'+this.getAttribute('img')+'">';
            } else {
                trigger = '❗️';
            }

            let position = this.hasAttribute('position') ? this.getAttribute('position') : '';
            if (!['left', 'bottom', 'right'].includes(position)) position = '';

            template.innerHTML = `<span class="wrapper"><span class="icon${triggerClass}" tabindex="0">${trigger}</span><span class="info ${position}">${text}</span></span>`;

            // Create some CSS to apply to the shadow dom
            let style = document.createElement('style');
            style.textContent = `.wrapper{position:relative}.info{z-index:3;width:272px;opacity:0;right:calc(272px * -.5 + 25%);top:-40px;display:inline-block;position:absolute;padding:10px;font-size:.8rem;border-radius:10px;border:1px solid #000;background:#fff;color:#000;box-shadow:3px 3px 5px gray;transition:.6s all;white-space:pre-wrap}.info.bottom{top:20px}.info.right{right:calc(272px * -1 - 25%);top:-10px}.info.left{right:calc(272px * 0 + 100%);top:-10px}img{width:1.2rem}.icon{cursor:help}.icon:hover+.info,.icon:focus+.info{opacity:1}`;

            // attach the created elements to the shadow dom
            shadow.appendChild(style);
            shadow.appendChild(template.content.cloneNode(true));
        }
    }

    // Define the new element
    customElements.define('popup-info', PopUpInfo);
}(window));
