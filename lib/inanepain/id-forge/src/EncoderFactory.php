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
 * Factory for creating common encoder implementations with sensible defaults.
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

namespace Inane\IdForge;

use Inane\IdForge\Config\EncoderConfig;
use Inane\IdForge\Encoder\Base32Encoder;
use Inane\IdForge\Encoder\Base58Encoder;
use Inane\IdForge\Encoder\Base64Encoder;

/**
 * Factory class for encoder instances
 *
 * Provides convenience constructors with appropriate alphabets for each base.
 */
class EncoderFactory {
	/**
	 * Creates a Base32 encoder using RFC 4648 alphabet.
	 *
	 * @return Base32Encoder Configured Base32 encoder
	 */
	public static function createBase32(): Base32Encoder {
		return new Base32Encoder(new EncoderConfig('ABCDEFGHIJKLMNOPQRSTUVWXYZ234567'));
	}

	/**
	 * Creates a Base58 encoder using Bitcoin alphabet (no 0,O,I,l).
	 *
	 * @return Base58Encoder Configured Base58 encoder
	 */
	public static function createBase58(): Base58Encoder {
		return new Base58Encoder(new EncoderConfig('123456789ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz'));
	}

	/**
	 * Creates a Base64 encoder using the standard alphabet.
	 *
	 * For URL-safe operations prefer `Base64Encoder::urlEncode()`/`urlDecode()`.
	 *
	 * @return Base64Encoder Configured Base64 encoder
	 */
	public static function createBase64(): Base64Encoder {
		return new Base64Encoder(new EncoderConfig('ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/'));
	}
}