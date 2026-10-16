import {Dumper} from './inane/dumper.mjs';

/**
 * Description placeholder
 *
 * @type {*}
 */
const dumper = Dumper.get('Develop');
dumper.setLevel(2);

window.dumper = dumper;

if (Backbone.Marionette == undefined) {
    Backbone.Marionette = Marionette;
}

/**
 * Description placeholder
 *
 * @type {*}
 */
const DevelopObj = Marionette.MnObject.extend({
    channelName: 'develop',
    initialize(options, arg2) {
        this.mergeOptions(options, ['logger']);
    },
    radioEvents: {
        'left:building': 'leftBuilding'
    },
    leftBuilding(person) {
        this.logger.debug(' has left the building!');
    },
});

/**
 * Description placeholder
 *
 * @type {*}
 */
const developobj = new DevelopObj({
    logger: dumper.get('DevelopObj'),
});

/**
 * Description placeholder
 *
 * @type {*}
 */
const MyApp = Marionette.Application.extend({
    region: '#content',
    channelName: 'develop',
    initialize(options) {
        this.mergeOptions(options, ['logger']);
        this.logger.debug('Initialize');
    },

    onBeforeStart(app, options) {
        this.logger.debug('onBeforeStart', 'app:', app, 'options:', options);
        // this.model = new MyModel(options.data);
    },

    onStart(app, options) {
        this.logger.debug('onStart', 'app:', app, 'options:', options);
        // this.showView(new MyView({ model: this.model }));
        // Backbone.history.start();

        Backbone.history.start({pushState: true, root: "/temp/"});
    }
});

/**
 * Description placeholder
 *
 * @type {*}
 */
const myApp = new MyApp({
    logger: dumper.get('App'),
});
window.app = myApp;

window.document.addEventListener('readystatechange', function (evt) {
    switch (evt.target.readyState) {
        case 'loading':
            // Loading Stuff
            dumper.debug('root.document.readyState == \'loading\'');
            break;
        case 'interactive':
            // if (JSLoader.manualTrigger == false) JSLoader.start();
            dumper.debug('root.document.readyState == \'interactive\'');
            break;
        case 'complete':
            dumper.debug('root.document.readyState == \'complete\'');
            myApp.start({
                data: {
                    id: 1,
                    text: 'value'
                }
            });
            Backbone.Radio.channel('develop').trigger('left:building');
            break;
    }
}, false);
