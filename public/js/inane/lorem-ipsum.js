/**
 * LoremIpsum
 *
 * Quickly generate a LoremIpsum word list, sentence or paragraph.
 * A words array can be supplied at instantiation to increase or replace the build-in list.
 *
 * @version 1.0.0
 */
class LoremIpsum {
    /**
     * Possible words
     *
     * @type {Array}
     */
    #words = ['a', 'ac', 'accumsan', 'ad', 'adipiscing', 'aenean', 'aenean', 'aliquam', 'aliquam', 'aliquet', 'amet', 'ante', 'aptent', 'arcu', 'at', 'auctor', 'augue', 'bibendum', 'blandit', 'class', 'commodo', 'condimentum', 'congue', 'consectetur', 'consequat', 'conubia', 'convallis', 'cras', 'cubilia', 'curabitur', 'curabitur', 'curae', 'cursus', 'dapibus', 'diam', 'dictum', 'dictumst', 'dolor', 'donec', 'donec', 'dui', 'duis', 'egestas', 'eget', 'eleifend', 'elementum', 'elit', 'enim', 'erat', 'eros', 'est', 'et', 'etiam', 'etiam', 'eu', 'euismod', 'facilisis', 'fames', 'faucibus', 'felis', 'fermentum', 'feugiat', 'fringilla', 'fusce', 'gravida', 'habitant', 'habitasse', 'hac', 'hendrerit', 'himenaeos', 'iaculis', 'id', 'imperdiet', 'in', 'inceptos', 'integer', 'interdum', 'ipsum', 'justo', 'lacinia', 'lacus', 'laoreet', 'lectus', 'leo', 'libero', 'ligula', 'litora', 'lobortis', 'lorem', 'luctus', 'maecenas', 'magna', 'malesuada', 'massa', 'mattis', 'mauris', 'metus', 'mi', 'molestie', 'mollis', 'morbi', 'nam', 'nec', 'neque', 'netus', 'nibh', 'nisi', 'nisl', 'non', 'nostra', 'nulla', 'nullam', 'nunc', 'odio', 'orci', 'ornare', 'pellentesque', 'per', 'pharetra', 'phasellus', 'placerat', 'platea', 'porta', 'porttitor', 'posuere', 'potenti', 'praesent', 'pretium', 'primis', 'proin', 'pulvinar', 'purus', 'quam', 'quis', 'quisque', 'quisque', 'rhoncus', 'risus', 'rutrum', 'sagittis', 'sapien', 'scelerisque', 'sed', 'sem', 'semper', 'senectus', 'sit', 'sociosqu', 'sodales', 'sollicitudin', 'suscipit', 'suspendisse', 'taciti', 'tellus', 'tempor', 'tempus', 'tincidunt', 'torquent', 'tortor', 'tristique', 'turpis', 'ullamcorper', 'ultrices', 'ultricies', 'urna', 'ut', 'ut', 'varius', 'vehicula', 'vel', 'velit', 'venenatis', 'vestibulum', 'vitae', 'vivamus', 'viverra', 'volutpat', 'vulputate'];

    /**
     * FormFiller
     *
     * @param {object} [options={words: [], replace: false}] options
     * @param {string[]} [options.words=[]] word array
     * @param {boolean} [options.replace=false] replace or increase build-in word list
     */
    constructor({ words = [], replace = false } = {}) {
        if (replace && words.length > 0) this.#words = words;
        else this.#words = this.#words.concat(words);

        this.#verify();
    }

    /**
     * Verify all words in the list are valid
     *
     * - remove duplicates
     * - remove non strings
     */
    #verify() {
        this.#words = this.#words.filter((word, pos) => {
            return typeof word == 'string' && this.#words.indexOf(word) == pos;
        });
    }

    /**
     * Get random number
     *
     * @param {number} x
     * @param {number} y
     *
     * @returns {number} random number
     */
    #random(x, y) {
        const rnd = (Math.random() * 2 - 1) + (Math.random() * 2 - 1) + (Math.random() * 2 - 1);
        return Math.round(Math.abs(rnd) * x + y);
    }

    /**
     * Get random number between min and max
     *
     * @param {number} [min] (optional) lower result limit
     * @param {number} [max] (optional) upper result limit
     *
     * @returns {number} random number
     */
    #count(min, max) {
        let result;
        if (min && max) result = Math.floor(Math.random() * (max - min + 1) + min);
        else if (min) result = min;
        else if (max) result = max;
        else result = this.#random(8, 2);

        return result;
    }

    /**
     * Number of words in the list
     *
     * @type {number}
     * @readonly
     */
    get wordCount() {
        return this.#words.length;
    }

    /**
     * Get random words
     *
     * @param {number} [min] (optional) minimal words count
     * @param {number} [max] (optional) maximal words count
     *
     * @returns {string[]} array of random words
     */
    words(min, max) {
        const result = [];
        const count = this.#count(min, max);

        // get random words
        while (result.length < count) {
            var pos = Math.floor(Math.random() * this.#words.length);
            var rnd = this.#words[pos];

            // do not allow same word twice in a row
            if (result.length && result[result.length - 1] === rnd) continue;

            result.push(rnd);
        }

        return result;
    }

    /**
     * Generate sentence
     *
     * @param {number} [min] (optional) minimal words count
     * @param {number} [max] (optional) maximal words count
     *
     * @returns {string} sentence
     */
    sentence(min, max) {
        const words = this.words(min, max);

        // add comma(s) to sentence
        var index = this.#random(6, 2);
        while (index < words.length - 2) {
            words[index] += ',';
            index += this.#random(6, 2);
        }

        // append puctation on end
        var punct = '...!?'
        words[words.length - 1] += punct.charAt(Math.floor(Math.random() * punct.length));

        // uppercase first letter
        words[0] = words[0].charAt(0).toUpperCase() + words[0].slice(1);

        return words.join(' ');
    }

    /**
     * Generate paragraph
     *
     * @param {number} [min] (optional) minimal words count
     * @param {number} [max] (optional) maximal words count
     *
     * @returns {string} paragraph
     */
    paragraph(min, max) {
        if (!min && !max) {
            min = 20;
            max = 60;
        }

        var result = '';
        var count = this.#count(min, max);

        // append sentences until limit is reached
        while (result.slice(0, -1).split(' ').length < count) result += this.sentence() + ' ';
        result = result.slice(0, -1)

        // remove words
        if (result.split(' ').length > count) {
            var punct = result.slice(-1);
            result = result.split(' ').slice(0, count).join(' ');
            result = result.replace(/,$/, '');
            result += punct;
        }

        return result;
    }
}

export { LoremIpsum };
