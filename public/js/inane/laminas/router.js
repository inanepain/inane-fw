/**
 * Routes
 */
import { Dumper } from '/js/inane/dumper.js';

/**
 * RouteMatch
 */
class RouteMatch {
    /**
     * @type {string} route path
     */
    #route;

    /**
     * @type {object} route options
     */
    #options;

    /**
     * @type {string} route url
     */
    #url;

    /**
     * @type {object[]} route parse error
     */
    #errors;

    /**
     * RouteMatch
     *
     * @param {object} param0 options
     * @param {string} param0.route route path
     * @param {object} param0.options route options
     * @param {string} param0.url route url
     * @param {object[]} param0.errors route parse errors
     */
    constructor({ route = '', options = {}, url = '', errors = [] }) {
        this.#route = route;
        this.#options = options;
        this.#url = url;
        this.#errors = errors;
    }

    /**
     * route path
     *
     * @property
     * @readonly
     * @type {string}
     */
    get route() {
        return this.#route;
    }

    /**
     * route options
     *
     * @property
     * @readonly
     * @type {object}
     */
    get options() {
        return this.#options.jsonString().parseJSON();
    }

    /**
     * route url
     *
     * @property
     * @readonly
     * @type {string}
     */
    get url() {
        return this.#url;
    }

    /**
     * route parse errors
     *
     * @property
     * @readonly
     * @type {onject[]}
     */
    get errors() {
        return this.#errors.jsonString().parseJSON();;
    }

    /**
     * Is route valid
     *
     * @returns {boolean} route is valid
     */
    isValid() {
        return (this.#errors.length == 0);
    }
}

/**
 * Router
 *
 * @version 0.5.0
 */
class Router {
    /**
     * Routes
     */
    #routes;

    /**
     * Regex for parssing variables in route patters
     */
    #re = /(\[.:[a-z]+\])/i;

    /**
     * Dumper
     * @type {Dumper}
     */
    #logger;

    /**
     * Constructor
     *
     * @param {Object} options param0 options
     * @param {Dumper} options.logger logger
     * @param {Object} options.routes available routes
     * @param {boolean} [options.debug=false] write debug information to console
     */
    constructor({ logger, routes, debug = false } = { debug: false }) {
        this.#logger = logger || Dumper.get(`Router`, {
            level: Boolean(debug) ? `debug` : `warn`,
        });

        this.#routes = routes;

        this.#logger.debug(`Router (${this.VERSION}):`, `initialised`);
    }

    /**
     * Version
     *
     * @property
     * @readonly
     * @type {string}
     */
    static get VERSION() {
        return '0.5.0';
    }

    /**
     * Version
     *
     * @property
     * @readonly
     * @type {string}
     */
    get VERSION() {
        return this.constructor.VERSION;
    }

    /**
      * Parse url from route
      *
      * @param {string} route route
      * @param {object} options route options
      *
      * @returns {RouteMatch} the parsed url for the route
      */
    parseRoute(route, options = {}) {
        const routeParams = {
            route: route,
            options: options,
            errors: [],
        };

        const parts = [];
        let haystack = this.#routes;
        for (const part of route.split(`/`)) {
            if (haystack == undefined) continue;

            let step = haystack[part];

            if (step == undefined) {
                this.#logger.warn(`Invalid Route:`, route, `Path:`, part);
                routeParams.errors.push({
                    id: routeParams.errors.length + 1,
                    type: `invalid route`,
                    segmant: part,
                });
            } else if (step.type.endsWith(`Literal`))
                parts.push(step.options.route);
            else if (step.type.endsWith(`Segment`)) {
                let seg = step.options.route;
                Object.keys(options).forEach((key) => {
                    if (seg.includes(`:`.concat(key))) {
                        seg = seg.replace(`:`.concat(key), options[key]);
                        delete options[key];
                    }
                });

                seg = seg.replace(this.#re, ``).replaceAll(`[`, ``).replaceAll(`]`, ``);
                parts.push(seg);
            }

            haystack = step?.child_routes;
        }
        routeParams.url = parts.join(``);

        return new RouteMatch(routeParams);
    }

    /**
      * Parse url from route
      *
      * @param {string} route route
      * @param {object} options route options
      *
      * @returns {RouteMatch} the parsed url for the route
      */
    resolveRoute(route, options = {}) {
        return new Promise((resolve, reject) => {
            const routeMatch = this.parseRoute(route, options);
            if (routeMatch.isValid()) resolve(routeMatch);
            else reject(routeMatch);
        });
    }
}

export default Router;
