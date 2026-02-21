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

declare(strict_types=1);

namespace Knot\Db\Table;

use Inane\Db\Sql\Where;
use Inane\Db\Table\AbstractTable;
use Knot\Db\Entity\Formula;

use const false;

/**
 * FormulasTable
 *
 * @method false|Formula fetch(string $id): \false|AbstractEntity
 * @method Formula[] fetchAll()
 * @method Formula[] find(array $conditions)
 * @method Formula[] search(array|Where|string $query)
 * @method Formula insert(Formula $entity)
 * @method Formula update(Formula $entity)
 * @method bool delete(Formula $entity)
 */
class FormulasTable extends AbstractTable {
    protected string $table = 'formulas';
    protected array $columns = [
        'name' => '',
        'desc' => '',
        'version' => '',
        'homepage' => '',
        'installed' => 0,
        'reviewed' => 0,
        'state' => 'update',
        'tags' => '',
        'flag' => 0,
        'updated' => null,
        'modified' => null,
    ];
    protected(set) string $primaryId = 'name';
    protected bool $autoIncrement = false;
    protected string $entityClass = Formula::class;
}
