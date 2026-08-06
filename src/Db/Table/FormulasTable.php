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
use Knot\Db\Entity\Formula;

use const false;

/**
 * FormulasTable
 *
 * @method false|Formula fetch(string $id): \false|AbstractEntity
 * @method Formula[] fetchAll()
 * @method Formula[] find(array $conditions)
 * @method Formula insert(Formula $entity)
 * @method Formula update(Formula $entity)
 * @method bool delete(Formula $entity)
 */
class FormulasTable extends AbstractTable {
    /**
     * The database table name for storing formulas.
     *
     * This variable holds the name of the database table used to store formulas.
     * It's a string constant that represents the table's identifier in the database schema.
     *
     * @var string
     */
    protected string $table = 'formulas';

    /**
     * Defines an associative array representing the structure of a software package or module.
     *
     * Each key in the array corresponds to a property of the package, and its associated value is typically initialised as an empty string or zero, indicating that it hasn't been set yet.
     *
     * @var array
     */
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

    /**
     * Specifies the primary identifier key for a software package or module.
     *
     * This variable holds the name of the key that uniquely identifies each package within a collection.
     *
     * @var string
     */
    protected(set) string $primaryId = 'name';

    /**
     * Indicates whether auto-increment functionality should be enabled for a database table or column.
     *
     * When set to `true`, it enables auto-increment, meaning that the value of the field will automatically increase with each new record inserted into the table.
     * When set to `false`, auto-increment is disabled, and manual values must be assigned to the field.
     *
     * @var bool
     */
    protected bool $autoIncrement = false;

    /**
     * Assigns the fully qualified class name of the Formula class to the variable entityClass.
     *
     * @var string $entityClass The class name of the Formula class.
     */
    protected string $entityClass = Formula::class;
}
