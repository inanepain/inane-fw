<?php

declare(strict_types=1);

/*
 *
 * Inane: PROJECT
 *
 * PROJECT_DESCRIPTION
 *
 * $Id$
 * $Date$
 *
 * PHP version 8.4
 *
 * @author   Philip Michael Raab <philip@cathedral.co.za>
 * @package  inanepain\PROJECT
 * @category PROJECT
 *
 * @license  UNLICENSE
 * @license  https://unlicense.org/UNLICENSE UNLICENSE
 *
 * _version_ $version
 */

namespace Knot\Gitea;

use Inane\Cli\Cli;
use Inane\Cli\Pencil;
use Inane\Cli\Pencil\Colour;
use Inane\Cli\Pencil\Style;
use Inane\Config\ConfigAwareTrait;
use Inane\File\File;
use Inane\File\Path;
use Inane\Stdlib\Exception\RuntimeException;
use Inane\Stdlib\Options;
use ReflectionException;

/**
 * Class GiteaPackageUploader
 *
 * This class is responsible for handling the uploading of packages to a Gitea repository.
 * It provides methods to interact with the Gitea API and manage package uploads efficiently.
 *
 */
class GiteaPackageUploader {
    use ConfigAwareTrait;

    /**
     * The current version of the application.
     *
     * @var string
     */
    public const string VERSION = '0.1.0';

    /**
     * @var array $defaultConfig
     *
     * This property holds the default configuration settings for the package.
     * It is an associative array where keys represent configuration options
     * and values represent their default values.
     */
    protected array $defaultConfig = [
        'server' => [
            'protocol' => 'https',
            'host' => 'localhost',
            'port' => 3000,
            'username' => '',
            'password' => '',
        ],
        'archive' => [
            'dir' => '/Users/philip/Downloads',
            'pattern' => '*-*.*.*.zip',
        ],
        'tweaks' => [
            'autoUpload' => false,
            'autoDelete' => false,
        ],
        'customise' => [
            'vendors' => ['inanepain', 'cathedral'],
        ],
        'develop' => [
            'dryRun' => false,
            'verbose' => false,
        ],
    ];

    /**
     * @var Options $pen a bunch of pretty colours for the console output.
     */
    protected Options $pen;

    /**
     * @var Path $path The path object representing the file or directory path.
     */
    protected Path $path;

    /**
     * Constructor for the class.
     *
     * @param array|Options|null $config Configuration options for the instance.
     *
     * @throws ReflectionException
     * @throws RuntimeException
     */
    public function __construct(array|Options|null $config = null) {
        $this->setConfig($config);
        $this->bootstrap();

        $this->pen->purple->line('Gitea Package Uploader ' . $this->pen->white . '(' . $this->pen->blue . static::VERSION . $this->pen->white . ')');
    }

    /**
     * Initializes the necessary components and configurations for the application.
     *
     * This method is responsible for bootstrapping the application by setting up
     * required dependencies, configurations, or any other initialization logic.
     *
     * @return void
     */
    protected function bootstrap(): void {
        if (!isset($this->pen)) $this->pen = new Options([
            'blue' => new Pencil(Colour::Blue),
            'green' => new Pencil(Colour::Green),
            'purple' => new Pencil(Colour::Purple),
            'red' => new Pencil(Colour::Red),
            'white' => new Pencil(Colour::White),
            'yellow' => new Pencil(Colour::Yellow, Style::Bold),
            'reset' => Pencil::reset(),
        ]);

        if (!isset($this->path)) $this->path = new Path($this->config->archive->dir);
    }

    #region console output

    /**
     * Displays an error message for a specific file.
     *
     * @param string $message The error message to be displayed.
     * @param int    $exit When greater than 0 it tells the application is ended.
     *
     * @return void
     */
    protected function showError(string $message, int $exit = 0): void {
        Cli::err("{$this->pen->red}Error:{$this->pen->reset} $message");
        if ($exit > 0) exit($exit);
    }

    /**
     * Displays an informational message.
     *
     * @param string $message The message to be displayed.
     * @return void
     */
    protected function showInfo(string $message): void {
        $this->pen->blue->line("Info:{$this->pen->reset} $message");
    }

    /**
     * Displays a success message.
     *
     * @param string $message The success message to be displayed.
     * @return void
     */
    protected function showSuccess(string $message): void {
        $this->pen->green->line("Success:{$this->pen->reset} $message");
    }

    protected function showWarning(string $message): void {
        $this->pen->yellow->line("Warning:{$this->pen->reset} $message");
    }

