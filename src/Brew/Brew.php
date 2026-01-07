<?php
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
 *
 */

namespace Knot\Brew;

use Inane\Stdlib\Options;
use Knot\Db\Table\FormulasTable;
use function array_filter;
use function explode;

class Brew {
    //#region Properties
    private array $tags;
    protected Options $cache;

    //#endregion Properties

    public function __construct(
        private FormulasTable $formulasTable = new FormulasTable(),
    ) {
        $this->cache = new Options();
    }

    public function getTagged(): Options {
        $key = 'tagged';
        if (!$this->cache->has($key)) {
            $formulas = $this->formulasTable->find(['tags', '', '<>']);
            $this->cache->set($key, $formulas);
        }

        return $this->cache->get($key);
    }

    public function getReview(): Options {
        $key = 'review';
        if (!$this->cache->has($key)) {
            $formulas = $this->formulasTable->find(['reviewed', 0]);
            $this->cache->set($key, $formulas);
        }

        return $this->cache->get($key);
    }

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
            //            $tags = array_unique(array_filter(explode(',', array_reduce($formulas->toArray(), fn($carry, $formula) => $carry . ',' . $formula->tags, ''))));
            //            sort($tags);

            $this->tags = $tags;
        }

        return $this->tags;
    }
}
