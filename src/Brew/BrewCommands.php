<?php

/**
 * Playground: develop
 *
 * Rough environment for testing, developing and playing around with PHP odds and ends.
 *
 * $Id$
 * $Date$
 *
 * PHP version 8.4
 *
 * @author   Philip Michael Raab<philip@cathedral.co.za>
 * @package  playground\develop
 * @category develop
 *
 * @license  UNLICENSE
 * @license  https://unlicense.org/UNLICENSE UNLICENSE
 *
 * _version_ $version
 */

declare(strict_types = 1);

namespace Knot\Brew;

use Inane\Cache\RemoteFileCache;
use Inane\Cli\{
    Cli,
    Pencil,
    Progress\Bar,
    TextTable};
use Inane\Console\Command\{
    Argument,
    Command,
    Option};
use Inane\Datetime\Unit\Hours;
use Inane\File\File;
use Inane\Stdlib\{
    Exception\Exception,
    Exception\JsonException,
    Exception\RuntimeException,
    Json,
    Options};
use Knot\Application;
use Knot\Db\Entity\Formula;
use Knot\Db\Table\FormulasTable;
use Psr\SimpleCache\InvalidArgumentException;
use function array_diff;
use function array_filter;
use function array_unique;
use function asort;
use function count;
use function exec;
use function explode;
use function implode;
use function sort;
use function str_contains;

/**
 * The BrewCommands class provides functionality for interacting with and managing
 * a local database of Homebrew formulas. It includes methods for formatting messages,
 * printing formula details, and updating the local formula database by syncing with
 * the remote Homebrew API.
 *
 * regex 1:
 * - search: ^
 * - replace: - [ ]
 *
 * regex 2:
 * - search: (\] )([a-z0-9-@\.]*)
 * - replace: $1**$2**
 */

class BrewCommands {
    //#region Class Constants
    /**
     * URL endpoint for accessing the formulae API data.
     */
    public const string FEED_URL = 'https://formulae.brew.sh/api/formula.json';
    //#endregion Class Constants
    //#region Properties
    protected Brew $brew;
    /**
     * Defines the length of the message.
     */
    protected int $messageLength = 10;
    #region Pencil
    protected Pencil $blue;     // Pencil: Output assigned a colour and style.
    protected Pencil $cyan;     // Pencil: Output assigned a colour and style.
    protected Pencil $green;    // Pencil: Output assigned a colour and style.
    protected Pencil $purple;   // Pencil: Output assigned a colour and style.
    protected Pencil $red;      // Pencil: Output assigned a colour and style.
    protected Pencil $yellow;   // Pencil: Output assigned a colour and style.
    protected Pencil $dim;      // Pencil: Output assigned a colour and style.
    protected Pencil $reset;
    //#endregion Properties    // Pencil: Output assigned a colour and style.
    #endregion Pencil

    protected Options $config {
        get => $this->brew->getConfig();
    }

    /**
     * Creates a new BrewCommands instance.
     *
     * @throws Exception
     */
    public function __construct() {
        $this->brew = Application::app()->createObject(Brew::class);

        $this->blue = new Pencil(Pencil\Colour::Blue, Pencil\Style::Italic);   // Pencil constructor

        $this->cyan = new Pencil(Pencil\Colour::Cyan);                           // Pencil constructor
        $this->green = new Pencil(Pencil\Colour::Green);                         // Pencil constructor
        $this->purple = new Pencil(Pencil\Colour::Purple);                       // Pencil constructor
        $this->red = new Pencil(Pencil\Colour::Red);                             // Pencil constructor
        $this->yellow = new Pencil(Pencil\Colour::Yellow, Pencil\Style::Bold);   // Pencil constructor

        $this->dim = new Pencil(Pencil\Colour::Blue, Pencil\Style::Dim);   // Pencil constructor

        $this->reset = new Pencil(Pencil\Colour::Default);   // Pencil constructor
    }

    #region Display Formatting

