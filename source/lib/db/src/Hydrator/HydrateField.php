<?php

/**
 * HydrateField
 *
 * Inane Library
 *
 * $Id$
 * $Date$
 *
 * PHP version 8.5
 *
 * @author   Philip Michael Raab <philip@cathedral.co.za>
 * @package  inanepain\HydrateField
 * @category HydrateField
 *
 * @license  UNLICENSE
 * @license  https://unlicense.org/UNLICENSE UNLICENSE
 *
 * _version_ $version
 */

declare(strict_types = 1);

namespace Inane\Db\Hydrator;

/**
 * Attribute to specify an alias for a property or parameter during hydration.
 *
 * This class is used to assign an alternative name to a property or parameter
 * when it is being populated, typically during data mapping or hydration processes.
 *
 * @param string|null    $alias  The alias to be assigned to the property or parameter.
 * @param FieldType|null $type   The type to be assigned to the property or parameter.
 * @param string|null    $format The format to be used for the property or parameter.
 */
#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::TARGET_PARAMETER)]
class HydrateField {
    /**
     * Constructor method to initialize the object with optional alias, type, and format.
     *
     * @param string|null    $alias  An optional string representing the alias.
     * @param FieldType|null $type   An optional FieldType object representing the type.
     * @param string|null    $format An optional string representing the format.
     *
     * @return void
     */
    public function __construct(public ?string $alias = null, public ?FieldType $type = null, public ?string $format = null) {}
}
