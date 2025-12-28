/**
 * Custom Element: InaneGravatar
 * 
 * InaneGravatar uses the value in its email attribute to show an avatar from Gravatar
 * tag: inane-gravatar
 * attribute: email
 * Example: <inane-gravatar email="peep@inane.co.za" />
 * 
 * @author Philip Michael Raab <philip@inane.co.za>
 * @version 0.9.0
 * @copyright 2019 Philip Michael Raab <philip@inane.co.za>
 * 
 * @package Inane\Element\InaneGravatar
 * 
 * @license MIT
 * @license https://raw.githubusercontent.com/CathedralCode/Builder/develop/LICENSE MIT License
 * 
 * vscode-fold=2̀
 */
/* global _ */
/* global I */
((window)=>{
	let ModuleID = 'f8761117-cb85-4dc4-9791-dfebe4c15e75';

	function customTag(tagName, fn) {
		document.createElement(tagName);
		//find all the tags occurrences (instances) in the document
		var tagInstances = document.getElementsByTagName(tagName);
		//for each occurrence run the associated function
		for (var i = 0; i < tagInstances.length; i++) {
			fn(tagInstances[i]);
		}
	}
	
	function inaneGravatar(element) {
		//code for rendering the element goes here
		if (element.attributes.email) {
			//get the email address from the element's email attribute
			var email = element.attributes.email.value;
			var gravatar = 'http://www.gravatar.com/avatar/' + I.md5(email) + '.png';
			element.innerHTML = '<img src=\'' + gravatar + '\' alt="Gravatar: ' + email + '" itemprop=\'contentUrl\' style="border-radius: .4rem;">';

			var attItemscope = document.createAttribute('itemscope');	// Create a "itemscope" attribute
			var attItemtype = document.createAttribute('itemtype');		// Create a "itemtype" attribute
			attItemtype.value = 'http://schema.org/ImageObject';		// Set the value of the class attribute

			element.setAttributeNode(attItemscope); 
			element.setAttributeNode(attItemtype); 
		}
	}
	
	if (_(window).has('IMODULE') == false) window.IMODULE = new Map();

	if (window.IMODULE.has(ModuleID) == false) {
		customTag('inane-gravatar', inaneGravatar);
		window.IMODULE.set(ModuleID, 'inane-gravatar');
	}
})(window);
