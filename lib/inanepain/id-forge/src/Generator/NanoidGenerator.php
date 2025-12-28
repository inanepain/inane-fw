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

use Random\RandomException;/**
 * Nanoid-like ID generator
 * Produces short, URL-friendly identifiers by mapping random bytes to a
 * configurable alphabet. Collisions are highly unlikely for typical sizes.
 */
class NanoidGenerator extends AbstractIdGenerator {
	/** @var string Alphabet used for ID characters */
	protected string $alphabet;
	/** @var int Number of characters in the generated ID */
	protected int $size;

	/**
	 * Constructor method for initializing the class with a custom alphabet and size.
	 *
	 * @param string $alphabet The set of characters to be used. Defaults to '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'.
	 * @param int    $size     The size value to be used. Defaults to 21.
	 *
	 * @return void
	 */
	public function __construct(string $alphabet = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', int $size = 21) {
		$this->alphabet = $alphabet;
		$this->size = $size;
	}

	/**
	 * Generates a new Nanoid-style identifier.
	 *
	 * @return string ID composed of characters from the configured alphabet
	 *
	 * @throws RandomException
	 */
	public function generate(): string {
		$id = '';
		$bytes = $this->getRandomBytes($this->size);
		$alphabetLength = strlen($this->alphabet);

		for($i = 0; $i < $this->size; $i++) {
			$index = ord($bytes[$i]) % $alphabetLength;
			$id .= $this->alphabet[$index];
		}

		return $id;
	}
}