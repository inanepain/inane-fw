(function(root){
    /**
     * versionMixin
     * @param {*} Base 
     */
    let versionMixin = Base => class extends Base {
        /**
         * Version
         *
         * @readonly
         * @static
         * @memberof versionMixin
         */
        static get VERSION() {
            return VERSION;
        }
    
        /**
         * Version
         *
         * @readonly
         * @memberof versionMixin
         */
        get VERSION() {
            return VERSION;
        }
    };

    if (typeof root['icBase'] === 'undefined') root.icBase = {};
    root.icBase.versionMixin = versionMixin;
}(window));