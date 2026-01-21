<?php

/**
 * Inane: PROJECT
 *
 * PROJECT_DESCRIPTION
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

declare(strict_types=1);

namespace Knot\Brew;

use Inane\Config\ConfigAwareAttribute;
use Inane\Config\ConfigAwareTrait;
use Inane\Datetime\Timestamp;
use Inane\Db\Query\Clause\OrderDirection;
use Inane\Stdlib\Array\OptionsInterface;
use Inane\Stdlib\Exception\RuntimeException;
use Inane\Stdlib\Options;
use Knot\Db\Entity\Formula;
use Knot\Db\Table\FormulasTable;
use function array_filter;
use function array_key_exists;
use function explode;
use function ksort;

#[ConfigAwareAttribute]
class Brew {
    use ConfigAwareTrait;

    //#region Properties
    /**
     * Default configuration settings for the Brew class.
     */
    protected array $defaultConfig = [
        'ui' => [
            /**
             * Flag icon to use for various statuses.
             */
            'icon' => [
                'flag' => '⚑', // ⛳️📌📍⚑⚐
                'new' => '✷', // ✷✦✜
            ],
            /**
             * Text colour options.
             *
             * colours: black, red, green, blue, yellow, magenta, cyan, white
             * styles: dim, bold, italic, underline, blink
             */
            'text' => [
                /**
                 * Action taken.
                 */
                'action' => 'blue',
                /**
                 * Package description.
                 */
                'desc' => 'blue',
                /**
                 * Progress counter.
                 */
                'counter' => 'cyan',
                /**
                 * Tag text.
                 */
                'tag' => 'purple',
                /**
                 * New indicator colour.
                 */
                'icon' => 'purple',
                /**
                 * Alert message.
                 */
                'alert' => 'red',
            ],
        ],
        'info' => [
            /**
             * Whether to display extended information about formulae automatically.
             */
            'extended' => false,
        ],
        'review' => [
            /**
             * Default Action to take when reviewing formulae.
             * 'next' - Move to the next formula.
             * 'hide' - Hide the current formula.
             */
            'action' => 'next', // options: next, hide
            /**
             * Whether to automatically update the review status of formulae when reviewing.
             * - update if the last update ran longer than the value in seconds ago
             * - 0 - off
             */
            'autoupdate' => 0,
        ],
    ];

    private array $tags;
    protected Options $cache;
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
        private FormulasTable $formulasTable = new FormulasTable(),
    ) {
        $this->cache = new Options();
    }
    #endregion Instantiation

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

        $qb = $this->formulasTable->queryBuilder()->select()->orderBy('updated', OrderDirection::DESC)->limit(1);//->table('formulas');
        $stmt = $this->formulasTable::$db->getDriver()->prepare($qb->toSql());
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
            $formulas = $this->formulasTable->find(['tags', '', '<>']);
            $this->cache->set($key, $formulas);
        }

        return $this->cache->get($key);
    }

    /**
     * Retrieves formulas that are pending review.
     *
     * @return Options Returns an Options object containing the reviewed formulas.
     *
     * @throws RuntimeException
     */
    public function getReview(): Options {
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
     * @return array Returns an associative array with tag names as keys and their counts as values.
     *
     * @throws RuntimeException
     */
    public function getTags(): array {
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
}
