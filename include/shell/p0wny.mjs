const script = [...document.scripts].find(script => script.src === import.meta.url);

const config = {
    username: script?.dataset.username || 'p0wny',
    hostname: script?.dataset.hostname || 'shell',
    version: script?.getAttribute('version') || '0.0.0',
};

window.p0wnyConfig = config;

let CWD = null;
const commandHistory = [];
let historyPosition = 0;
let eShellCmdInput = null;
let eShellContent = null;

// Keep command rendering and output rendering separate to preserve prompt formatting.
/**
 * Inserts a command into the shell content.
 *
 * @param {string} command - The command to be inserted.
 * @return {void}
 */
function _insertCommand(command) {
    eShellContent.innerHTML += '\n\n';
    eShellContent.innerHTML += '<span class=\"shell-prompt\">' + genPrompt(CWD) + '</span> ';
    eShellContent.innerHTML += escapeHtml(command);
    eShellContent.innerHTML += '\n';
    eShellContent.scrollTop = eShellContent.scrollHeight;
}

/**
 * Inserts the given stdout content into the shell content element and scrolls it to the bottom.
 *
 * @param {string} stdout - The standard output content to be inserted.
 * @return {void}
 */
function _insertStdout(stdout) {
    eShellContent.innerHTML += escapeHtml(stdout);
    eShellContent.scrollTop = eShellContent.scrollHeight;
}

/**
 * Executes a callback function after the current call stack has cleared.
 *
 * @param {Function} callback - The function to be executed asynchronously.
 * @return {void}
 */
function _defer(callback) {
    setTimeout(callback, 0);
}

/**
 * Executes a shell command.
 *
 * @param {string} command - The command to be executed in the shell.
 * @return {void}
 */
function featureShell(command) {
    _insertCommand(command);
    if (/^\s*upload\s+\S+\s*$/.test(command)) {
        featureUpload(command.match(/^\s*upload\s+(\S+)\s*$/)[1]);
    } else if (/^\s*clear\s*$/.test(command)) {
        // The backend has no TERM setting, so clear the visible output locally.
        eShellContent.innerHTML = '';
    } else {
        makeRequest('?feature=shell', {cmd: command, cwd: CWD}, function (response) {
            if (response.hasOwnProperty('file')) {
                featureDownload(atob(response.name), response.file)
            } else {
                _insertStdout(atob(response.stdout));
                updateCwd(atob(response.cwd));
            }
        });
    }
}

/**
 * Provides command or file completion based on the current input in the shell command input field.
 * Determines whether to complete a command or a file and makes a request to fetch suggestions.
 * If only one suggestion is available, it completes the input. For multiple suggestions,
 * inserts them into the standard output area.
 *
 * @function featureHint
 */
const featureHint = () => {
    const type = (currentCmd.length === 1) ? 'cmd' : 'file';
    if (eShellCmdInput.value.trim().length === 0) return;  // field is empty -> nothing to complete

    /**
     * Handles the callback for a request, processing the received data.
     *
     * @param {Object} data - The data object containing files to be processed.
     *                        Expected structure: `{ files: Array<string> }`.
     * @return {void}
     */
    function _requestCallback(data) {
        if (data.files.length <= 1) return;  // no completion
        data.files = data.files.map(function (file) {
            return atob(file);
        });
        if (data.files.length === 2) {
            if (type === 'cmd') {
                eShellCmdInput.value = data.files[0];
            } else {
                const currentValue = eShellCmdInput.value;
                eShellCmdInput.value = currentValue.replace(/(\S*)$/, data.files[0]);
            }
        } else {
            _insertCommand(eShellCmdInput.value);
            _insertStdout(data.files.join('\n'));
        }
    }

    const currentCmd = eShellCmdInput.value.split(' ');
    const fileName = (type === 'cmd') ? currentCmd[0] : currentCmd[currentCmd.length - 1];

    makeRequest(
        '?feature=hint',
        {
            filename: fileName,
            cwd: CWD,
            type: type
        },
        _requestCallback
    );

};

/**
 * Triggers a download of a specified file.
 *
 * @param {string} name - The desired name for the downloaded file.
 * @param {string} file - The base64 encoded string representing the content of the file to be downloaded.
 * @return {void}
 */
function featureDownload(name, file) {
    const element = document.createElement('a');
    element.setAttribute('href', 'data:application/octet-stream;base64,' + file);
    element.setAttribute('download', name);
    element.style.display = 'none';
    document.body.appendChild(element);
    element.click();
    document.body.removeChild(element);
    _insertStdout('Done.');
}

/**
 * Initiates a file upload feature.
 *
 * @param {string} path - The destination path where the file should be uploaded.
 * @return {void}
 */
function featureUpload(path) {
    const element = document.createElement('input');
    element.setAttribute('type', 'file');
    element.style.display = 'none';
    document.body.appendChild(element);
    element.addEventListener('change', function () {
        const promise = getBase64(element.files[0]);
        promise.then(function (file) {
            makeRequest('?feature=upload', {path: path, file: file, cwd: CWD}, function (response) {
                _insertStdout(atob(response.stdout));
                updateCwd(atob(response.cwd));
            });
        }, function () {
            _insertStdout('An unknown client-side error occurred.');
        });
    });
    element.click();
    document.body.removeChild(element);
}

