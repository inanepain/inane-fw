/**
 * iTemplate
 *
 * Simple JavaScript Templating
 * @see https://git.inane.co.za:3000/Inane/inane-js/wiki/Inane_icRoot-iTemplate
 *
 * _icroot.iHelper.importModule(_icroot.iHelper.icModules.iTemplate).then(iTemplate=>window.iTemplate=iTemplate)
 *
 * @author Philip Michael Raab <philip@cathedral.co.za>
 */

/**
 * Version
 *
 * @constant
 * @type {String}
 * @memberof iTemplate
 */
const VERSION = '0.6.1';

/**
* moduleName
*
* @constant
* @type {String}
*/
const moduleName = 'iTemplate.js';

if (window.Dumper) Dumper.dump('MODULE', moduleName.concat(' v').concat(VERSION), 'LOAD');

/**
 * Template function cache
 *
 * @constant
 * @type {Object}
 * @memberof iTemplate
 */
const cache = {};

/**
 * iTemplate
 *
 * @param {String} tpl the template string or template id
 * @param {Object} data the object used to fill the template
 *
 * @returns {Function|String}
 *
 * @version 0.6.1
 * @class iTemplate
 */
const iTemplate = function (tpl, data) {
    // function iTemplate(tpl, data) {
    // Figure out if we're getting a template, or if we need to
    // load the template - and be sure to cache the result.
    let fn = !/\W/.test(tpl) ?
        cache[tpl] = cache[tpl] ||
        iTemplate(document.getElementById(tpl).innerHTML) :

        // Generate a reusable function that will serve as a template
        // generator (and which will be cached).
        new Function("obj={}",
            "let p=[],print=function(){p.push.apply(p,arguments);};" +

            // Introduce the data as local variables using with(){}
            "with(obj){p.push('" +

            // Convert the template into pure JavaScript
            tpl
                .replace(/[\r\t\n]/g, " ")
                .split("<%").join("\t")
                .replace(/((^|%>)[^\t]*)'/g, "$1\r")
                .replace(/\t=(.*?)%>/g, "',$1,'")
                .split("\t").join("');")
                .split("%>").join("p.push('")
                .split("\r").join("\\'")
            + "');}return p.join('');");

    // Provide some basic currying to the user
    return data ? fn(data) : fn;
};

// Set the version number
Object.defineProperty(iTemplate, 'VERSION', {
    value: VERSION,
    writable: false
});

export default iTemplate;
