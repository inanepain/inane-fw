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

use Inane\IdForge\Config\SnowflakeConfig;
use Inane\IdForge\Interface\EncoderInterface;
use Inane\Stdlib\Exception\InvalidArgumentException;
use Inane\Stdlib\Exception\RuntimeException;

/**
 * Snowflake-inspired ID generator (64-bit composed identifier)
 *
 * Bit layout (from most significant to least significant):
 * - timestamp:   variable bits (milliseconds since custom epoch)
 * - datacenter:  D bits
 * - worker:      W bits
 * - sequence:    S bits (per millisecond)
 *
 * Where D, W, S are provided by `SnowflakeConfig`.
 */
class SnowflakeIdGenerator extends AbstractIdGenerator {
	/** @var SnowflakeConfig Snowflake configuration (epoch, bit allocation) */
	protected SnowflakeConfig $config;
	/** @var int Worker identifier */
	protected int $workerId;
	/** @var int Datacenter identifier */
	protected int $datacenterId;
	/** @var int Sequence number within the same millisecond */
	protected int $sequence = 0;
	/** @var int Last timestamp used (ms) */
	protected int $lastTimestamp = -1;

	/**
	 * Constructor method to initialize the Snowflake instance.
	 *
	 * @param int                  $workerId     The worker ID, must be within the valid range defined by the configuration.
	 * @param int                  $datacenterId The datacenter ID, must be within the valid range defined by the configuration.
	 * @param SnowflakeConfig|null $config       Optional configuration instance. If not provided, a default configuration is used.
	 *
	 * @return void
	 *
	 * @throws InvalidArgumentException If the worker ID or datacenter ID is out of the valid range.
	 */
	public function __construct(int $workerId = 0, int $datacenterId = 0, ?SnowflakeConfig $config = null) {
		$this->config = $config ?? new SnowflakeConfig();
		if ($workerId > (1 << $this->config->getWorkerIdBits()) - 1 || $workerId < 0) {
			throw new InvalidArgumentException('Worker ID out of range');
		}
		if ($datacenterId > (1 << $this->config->getDatacenterIdBits()) - 1 || $datacenterId < 0) {
			throw new InvalidArgumentException('Datacenter ID out of range');
		}
		$this->workerId = $workerId;
		$this->datacenterId = $datacenterId;
	}

	/**
	 * Generates a new Snowflake-style 64-bit identifier as a string.
	 *
	 * Ensures monotonicity within the same millisecond by incrementing the
	 * sequence and, on overflow, waiting for the next millisecond.
	 *
	 * @return string Numeric identifier represented as a string
	 *
	 * @throws RuntimeException If a system clock moves backwards
	 */
	public function generate(): string {
		$timestamp = $this->getTimestamp();
		if ($timestamp < $this->lastTimestamp) {
			throw new RuntimeException('Clock moved backwards');
		}

		if ($timestamp === $this->lastTimestamp) {
			$this->sequence = ($this->sequence + 1) & ((1 << $this->config->getSequenceBits()) - 1);
			if ($this->sequence === 0) {
				$timestamp = $this->waitNextMillis($timestamp);
			}
		}
		else {
			$this->sequence = 0;
		}

		$this->lastTimestamp = $timestamp;

		// Compose ID by shifting and OR-ing each component into place
		$id = ($timestamp - $this->config->getEpoch()) << ($this->config->getWorkerIdBits() + $this->config->getDatacenterIdBits() + $this->config->getSequenceBits());
		$id |= $this->datacenterId << ($this->config->getWorkerIdBits() + $this->config->getSequenceBits());
		$id |= $this->workerId << $this->config->getSequenceBits();
		$id |= $this->sequence;

		return (string)$id;
	}

	/**
	 * Busy-waits until the next millisecond.
	 *
	 * @param int $lastTimestamp The timestamp used for the previous ID
	 *
	 * @return int A timestamp strictly greater than $lastTimestamp
	 */
	protected function waitNextMillis(int $lastTimestamp): int {
		$timestamp = $this->getTimestamp();
		while($timestamp <= $lastTimestamp) {
			$timestamp = $this->getTimestamp();
		}

		return $timestamp;
	}

	/**
	 * Generates and encodes the ID using the provided encoder.
	 *
	 * @param EncoderInterface $encoder Target encoder (e.g., Base58)
	 *
	 * @return string Encoded representation of the generated ID
	 *
	 * @throws RuntimeException
	 */
	public function toEncoded(EncoderInterface $encoder): string {
		return $encoder->encode($this->generate());
	}
}