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

use Inane\IdForge\Config\EncoderConfig;
use Inane\IdForge\Interface\EncoderInterface;
use Inane\Stdlib\Exception\InvalidArgumentException;
use Random\RandomException;

/**
 * ULID generator (Crockford's Base32 alphabet)
 *
 * ULIDs are 26-character, lexicographically sortable identifiers consisting of
 * a 48-bit timestamp and 80 bits of randomness. This implementation supports a
 * monotonic mode to guarantee strict ordering for IDs generated within the same
 * millisecond.
 */
class ULIDGenerator extends AbstractIdGenerator {
	/** Number of Base32 chars used for the timestamp portion */
	protected const TIMESTAMP_LENGTH = 10;
	/** Number of Base32 chars used for the random portion */
	protected const RANDOM_LENGTH    = 16;
	/** @var EncoderConfig Alphabet configuration (Crockford by default) */
	protected EncoderConfig $config;
	/** @var bool Whether to use monotonic sequencing within the same ms */
	protected bool $monotonic = false;
	/** @var int Last timestamp used (ms) */
	protected int $lastTimestamp = 0;
	/** @var string Last random bytes used when monotonic */
	protected string $lastRandomBytes = '';

	/**
	 * Constructs a new instance of the class with optional configuration and monotonicity settings.
	 *
	 * @param EncoderConfig|null $config    Optional configuration object for encoding. Defaults to a new EncoderConfig with a predefined alphabet.
	 * @param bool               $monotonic Determines whether monotonic behavior is enabled. Defaults to false.
	 *
	 * @return void
	 */
	public function __construct(?EncoderConfig $config = null, bool $monotonic = false) {
		$this->config = $config ?? new EncoderConfig('0123456789ABCDEFGHJKMNPQRSTVWXYZ');
		$this->monotonic = $monotonic;
	}

	/**
	 * Generates a new ULID string.
	 *
	 * @param int|null $timestamp Optional timestamp (ms). If given with fewer
	 *                            than 13 digits, it will be right-padded.
	 *
	 * @return string 26-character ULID
	 *
	 * @throws RandomException
	 */
	public function generate(?int $timestamp = null): string {
		$timestamp = $timestamp === null ? $this->getTimestamp() : (strlen((string)$timestamp) === 13 ? $timestamp : (int)str_pad((string)$timestamp, 13, '0', STR_PAD_RIGHT));
		$randomBytes = $this->getMonotonicRandom($timestamp);

		return $this->encodeTimestamp($timestamp) . $this->encodeRandom($randomBytes);
	}

	/**
	 * Generates a monotonic random value based on a timestamp, ensuring that
	 * consecutive calls with the same timestamp produce increasing random values.
	 *
	 * @param int $timestamp The current timestamp in milliseconds, used to determine
	 *                       whether to generate a new random sequence or increment
	 *                       the previous value.
	 *
	 * @return string A 10-byte string containing the generated monotonic random value.
	 *
	 * @throws RandomException
	 */
	protected function getMonotonicRandom(int $timestamp): string {
		if (!$this->monotonic || $timestamp !== $this->lastTimestamp || $this->lastRandomBytes === '') {
			$randomBytes = $this->getRandomBytes(10);
			if ($this->monotonic) {
				$this->lastTimestamp = $timestamp;
				$this->lastRandomBytes = $randomBytes;
			}

			return $randomBytes;
		}
		$randomInt = gmp_add($this->bytesToInt($this->lastRandomBytes), 1);
		// If overflowed 80 bits, wait for the next millisecond and reset
		if (gmp_cmp($randomInt, gmp_init('1' . str_repeat('0', 80))) >= 0) {
			// Busy-wait until the clock advances to the next millisecond
			while(($timestamp = $this->getTimestamp()) <= $this->lastTimestamp) {
				// no-op
			}
			$randomBytes = $this->getRandomBytes(10);
			$this->lastTimestamp = $timestamp;
			$this->lastRandomBytes = $randomBytes;

			return $randomBytes;
		}
		$randomBytes = $this->intToBytes(gmp_strval($randomInt, 16), 10);
		$this->lastTimestamp = $timestamp;
		$this->lastRandomBytes = $randomBytes;

		return $randomBytes;
	}

	/**
	 * Converts a binary string into an integer represented as a GMP object.
	 *
	 * @param string $bytes The binary string to be converted into an integer.
	 *
	 * @return \GMP The GMP object representation of the integer.
	 */
	protected function bytesToInt(string $bytes): \GMP {
		return gmp_init('0x' . bin2hex($bytes));
	}

	/**
	 * Converts a hexadecimal string into a binary string of the specified length.
	 *
	 * @param string $hex    The hexadecimal string to be converted.
	 * @param int    $length The desired length of the output binary string.
	 *
	 * @return string The binary string representation of the hexadecimal input, padded to the specified length.
	 */
	protected function intToBytes(string $hex, int $length): string {
		return hex2bin(str_pad($hex, $length * 2, '0', STR_PAD_LEFT)) ?? str_repeat(chr(0), $length);
	}

