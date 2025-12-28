const _defaults = {

};

class iPb {
    #log = console;

    constructor(selector, {source, button} = {}) {
        if (!selector) return this.#log.error('Error: no element given to copy from');

        let options = Object.assign({}, {
            element: selector,
        });
    }
}

export default iPb;
