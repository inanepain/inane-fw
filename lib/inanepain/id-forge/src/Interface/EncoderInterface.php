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
 * This file declares the `EncoderInterface`, a simple contract for
 * reversible string encoders used by IdForge. Implementations should be
 * deterministic and must guarantee that `decode(encode($x)) === $x` for
 * valid input.
 *
 * Typical implementations include Base32/Base58/Base64 encoders and URL-safe
 * variants. Implementors may throw an exception when decoding invalid data.
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
 * Contract for reversible string encoders
 *
 * Implementations convert arbitrary binary-safe strings to an encoded
 * representation and back again.
 */
interface EncoderInterface {
	/**
	 * Encodes a binary-safe string to its textual representation.
	 *
	 * Implementations should be pure and must not modify the global state.
	 *
	 * @param string $data Arbitrary binary-safe input data
	 *
	 * @return string Encoded representation of the provided data
	 */
	public function encode(string $data): string;

	/**
	 * Decodes an encoded string back to its original binary data.
	 *
	 * Implementations should validate the input and are encouraged to throw a
	 * domain-specific exception (e.g. `InvalidArgumentException`) if the value
	 * cannot be decoded.
	 *
	 * @param string $data Encoded string to decode
	 *
	 * @return string Original binary-safe data
	 */
	public function decode(string $data): string;
}