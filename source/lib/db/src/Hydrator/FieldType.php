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
 * @package  inanepain\inane-fw
 * @category inane-fw
 *
 * @license  UNLICENSE
 * @license  https://unlicense.org/UNLICENSE UNLICENSE
 *
 * _version_ $version
 *
 */

declare(strict_types = 1);

namespace Inane\Db\Hydrator;

/**
 * Enum representing various field types and their corresponding data formats.
 *
 * This enum defines a set of field types commonly used in data representation,
 * along with their associated values indicating the expected data format.
 */
enum FieldType: string {
    /**
     * Defines the value representation as 'array' for JSON-related operations.
     */
    case JSON      = 'array';
    /**
     * Represents a timestamp value as an integer.
     */
    case Timestamp = 'int';
    /**
     * Defines the value representation as 'string' for Datetime-related operations.
     */
    case Datetime = 'string';
}
