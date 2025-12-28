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

namespace Inane\IdForge\Encoder;

use Inane\IdForge\Config\EncoderConfig;
use Inane\IdForge\Interface\EncoderInterface;

/**
 * Base class for encoders that use a configurable alphabet
 *
 * Provides accessors for the configured alphabet and its length. Concrete
 * encoders (e.g., Base32/Base58/Base64) extend this class to share
 * configuration handling.
 */
abstract class AbstractEncoder implements EncoderInterface {
	/** @var EncoderConfig Encoder configuration (alphabet and derived values) */
	protected EncoderConfig $config;

	/**
	 * @param EncoderConfig $config The configuration object for the encoder.
	 *
	 * @return void
	 */
	public function __construct(EncoderConfig $config) {
		$this->config = $config;
	}

	/**
	 * Returns the active alphabet for this encoder.
	 *
	 * @return string Alphabet characters in index order
	 */
	protected function getAlphabet(): string {
		return $this->config->getAlphabet();
	}

	/**
	 * Returns the length of the active alphabet.
	 *
	 * @return int Number of characters in the alphabet
	 */
	protected function getAlphabetLength(): int {
		return $this->config->getAlphabetLength();
	}
}