    /**
     * Formats a given message by ensuring it meets the specified length.
     * If the message is shorter than the required length, it is padded.
     * If the current message length exceeds the specified length, it adjusts accordingly.
     *
     * @param string $message The message to format.
     * @param int    $length  The desired length of the message. Defaults to 10.
     *
     * @return string The formatted message with appropriate padding.
     */
    protected function formatMessage(string $message, int $length = 10): string {
        if ($this->messageLength > $length) $length = $this->messageLength;                      // Formats a given message by ensuring it meets the specified length. | Defines the length of the message.
        if (strlen($message) > $length) $this->messageLength = $length = strlen($message) + 0;   // Formats a given message by ensuring it meets the specified length. | Get string length

        return str_pad($message, $length, ' ');   // Pad a string to a certain length with another string
    }

    /**
     * Outputs the details of a given formula to the CLI.
     * The printed details include the formula's name, version, and description.
     *
     * @param Formula $formula  The formula object containing name, version, and description attributes.   // Formula
     * @param bool    $extended If true, prints additional details such as tags and status. Defaults to false.
     *
     * @return void No value is returned.
     */
    protected function printFormula(Formula $formula, bool $extended = false): void {   // Formula
        $state = '';
        $tags = '';
        if ($extended || $this->config->info->extended) {                                                                  // Outputs the details of a given formula to the CLI.
            if ($formula->state === 'new') $state .= $this->purple->format('*');               // Create a string with the current format
            if ($formula->installed) $state .= $this->blue->format('(i)');                     // Create a string with the current format
            $tags = $formula->tags === '' ? '' : $this->purple->format(" [$formula->tags]");   // Create a string with the current format
        }

        $name = $formula->name;
        if ($formula->flag) {
            $name .= $this->purple->format($this->config->ui->icon->flag);
        }

        Cli::line('- ' . $state . "$name ({$formula->version}) " . $this->dim->format($formula->desc) . $tags);   // Outputs a line of text to the CLI.
    }
    #endregion Display Formatting

    #region Formula Commands
    /**
     * Modifies properties or retrieves information about specified brew packages.
     * Supports multiple actions including installation, uninstallation, flagging,
     * unflagging, hiding, unhiding, or displaying detailed package information.
     *
     * @param string $action      The action to perform on the packages.
     *                            Supported actions are: install, uninstall, flag, unflag, hide, unhide, info.
     *                            Defaults to 'info'.
     * @param string ...$packages A list of package names to apply the action to.
     *
     * @return int Returns 0 upon successful execution of the specified action on the given packages.
     */
    #[Command('brew:attrib', 'Modify package properties', ['bha'])]   // Constructor method for initialising a console command with a name, description, and aliases.
    public function attribCommand(
        #[Argument('Actions: install, uninstall, flag, unflag, hide, unhide, info', required: true, default: 'info')]   // Command line argument constructor.
        string $action = 'info',

        #[Argument('List of packages', required: true)]   // Command line argument constructor.
        string ...$packages,
    ): int {
        $formulas = $this->brew->getPackages(...$packages);   // Retrieves a list of packages from the formulas table based on the provided package names.
        foreach($formulas as $formula) {
            Cli::line('Brew package: ' . $formula->name);   // Outputs a line of text to the CLI.

            if ($action === 'install') {                                  // Modifies properties or retrieves information about specified brew packages.
                $formula->installed = true;                               // @var bool If the formula is installed.
                Cli::line($this->blue . "\tInstalled." . $this->reset);   // Outputs a line of text to the CLI.
            } elseif ($action === 'uninstall') {                            // Modifies properties or retrieves information about specified brew packages.
                $formula->installed = false;                                // @var bool If the formula is installed.
                Cli::line($this->blue . "\tUninstalled." . $this->reset);   // Outputs a line of text to the CLI.
            } elseif ($action === 'flag') {   // Modifies properties or retrieves information about specified brew packages.
                $formula->flag = true;
                Cli::line($this->blue . "\tFlagged." . $this->reset);   // Outputs a line of text to the CLI.
            } elseif ($action === 'unflag') {   // Modifies properties or retrieves information about specified brew packages.
                $formula->flag = false;
                Cli::line($this->blue . "\tUnflagged." . $this->reset);   // Outputs a line of text to the CLI.
            } elseif ($action === 'hide') {   // Modifies properties or retrieves information about specified brew packages.
                if (!str_contains($formula->tags, 'hide')) {   // Checks if $needle is found in $haystack and returns a boolean value
                    $tags = explode(',', $formula->tags);      // Split a string by a string
                    $tags[] = 'hide';
                    sort($tags);                                           // Sort an array
                    $formula->tags = implode(',', array_unique($tags));    // Join array elements with a string
                    Cli::line($this->blue . "\tHidden." . $this->reset);   // Outputs a line of text to the CLI.
                }
            } elseif ($action === 'unhide') {   // Modifies properties or retrieves information about specified brew packages.
                if (str_contains($formula->tags, 'hide')) {                  // Checks if $needle is found in $haystack and returns a boolean value
                    $tags = explode(',', $formula->tags);                    // Split a string by a string
                    $tags = array_diff($tags, ['hide']);                     // Computes the difference of arrays
                    sort($tags);                                             // Sort an array
                    $formula->tags = implode(',', array_unique($tags));      // Join array elements with a string
                    Cli::line($this->blue . "\tUnhidden." . $this->reset);   // Outputs a line of text to the CLI.
                }
            } else {
                $this->printFormula($formula, true);   // Outputs the details of a given formula to the CLI.
            }

            $formula->save();   // Saves the current entity to the database.
        }

        return 0;
    }

