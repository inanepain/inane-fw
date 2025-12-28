/**
 * Image Drop
 *
 * Drop/Select image and the control displays it ready for upload.
 *
 * @see https://git.inane.co.za:3000/Inane/inane-js/wiki/Inane_Control-ImageDrop
 *
 * @class ImageDrop
 * @author Philip Michael Raab <philip@cathedral.co.za>
 * @version 0.9.1
 *
 * CHANGELOG
 * 0.9.1 (2020 May 07)
 *  - Uses iHelper to iFile dependency
 */
(function (window, I) {
    /**
     * Version
     *
     * @constant
     * @type {String}
     * @memberof ImageDrop
     */
    const VERSION = '0.9.1';

    _icroot.iHelper.importModule('iFile');

    const Config = {
        ext: ['jpg', 'png', 'svg'],
        sizeLimitKB: 500,
        logger: console,
        ui: {
            hover: 'highlight'
        },
        view: {
            zone: '.image-drop-zone',
            image: '.image-drop-image',
            file: '.image-drop-file',
        },
    };

	/**
	 * createEvent
	 *
	 * @param {string} namespace
	 * @param {string} action
	 * @param {*} detail
	 * @returns {CustomEvent}
	 * @memberof ImageDrop
	 */
    function createEvent(namespace, action, detail = {}) {
        detail.namespace = namespace;
        detail.action = action;

        return new CustomEvent(namespace + ':' + action, { bubbles: false, detail });
    }

	/**
	 * Create an Image Drop Object
	 *
	 * @param {Element} imageDrop
	 * @param {*} options
	 */
    const ImageDrop = function (imageDrop, options = {}) {
        this.VERSION = VERSION;
        Object.assign(options, Config);
        // _(this).extend(Backbone.Events);
        // const self = this;

        const reader = new FileReader();
        const dropZone = imageDrop.classList.contains(options.view.zone.replace('.', '')) ? imageDrop : imageDrop.querySelector(options.view.zone) == null ? imageDrop : imageDrop.querySelector(options.view.zone);
        // const imgView = dropZone.querySelector(options.view.image) || dropZone.querySelector('img');
        const imgView = imageDrop.querySelector(options.view.image) || imageDrop.querySelector('img');
        const inFile = imageDrop.querySelector(options.view.file) || imageDrop.querySelector('input[type="file"]');

        this.image = imgView;

        // this.elements = {
        // 	id: imageDrop,
        // 	fileinput: inFile,
        // 	imageview: imgView,
        // 	zone: dropZone,
        // };

        dropZone.addEventListener('dragover', function (event) {
            event.preventDefault();
            event.stopPropagation();
        }, true);

        dropZone.addEventListener('dragenter', function (event) {
            event.preventDefault();
            event.stopPropagation();
            this.classList.add(options.ui.hover);
        }, true);

        dropZone.addEventListener('dragleave', function (event) {
            event.preventDefault();
            event.stopPropagation();
            this.classList.remove(options.ui.hover);
        }, true);

        dropZone.addEventListener('drop', function (event) {
            event.preventDefault();
            event.stopPropagation();
            inFile.files = event.dataTransfer.files;
            this.classList.remove(options.ui.hover);
        }, true);

        dropZone.addEventListener('click', function (event) {
            options.logger.debug('dropZone', 'click');
            event.stopPropagation();
            inFile.click();
        }, true);

        reader.addEventListener('load', function (event) {
            event.stopPropagation();
            imgView.src = reader.result;
        }, true);

        inFile.addEventListener('change', function (event) {
            event.stopPropagation();
            if (this.files && this.files[0]) {
                /**
                 * file
                 *
                 * @type {iFile}
                 * @memberof ImageDrop
                 */
                let file = new _icroot.iFile(this.files[0]);
                // file.dump();

                if (!options.ext.includes(file.ext)) return console.log('Invalid ext. use: ' + options.ext.join(', '));
                if (file.KB > options.sizeLimitKB) return console.log('File size to large. Limit: ' + options.sizeLimitKB);
                reader.readAsDataURL(file.file);
            }
        }, true);

        imgView.addEventListener('load', function (event) {
            options.logger.debug('ImageDrop:Image:Load');
            // event.stopPropagation();
            // self.trigger('image:load', createEvent('image', 'load'));
        }, true);

        // return this;
    }

    ImageDrop.VERSION = VERSION;

    ImageDrop.defaults = function (defaults) {
        Object.assign(Config, defaults);
    }

    window.ImageDrop = ImageDrop;
}(window, I));
