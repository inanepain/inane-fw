class DataMaker {
    createJunk(words = 5, max = 10, options = {}) {
        options = I.margeOptions(options, {
            word: {
                min: 1,
                max: 10
            },
            punctuate: {
                caps: true,
                mark: true,
            },
        });

        let i = 0;
        if (max > words) words = _.random(words, max);
        let sentence = [];
        while (i < words) {
            i++;
            sentence.push(Math.random().toString(36).replace(/[^a-z]+/g, '').substr(0, _.random(options.word.min, options.word.max)));
        }

        // append punctuation on end
        const punct = '....!??';
        if (options.punctuate.mark)
            sentence[sentence.length - 1] += punct.charAt(Math.floor(Math.random() * punct.length));

        // First letter uppercase
        if (options.punctuate.caps)
            sentence[0] = sentence[0].charAt(0).toUpperCase() + sentence[0].slice(1);

        return sentence.join(' ');
    }

    createEmail() {
        let email = [];
        email.push(this.createJunk(1, 0, { word: { min: 2, max: 2 }, punctuate: { caps: false, mark: false}}).concat('@'));
        email.push(this.createJunk(1, 0, { word: { min: 2, max: 2 }, punctuate: { caps: false, mark: false}}).concat('.'));
        email.push(this.createJunk(1, 0, { word: { min: 2, max: 2 }, punctuate: { caps: false, mark: false}}));
        return email.join('');
    }
}

let dataMaker = new DataMaker();

// const ddd = {
//     word: {
//         min: 1,
//         max: 10
//     },
//     punctuate: {
//         caps: true,
//         mark: true,
//     },
// };
