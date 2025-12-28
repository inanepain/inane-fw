/**
 * Setup the modules for JSLoader
 */

JSLoader.moduleList = {
	xdate: {
		script: 'extend/date-1.0.0.js'
	},
	xnumber: {
		script: 'extend/number-1.0.0.js'
	},
	xstring: {
		script: 'extend/string-1.3.0.js'
	},
	xobject: {
		script: 'extend/object-1.0.1.js'
	},
	xscripts: {
		require: ['xdate', 'xnumber', 'xstring', 'xobject']
	},
	backbone: {
		script: 'Backbone/1.3.3/backbone-1.3.3.js',
	},
	logger: {
		script: 'LoggerJS/1.4.1/logger-1.4.1.js'
	},
	jscookie: {
		script: 'JSCookie/0.9.0/jscookie-0.9.0.js'
	},
	animatecss: {
		style: 'AnimateCSS/3.6.0/animate-3.6.0.min.css'
	}
}
