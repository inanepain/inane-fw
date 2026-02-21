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
use Knot\Db\Entity\User;

/**
 * UsersTable
 *
 * @method false|User fetch(string|int|float $id)
 * @method User[] fetchAll()
 * @method User[] search(array|string $query)
 * @method User insert(User $entity)
 * @method User update(User $entity)
 * @method bool delete(User $entity)
 */
class UsersTable extends AbstractTable {
    protected string $table = 'users';
    protected(set) string $primaryId = 'id';
    protected string $entityClass = User::class;
}
