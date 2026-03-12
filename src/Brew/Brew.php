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

namespace Knot\Brew;

use Inane\Config\ConfigAware\{
    ConfigAwareAttribute,
    ConfigAwareTrait};
use Inane\Datetime\Timestamp;
use Inane\Db\Query\Clause\OrderDirection;
use Inane\Stdlib\{
    Array\OptionsInterface,
    Exception\RuntimeException,
    Options};
use Knot\Db\Entity\Formula;
use Knot\Db\Table\FormulasTable;

use function array_filter;
use function array_key_exists;
use function explode;
use function ksort;
use function passthru;

/**
 * Trait ConfigAwareTrait
 *
 * Provides functionality for managing configuration settings
 * within a class. This trait facilitates the storage, retrieval,
 * and existence checks of configuration data, enabling reusable logic.
 *
 * Methods:
 * - setConfig: Assigns a key-value pair or multiple configurations at once.
 * - getConfig: Retrieves a specific configuration value or all configurations.
 * - hasConfig: Verifies the presence of a particular configuration key.
 * - clearConfig: Deletes specific configuration key(s) or clears all configurations.
 */
#[ConfigAwareAttribute]
class Brew {
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

    /**
     * Holds a collection of event listeners to handle specific events.
     */
    protected Options $listeners;

    //#region Properties
    /**
     * A collection of tags associated with a specific item or entity.
     */
    public array $tags {
        get {
            if (!isset($this->tags)) {
                $formulas = $this->getTagged();
                $tags = [];
                foreach($formulas as $formula) {
                    $formula_tags = array_filter(explode(',', $formula->tags));
                    foreach($formula_tags as $tag) {
                        if (!array_key_exists($tag, $tags)) {
                            $tags[$tag] = 0;
                        }
                        $tags[$tag]++;
                    }
                }
                ksort($tags);

                $this->tags = $tags;
            }
            return $this->tags;
        }
    }

    /**
     * Default configuration settings for the application.
     */
    protected array $defaultConfig = [
        'ui'     => [
            /**
             * Flag icon to use for various statuses.
             */
            'icon' => [
                'flag' => '⚑', // ⛳️📌📍⚑⚐
                'new'  => '✷', // ✷✦✜
                'url'  => '☍', // ☍⎈
            ],
            /**
             * Text colour options.
             *
             * colours: black, red, green, blue, yellow, purple, cyan, white
             * styles: dim, bold, italic, underline, blink
             */
            'text' => [
                /**
                 * Action taken.
                 */
                'action'  => 'blue',
                /**
                 * Package description.
                 */
                'desc'    => 'blue',
                /**
                 * Webpage url.
                 */
                'url'     => 'underline green',
                /**
                 * Progress counter.
                 */
                'counter' => 'cyan',
                /**
                 * Tag text.
                 */
                'tag'     => 'purple',
                /**
                 * New indicator colour.
                 */
                'icon'    => 'purple',
                /**
                 * Alert message.
                 */
                'alert'   => 'red',
            ],
        ],
        'info'   => [
            /**
             * Whether to display extended information about formulae automatically.
             */
            'extended' => true,
        ],
        'review' => [
            /**
             * Default Action to take when reviewing formulae.
             * 'next' - Move to the next formula.
             * 'hide' - Hide the current formula.
             */
            'action'     => 'hide', // options: next, hide
            /**
             * Whether to automatically update the review status of formulae when reviewing.
             * - update if the last update ran longer than the value in seconds ago
             * - 0 - off
             */
            'autoupdate' => 3600,
        ],
        /**
         * Whether to perform a dry run without actually installing or updating formulae.
         */
        'dry-run' => false,
    ];

    /**
     * Stores cache data for various operations.
     */
    protected Options $cache;

    /**
     * Whether to perform a dry run without actually installing or updating formulae.
     */
    public bool $dryRun {
        get => $this->config->dryRun;
    }
    //#endregion Properties

    #region Instantiation
    /**
     * Constructor for the Brew class.
     *
     * Initializes the object with a FormulasTable instance and sets up a cache.
     *
     * @param FormulasTable $formulasTable An instance of the FormulasTable class to interact with the database.
     */
    public function __construct(
        private readonly FormulasTable $formulasTable = new FormulasTable(),
    ) {
        $this->cache = new Options();
        $this->cache->lockWriteError = false;

        $this->listeners = new Options();
        Formula::$brew = $this;
    }
    #endregion Instantiation

    #region Events
    /**
     * Registers an event listener for a specified event.
     *
     * @param string   $event    The name of the event to listen for.
     * @param callable $listener A callable to be executed when the event is triggered.
     *
     * @return void
     */
    public function on(string $event, callable $listener): void {
        if (!$this->listeners->has($event)) {
            $this->listeners->$event = [];
        }

        $this->listeners->$event[] = $listener;
    }

