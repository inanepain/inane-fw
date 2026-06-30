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

use Inane\Db\Table\AbstractTable;
use Knot\Db\Entity\Department;

/**
 * DepartmentsTable
 *
 * @method false|Department fetch(string|int|float $id)
 * @method Department[] fetchAll()
 * @method Department[] find(array $conditions)
 * @method Department insert(Department $entity)
 * @method Department update(Department $entity)
 * @method bool delete(Department $entity)
 */
class DepartmentsTable extends AbstractTable {
    protected string $table = 'departments';
    protected array $columns = [
        'id' => null,
        'name' => '',
    ];
    protected(set) string $primaryId = 'id';
    protected string $entityClass = Department::class;
}
