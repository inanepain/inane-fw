/**
 * Custom Element: InaneTextSet
 * 
 * Shows text in a elements textContent by getting the value from a random data attribute
 * tag: inane-textset
 * attribute: data-*
 * Example: <inane-textset data-english="Hello" data-spanish="Holla" />
 * 
 * @author Philip Michael Raab <philip@inane.co.za>
 * @version 0.1.0
 * @copyright 2019 Philip Michael Raab <philip@inane.co.za>
 * 
 * @package Inane\Element\InaneTextSet
 * 
 * @license UNLICENSE
 * @license https://unlicense.org/UNLICENSE UNLICENSE
 * 
 * vscode-fold=2̀
 */
/* global _ */
((window) => {
	document.addEventListener('readystatechange', function (evt) {
		switch (evt.target.readyState) {
		case 'loading':
			// loading
			break;
		case 'interactive':
			// interactive
			let ModuleID = '2641d2f3-312b-4097-ba9e-f00679489c8c';

			function customTag(tagName, fn) {
				document.createElement(tagName);
				//find all the tags occurrences (instances) in the document
				var tagInstances = document.getElementsByTagName(tagName);
				//for each occurrence run the associated function
				for (var i = 0; i < tagInstances.length; i++) {
					fn(tagInstances[i]);
				}
			}

			function inaneTextSet(element) {
				//code for rendering the element goes here
				let datatextText = element.textContent;
				do {
					let datatextSet = element.dataset;
					let datatextKeys = _(element.dataset).allKeys();
					let datatextKey = _.random(0, datatextKeys.length - 1);
					datatextText = datatextSet[datatextKeys[datatextKey]];
				}
				while (element.textContent == datatextText);
				element.textContent = datatextText;
			}

			if (_(window).has('IMODULE') == false) window.IMODULE = new Map();

			if (window.IMODULE.has(ModuleID) == false) {
				customTag('inane-textset', inaneTextSet);
				window.IMODULE.set(ModuleID, 'inane-textset');
			}
			break;
		case 'complete':
			// complete
			break;
		}
	}, false);
})(window);
