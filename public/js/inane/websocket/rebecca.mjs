import { Dispatcher } from "./dispatcher.js";

const States = {
    0: {
        name: 'CONNECTING',
        value: 0,
        description: 'Socket has been created.The connection is not yet open.'
    },
    1: {
        name: 'OPEN',
        value: 1,
        description: 'The connection is open and ready to communicate.'
    },
    2: {
        name: 'CLOSING',
        value: 2,
        description: 'The connection is in the process of closing.'
    },
    3: {
        name: 'CLOSED',
        value: 3,
        description: 'The connection is closed or couldn\'t be opened.'
    }
};

for (const key in States) Object.freeze(States[key]);
Object.freeze(States);

/**
 * Rebecca Message
 */
class Message {
    constructor() {

    }
}

/**
 * Rebecca
 *
 * @version 0.0.1
 */
class Rebecca extends Dispatcher {
    /**
     * Version
     *
     * @readonly
     * @static
     * @type {string}
     */
    static get VERSION() {
        return '0.0.1';
    }
    /**
     * Version
     *
     * @readonly
     * @type {string}
     * @ignore
     */
    get VERSION() {
        return this.constructor.VERSION;
    }

    /**
     * @type {WebSocket}
     */
    #socket = null;

    /**
     * @type {Object}
     */
    #options = {};

    #queue = [];

    constructor({ url = 'ws://127.0.0.1:9394', logger } = {}) {
        super();
        this.#options.url = url;
        this.#options.logger = logger || console;
    }

    /**
     * Connection
     *
     * If not connected it tries to connect before returning
     *
     * @type {WebSocket}
     */
    get #con() {
        if (!this.#socket) this.#connect();
        return this.#socket;
    }

    /**
     * Connects to websocket
     */
    #connect() {
        try {
            this.#socket = new WebSocket(this.#options.url);
            this.#socket.addEventListener('open', this.#onOpen.bind(this));
            this.#socket.addEventListener('close', this.#onClose.bind(this));
            this.#socket.addEventListener('message', this.#onMessage.bind(this));
            this.#socket.addEventListener('error', this.#onError.bind(this));
        } catch (error) {
            this.logger.error('Connection failed:', error);
        }
    }

    /**
     * Open Event Handler
     *
     * @param {Event} event open event
     */
    #onOpen(event) {
        this.logger.info('onOpen', event);

        while (this.#queue.length > 0)
            this.#send(this.#queue.shift());
        
        // while (this.#queue.length > 0) this.#send(this.#queue.shift());

        // while (this.#queue.length > 0) {
        //     const request = this.#queue.shift();
        //     this.#send(request);
        // }
    }

    /**
     * Close Event Handler
     *
     * @param {CloseEvent} event close event
     */
    #onClose(event) {
        this.logger.info('onClose', (event.wasClean ? 'clean' : 'dirty') + `:${event.code} => ${event.reason}`);
    }

    /**
     * Message Event Handler
     *
     * @param {MessageEvent} event message event
     */
    #onMessage(event) {
        let json = event.data.parseJSON();
        this.logger.info('onMessage', `id:${event.lastEventId}`, json);
    }

    /**
     * Error Event Handler
     *
     * @param {Event} event error event
     */
    #onError(event) {
        this.logger.error('Rebecca error', event);
    }

    /**
     * Send Request
     *
     * @param {object} request request
     */
    #send(request) {
        try {
            this.#con.send(request.jsonString());
        } catch (error) {
            this.#queue.push(request);
            this.logger.info('Send failure, request queued!');
        }
    }

    /**
     * State
     *
     * - null: no socket
     * - 0: CONNECTING: Socket has been created.The connection is not yet open.
     * - 1: OPEN: The connection is open and ready to communicate.
     * - 2: CLOSING: The connection is in the process of closing.
     * - 3: CLOSED: The connection is closed or couldn\'t be opened.
     *
     */
    get state() {
        return States[this.#socket?.readyState];
    }

    /**
     * Send Action Request
     *
     * @param {string} [action=ping] actionCommand
     * @param {object} options action options
     */
    action(action = 'ping', options = {}) {
        this.#send({ command: 'action', action: action, options: options });
    }

    /**
     * Reload Request
     */
    reload() {
        this.#send({ command: 'reload' });
    }

    /**
     * Shutdown Request
     */
    shutdown() {
        this.#send({ command: 'shutdown' });
    }

    get logger() {
        return this.#options.logger;
    }
}

// const s = new Rebecca();

export { Rebecca };
