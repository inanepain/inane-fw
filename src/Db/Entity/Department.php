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

namespace Knot\Db\Entity;

use Knot\Db\Table\DepartmentsTable;
use Inane\Db\Entity\AbstractEntity;

/**
 * Department
 */
class Department extends AbstractEntity {
    protected string $dataTableClass = DepartmentsTable::class;

    /**
     * @var array An array to hold torrent properties.
     */
    protected array $data = [
        'id' => null,
        'name' => '',
    ];

    /**
     * @var int|null The id of the user.
     */
    public int|null $id {
        get => $this->data[__PROPERTY__];
        set(int|null $value) {
            $this->data[__PROPERTY__] = $value;
        }
    }

    /**
     * @var string The name of the user.
     */
    public string $name {
        get => $this->data[__PROPERTY__];
        set(string $value) {
            $this->data[__PROPERTY__] = $value;
        }
    }
}
