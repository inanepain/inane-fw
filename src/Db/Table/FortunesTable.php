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
use Knot\Db\Entity\Fortune;

/**
 * FortunesTable
 *
 * @method false|Fortune fetch(int $id)
 * @method Fortune[] fetchAll()
 * @method Fortune[] find(array $conditions)
 * @method Fortune insert(Fortune $entity)
 * @method Fortune update(Fortune $entity)
 * @method bool delete(Fortune $entity)
 */
class FortunesTable extends AbstractTable {
    protected string $table = 'fortunes';
    protected array $columns = [
        'id' => null,
        'favourite' => 0,
        'fortune' => '',
        'details' => '{}',
        'views' => 1,
        'created' => null,
        'viewed' => null,
    ];
    protected(set) string $primaryId = 'id';
    protected bool $autoIncrement = false;
    protected string $entityClass = Fortune::class;
}
