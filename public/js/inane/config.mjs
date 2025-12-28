import {Dumper} from './dumper.mjs';

Dumper.dump('MODULE', 'config', 'Load');
/**
 * Common settings & configurations
 *
 * Does whatever setting up, configuring, instantiation that I commonly do in one place.
 */
// CONFIGURATION SETTING
const Options = {
    Backbone: {
        Radio: {
            ChannelName: `Inane`,
            TestEvent: `test:event`,
            TestRequest: `test:request`,
        },
    },
    ICRoot: {
        ChannelProperty: 'iChannel',
    },
};

if (window.__backboneAgent) window.__backboneAgent.handleBackbone(Backbone);
if (window.__agent) {
    Marionette.CompositeView = Marionette.CollectionView.extend({});
    window.__agent.start(Backbone, Marionette);
}

// internal references
const dumper = Dumper.get?.('Inane', {
    level: `debug`
})?.get(`Config`) || console;
const icroot = _icroot || {};

/**
 * config for control debugging
 */
if (!_icroot['config']) {
    /**
     * GlobalControl Options
     * @typedef {Object} GlobalControlOptions
     * @property {string} level LogLevel name
     * @property {boolean} preloader dis/enable control preloader
     */

    /**
     * Build Global Config Options for a control
     *
     * @param {GlobalControlOptions} param0
     * @returns
     */
    const buildOptions = ({ level, preloader } = {}) => {
        const _tmp = {};
        if (level) _tmp['dumper'] = { level: level };
        if (preloader !== undefined) _tmp['preloader'] = preloader;
        return _tmp;
    }

    _icroot['config'] = {
        QuickHeader: buildOptions(),
        // QuickHeader: buildOptions({level:'debug'}),
        ContextSpan: buildOptions(),
    };
}

/**
* Backbone & Marionette
* Create a Marionette property of the Backbone object.
*/
if (Backbone) {
    dumper.info(`Backbone`);
    if (window[`Marionette`] && !Backbone.Marionette) Backbone.Marionette = Marionette;

    if (Backbone.Radio) {
        dumper.info(`Backbone.Radio`);
        Object.defineProperty(icroot, Options.ICRoot.ChannelProperty, {
            enumerable: false,
            configurable: false,
            writable: false,
            value: Backbone.Radio.channel(Options.Backbone.Radio.ChannelName)
        });
    }

    // Setting up a test
    if (icroot[Options.ICRoot.ChannelProperty]) {
        dumper.info(`Backbone.Channel`, `Test:`, Options.Backbone.Radio.TestEvent, Options.Backbone.Radio.TestRequest);
        icroot[Options.ICRoot.ChannelProperty].on(Options.Backbone.Radio.TestEvent, function () {
            // test in the console with: _icroot.iChannel.trigger('test:event', {name:'Peep'})
            dumper.log(`Radio channel: '${this.channelName}' test event '${Options.Backbone.Radio.TestEvent}' with arguments:`, arguments);
        });
        icroot[Options.ICRoot.ChannelProperty].reply(Options.Backbone.Radio.TestRequest, function () {
            // test in the console with: bob=_icroot.iChannel.request('test:request', {name:'Peep'})
            dumper.log(`Radio channel: '${this.channelName}' test event '${Options.Backbone.Radio.TestRequest}' with arguments:`, arguments);
            return `Reply: ${Options.Backbone.Radio.TestRequest}`;
        });
    }
}
