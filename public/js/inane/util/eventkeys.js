const _properties = new WeakMap();

class EventKeys {
    constructor(event) {
        _properties.set(this, {
            event: Object.assign({
                shiftKey: false,
                altKey: false,
                ctrlKey: false,
                metaKey: false,
            }, {
                shiftKey: event.shiftKey | false,
                altKey: event.altKey | false,
                ctrlKey: event.ctrlKey | false,
                metaKey: event.metaKey | false,
            }),
        });
    }

    get none() {
        return 0;
    }

    get shift() {
        return _properties.get(this).event.shiftKey ? 1 : 0;
    }

    get alt() {
        return _properties.get(this).event.altKey ? 2 : 0;
    }

    get shiftAlt() {
        return (this.shift && this.alt) ? 3 : 0;
    }

    get ctrl() {
        return _properties.get(this).event.ctrlKey ? 4 : 0;
    }

    // get ctrl() {
    //     return (_properties.get(this).event.ctrlKey | _properties.get(this).event.metaKey) ? 4 : 0;
    // }

    get shiftCtrl() {
        return (this.shift && this.ctrl) ? 5 : 0;
    }

    get altCtrl() {
        return (this.alt && this.ctrl) ? 6 : 0;
    }

    get shiftAltCtrl() {
        return (this.shiftAlt && this.ctrl) ? 7 : 0;
    }

    get meta() {
        return _properties.get(this).event.metaKey ? 8 : 0;
    }

    get shiftMeta() {
        return (this.shift && this.meta) ? 9 : 0;
    }

    get value() {
        return this.shift | this.alt | this.ctrl | this.meta;
    }
}

export default EventKeys;

const Event_Keys = {
    _event: {
        shiftKey: false,
        altKey: false,
        ctrlKey: false,
        metaKey: false,
    },
    set event(value) {
        Object.assign(this._event, {
            shiftKey: value.shiftKey | false,
            altKey: value.altKey | false,
            ctrlKey: value.ctrlKey | false,
            metaKey: value.metaKey | false,
        });
    },
    get event() {
        return this._event;
    },
    get red() {
        return this.event.shiftKey ? 1 : 0;
    },
    get shift() {
        return this.event.shiftKey ? 1 : 0;
    },
    get purple() {
        return this.event.altKey ? 2 : 0;
    },
    get alt() {
        return this.event.alt ? 2 : 0;
    },
    get orange() {
        return (this.event.shiftKey && this.event.altKey) ? 3 : 0;
    },
    get shiftAlt() {
        return (this.event.shiftKey && this.event.altKey) ? 3 : 0;
    },
    get ctrl() {
        return (this.event.ctrlKey | this.event.metaKey) ? 4 : 0;
    },
    get value() {
        return this.red | this.purple | this.ctrl;
    }
};