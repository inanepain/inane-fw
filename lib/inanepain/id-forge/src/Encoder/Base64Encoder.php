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
 * Base64 encoder wrapper around PHP's built-in functions
 *
 * Provides both standard and URL-safe Base64 encoding/decoding while
 * conforming to the shared `EncoderInterface` contract.
 */
class Base64Encoder extends AbstractEncoder {
	/**
	 * Encodes the provided data using standard Base64.
	 *
	 * @param string $data Binary-safe input data
	 *
	 * @return string Base64-encoded string (may include '=' padding)
	 */
	public function encode(string $data): string {
		return base64_encode($data);
	}

	/**
	 * Decodes a standard Base64 string into its original bytes.
	 *
	 * @param string $data Base64-encoded string
	 *
	 * @return string Decoded binary-safe data
	 *
	 * @throws InvalidArgumentException If the input is not valid, Base64
	 */
	public function decode(string $data): string {
		$decoded = base64_decode($data, true);
		if ($decoded === false) {
			throw new InvalidArgumentException('Invalid Base64 string');
		}

		return $decoded;
	}

	/**
	 * Encodes to a URL-safe Base64 variant without padding.
	 *
	 * Replaces '+' and '/' with '-' and '_' respectively and strips '='.
	 *
	 * @param string $data Binary-safe input data
	 *
	 * @return string URL-safe Base64 string without padding
	 */
	public function urlEncode(string $data): string {
		return str_replace(['+', '/', '='], ['-', '_', ''], $this->encode($data));
	}

	/**
	 * Decodes a URL-safe Base64 string back to the original bytes.
	 * Restores standard characters and padding before decoding.
	 *
	 * @param string $data URL-safe Base64 string (padding optional)
	 *
	 * @return string Decoded binary-safe data
	 *
	 * @throws InvalidArgumentException
	 */
	public function urlDecode(string $data): string {
		$data = str_replace(['-', '_'], ['+', '/'], $data);
		$padding = strlen($data) % 4;
		if ($padding > 0) {
			$data .= str_repeat('=', 4 - $padding);
		}

		return $this->decode($data);
	}
}