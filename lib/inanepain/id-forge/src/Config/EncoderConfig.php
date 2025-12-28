<?php

/**
 * Inane: IdForge
 * Inane Encoder & ID Library
 * $Id$
 * $Date$
 * PHP version 8.4
 * Provides configuration values for encoders, primarily the alphabet and its
 * derived length.
 *
 * @author   Philip Michael Raab<philip@cathedral.co.za>
 * @package  inanepain\id-forge
 * @category id-forge
 * @license  UNLICENSE
 * @license  https://unlicense.org/UNLICENSE UNLICENSE
 * _version_ $version
 */

declare(strict_types = 1);

namespace Inane\IdForge\Config;

/**
 * Configuration for encoders
 * Stores the alphabet and precomputes its length for faster access in
 * performance-sensitive code paths.
 */
class EncoderConfig {
	/** @var string Characters that make up the encoding alphabet */
	protected string $alphabet;
	/** @var int Cached length of the alphabet */
	protected int $alphabetLength;

	/**
	 * @param string $alphabet A string representing the alphabet to be used.
	 *
	 * @return void
	 */
	public function __construct(string $alphabet) {
		$this->alphabet = $alphabet;
		$this->alphabetLength = strlen($alphabet);
	}

	/**
	 * Returns the configured alphabet.
	 *
	 * @return string Alphabet string
	 */
	public function getAlphabet(): string {
		return $this->alphabet;
	}

	/**
	 * Returns the precomputed alphabet length.
	 *
	 * @return int Number of characters in the alphabet
	 */
	public function getAlphabetLength(): int {
		return $this->alphabetLength;
	}
}