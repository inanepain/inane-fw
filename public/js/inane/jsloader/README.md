# JSLoader

**Please note:** More documentation can be found on [InaneJS Wiki](https://git.inane.co.za:3000/Inane/inane-js/wiki/Inane-JSLoader).

Load scripts/css lazy  
Setup deps between scripts

## Document Ready

Have your JS run once the DOM is loaded.

```javascript
JSLoader.ready(function(){
    # Code...
});
```

## Modules

Setup modules. Its pretty simple just create an object with your modules and assign to moduleList property of JSLoader.

### ModuleList definition

Add a property for each module the property name is the module name. Each module can be assigned a stylesheet and/or javascript file and a list of prerequisites. See example bellow.

jsloader-modules.js

```javascript
JSLoader.moduleList = {
    liba: {
        script: '/path/to/liba.js',
        style: '/path/to/liba.css'
    },
    scripta: {
        script: 'path/to/scripta.js',
        require: ['liba']
    }
}
```

To load scripta and all its prerequisites:

```javascript
JSLoader.loadModules(['scripta']);
```

## Putting it all together

Example:

```javascript
JSLoader.loadModules(['scripta']).ready(function(){
    # Code...
    # Do something with scripta...
});
```

## EVENTS

* add
* start
* queueing
* queued
* skipped
* loaded
* error
* done
* finished
