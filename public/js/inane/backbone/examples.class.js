/**
 * Examples Classes
 */
import { Dumper } from '/js/inane/dumper.js';

const dumper = Dumper.get('API');

/**
 * config
 * @typedef {object} config
 * @property {string} modelName - the model name
 * @property {string} collectionName - the collection name
 * @property {string} idAttribute - the id attribute
 * @property {string} url - the url
 */
/**
 * @type {config}
 */
const config = {
    idAttribute: 'id',
    modelName: 'Example',
    get collectionName() {
        return this.modelName + 's';
    },
    get url() {
        // return '/api/' + this.collectionName.toLowerCase();
        return '/api/' + this.collectionName.toLowerCase();
    },
    logLevel: {
        model: dumper.level,
        collection: dumper.level,
    },
};

/**
 * Example Entity
 */
class Example extends Backbone.Model {

    preinitialize() {
        this.logger = dumper.get(config.modelName, { level: config.logLevel.model });

        this.name = config.modelName;
        this.idAttribute = config.idAttribute;
        this.urlRoot = config.url;

        this.defaults = {
            id: null,
        };

        // this.on('add', (model, collection, changes) => {
        //     console.log(`Add model event got fired!`, model);
        // });
    }

    parse(response) {
        return response.hasOwnProperty('payload') ? response.payload : response;
    }
}

/**
 * Examples is a Backbone Collection that manages a collection of Example models.
 * It includes custom initialization, sorting, parsing, and fetching logic.
 *
 * @class Examples
 *
 * @extends {Backbone.Collection}
 *
 * @property {Object} logger - Logger instance for the collection.
 * @property {string} name - Name of the collection.
 * @property {string} url - URL for the collection's endpoint.
 * @property {string} sortAttribute - Attribute used for sorting models.
 * @property {Function} model - Model constructor for the collection.
 * @property {Object} options - Additional options for the collection.
 */
class Examples extends Backbone.Collection {
    /**
     * Pre-initializes the collection with default properties and configurations.
     *
     * @method preinitialize
     * @memberof Collection
     * @instance
     *
     * @property {Object} logger - Logger instance for the collection.
     * @property {string} name - Name of the collection from the configuration.
     * @property {string} url - URL for the collection from the configuration.
     * @property {string} sortAttribute - Attribute used for sorting the collection.
     * @property {Function} model - Model constructor for the collection.
     * @property {Object} options - Additional options for the collection.
     */
    preinitialize() {
        this.logger = dumper.get(config.collectionName, { level: config.logLevel.collection });

        this.name = config.collectionName;
        this.url = config.url;

        this.sortAttribute = Example.prototype.idAttribute;

        this.model = Example;
        this.options = {};

        this.on('add', (model, collection, changes) => {
            this.logger.debug(`Add collection event got fired!`, model.attributes);
        });
    }

    /**
     * Comparator function for sorting models based on a specified attribute.
     *
     * @param {Backbone.Model} model - The model to be compared.
     *
     * @returns {*} The value of the attribute used for sorting.
     */
    comparator(model) {
        return model.get(this.sortAttribute);
    }

    /**
     * Parses the response object and updates the options property if present.
     *
     * @param {Object} response - The response object to parse.
     * @param {Object} [response.options] - Optional object containing options to update.
     * @param {Object} [response.payload] - Optional payload object to return.
     *
     * @returns {Object} - The payload object if present, otherwise the original response object.
     */
    parse(response) {
        let self = this;
        if (response.hasOwnProperty('options')) Object.keys(response.options).map(key => self.options[key] = response.options[key]);

        return response.payload || response;
    }

    /**
     * Fetches a collection with the given options.
     *
     * If no options are provided, it defaults to using `this.options` as the data.
     * If options are provided but `options.data` is undefined, it sets `options.data` to `this.options`.
     * Otherwise, it merges `this.options` with the provided options.
     *
     * @param {Object} [options] - The options for fetching the collection.
     * @param {Object} [options.data] - The data to be sent with the fetch request.
     *
     * @returns {Promise} - A promise that resolves when the fetch is complete.
     */
    fetch(options) {
        if (options === undefined) options = { data: this.options };
        else if (options.data === undefined) options.data = this.options;
        else options = Object.assign({ data: this.options }, options);

        return Backbone.Collection.prototype.fetch.call(this, options);
    }
}

export { Example, Examples };
