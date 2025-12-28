<?php

/**
 * Inane: IdForge
 *
 * Inane Encoder & ID Library
 *
 * $Id$
 * $Date$
 *
 * PHP version 8.4
 *
 * This file declares the `IdGeneratorInterface`, a minimal contract for
 * generating unique identifiers as strings.
 *
 * Implementations include UUID, ULID, Nanoid, and Snowflake-style IDs. The
 * return type is a string to allow arbitrary encodings and numeric sizes that
 * exceed PHP's native integer range.
 *
 * @author Philip Michael Raab<philip@cathedral.co.za>
 * @package inanepain\id-forge
 * @category id-forge
 *
 * @license UNLICENSE
 * @license https://unlicense.org/UNLICENSE UNLICENSE
 *
 * _version_ $version
 */

declare(strict_types=1);

namespace Inane\IdForge\Interface;

/**
 * Contract for ID generators
 *
 * Implementations must be stateless or internally synchronized when used
 * concurrently. They should aim to be fast and have a very low collision risk.
 */
interface IdGeneratorInterface {
	/**
	 * Generates a new identifier.
	 *
	 * Implementations should ensure that identifiers are highly unlikely to
	 * collide within the expected usage scope, and must return a string even if
	 * the underlying representation is numeric.
	 *
	 * @return string Newly generated identifier
	 */
	public function generate(): string;
}