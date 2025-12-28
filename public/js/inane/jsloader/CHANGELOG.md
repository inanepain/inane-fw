# JSLoader CHANGELOG

**Please note:** From v0.10.0 the changelog is on [InaneJS Wiki](https://git.inane.co.za:3000/Inane/inane-js/wiki/Inane-JSLoader)

Back to [README](README.md)

## 0.9.0 (2018 Oct 04)

- loadCheck  : About time, validate module name right up front and ignore invalid ones and triggers an ignore event
- dotRequire : For some items it make sense to put to requirement onto the module name. IE a custom jquiery select => myselect could be called jqueryui.myselect. This looks for jqueryui as a requirement.
- events     : Replaced onreadystatechange with addEvent...

## 0.8.3 (2018 Sep 19)

- logging    : Totally quiet when off
- Inane      : No longer used

## 0.8.2 (2018 Jul 3)

- ready      : Fixed: Ready scripts not run if no modules loaded
- arg check  : Fix: Error if loadModules called with no arguments

## 0.8.1 (2017 Nov 23)

- baseURL    : Add option for a base url to prepend urls
- fix spinner: Spinner fades out based on transTime

## 0.8.0 (2016 May 26)

- ? Fix Loop   : Multi load required modules
- Ready      : Register callbacks for done

## 0.7.0 (2016 May 18)

- history    : Keep history of loaded files, prevent double loading
- event & log: Fixed a problem with css files sent as empty string to events
- other      : Little tweaks to speed things up

## 0.6.0 (2016 Apr 29)

- noCache    : uses a query string to stop browser caching of stylesheets
- Spinner    : optional loading spinner while scripts loading (only handy for large queues)
- Underscore : removed dependance (next backbone?)

## 0.5.0 (2016 Mar 06)

- Modules configuration separated from main code
- Logging <script mode-verbose=1... enables console output
- More code comments added

## 0.4.0 (2016 Feb 16)

- initial release