    /**
     * Displays statistical information about brew formulas, including totals, installed, hidden, and those pending review.
     *
     * @return int Exit code of the command execution, where 0 indicates success.
     */
    #[Command('brew:stats', 'Show stats for formulas')]   // Constructor method for initialising a console command with a name, description, and aliases.
    public function statsCommand(): int {
        Cli::line('Brew formula stats');   // Outputs a line of text to the CLI.

        $formulasTable = new FormulasTable();                                                                 // * Constructor for the AbstractTable class.
        $total = count($formulasTable->fetchAll());                                                           // Counts all elements in an array, or something in an object.
        $installed = count($formulasTable->find(['installed', 1]));                                           // Counts all elements in an array, or something in an object.
        $hidden = count($formulasTable->find(['column' => 'tags', 'value' => '%hide%', 'type' => 'like']));   // Counts all elements in an array, or something in an object.
        $review = count($formulasTable->find(['reviewed', 0]));                                               // Counts all elements in an array, or something in an object.
        $flagged = count($formulasTable->find(['flag', 1]));                                                  // Counts all elements in an array, or something in an object.

        $table = new TextTable();                                                     // TextTable Constructor
        $table->addHeader(['Total', 'Installed', 'Flagged', 'Hidden', 'Review']);     // Adds a header row
        $table->addRow(["$total", "$installed", "$flagged", "$hidden", "$review"]);   // Adds a row

        Cli::line($table->render());   // Outputs a line of text to the CLI.

        return 0;
    }

