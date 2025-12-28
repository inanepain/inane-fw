/**
 * DBSync
 * This library is no longer under development. See DBLayer.
 * 
 * @author philip<peep@cathedral.co.za>
 * @version 0.3.1
 * 
 * @deprecated
 * @see DBLayer
 * @class DBSync
 */
var DBSync = {
	collection: [],
	staticCollection: {},
	interval: 50000,
	timer: null,
	debug: false,
	start: function() {
		DBSync.log('DBSync::start');
		DBSync.timer = setInterval(function() {
			for (var i in DBSync.collection) {
				DBSync.log('DBSync::sync::' + i);
				DBSync.collection[i].fetch();
			}
		}, DBSync.interval);
	},
	stop: function() {
		DBSync.log('DBSync::stop');
		clearInterval(DBSync.timer);
	},
	createModel: function(modelName, id, url, attributeDefaults) {
		return Backbone.Model.extend({
			name: modelName,
			url: url,
			urlRoot: url,
			idAttribute: id,
			debug: false,
			defaults: attributeDefaults,
			initialize: function() {
				this.on('add', function(model) {
					this.log(this.name + '::add::' + model.id);
				});
				this.on('change', function(model, state) {
					this.log(this.name + '::change::' + model.id);
				});
				this.on('remove', function(model, state) {
					this.log(this.name + '::remove::' + model.id);
				});
				this.on('sync', function(model, collection, response) {
					this.log(this.name + '::sync::' + model.id);
				});
			},
			log: function(message) {
				if (this.debug === true) {
					if (typeof Logger !== 'undefined') {
						Logger.get('DBSync').debug(message);
					} else {
						console.debug(message);
					}
				}
			}
		});
	},
	createCollection: function(collectionName, url, modelObj) {
		return Backbone.Collection.extend({
			name: collectionName,
			url: url,
			model: modelObj,
			debug: false,
			sortAttribute: modelObj.idAttribute,
			initialize: function() {
				this.on('add', function(model) {
					this.log(this.name + '::add::' + model.id);
				});
				this.on('remove', function(model) {
					this.log(this.name + '::remove::' + model.id);
				});
				this.on('change', function(model) {
					this.log(this.name + '::change::' + model.id);
				});
				this.on('sync', function(e) {
					this.log(this.name + '::sync::' + e.length);
				});
			},
			comparator: function(model) {
				return model.get(this.sortAttribute);
			},
			log: function(message) {
				if (this.debug === true) {
					if (typeof Logger !== 'undefined') {
						Logger.get('DBSync').debug(message);
					} else {
						console.debug(message);
					}
				}
			}
		});
	},
	log: function(message) {
		if (this.debug === true) {
			if (typeof Logger !== 'undefined') {
				Logger.get('DBSync').debug(message);
			} else {
				console.debug(message);
			}

		}
	}
};
