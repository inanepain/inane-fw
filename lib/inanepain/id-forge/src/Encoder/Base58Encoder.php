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

namespace Inane\IdForge\Encoder;

use Inane\Stdlib\Exception\InvalidArgumentException;

/**
 * Base58 encoder using the configured alphabet (e.g., Bitcoin alphabet)
 *
 * Base58 avoids visually ambiguous characters and is commonly used in
 * user-facing identifiers. Leading zero bytes are preserved as leading
 * occurrences of the first alphabet character.
 */
class Base58Encoder extends AbstractEncoder {
	/**
	 * Encodes binary data into a Base58 string.
	 *
	 * Converts the input to a big integer and repeatedly divides by the base
	 * (alphabet length), collecting remainders as digits.
	 *
	 * @param string $data Binary-safe input data
	 *
	 * @return string Base58-encoded string
	 */
	public function encode(string $data): string {
		$num = gmp_init(0);
		foreach(str_split($data) as $char) {
			$num = gmp_add(gmp_mul($num, 256), ord($char));
		}

		$encoded = '';
		while(gmp_cmp($num, 0) > 0) {
			$remainder = gmp_mod($num, $this->getAlphabetLength());
			$encoded = $this->getAlphabet()[gmp_intval($remainder)] . $encoded;
			$num = gmp_div($num, $this->getAlphabetLength());
		}

		// Preserve leading zero bytes as leading first-alphabet characters
		foreach(str_split($data) as $char) {
			if (ord($char) === 0) {
				$encoded = $this->getAlphabet()[0] . $encoded;
			}
			else {
				break;
			}
		}

		return $encoded ?: $this->getAlphabet()[0];
	}

	/**
	 * Decodes a Base58 string back to its original binary data.
	 *
	 * @param string $data Base58-encoded string
	 *
	 * @return string Decoded binary-safe data
	 *
	 * @throws InvalidArgumentException If an unknown character is encountered
	 */
	public function decode(string $data): string {
		$num = gmp_init(0);
		foreach(str_split($data) as $char) {
			$index = strpos($this->getAlphabet(), $char);
			if ($index === false) {
				throw new InvalidArgumentException('Invalid Base58 character: ' . $char);
			}
			$num = gmp_add(gmp_mul($num, $this->getAlphabetLength()), $index);
		}

		$decoded = '';
		while(gmp_cmp($num, 0) > 0) {
			$byte = gmp_mod($num, 256);
			$decoded = chr(gmp_intval($byte)) . $decoded;
			$num = gmp_div($num, 256);
		}

		// Restore leading zero bytes
		$leadingZeros = 0;
		foreach(str_split($data) as $char) {
			if ($char === $this->getAlphabet()[0]) {
				$leadingZeros++;
			}
			else {
				break;
			}
		}

		return str_repeat("\0", $leadingZeros) . $decoded;
	}
}