    /**
     * Updates the local database of Homebrew formulas by fetching and processing
     * the latest data from the specified feed URL. It identifies new and updated
     * formulas, updating their details in the database as needed. This method
     * also logs the changes and writes them to a JSON file for reference.
     *
     * @return int Returns 0 upon successful execution of the update process.
     *
     * @throws JsonException   // Exception thrown if JSON_THROW_ON_ERROR option is set for Json::encode() or Json::decode(). code contains the error type, for possible values see json_last_error().
     * @throws InvalidArgumentException   // Exception interface for invalid cache arguments.
     */
    #[Command('brew:update', 'Update local homebrew formula database', ['hbu'])]   // Constructor method for initialising a console command with a name, description, and aliases.
    public function updateDbCommand(): int {
        $formulasTable = new FormulasTable();               // * Constructor for the AbstractTable class.
        $formulas = $formulasTable->fetchAll();             // FormulasTable
        Cli::line('Total formulas: ' . count($formulas));   // Outputs a line of text to the CLI.

        $ttl = Hours::hours(1)->seconds->unit;
        $rfc = new RemoteFileCache(defaultTTL: $ttl);   // Remote File Cache Constructor

        $json = $rfc->get(self::FEED_URL);                     // Fetches a value from the cache.
        $feeds = Json::decode($json, ['asOptions' => true]);   // Takes a JSON encoded string and converts it into a PHP value.

        Cli::line('Total formulas: ' . $feeds->count());   // Outputs a line of text to the CLI.

        $bar = new Bar('Formula', $feeds->count());   // * Instantiates a Progress Notifier.
        $bar->display();                              // * Prints the progress bar to the screen with percent complete, elapsed time

        $changes = [
            'updated' => [],
            'new'     => [],
        ];

        foreach($feeds as $feed) {
            if ($feed->get('name') !== $feed->get('full_name')) {
                Cli::line($feed->get('name') . "({$feed->get('full_name')})");   // Outputs a line of text to the CLI.
            }

            $f = $formulasTable->fetch($feed->get('name'));   // FormulasTable

            if ($f) {
                if (version_compare($feed->get('versions')
                        ->get('stable'), $f->version) > 0) {   // Compares two "PHP-standardized" version number strings
                    $f->version = $feed->get('versions')
                        ->get('stable')
                    ;                                          // @var string The version of the formula.
                    $f->state = 'update';

                    $tags = explode(',', $f->tags);   // Split a string by a string
                    if (!$f->installed && !in_array('hide', $tags)) {   // @var bool If the formula is installed. | Checks if a value exists in an array
                        $f->reviewed = false;
                    }

                    $f->save();   // Saves the current entity to the database.
                    $changes['updated'][] = $f;
                }
            } else {
                $f = new Formula([   // Constructor for the AbstractEntity class.
                    'name'     => $feed->get('name'),
                    'version'  => $feed->get('versions')
                        ->get('stable'),
                    'homepage' => $feed->get('homepage'),
                    'desc'     => $feed->get('desc'),
                    'reviewed' => false,
                    'state'    => 'new',
                ]);
                $f->save();   // Saves the current entity to the database.
                $changes['new'][] = $f;
            }

            $bar->tick(1, $this->formatMessage($feed->get('name')));   // * This method augments the base definition from cli\Notify to optionally
        }
        $bar->finish();   // * Forces the current tick count to the total ticks given at instatiation

        if (!empty($changes['updated']) || !empty($changes['new'])) {   // Determine whether a variable is considered to be empty. A variable is considered empty if it does not exist or if its value
            $write = [];
            if (!empty($changes['updated'])) {                         // Determine whether a variable is considered to be empty. A variable is considered empty if it does not exist or if its value
                Cli::line('Updated: ' . count($changes['updated']));   // Outputs a line of text to the CLI.
                foreach($changes['updated'] as $formula) {
                    $write[$formula->name] = $formula->desc;
                    $this->printFormula($formula, true);   // Outputs the details of a given formula to the CLI.
                }
            }
            Cli::line();   // Outputs a line of text to the CLI.
            if (!empty($changes['new'])) {                     // Determine whether a variable is considered to be empty. A variable is considered empty if it does not exist or if its value
                Cli::line('New: ' . count($changes['new']));   // Outputs a line of text to the CLI.
                foreach($changes['new'] as $formula) {
                    $write[$formula->name] = $formula->desc;
                    $this->printFormula($formula);   // Outputs the details of a given formula to the CLI.
                }
            }

            $fileNameDate = date('Y-m-d_H-i-s');   // Format a local time/date

            $file = new File("data/formulas/changes-{$fileNameDate}.json");   // * FileInfo
            $file->write(Json::encode($write));                               // * Writes the given contents to the file.
        }

        return 0;
    }

    /**
     * Updates the installed status of brew formulas by fetching the list of installed formulas,
     * updating their status in the database, and displaying a summary of the process.
     *
     * @return int Returns 0 upon successful execution of the method.
     */
    #[Command('brew:sync', 'Sync installed status for packages', ['hbs'])]   // Constructor method for initialising a console command with a name, description, and aliases.
    public function syncInstalledCommand(): int {
        Cli::line('Brew formulas installed: updating...');   // Outputs a line of text to the CLI.

        $cmd = 'brew list --formulae -1';
        exec($cmd, $output);                    // Execute an external program
        $formulasTable = new FormulasTable();   // * Constructor for the AbstractTable class.

        $counter = new Options([                                                                                   // Options
            'total'     => count($output ?: []),
            // Counts all elements in an array, or something in an object.
            'installed' => 0,
        ]);

        foreach($output ?: [] as $formula) {
            $f = $formulasTable->fetch($formula);   // FormulasTable
            if (!$f) continue;
            $f->installed = true;   // @var bool If the formula is installed.
            $f->save();             // Saves the current entity to the database.
            $counter->installed++;
        }

        Cli::line('Brew formulas installed: updated.');                                        // Outputs a line of text to the CLI.
        Cli::line("Processed {$counter->total} formulas, {$counter->installed} installed.");   // Outputs a line of text to the CLI.

        return 0;
    }

