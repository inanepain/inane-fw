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
 * Base class for ID generators, providing utilities for secure random bytes
 * generation and millisecond timestamps.
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

namespace Inane\IdForge\Generator;

use Inane\IdForge\Interface\IdGeneratorInterface;
use Random\RandomException;

/**
 * Base class for ID generators
 *
 * Supplies common helpers such as cryptographically secure random byte
 * generation and a millisecond-resolution timestamp.
 */
abstract class AbstractIdGenerator implements IdGeneratorInterface {
	/**
	 * Returns cryptographically secure random bytes.
	 *
	 * @param int $length Number of bytes to generate
	 *
	 * @return string Raw binary string of random bytes
	 *
	 * @throws RandomException
	 */
	protected function getRandomBytes(int $length): string {
		return random_bytes($length);
	}

	/**
	 * Returns the current UNIX timestamp in milliseconds.
	 *
	 * @return int Milliseconds since the UNIX epoch
	 */
	protected function getTimestamp(): int {
		return (int)(microtime(true) * 1000);
	}
}