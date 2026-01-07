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
use Inane\Cli\Cli;
use Inane\Cli\Pencil;
use Inane\Cli\Progress\Bar;
use Inane\Cli\TextTable;
use Inane\Console\Command\Argument;
use Inane\Console\Command\Command;
use Inane\Console\Command\Option;
use Inane\Datetime\Unit\Hours;
use Inane\File\File;
use Inane\Stdlib\Exception\JsonException;
use Inane\Stdlib\Json;
use Inane\Stdlib\Options;
use Knot\Db\Entity\Formula;
use Knot\Db\Table\FormulasTable;
use Psr\SimpleCache\InvalidArgumentException;
use function array_filter;
use function count;
use function exec;
use function explode;
use function sort;

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
    protected Brew $brew;

    /**
     * URL endpoint for accessing the formulae API data.
     */
    public const string FEED_URL = 'https://formulae.brew.sh/api/formula.json';
    /**
     * Defines the length of the message.
     */
    protected int $messageLength = 10;

    protected Pencil $blue;

    protected Pencil $cyan;
    protected Pencil $green;
    protected Pencil $purple;
    protected Pencil $red;
    protected Pencil $yellow;

    protected Pencil $dim;

    protected Pencil $reset;

    /**
     * Creates a new BrewCommands instance.
     *
     * @return void
     */
    public function __construct() {
        $this->brew = new Brew();

        $this->blue = new Pencil(Pencil\Colour::Blue, Pencil\Style::Italic);

        $this->cyan = new Pencil(Pencil\Colour::Cyan);
        $this->green = new Pencil(Pencil\Colour::Green);
        $this->purple = new Pencil(Pencil\Colour::Purple);
        $this->red = new Pencil(Pencil\Colour::Red);
        $this->yellow = new Pencil(Pencil\Colour::Yellow, Pencil\Style::Bold);

        $this->dim = new Pencil(Pencil\Colour::Blue, Pencil\Style::Dim);

        $this->reset = new Pencil(Pencil\Colour::Default);
    }

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
        if ($this->messageLength > $length) $length = $this->messageLength;
        if (strlen($message) > $length) $this->messageLength = $length = strlen($message) + 0;

        return str_pad($message, $length, ' ');
    }

    /**
     * Outputs the details of a given formula to the CLI.
     * The printed details include the formula's name, version, and description.
     *
     * @param Formula $formula The formula object containing name, version, and description attributes.
     * @param bool    $extended If true, prints additional details such as tags and status. Defaults to false.
     *
     * @return void No value is returned.
     */
    protected function printFormula(Formula $formula, bool $extended = false): void {
        $state = '';
        $tags = '';
        if ($extended) {
            if ($formula->state === 'new') $state = $this->purple->format('*');
            $tags = $formula->tags === ''? '' : $this->purple->format(" [$formula->tags]");
        }

        $name = $formula->flag ? $this->purple->format($formula->name) : $formula->name;

        Cli::line('- ' . $state . "$name ({$formula->version}) " . $this->dim->format($formula->desc) . $tags);
    }

    /**
     * Displays statistical information about brew formulas, including totals, installed, hidden, and those pending review.
     *
     * @return int Exit code of the command execution, where 0 indicates success.
     */
    #[Command('brew:stats', 'Show stats for formulas')]
    public function formulaStats(): int {
        Cli::line('Brew formula stats');

        $formulasTable = new FormulasTable();
        $total = count($formulasTable->fetchAll());
        $installed = count($formulasTable->find(['installed', 1]));
        $hidden = count($formulasTable->find(['column' => 'tags', 'value' => '%hide%', 'type' => 'like']));
        $review = count($formulasTable->find(['reviewed', 0]));
        $flagged = count($formulasTable->find(['flag', 1]));

        $table = new TextTable();
        $table->addHeader(['Total', 'Installed', 'Flagged', 'Hidden', 'Review']);
        $table->addRow(["$total", "$installed", "$flagged", "$hidden", "$review"]);

        Cli::line($table->render());

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
     * @throws JsonException
     * @throws InvalidArgumentException
     */
    #[Command('brew:update', 'Update local homebrew formula database', ['hbu'])]
    public function updateHomebrewDatabase(): int {
        $formulasTable = new FormulasTable();
        $formulas = $formulasTable->fetchAll();
        Cli::line('Total formulas: ' . count($formulas));

        $ttl = Hours::hours(1)->seconds->unit;
        $rfc = new RemoteFileCache(defaultTTL: $ttl);

        $json = $rfc->get(self::FEED_URL);
        $feeds = Json::decode($json, ['asOptions' => true]);

        Cli::line('Total formulas: ' . $feeds->count());

        $bar = new Bar('Formula', $feeds->count());
        $bar->display();

        $changes = [
            'updated' => [],
            'new' => [],
        ];

        foreach ($feeds as $feed) {
            if ($feed->get('name') !== $feed->get('full_name')) {
                Cli::line($feed->get('name') . "({$feed->get('full_name')})");
            }

            $f = $formulasTable->fetch($feed->get('name'));

            if ($f) {
                if (version_compare($feed->get('versions')->get('stable'), $f->version) > 0) {
                    $f->version = $feed->get('versions')->get('stable');
                    $f->state = 'update';

                    $tags = explode(',', $f->tags);
                    if (!$f->installed && !in_array('hide', $tags)) {
                        $f->reviewed = false;
                    }

                    $f->save();
                    $changes['updated'][] = $f;
                }
            } else {
                $f = new Formula([
                    'name' => $feed->get('name'),
                    'version' => $feed->get('versions')->get('stable'),
                    'homepage' => $feed->get('homepage'),
                    'desc' => $feed->get('desc'),
                    'reviewed' => false,
                    'state' => 'new'
                ]);
                $f->save();
                $changes['new'][] = $f;
            }

            $bar->tick(1, $this->formatMessage($feed->get('name')));
        }
        $bar->finish();

        if (!empty($changes['updated']) || !empty($changes['new'])) {
            $write = [];
            if (!empty($changes['updated'])) {
                Cli::line('Updated: ' . count($changes['updated']));
                foreach($changes['updated'] as $formula) {
                    $write[$formula->name] = $formula->desc;
                    $this->printFormula($formula, true);
                }
            }
            Cli::line();
            if (!empty($changes['new'])) {
                Cli::line('New: ' . count($changes['new']));
                foreach($changes['new'] as $formula) {
                    $write[$formula->name] = $formula->desc;
                    $this->printFormula($formula);
                }
            }

            $fileNameDate = date('Y-m-d_H-i-s');

            $file = new File("data/formulas/changes-{$fileNameDate}.json");
            $file->write(Json::encode($write));
        }

        return 0;
    }

    /**
     * Updates the installed status of brew formulas by fetching the list of installed formulas,
     * updating their status in the database, and displaying a summary of the process.
     *
     * @return int Returns 0 upon successful execution of the method.
     */
    #[Command('brew:sync', 'Sync installed status for packages', ['hbs'])]
    public function updateInstalledStatus(): int {
        Cli::line('Brew formulas installed: updating...');

        $cmd = 'brew list --formulae -1';
        exec($cmd, $output);
        $formulasTable = new FormulasTable();

        $counter = new Options([
            'total' => count($output ?: []),
            'installed' => 0
        ]);

        foreach($output ?: [] as $formula) {
            $f = $formulasTable->fetch($formula);
            if (!$f) continue;
            $f->installed = true;
            $f->save();
            $counter->installed++;
        }

        Cli::line('Brew formulas installed: updated.');
        Cli::line("Processed {$counter->total} formulas, {$counter->installed} installed.");

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
    #[Command('brew:filter', 'Filter formulas', ['hbf'])]
    public function filterFormulas(
        #[Argument('Tag filter', required: false)]
        string $tag = '',

        #[Option('installed', 'i', 'Installed', valueless: true)]
        bool   $installed = false,

        #[Option('reviewed', 'r', 'For review', valueless: true)]
        bool   $review = false,

        #[Option('flagged', 'f', 'Flagged items', valueless: true)]
        bool   $flag = false,

        #[Option('extra', 'e', 'Extra information', valueless: true)]
        bool   $extra = false,
    ): int {
        Cli::line('Show brew formulas:');

        $where = [];
        if ($installed) {
            $where[] = ['installed', 1];
            Cli::line(' - Installed:');
        }
        if ($flag) {
            $where[] = ['flag', 1];
            Cli::line(' - Flagged:');
        }
        if ($review) {
            $where[] = ['reviewed', 0];
            Cli::line(' - For review:');
        }
        if (!empty($tag)) {
            $where->addWhere(['column' => 'tags', 'value' => '%' . $tag . '%', 'type' => 'like']);
            Cli::line(' - Tag: ' . $tag);
        }

        $formulasTable = new FormulasTable();
        $formulas = $formulasTable->find($where);

        Cli::line('Total formulas: ' . (string)count($formulas));
        foreach($formulas as $formula) {
            $this->printFormula($formula, $extra);
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
     */
    #[Command('brew:review', 'Review new/updated formulas', ['hbr'])]
    public function reviewFormulas(): int {
        $formulas = $this->brew->getReview();

        $total = $formulas->count();
        $current = 0;
        $width = strlen((string)$total);

        $menu = [
            'exit' => 'End review',
            'next' => 'Next',
            'homepage' => 'Open homepage',
            'install' => 'Install',
            'flag' => 'Flag to investigate further',
            'hide' => 'Hide from future reviews, calls next',
        ];

        Cli::line('Formulas to review: ' . (string)$total);

        foreach($formulas as $formula) {
            $this->cyan->out('- ' . str_pad(string: (string)++$current, length: $width, pad_type: STR_PAD_LEFT) . '/' . (string)$total . ' ');
            $this->printFormula($formula, true);

            while (($choice = Cli::menu($menu, 'next', 'Choose an option')) !== 'next') {
                if ($choice === 'exit') {
                    $this->red->line('Review cancelled.');
                    break 2;
                }

                if ($choice === 'install') {
                    Cli::line($this->blue . "\tInstalled." . $this->reset);
                    $formula->installed = true;
                } elseif ($choice === 'homepage') {
                    Cli::line($this->blue . "\tOpening homepage: {$formula->homepage}" . $this->reset);
                    shell_exec("open {$formula->homepage}");
                } elseif ($choice === 'flag') {
                    Cli::line($this->blue . "\tFlagged as an item to investigate further." . $this->reset);
                    $formula->flag = true;
                } elseif ($choice === 'hide') {
                    Cli::line($this->blue . "\tHiding from future reviews." . $this->reset);

                    $tags = explode(',', $formula->tags);
                    $tags[] = 'hide';
                    sort($tags);
                    $tags = array_unique($tags);
                    $tags = array_filter($tags, 'strlen');
                    $formula->tags = implode(',', $tags);
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
     * Lists all tags currently in use along with their count and details.
     *
     * @return int Returns 0 on successful completion of the command.
     */
    #[Command('brew:tags', 'List all tags in use', ['bht'])]
    public function listTags(
        #[Option('usage', 'u', 'Sort by usage count', valueless: true)]
        bool $usage = false,
    ): int {
        Cli::line('Brew tags:');

        $tags = $this->brew->getTags();
        if ($usage) asort($tags);

        $table = new TextTable();
        $table->addHeader(['Count', 'Tag', 'Usage']);

        $current = 1;
        Cli::line('Tagged formulas: ' . (string)$this->brew->getTagged()->count());
        foreach($tags as $tag => $count)
            $table->addRow([(string)($current++), $tag, (string)$count]);

        Cli::line($table->render());

        return 0;
    }
}
