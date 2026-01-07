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
 * @author Philip Michael Raab<philip@cathedral.co.za>
 * @package playground\develop
 * @category develop
 *
 * @license UNLICENSE
 * @license https://unlicense.org/UNLICENSE UNLICENSE
 *
 * _version_ $version
 */

declare(strict_types=1);

namespace Knot\Db\Table;

use Knot\Db\Entity\Formula;
use Inane\Db\Sql\Where;
use Inane\Db\Table\AbstractTable;

/**
 * FormulasTable
 *
 * @method false|Formula fetch(string $id)
 * @method Formula[] fetchAll()
 * @method Formula[] search(array|Where|string $query)
 * @method Formula insert(Formula $entity)
 * @method Formula update(Formula $entity)
 * @method bool delete(Formula $entity)
 */
class FormulasTable extends AbstractTable {
    protected string $table = 'formulas';
    protected(set) string $primaryId = 'name';
    protected bool $autoIncrement = false;
    protected string $entityClass = Formula::class;
}