    /**
     * Filters the Homebrew formulas based on specified criteria and displays the matching formulas.
     * It allows filtering by installed status, review status, flagged status, specific tags, and
     * optionally includes extra information about each formula.
     *
     * @param string $tag       Optional tag filter to match formulas by tags.
     * @param bool   $installed Whether to include only installed formulas. Defaults to false.
     * @param bool   $review    Whether to include formulas marked for review. Defaults to false.
     * @param bool   $flag      Whether to include flagged formulas. Defaults to false.
     * @param bool   $extra     Whether to display extra information for each formula. Defaults to false.
     *
     * @return int Returns 0 upon successful execution of the filter process.
     */
    #[Command('brew:filter', 'Filter formulas', ['hbf'])]   // Constructor method for initialising a console command with a name, description, and aliases.
    public function filterCommand(
        #[Argument('Tag filter', required: false)]   // Command line argument constructor.
        string $tag = '',

        #[Option('installed', 'i', 'Installed', valueless: true)]   // Constructor method to initialize the class with specific properties.
        bool $installed = false,

        #[Option('uninstalled', 'u', 'Not currently installed', valueless: true)]   // Constructor method to initialize the class with specific properties.
        bool $uninstalled = false,

        #[Option('reviewed', 'r', 'For review', valueless: true)]   // Constructor method to initialize the class with specific properties.
        bool $review = false,

        #[Option('flagged', 'f', 'Flagged items', valueless: true)]   // Constructor method to initialize the class with specific properties.
        bool $flag = false,

        #[Option('extra', 'e', 'Extra information', valueless: true)]   // Constructor method to initialize the class with specific properties.
        bool $extra = false,
    ): int {
        Cli::line('Show brew formulas:');   // Outputs a line of text to the CLI.

//        Dumper::$enabled = true;
//        dd($this->config->toArray(), 'config', ['parseDepth' => 10]);

        $where = [];
        if ($uninstalled) {   // Filters the Homebrew formulas based on specified criteria and displays the matching formulas.
            $where[] = ['installed', 0];
            Cli::line(' - Uninstalled:');   // Outputs a line of text to the CLI.
        }
        if ($installed) {   // Filters the Homebrew formulas based on specified criteria and displays the matching formulas.
            $where[] = ['installed', 1];
            Cli::line(' - Installed:');   // Outputs a line of text to the CLI.
        }
        if ($flag) {   // Filters the Homebrew formulas based on specified criteria and displays the matching formulas.
            $where[] = ['flag', 1];
            Cli::line(' - Flagged:');   // Outputs a line of text to the CLI.
        }
        if ($review) {   // Filters the Homebrew formulas based on specified criteria and displays the matching formulas.
            $where[] = ['reviewed', 0];
            Cli::line(' - For review:');   // Outputs a line of text to the CLI.
        }
        if (!empty($tag)) {   // Determine whether a variable is considered to be empty. A variable is considered empty if it does not exist or if its value
            $where[] = ['column' => 'tags', 'value' => '%' . $tag . '%', 'type' => 'like'];
            Cli::line(' - Tag: ' . $tag);   // Outputs a line of text to the CLI.
        }

        $formulasTable = new FormulasTable();       // * Constructor for the AbstractTable class.
        $formulas = $formulasTable->find($where);   // FormulasTable

        Cli::line('Total formulas: ' . (string)count($formulas));   // Outputs a line of text to the CLI.
        foreach($formulas as $formula) {
            $this->printFormula($formula, $extra);   // Outputs the details of a given formula to the CLI.
        }

        return 0;
    }