/**
 * Converts a file to its base64 representation.
 *
 * @param {File} file - The file to be converted.
 * @return {Promise<string>} A promise that resolves with the base64 string of the file.
 */
function getBase64(file) {
    return new Promise(function (resolve, reject) {
        const reader = new FileReader();
        reader.onload = function () {
            resolve(reader.result.match(/base64,(.*)$/)[1]);
        };
        reader.onerror = reject;
        reader.readAsDataURL(file);
    });
}

/**
 * Generates a prompt string based on the current working directory.
 *
 * @param {string} cwd - The full path of the current working directory. Defaults to '~' if not provided.
 * @returns {string} A formatted prompt string containing the username, hostname, and a shortened version of the current working directory.
 */
function genPrompt(cwd) {
    cwd = cwd || '~';
    let shortCwd = cwd;
    if (cwd.split('/').length > 3) {
        const splitCwd = cwd.split('/');
        shortCwd = '…/' + splitCwd[splitCwd.length - 2] + '/' + splitCwd[splitCwd.length - 1];
    }
    return config['username'] + '@' + config['hostname'] + ':<span title="' + cwd + '">' + shortCwd + '</span>#';
}

/**
 * Updates the current working directory.
 *
 * @param {string|null} cwd - The new current working directory. If null, fetches it from the server.
 * @return {void}
 */
function updateCwd(cwd = null) {
    if (cwd) {
        CWD = cwd;
        _updatePrompt();
        return;
    }
    makeRequest('?feature=pwd', {}, function (response) {
        CWD = atob(response.cwd);
        _updatePrompt();
    });

}

/**
 * Escapes HTML special characters in a given string.
 *
 * @param {string} string - The input string containing HTML special characters.
 * @return {string} A new string with HTML special characters escaped.
 */
function escapeHtml(string) {
    return string
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;');
}

/**
 * Updates the content of the shell prompt element with a new prompt string generated by the `genPrompt` function using the current working directory (CWD).
 *
 * @return {void}
 */
function _updatePrompt() {
    const eShellPrompt = document.getElementById('shell-prompt');
    eShellPrompt.innerHTML = genPrompt(CWD);
}

/**
 * Inserts a command into the command history and updates the history position.
 *
 * @param {string} cmd - The command to be inserted into the history.
 * @return {void}
 */
function insertToHistory(cmd) {
    commandHistory.push(cmd);
    historyPosition = commandHistory.length;
}

/**
 * Handles key down events for the shell input.
 *
 * @param {KeyboardEvent} event - The keyboard event object containing information about the key pressed.
 */
function onShellInputKeyDown(event) {
    switch (event.key) {
        case 'Enter':
            featureShell(eShellCmdInput.value);
            insertToHistory(eShellCmdInput.value);
            eShellCmdInput.value = '';
            break;
        case 'ArrowUp':
            if (historyPosition > 0) {
                historyPosition--;
                eShellCmdInput.blur();
                eShellCmdInput.value = commandHistory[historyPosition];
                _defer(function () {
                    eShellCmdInput.focus();
                });
            }
            break;
        case 'ArrowDown':
            if (historyPosition >= commandHistory.length) {
                break;
            }
            historyPosition++;
            if (historyPosition === commandHistory.length) {
                eShellCmdInput.value = '';
            } else {
                eShellCmdInput.blur();
                eShellCmdInput.focus();
                eShellCmdInput.value = commandHistory[historyPosition];
            }
            break;
        case 'Tab':
            event.preventDefault();
            featureHint();
            break;
    }
}

/**
 * Sends a POST request to the specified URL with given parameters and processes the response using the provided callback function.
 *
 * @param {string} url - The URL to which the request will be sent.
 * @param {Object} params - An object containing key-value pairs of parameters to be sent in the request body.
 * @param {function} callback - A function that takes the parsed JSON response as an argument and processes it.
 * @return {void}
 */
function makeRequest(url, params, callback) {
    function getQueryString() {
        const a = [];
        for (const key in params) {
            if (params.hasOwnProperty(key)) {
                a.push(encodeURIComponent(key) + '=' + encodeURIComponent(params[key]));
            }
        }
        return a.join('&');
    }

    const xhr = new XMLHttpRequest();
    xhr.open('POST', url, true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onreadystatechange = function () {
        if (xhr.readyState === 4 && xhr.status === 200) {
            try {
                const responseJson = JSON.parse(xhr.responseText);
                callback(responseJson);
            } catch (error) {
                alert('Error while parsing response: ' + error);
            }
        }
    };
    xhr.send(getQueryString());
}

document.getElementById('shell-cmd').addEventListener('keydown', onShellInputKeyDown);

document.onclick = function (event) {
    const selection = window.getSelection();
    const target = event.target;

    if (target.tagName === 'SELECT') {
        return;
    }

    if (!selection.toString()) {
        eShellCmdInput.focus();
    }
};

window.onload = function () {
    eShellCmdInput = document.getElementById('shell-cmd');
    eShellContent = document.getElementById('shell-content');
    updateCwd();
    eShellCmdInput.focus();
};
