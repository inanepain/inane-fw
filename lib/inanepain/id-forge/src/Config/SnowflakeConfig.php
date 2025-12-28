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
 * Configuration for Snowflake-style ID generation.
 *
 * Controls the custom epoch and the bit allocation for worker, datacenter, and
 * sequence components.
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

namespace Inane\IdForge\Config;

/**
 * Configuration class for generating Snowflake IDs.
 * This class defines the settings for the Snowflake algorithm, including the custom epoch and bit allocations
 * for worker ID, datacenter ID, and sequence.
 */
class SnowflakeConfig {
	/** @var int Custom epoch in milliseconds */
	protected int $epoch;
	/** @var int Number of bits allocated for the worker ID */
	protected int $workerIdBits;
	/** @var int Number of bits allocated for the datacenter ID */
	protected int $datacenterIdBits;
	/** @var int Number of bits allocated for the per-millisecond sequence */
	protected int $sequenceBits;

	/**
	 * Constructor for initializing the class properties.
	 *
	 * @param int $epoch            Custom epoch in milliseconds.
	 * @param int $workerIdBits     Number of bits allocated for the worker ID.
	 * @param int $datacenterIdBits Number of bits allocated for the datacenter ID.
	 * @param int $sequenceBits     Number of bits allocated for the sequence.
	 *
	 * @return void
	 */
	public function __construct(int $epoch = 1609459200000, int $workerIdBits = 5, int $datacenterIdBits = 5, int $sequenceBits = 12) {
		$this->epoch = $epoch;
		$this->workerIdBits = $workerIdBits;
		$this->datacenterIdBits = $datacenterIdBits;
		$this->sequenceBits = $sequenceBits;
	}

	// Getters

	/**
	 * Get Epoch
	 *
	 * @return int The epoch value.
	 */
	public function getEpoch(): int {
		return $this->epoch;
	}

	/**
	 * Get Worker ID Bits
	 *
	 * @return int The number of bits allocated for the worker ID.
	 */
	public function getWorkerIdBits(): int {
		return $this->workerIdBits;
	}

	/**
	 * Get Datacenter ID Bits
	 *
	 * @return int The value of datacenter ID bits.
	 */
	public function getDatacenterIdBits(): int {
		return $this->datacenterIdBits;
	}

	/**
	 * Get Sequence Bits
	 *
	 * @return int The sequence bits value.
	 */
	public function getSequenceBits(): int {
		return $this->sequenceBits;
	}
}