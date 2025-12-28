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
 * Base32 encoder (RFC 4648 alphabet by default)
 *
 * This encoder converts binary-safe strings to a Base32 representation using
 * the configured alphabet, and back again. Padding is not applied; trailing
 * zero bits are used to complete the last 5-bit chunk.
 */
class Base32Encoder extends AbstractEncoder {
	/**
	 * Encodes the provided data into Base32 text.
	 *
	 * The input is split into 8-bit chunks and re-grouped into 5-bit values,
	 * which index into the configured alphabet.
	 *
	 * @param string $data Binary-safe input data
	 *
	 * @return string Base32-encoded string
	 */
	public function encode(string $data): string {
		$binary = '';
		foreach(str_split($data) as $char) {
			// Convert each byte to 8-bit binary and append
			$binary .= str_pad(decbin(ord($char)), 8, '0', STR_PAD_LEFT);
		}

		$encoded = '';
		// Pad binary stream with zeros so its length is a multiple of 5
		while(strlen($binary) % 5 !== 0) {
			$binary .= '0';
		}

		// Map every 5 bits to an alphabet character
		for($i = 0, $iMax = strlen($binary); $i < $iMax; $i += 5) {
			$chunk = substr($binary, $i, 5);
			$index = bindec($chunk);
			$encoded .= $this->getAlphabet()[$index];
		}

		return $encoded;
	}

	/**
	 * Decodes a Base32 string back into the original bytes.
	 *
	 * @param string $data Base32-encoded string
	 *
	 * @return string Decoded binary-safe data
	 *
	 * @throws InvalidArgumentException When the input contains characters not present in the alphabet
	 */
	public function decode(string $data): string {
		$data = strtoupper($data);
		$binary = '';
		foreach(str_split($data) as $char) {
			$index = strpos($this->getAlphabet(), $char);
			if ($index === false) {
				throw new InvalidArgumentException('Invalid Base32 character: ' . $char);
			}
			$binary .= str_pad(decbin($index), 5, '0', STR_PAD_LEFT);
		}

		$decoded = '';
		// Reassemble 8-bit bytes from the bit stream, dropping incomplete tail
		for($i = 0; $i < strlen($binary) - 7; $i += 8) {
			$chunk = substr($binary, $i, 8);
			$decoded .= chr(bindec($chunk));
		}

		return $decoded;
	}
}