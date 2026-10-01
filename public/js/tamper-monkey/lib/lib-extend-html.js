// ==UserScript==
// @name         lib-extend-html
// @namespace    https://www.cathedral.co.za/tm/lib/extend/html
// @version      1790882849
// @description  Extends HTML objects with useful methods.
// @author       Philip Michael Raab<philip@cathedral.co.za>
// @match        *://*/*
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
 * Extends HTML objects with useful methods.
 *
 * @date 01 October 2026 09:27:29 PM SAST
 *
 * Execution order: 1
 */

//#region Libraries Extensions
((window) => {
    for(let element of[HTMLCollection,NodeList]){if(!element.prototype.toArray){element.prototype.toArray=function(){return Array.from(this)}}}
    for(let element of[Document,HTMLElement,ShadowRoot,HTMLDocument]){if(!element.prototype.iqs){element.prototype.iqs=function(selectors){const el=this?.querySelector?this:window.document;return el.querySelector(selectors)}}
    if(!window.iqs&&window.document.iqs)window.iqs=window.document.iqs;if(!element.prototype.iqsa){element.prototype.iqsa=function(selectors){const el=this?.querySelectorAll?this:window.document;return Array.from(el.querySelectorAll(selectors))}}
    if(!window.iqsa&&window.document.iqsa)window.iqsa=window.document.iqsa;if(!element.prototype.iq){element.prototype.iq=function(selectors){const dynamic=selectors.startsWith('@@');if(dynamic)selectors=selectors.substring(2);const cmd=selectors.startsWith('@')||selectors.split(' ').pop().charAt(0)==="#"&&!selectors.includes(',')?'querySelector':'querySelectorAll';if(selectors.startsWith('@'))selectors=selectors.substring(1);const el=this?.[cmd]?this:window.document;result=el[cmd](selectors);result=cmd==='querySelector'?result:Array.from(result);if(dynamic)return Array.isArray(result)?(result.length===1?result.pop():result):result;return result}}
    if(!window.iq&&window.document.iq)window.iq=window.document.iq}
})(unsafeWindow);
