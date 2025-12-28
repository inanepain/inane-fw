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

use Inane\IdForge\Encoder\Base64Encoder;
use Inane\Stdlib\Exception\InvalidArgumentException;
use Random\RandomException;

/**
 * UUIDv4 generator
 *
 * Generates RFC 4122 version 4 UUIDs using cryptographically secure random
 * bytes. Includes helper methods to validate and convert to/from URL-safe
 * Base64.
 */
class UUIDGenerator extends AbstractIdGenerator {
	/**
	 * Generates a random UUIDv4 string.
	 *
	 * @return string UUID in the canonical 8-4-4-4-12 hex format
	 *
	 * @throws RandomException
	 */
	public function generate(): string {
		$bytes = $this->getRandomBytes(16);
		// Set version (0100) in the high nibble of byte 6
		$bytes[6] = chr(ord($bytes[6]) & 0x0f | 0x40);
		// Set variant (10xx) in the high bits of byte 8
		$bytes[8] = chr(ord($bytes[8]) & 0x3f | 0x80);

		return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($bytes), 4));
	}

	/**
	 * Validates whether a string matches the UUIDv4 format.
	 *
	 * @param string $uuid Candidate string
	 *
	 * @return bool True if the string is a valid UUIDv4
	 */
	public function isValid(string $uuid): bool {
		return preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i', $uuid) === 1;
	}

	/**
	 * Converts a UUID to URL-safe Base64 (without padding).
	 *
	 * @param string $uuid Canonical UUID string
	 * @param Base64Encoder $base64 Base64 encoder instance
	 *
	 * @return string URL-safe Base64 representation
	 *
	 * @throws InvalidArgumentException If the input is not a valid UUID
	 */
	public function toBase64(string $uuid, Base64Encoder $base64): string {
		if (!$this->isValid($uuid)) {
			throw new InvalidArgumentException('Invalid UUID');
		}
		$hex = str_replace('-', '', $uuid);
		$binary = hex2bin($hex);

		return $base64->urlEncode($binary);
	}

	/**
	 * Converts from URL-safe Base64 back to a canonical UUID string.
	 *
	 * @param string        $base64str URL-safe Base64 string (no padding)
	 * @param Base64Encoder $base64    Base64 encoder instance
	 *
	 * @return string Canonical UUID string
	 *
	 * @throws InvalidArgumentException
	 */
	public function fromBase64(string $base64str, Base64Encoder $base64): string {
		$binary = $base64->urlDecode($base64str);
		$hex = bin2hex($binary);

		return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split($hex, 4));
	}
}