    /**
     * Reviews new or updated Homebrew formulas by presenting them one at a time for user interaction.
     * The user is given options to perform actions such as viewing the homepage, marking the formula
     * as installed, hiding it from future reviews, or exiting the review process. The formulas are
     * updated based on the user's selections.
     *
     * @return int Returns 0 upon successfully completing or exiting the review process.
     *
     * @throws RuntimeException
     */
    #[Command('brew:review', 'Review new/updated formulas', ['hbr'])]   // Constructor method for initialising a console command with a name, description, and aliases.
    public function reviewCommand(): int {
        if ($this->brew->autoUpdate()) {
            Cli::line('Brew formulas auto update: triggered...');
            try {
                $this->updateDbCommand();
            } catch (JsonException|InvalidArgumentException $e) {
                Cli::err($e->getMessage());
                return $e->getCode();
            }
        }

        $formulas = $this->brew->getReview();

        $total = $formulas->count();   // count
        $current = 0;
        $width = strlen((string)$total);   // Get string length

        $menu = [
            'exit'     => 'End review',
            'next'     => 'Next',
            'homepage' => 'Open homepage',
            'install'  => 'Install',
            'flag'     => 'Flag to investigate further',
            'hide'     => 'Hide from future reviews, calls next',
        ];

        $action = $this->config->review->action === 'hide' ? 'hide' : 'next';

        Cli::line('Formulas to review: ' . (string)$total);   // Outputs a line of text to the CLI.

        foreach($formulas as $formula) {
            $this->cyan->out('- ' . str_pad(string: (string)++$current, length: $width, pad_type: STR_PAD_LEFT) . '/' . (string)$total . ' ');   // Write to STDOUT ending on the same line.
            $this->printFormula($formula, true);                                                                                                 // Outputs the details of a given formula to the CLI.

            while(($choice = Cli::menu($menu, $action, 'Choose an option')) !== 'next') {   // Displays an array of strings as a menu where a user can enter a number to
                if ($choice === 'exit') {
                    $this->red->line('Review cancelled.');   // Write to STDOUT ending on a newline.
                    break 2;
                }

                if ($choice === 'install') {
                    Cli::line($this->blue . "\tInstalled." . $this->reset);   // Outputs a line of text to the CLI.
                    $formula->installed = true;
                } elseif ($choice === 'homepage') {
                    Cli::line($this->blue . "\tOpening homepage: {$formula->homepage}" . $this->reset);   // Outputs a line of text to the CLI.
                    shell_exec("open {$formula->homepage}");                                              // Execute command via shell and return the complete output as a string
                } elseif ($choice === 'flag') {
                    Cli::line($this->blue . "\tFlagged as an item to investigate further." . $this->reset);   // Outputs a line of text to the CLI.
                    $formula->flag = true;
                } elseif ($choice === 'hide') {
                    Cli::line($this->blue . "\tHiding from future reviews." . $this->reset);   // Outputs a line of text to the CLI.

                    $tags = explode(',', $formula->tags);   // Split a string by a string
                    $tags[] = 'hide';
                    sort($tags);                             // Sort an array
                    $tags = array_unique($tags);             // Removes duplicate values from an array
                    $tags = array_filter($tags, 'strlen');   // Iterates over each value in the <b>array</b>
                    $formula->tags = implode(',', $tags);    // Join array elements with a string
                    $formula->reviewed = true;
                    $formula->save();
                    break;
                }
            }

            $formula->reviewed = true;
            $formula->save();
        }

        return 0;
    }

    /**
     * Lists all tags currently in use, optionally sorting them by their usage count, and displays
     * the information in a table format including tag count and usage statistics.
     *
     * @param bool $usage Whether to sort the tags by their usage count. Defaults to false.
     *
     * @return int Returns 0 upon successful execution of the tag listing process.
     */
    #[Command('brew:tags', 'List all tags in use', ['bht'])]   // Constructor method for initialising a console command with a name, description, and aliases.
    public function tagsCommand(
        #[Option('usage', 'u', 'Sort by usage count', valueless: true)]   // Constructor method to initialize the class with specific properties.
        bool $usage = false,
    ): int {
        Cli::line('Brew tags:');   // Outputs a line of text to the CLI.

        $tags = $this->brew->getTags();
        if ($usage) asort($tags);   // Lists all tags currently in use, optionally sorting them by their usage count, and displays | Sort an array and maintain index association

        $table = new TextTable();                       // TextTable Constructor
        $table->addHeader(['Count', 'Tag', 'Usage']);   // Adds a header row

        $current = 1;
        Cli::line('Tagged formulas: ' . (string)$this->brew->getTagged()
                ->count());                                                                              // Outputs a line of text to the CLI.
        foreach($tags as $tag => $count) $table->addRow([(string)($current++), $tag, (string)$count]);   // Adds a row

        Cli::line($table->render());   // Outputs a line of text to the CLI.

        return 0;
    }
    #endregion Formula Commands
}