    /**
     * Triggers an event and notifies all registered listeners for that event.
     *
     * @param string $event The name of the event to trigger.
     * @param mixed  $value The value to pass to the event listeners.
     *
     * @return void
     */
    protected function trigger(string $event, mixed $value): void {
        if (!$this->listeners->has($event)) {
            $this->listeners->$event = [];
        }

        foreach ($this->listeners->$event as $listener) {
            $listener($value);
        }
    }

    /**
     * Retrieves the list of registered listeners.
     *
     * @return array Returns an array of registered listeners.
     */
    public function getListeners(): array {
        return $this->listeners->toArray();
    }
    #endregion Events

    #region Configuration
    /**
     * Retrieve the configuration options.
     *
     * @return OptionsInterface The configuration options.
     */
    public function getConfig(): OptionsInterface {
        return $this->config;
    }
    #endregion Configuration

    #region Auto Update
    /**
     * Checks if an automatic update is required based on the last update time.
     *
     * @return bool|int Returns seconds since the last update if an update is needed, false otherwise.
     */
    public function autoUpdate(): bool|int {
        if ($this->config->review->autoupdate === 0) return false;

        $qb = $this->formulasTable->queryBuilder()
            ->select()
            ->orderBy('updated', OrderDirection::DESC)
            ->limit(1)
        ;//->table('formulas');
        $stmt = $this->formulasTable::$db->getDriver()
            ->prepare($qb->toSql())
        ;
        $stmt->execute($qb->getBindings());

        $result = $stmt->fetchAll($this->formulasTable::$db->getDriver()::FETCH_CLASS, Formula::class, [null, $this->formulasTable]);
        $diff = new Timestamp((int)$result[0]->updated)->diff(new Timestamp());

        return $diff->getSeconds() > $this->config->review->autoupdate ? $diff->getSeconds() : false;
    }
    #endregion Auto Update

    #region Data Retrieval
    /**
     * Retrieves formulas that have tags associated with them.
     *
     * @return Options Returns an Options object containing the tagged formulas.
     *
     * @throws RuntimeException
     */
    public function getTagged(): Options {
        $key = 'tagged';
        if (!$this->cache->has($key)) {
            $formulas = $this->formulasTable->find(['tags', '<>', '']);
            $this->cache->set($key, $formulas);
        }

        return $this->cache->get($key);
    }

    /**
     * Retrieves formulas that are pending review.
     *
     * @return Formula[] Returns an Options object containing the reviewed formulas.
     *
     * @throws RuntimeException
     */
    public function getReviewQueue(): Options {
        $key = 'review';
        if (!$this->cache->has($key)) {
            $formulas = $this->formulasTable->find(['reviewed', 0]);
            $this->cache->set($key, $formulas);
        }

        return $this->cache->get($key);
    }

    /**
     * Generates a list of tags from the formulas table.
     *
     * @deprecated $tags property instead. This method will be removed in a future release.
     *
     * @see tags A collection of tags associated with a specific item or entity.
     *
     * @return array Returns an associative array with tag names as keys and their counts as values.
     */
    public function getTags(): array {
        return $this->tags;
    }

    /**
     * Retrieves a specific package by name.
     *
     * @param string $package The name of the package to retrieve.
     *
     * @return Formula|false Returns the Formula object if found, or false if not found.
     */
    public function getPackage(string $package): Formula|false {
        return $this->formulasTable->fetch($package);
    }

    /**
     * Retrieves multiple packages by their names.
     *
     * @param string ...$package Variable number of package names to retrieve.
     *
     * @return Formula[] Returns an array of Formula objects matching the provided package names.
     */
    public function getPackages(string ...$package): array {
        return $this->formulasTable->find([['type' => 'in', 'column' => 'name', 'values' => $package]]);
    }
    #endregion Data Retrieval

    #region Brew Actions
    /**
     * Triggers a notification alert when operating in dry-run mode, indicating no changes have been made.
     *
     * @return void
     */
    protected function notifyDryRun(): void {
        if ($this->dryRun) {
            $this->trigger('alert', 'DRY-RUN: no changes made to files or data.');
        }
    }

    /**
     * Installs the given formula using the Homebrew package manager.
     * If operating in dry-run mode, no actual installation is performed.
     *
     * @param Formula $formula The formula to be installed.
     *
     * @return bool Returns true if the formula was successfully installed, false otherwise.
     */
    public function installAction(Formula $formula): bool {
        $this->notifyDryRun();
        $dr = $this->dryRun ? '-n ' : '';
        passthru('brew install ' . $dr . $formula->name, $resultCode);

        return $resultCode === 0 && !$this->dryRun;
    }

    /**
     * Executes the uninstallation of a given formula using the Brew package manager.
     * Supports a dry-run mode where no actual changes are made.
     *
     * @param Formula $formula The formula to be uninstalled.
     *
     * @return bool Returns true if the uninstallation was successful and not in dry-run mode, otherwise false.
     */
    public function uninstallAction(Formula $formula): bool {
        $this->notifyDryRun();
        $dr = $this->dryRun ? '-n ' : '';
        passthru('brew uninstall ' . $dr . $formula->name, $resultCode);

        return $resultCode === 0 && !$this->dryRun;
    }
    #endregion Brew Actions
}
