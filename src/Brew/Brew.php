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

use Inane\Stdlib\Exception\RuntimeException;
use Inane\Stdlib\Options;
use Knot\Db\Entity\Formula;
use Knot\Db\Table\FormulasTable;
use function array_filter;
use function array_key_exists;
use function explode;
use function ksort;

class Brew {
    //#region Properties
    private array $tags;
    protected Options $cache;
    //#endregion Properties

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
}
