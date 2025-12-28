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

namespace Inane\IdForge;

use Inane\IdForge\Config\EncoderConfig;
use Inane\IdForge\Config\SnowflakeConfig;
use Inane\IdForge\Generator\NanoidGenerator;
use Inane\IdForge\Generator\SnowflakeIdGenerator;
use Inane\IdForge\Generator\ULIDGenerator;
use Inane\IdForge\Generator\UUIDGenerator;
use Inane\Stdlib\Exception\InvalidArgumentException;

/**
 * Factory class for creating various types of ID generators
 * 
 * This factory provides static methods to create different ID generator instances
 * including Nanoid, Snowflake, UUID, and ULID generators with customizable configurations.
 * 
 * @package Inane\IdForge
 * @version 1.0.0
 */
class IdGeneratorFactory {
	/**
	 * Creates a new Nanoid generator instance
	 * 
	 * Nanoid is a URL-friendly unique string ID generator that generates
	 * compact, secure, and collision-resistant identifiers.
	 * 
	 * @param string $alphabet The alphabet to use for generating IDs (default: alphanumeric)
	 * @param int $size The length of generated IDs (default: 21 characters)
	 *
	 * @return NanoidGenerator The configured Nanoid generator instance
	 */
	public static function createNanoid(string $alphabet = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', int $size = 21): NanoidGenerator {
		return new NanoidGenerator($alphabet, $size);
	}

	/**
	 * Creates a new Snowflake ID generator instance
	 * Snowflake IDs are 64-bit unique identifiers that incorporate timestamp,
	 * worker ID, and datacenter ID components for distributed systems.
	 *
	 * @param int                  $workerId     Unique identifier for the worker node (0-31, default: 0)
	 * @param int                  $datacenterId Unique identifier for the datacenter (0-31, default: 0)
	 * @param SnowflakeConfig|null $config       Optional configuration for custom Snowflake settings
	 *
	 * @return SnowflakeIdGenerator The configured Snowflake generator instance
	 *
	 * @throws InvalidArgumentException
	 */
	public static function createSnowflake(int $workerId = 0, int $datacenterId = 0, ?SnowflakeConfig $config = null): SnowflakeIdGenerator {
		return new SnowflakeIdGenerator($workerId, $datacenterId, $config);
	}

	/**
	 * Creates a new UUID generator instance
	 * 
	 * UUID (Universally Unique Identifier) generator creates standard UUIDs
	 * following RFC 4122 specification.
	 * 
	 * @return UUIDGenerator The UUID generator instance
	 */
	public static function createUUID(): UUIDGenerator {
		return new UUIDGenerator();
	}

	/**
	 * Creates a new ULID generator instance
	 * 
	 * ULID (Universally Unique Lexicographically Sortable Identifier) generates
	 * sortable identifiers that are timestamp-based and URL-safe.
	 * 
	 * @param EncoderConfig|null $config Optional encoder configuration for custom encoding settings
	 *
	 * @return ULIDGenerator The configured ULID generator instance
	 */
	public static function createULID(?EncoderConfig $config = null): ULIDGenerator {
		return new ULIDGenerator($config);
	}
}