	/**
	 * Encodes the given timestamp into a Base32 representation using the configured alphabet.
	 *
	 * @param int $timestamp The timestamp to encode.
	 *
	 * @return string The encoded timestamp as a Base32 string.
	 */
	protected function encodeTimestamp(int $timestamp): string {
		$encoded = '';
		for($i = 0; $i < self::TIMESTAMP_LENGTH; $i++) {
			$encoded .= $this->config->getAlphabet()[($timestamp >> ((self::TIMESTAMP_LENGTH - $i - 1) * 5)) & 31];
		}

		return $encoded;
	}

	/**
	 * Encodes the given string of bytes into a specific string format using a custom alphabet.
	 * This method converts each character in the input string into its binary representation,
	 * concatenates these binary values, and groups the bits into chunks of 5. Each chunk is
	 * then mapped to a character from a pre-defined alphabet to construct the encoded string.
	 *
	 * @param string $bytes The input string of bytes to be encoded.
	 *
	 * @return string The encoded string resulting from the transformation.
	 */
	protected function encodeRandom(string $bytes): string {
		$binary = implode('', array_map(fn($b) => str_pad(decbin(ord($b)), 8, '0', STR_PAD_LEFT), str_split($bytes)));
		$encoded = '';
		for($i = 0; $i < 80; $i += 5) {
			$encoded .= $this->config->getAlphabet()[bindec(substr($binary, $i, 5))];
		}

		return $encoded;
	}

	/**
	 * Decodes the timestamp portion of a ULID (Universally Unique Lexicographically Sortable Identifier).
	 * This method validates the ULID format, extracts the timestamp segment, and converts it
	 * from a custom base-32 alphabet to a 48-bit integer representation.
	 *
	 * @param string $ulid The ULID string to decode, expected to be in uppercase and of valid length.
	 *
	 * @return int The decoded 48-bit integer representation of the timestamp.
	 *
	 * @throws InvalidArgumentException If the ULID has an invalid length or contains invalid characters.
	 */
	public function decodeTimestamp(string $ulid): int {
		$ulid = strtoupper($ulid);
		if (strlen($ulid) !== self::TIMESTAMP_LENGTH + self::RANDOM_LENGTH) {
			throw new InvalidArgumentException('Invalid ULID length');
		}
		$timestamp = 0;
		for($i = 0; $i < self::TIMESTAMP_LENGTH; $i++) {
			$index = strpos($this->config->getAlphabet(), $ulid[$i]);
			if ($index === false) {
				throw new InvalidArgumentException('Invalid ULID character: ' . $ulid[$i]);
			}
			$timestamp = ($timestamp << 5) | $index;
		}

		return $timestamp & ((1 << 48) - 1);
	}

	/**
	 * Decodes a ULID string into its constituent timestamp and random components.
	 * The method validates the ULID format based on length and character constraints,
	 * extracts the timestamp by converting the initial characters to a binary timestamp,
	 * and processes the remaining characters to retrieve the random binary string.
	 *
	 * @param string $ulid The ULID string to be decoded.
	 *
	 * @return array An associative array containing the decoded components:
	 *               - 'timestamp': The numerical timestamp extracted from the ULID (integer).
	 *               - 'random': The binary random part of the ULID reconstructed into a string.
	 *
	 * @throws InvalidArgumentException If the ULID length is invalid or if the ULID string contains forbidden characters.
	 */
	public function decode(string $ulid): array {
		$ulid = strtoupper($ulid);
		if (strlen($ulid) !== self::TIMESTAMP_LENGTH + self::RANDOM_LENGTH) {
			throw new InvalidArgumentException('Invalid ULID length');
		}

		$timestamp = 0;
		for($i = 0; $i < self::TIMESTAMP_LENGTH; $i++) {
			$index = strpos($this->config->getAlphabet(), $ulid[$i]);
			if ($index === false) {
				throw new InvalidArgumentException('Invalid ULID character: ' . $ulid[$i]);
			}
			$timestamp = ($timestamp << 5) | $index;
		}
		$timestamp &= (1 << 48) - 1;

		$randomBinary = '';
		for($i = 0; $i < self::RANDOM_LENGTH; $i++) {
			$index = strpos($this->config->getAlphabet(), $ulid[self::TIMESTAMP_LENGTH + $i]);
			if ($index === false) {
				throw new InvalidArgumentException('Invalid ULID character: ' . $ulid[self::TIMESTAMP_LENGTH + $i]);
			}
			$randomBinary .= str_pad(decbin($index), 5, '0', STR_PAD_LEFT);
		}
		$random = '';
		for($i = 0; $i < 80; $i += 8) {
			$random .= chr(bindec(substr($randomBinary, $i, 8)));
		}

		return ['timestamp' => $timestamp, 'random' => $random];
	}

	/**
	 * Encodes a generated value using a provided encoder implementation.
	 * This method uses the given encoder to transform the generated value
	 * into an encoded string representation.
	 *
	 * @param EncoderInterface $encoder The encoder instance responsible for performing the encoding process.
	 *
	 * @return string The encoded string resulting from the encoding operation.
	 */
	public function toEncoded(EncoderInterface $encoder): string {
		return $encoder->encode($this->generate());
	}
}