    protected function showDebug(string $message): void {
        $this->pen->purple->line("Debug:{$this->pen->reset} $message");
    }

    protected function showNotice(string $message): void {
        $this->pen->white->line("Notice:{$this->pen->reset} $message");
    }

    #endregion console output

    #region gather information

    /**
     * Retrieves a list of package files that match the given pattern.
     *
     * @param string $pattern The pattern to match files against.
     *                         This can include wildcards or other matching rules.
     * @return array An array of file paths that match the specified pattern.
     */
    protected function getPackageFiles(string $pattern): array {
        $files = $this->path->getFiles($pattern);
        if (empty($files)) $this->showError('No possible package upload files found.', 10);

        $items = [];
        foreach ($files as $file) $items[(string)$file] = $file->getBasename();

        return $items;
    }

    /**
     * Retrieves the package name as a string.
     *
     * @return string The name of the package.
     */
    protected function getPackage(): string {
        $items = $this->getPackageFiles($this->config->archive->pattern);
        return Cli::menu($items, array_values($items)[0], 'Please pick a file to upload', 1);
    }

    /**
     * Retrieves the version of a package from the specified file.
     *
     * @param string $file The path to the file containing the package version information.
     * @return string The version of the package.
     */
    protected function getPackageVersion(string $file): string {
        $array = explode('/', $file);
        $fn = basename(end($array)  , '.zip');
        [$packageName, $version] = explode('-', $fn);

        return $version;
    }

    /**
     * Retrieves the vendor name.
     *
     * @return string The name of the vendor.
     */
    protected function getVendor(): string {
        $vendors = array_merge($this->config->customise->vendors->toArray(), ['other']);
        $vendors = array_combine($vendors, $vendors);

        $vendor = Cli::menu($vendors, array_values($vendors)[0], 'Please pick vendor', 1);
        if ($vendor === 'other') $vendor = Cli::prompt('Please enter vendor name', $vendors[0]);

        return $vendor;
    }

    #endregion gather information

    #region manage package

    /**
     * Uploads a package to the repository.
     *
     * @param string $file    The path to the package file to be uploaded.
     * @param string $version The version of the package being uploaded.
     * @param string $vendor  The vendor associated with the package.
     *
     * @return bool Returns true on successful upload, false otherwise.
     */
    protected function uploadPackage(string $file, string $version, string $vendor): bool {
        // Building curl command
        $cmd_upload = "curl -k -X 'PUT' -u {$this->config->server->username}:{$this->config->server->password} -H 'Content-Type: application/octet-stream' --upload-file \"$file\" {$this->config->server->protocol}://{$this->config->server->host}:{$this->config->server->port}/api/packages/{$vendor}/composer?version={$version}";
        if ($this->config->develop->verbose) Cli::line('Upload command:' . PHP_EOL . str_replace($this->config->server->password, '*****', $cmd_upload));

        // Run command
        if ($this->config->tweaks->autoUpload || Cli::confirm('Do you want to run this command?', false)) {
            $this->pen->red->line('Running command...');
            if (!$this->config->develop->dryRun) shell_exec($cmd_upload);
            return true;
        }

        return false;
    }

    /**
     * Deletes a specified package file.
     *
     * @param string $file The path to the package file to be deleted.
     * @return bool Returns true if the file was successfully deleted, false otherwise.
     */
    protected function deletePackage(string $file): bool {
        if ($this->config->tweaks->autoDelete || Cli::confirm("Delete file [$file]?", true)) {
            $this->pen->red->line("Deleting file... {$this->pen->blue}$file");
            if (!$this->config->develop->dryRun) new File($file)->remove();
            return true;
        }

        return false;
    }

    #endregion manage package

    /**
     * Executes the main logic of the class.
     *
     * This method is the entry point for running the functionality
     * encapsulated within the class. It does not take any parameters
     * and does not return any value.
     *
     * @return void
     */
    public function run(): void {
        if ($this->config->develop->dryRun) $this->showInfo('Running in dry run mode. No changes will be made to uploads or deletes.');

        Cli::line();
        $file = $this->getPackage();
        $version = $this->getPackageVersion($file);

        Cli::line();
        $vendor = $this->getVendor();

        Cli::line();
        $uploaded = $this->uploadPackage($file, $version, $vendor);

        Cli::line();
        $deleted = $this->deletePackage($file);

        Cli::line();
        $this->showSuccess($uploaded ? 'New package uploaded.' : 'Finished.');
    }
}
