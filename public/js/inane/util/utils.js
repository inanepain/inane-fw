// utils.js
function insertJs({ src, isModule, async, defer }) {
    const script = document.createElement('script');

    if (isModule) {
        script.type = 'module';
    } else {
        script.type = 'application/javascript';
    }
    if (async) {
        script.setAttribute('async', '');
    }
    if (defer) {
        script.setAttribute('defer', '');
    }

    document.head.appendChild(script);

    return new Promise((success, error) => {
        script.onload = success;
        script.onerror = error;
        script.src = src;// start loading the script
    });
}

function awaitStylesheets() {
    let interval;
    return new Promise(resolve => {
        interval = setInterval(() => {
            for (let i = 0; i < document.styleSheets.length; i++) {
                // A stylesheet is loaded when its object has a 'cssRules' property
                if (typeof document.styleSheets[i].cssRules === 'undefined') {
                    return;
                }
            }

            // Only reached when all stylesheets have been loaded
            clearInterval(interval);
            resolve();
        }, 10);
    });
}

export { insertJs, awaitStylesheets };
