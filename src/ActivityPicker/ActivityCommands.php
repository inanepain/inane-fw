<?php

/**
 * inane-fw
 *
 * Inane Framework
 *
 * $Id$
 * $Date$
 *
 * PHP version 8.5
 *
 * @author   Philip Michael Raab <philip@cathedral.co.za>
 * @package  inanepain\PROJECT
 * @category PROJECT
 *
 * @license  UNLICENSE
 * @license  https://unlicense.org/UNLICENSE UNLICENSE
 *
 * _version_ $version
 *
 */

declare(strict_types = 1);

namespace Knot\ActivityPicker;

use Inane\Cli\Cli;
use Inane\Cli\Pencil;
use Inane\Cli\Pencil\{
    Colour,
    Style};
use Inane\Config\ConfigAware\ConfigAwareAttribute;
use Inane\Config\ConfigAware\ConfigAwareTrait;
use Inane\Config\ConfigManager;
use Inane\Console\Command\{
    Argument,
    Command,
    Option};
use Inane\Datetime\Timespan;
use Random\RandomException;

/**
 * Trait ConfigAwareTrait
 *
 * Provides functionality for managing configuration settings
 * within a class. This trait allows the storage, retrieval,
 * and existence checks for configuration data in a reusable way.
 *
 * Implementing classes can utilize this trait to handle
 * configuration-related functionality without duplicating logic.
 *
 * Methods:
 * - setConfig: Sets a specific configuration key-value pair or an array of configurations.
 * - getConfig: Retrieves the value of a configuration key or returns all configurations.
 * - hasConfig: Checks if a specific configuration key exists.
 * - clearConfig: Removes a specific configuration key or clears all configurations.
 */
#[ConfigAwareAttribute]
class ActivityCommands {
    /**
     * Trait ConfigAwareTrait
     *
     * Provides functionality for managing configuration settings
     * within a class. This trait allows the storage, retrieval,
     * and existence checks for configuration data in a reusable way.
     *
     * Implementing classes can utilize this trait to handle
     * configuration-related functionality without duplicating logic.
     *
     * Methods:
     * - setConfig: Sets a specific configuration key-value pair or an array of configurations.
     * - getConfig: Retrieves the value of a configuration key or returns all configurations.
     * - hasConfig: Checks if a specific configuration key exists.
     * - clearConfig: Removes a specific configuration key or clears all configurations.
     */
    use  ConfigAwareTrait;

    private ?int $timeout = null;

    /**
     * Retrieves the cyan pen instance. If not already initialized, creates a new instance
     * of the Pencil class with the color set to Cyan.
     */
    protected Pencil $penCyan {
        get => $this->penCyan ?? $this->penCyan = new Pencil(colour: Colour::Blue, style: Style::Plain);
    }

    /**
     *
     * @return void
     */
    public function __construct() {
        ConfigManager::instance()
            ->setConfigFor($this)
        ;
    }

    #region DISPLAY

    /**
     * Displays activities from the provided activity picker instance.
     *
     * @param ActivityPicker $ap The activity picker instance used to retrieve and display activities.
     *
     * @return void
     * @throws RandomException
     */
    protected function displayActivities(ActivityPicker $ap): void {
        $stub = '';
        if ($this->timeout !== null) {
            Cli::line('Questions are automatically shown after: ' . new Timespan($this->timeout)->duration());
            $stub = "\n";
        }
        $i = 0;
        while(!$ap->end) {
            if ('q' === Cli::promptStreamSelect(question: 'Enter for activity (q to quit)', timeout: $this->timeout)) break;
            Cli::out($stub . "\t" . ++$i . '. ');
            $this->penCyan->line($ap->pick());
        }
    }
    #endregion DISPLAY

    #region Commands
    /**
     * Handles the "activity:picker" console command to select a default set of activities.
     *
     * @param string|null $activities    An optional argument specifying the activity list to use, or null to use the default list.
     * @param int|null    $numberOfPicks An optional option specifying the number of activities to pick. If not provided, no limit is set.
     *
     * @return int Returns 0 on successful execution of the command.
     * @throws RandomException
     */
    #[Command('activity:picker', 'Pick default activities', ['ap'])]   // Constructor method for initialising a console command with a name, description, and aliases.
    public function activitiesCommand(
        #[Argument('Activity list: null for default', required: false, default: null)]   // Command line argument constructor.
        ?string $activities = null,

        #[Option('picks', 'p', 'Number of picks required', default: null, valueless: false)]
        ?int $numberOfPicks = null,

        #[Option('timeout', 't', 'Number of seconds before automatically showing next pick', default: null, valueless: false)]
        ?int $timeout = null,
    ): int {
        $list = null;
        $this->timeout = $timeout;

        if ($activities !== null && $this->config->lists->has($activities)) {
            $list = $this->config->lists->get($activities);
        } else {
            $activities = null;
        }

        $setDebug = false;

        $ap = new ActivityPicker(activities: $list, options: [
            'debug' => [
                'totalActivities' => $setDebug,
                'options'         => $setDebug,
                'rangeSize'       => $setDebug,
            ],
        ]);

        if ($numberOfPicks !== null) {
            $ap->setNumberOfPicks($numberOfPicks, true);
        }

        Cli::line('Displaying activities: ' . $activities ?? 'default');
        $this->displayActivities($ap);

        return 0;
    }
    #endregion Commands
}
