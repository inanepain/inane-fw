# ![icon](./icon.png) inanepain/id-forge

A lightweight, versatile PHP library for generating and encoding unique
identifiers. It supports base32, base58, base64, nanoid, snowflakeid,
uuid, and ulid, providing fast, secure, and flexible solutions for ID
generation and encoding in modern applications.

- Reversible encoders (`Base32`, `Base58`, `Base64`) via a common
  `EncoderInterface`

- Several ID generators (`UUIDv4`, `ULID`, `Nanoid`, Snowflake-like IDs)
  via a common `IdGeneratorInterface`

- Simple configuration of objects and factory helpers

All examples target PHP 8.2+ (the codebase is PHP 8.4-ready). Namespaces
are rooted under `Inane\IdForge`.

# Install

$ composer require inanepain/id-forge

# Quickstart

Generate a few IDs

    use Inane\IdForge\IdGeneratorFactory;
    use Inane\IdForge\EncoderFactory;

    $uuid    = IdGeneratorFactory::createUUID()->generate();
    $ulid    = IdGeneratorFactory::createULID()->generate();
    $nanoid  = IdGeneratorFactory::createNanoid()->generate();
    $snow    = IdGeneratorFactory::createSnowflake(1, 2)->generate();

    $base58  = EncoderFactory::createBase58();
    $encoded = $base58->encode($ulid);
    $decoded = $base58->decode($encoded);

# Modules

# Encoders

This module provides reversible string encoders that share a simple
contract, `EncoderInterface`. Implementations cover Base32, Base58, and
Base64 (including a URL-safe variant).

## Contracts and base classes

### `Inane\IdForge\Interface\EncoderInterface`

Contract for reversible string encoders:

- `encode(string $data): string` — converts binary-safe input to an
  encoded string

- `decode(string $data): string` — converts an encoded string back to
  the original binary data

Implementations must be deterministic and satisfy
`decode(encode($x)) === $x` for valid input. Invalid input should raise
`Inane\Stdlib\Exception\InvalidArgumentException` (or a domain-specific
exception).

### `Inane\IdForge\Encoder\AbstractEncoder`

A small base class that stores an `EncoderConfig` and exposes helpers:

- `getAlphabet(): string`

- `getAlphabetLength(): int`

Concrete encoders (Base32/58/64) extend this class.

## Implementations

### Base32

Namespace: `Inane\IdForge\Encoder\Base32Encoder`

- Alphabet: configurable (RFC 4648 by default when using
  `EncoderFactory::createBase32()`)

- Padding: not applied; trailing zero bits are used to complete the last
  5-bit group

- Errors: throws `InvalidArgumentException` if a character is not in the
  alphabet during `decode()`

Usage

    use Inane\IdForge\EncoderFactory;

    $base32 = EncoderFactory::createBase32();
    $enc = $base32->encode("\x00\xFFHello");
    $bin = $base32->decode($enc);

### Base58

Namespace: `Inane\IdForge\Encoder\Base58Encoder`

- Alphabet: configurable (Bitcoin alphabet via
  `EncoderFactory::createBase58()`)

- Preserves leading zero bytes as leading first-alphabet characters

- Errors: throws `InvalidArgumentException` for unknown characters
  during `decode()`

Usage

    use Inane\IdForge\EncoderFactory;

    $base58 = EncoderFactory::createBase58();
    $enc = $base58->encode("\0\0payload");
    $bin = $base58->decode($enc);

### Base64

Namespace: `Inane\IdForge\Encoder\Base64Encoder`

- Standard Base64 via `encode()`/`decode()`

- URL-safe helpers: `urlEncode()` replaces `+`/`/` with `-`/`_` and
  strips padding; `urlDecode()` restores and decodes

- Errors: `decode()` and `urlDecode()` throw `InvalidArgumentException`
  for invalid Base64 input

Usage (URL-safe)

    use Inane\IdForge\EncoderFactory;

    $base64 = EncoderFactory::createBase64();
    $token  = $base64->urlEncode(random_bytes(16));
    $bytes  = $base64->urlDecode($token);

## Configuration

Encoders accept `Inane\IdForge\Config\EncoderConfig` which holds:

- `alphabet: string`

- `alphabetLength: int` (precomputed)

Use `EncoderFactory` for sensible defaults or construct encoders
manually with a custom alphabet.

## Exceptions

- `Inane\Stdlib\Exception\InvalidArgumentException` — invalid input
  during `decode()`

## See also

- [Configuration](config.xml)

- [Factories](factories.xml)

# Generators

IdForge includes several generators that implement a common contract,
`IdGeneratorInterface`. Each generator focuses on a different trade-off:
interoperability (UUID), sortability (ULID, Snowflake), or brevity and
URL-friendliness (Nanoid).

## Contract and base class

### `Inane\IdForge\Interface\IdGeneratorInterface`

- `generate(): string` — create a new identifier as a string

Implementations should be fast, low-collision, and safe to use
concurrently.

### `Inane\IdForge\Generator\AbstractIdGenerator`

Provides helpers shared by all generators:

- `getRandomBytes(int $length): string` — cryptographically secure
  random bytes (can throw `Random\RandomException`)

- `getTimestamp(): int` — current UNIX timestamp in milliseconds

## Implementations

### UUIDv4

Namespace: `Inane\IdForge\Generator\UUIDGenerator`

- RFC 4122 UUID version 4

- Format: canonical 36-char string `8-4-4-4-12`

- Helpers:

- `isValid(string $uuid): bool`

- `toBase64(string $uuid, Base64Encoder $base64): string` — URL-safe
  Base64 (no padding)

- `fromBase64(string $b64, Base64Encoder $base64): string` — back to
  canonical string

- Errors: `toBase64()` throws `InvalidArgumentException` for invalid
  input

Usage

    use Inane\IdForge\IdGeneratorFactory;
    use Inane\IdForge\EncoderFactory;

    $uuid = IdGeneratorFactory::createUUID()->generate();
    $base64 = EncoderFactory::createBase64();
    $b64 = IdGeneratorFactory::createUUID()->toBase64($uuid, $base64);
    $uuid2 = IdGeneratorFactory::createUUID()->fromBase64($b64, $base64);

### ULID

Namespace: `Inane\IdForge\Generator\ULIDGenerator`

- 26-char Crockford Base32, lexicographically sortable

- Structure: 48-bit timestamp + 80 bits randomness

- Monotonic mode ensures strict ordering within the same millisecond

- Key methods:

- `__construct(?EncoderConfig $config = null, bool $monotonic = false)`

- `generate(?int $timestamp = null): string`

- `decodeTimestamp(string $ulid): int`

- `decode(string $ulid): array{timestamp:int, random:string}`

- `toEncoded(EncoderInterface $encoder): string`

- Errors: `InvalidArgumentException` for bad characters/length;
  `Random\RandomException` for entropy issues

Usage

    use Inane\IdForge\IdGeneratorFactory;

    $ulid = IdGeneratorFactory::createULID()->generate();

Monotonic ULID

    use Inane\IdForge\Generator\ULIDGenerator;
    use Inane\IdForge\Config\EncoderConfig;

    $mono = new ULIDGenerator(new EncoderConfig('0123456789ABCDEFGHJKMNPQRSTVWXYZ'), true);
    $a = $mono->generate();
    $b = $mono->generate(); // guaranteed $a < $b when in same ms

### Nanoid

Namespace: `Inane\IdForge\Generator\NanoidGenerator`

- Short, URL-friendly random IDs

- Constructor:
  `__construct(string $alphabet = '0-9a-zA-Z', int $size = 21)`

- `generate(): string`

Usage

    use Inane\IdForge\IdGeneratorFactory;

    $id = IdGeneratorFactory::createNanoid()->generate();

### Snowflake-like

Namespace: `Inane\IdForge\Generator\SnowflakeIdGenerator`

- 64-bit composed numeric ID (as a string): timestamp + datacenter +
  worker + sequence

- Configured via `SnowflakeConfig` (epoch, bit allocations for
  worker/datacenter/sequence)

- Methods:

- `__construct(int $workerId = 0, int $datacenterId = 0, ?SnowflakeConfig $config = null)`

- `generate(): string`

- `toEncoded(EncoderInterface $encoder): string`

- Behavior: on sequence overflow within the same millisecond, waits for
  the next millisecond

- Errors: `RuntimeException` if the clock moves backwards;
  `InvalidArgumentException` for out-of-range worker/datacenter

Usage

    use Inane\IdForge\IdGeneratorFactory;

    $gen = IdGeneratorFactory::createSnowflake(workerId: 1, datacenterId: 2);
    $id  = $gen->generate();

## See also

- [Configuration](config.xml)

- [Factories](factories.xml)

- [Encoders](encoders.xml)

# Configuration

This module documents the small configuration objects that accompany
encoders and generators.

## EncoderConfig

Namespace: `Inane\IdForge\Config\EncoderConfig`

Holds the alphabet used by encoders and caches its length.

Fields

- `alphabet: string` — characters used by the encoding

- `alphabetLength: int` — cached length of the alphabet

API

- `__construct(string $alphabet)` — set the alphabet and precompute its
  length

- `getAlphabet(): string`

- `getAlphabetLength(): int`

Usage

    use Inane\IdForge\Config\EncoderConfig;
    use Inane\IdForge\Encoder\Base32Encoder;

    $config  = new EncoderConfig('ABCDEFGHIJKLMNOPQRSTUVWXYZ234567');
    $encoder = new Base32Encoder($config);

## SnowflakeConfig

Namespace: `Inane\IdForge\Config\SnowflakeConfig`

Controls the epoch and bit allocations for Snowflake-like IDs.

Fields

- `epoch: int` — custom epoch in milliseconds (default: 1609459200000,
  2021-01-01)

- `workerIdBits: int` — bits for worker/node id (default: 5)

- `datacenterIdBits: int` — bits for datacenter id (default: 5)

- `sequenceBits: int` — bits for per-millisecond sequence (default: 12)

API

- `__construct(int $epoch = 1609459200000, int $workerIdBits = 5, int $datacenterIdBits = 5, int $sequenceBits = 12)`

- Getters:

- `getEpoch(): int`

- `getWorkerIdBits(): int`

- `getDatacenterIdBits(): int`

- `getSequenceBits(): int`

Usage

    use Inane\IdForge\Generator\SnowflakeIdGenerator;
    use Inane\IdForge\Config\SnowflakeConfig;

    $config = new SnowflakeConfig(epoch: 1700000000000, workerIdBits: 6, datacenterIdBits: 4, sequenceBits: 12);
    $gen    = new SnowflakeIdGenerator(workerId: 3, datacenterId: 1, config: $config);
    $id     = $gen->generate();

## See also

- [Encoders](encoders.xml)

- [Generators](generators.xml)

- [Factories](factories.xml)

# Factories

Factory helpers provide convenient, opinionated constructors for common
encoders and generators.

## EncoderFactory

Namespace: `Inane\IdForge\EncoderFactory`

Creates encoder instances with sensible default alphabets.

API

- `createBase32(): Base32Encoder` — RFC 4648 alphabet `A-Z2-7`

- `createBase58(): Base58Encoder` — Bitcoin alphabet (no `0`, `O`, `I`,
  `l`)

- `createBase64(): Base64Encoder` — Standard Base64 alphabet

Usage

    use Inane\IdForge\EncoderFactory;

    $base32 = EncoderFactory::createBase32();
    $base58 = EncoderFactory::createBase58();
    $base64 = EncoderFactory::createBase64();

## IdGeneratorFactory

Namespace: `Inane\IdForge\IdGeneratorFactory`

Creates generator instances with defaults and safe validation where
applicable.

API

- `createNanoid(string $alphabet = '0-9a-zA-Z', int $size = 21): NanoidGenerator`

- `createSnowflake(int $workerId = 0, int $datacenterId = 0, ?SnowflakeConfig $config = null): SnowflakeIdGenerator`

- `createUUID(): UUIDGenerator`

- `createULID(?EncoderConfig $config = null): ULIDGenerator`

Usage

    use Inane\IdForge\IdGeneratorFactory;

    $uuid   = IdGeneratorFactory::createUUID()->generate();
    $ulid   = IdGeneratorFactory::createULID()->generate();
    $nanoid = IdGeneratorFactory::createNanoid()->generate();
    $snow   = IdGeneratorFactory::createSnowflake(workerId: 1, datacenterId: 2)->generate();

## See also

- [Encoders](encoders.xml)

- [Generators](generators.xml)

- [Configuration](config.xml)

# Example

Some examples

    use Inane\IdForge\Config\EncoderConfig;
    use Inane\IdForge\Config\SnowflakeConfig;
    use Inane\IdForge\Encoder\AbstractEncoder;
    use Inane\IdForge\EncoderFactory;
    use Inane\IdForge\Generator\AbstractIdGenerator;
    use Inane\IdForge\IdGeneratorFactory;

    // Example usage
    try {
        // Create encoders via factory
        $base32 = EncoderFactory::createBase32();
        $base58 = EncoderFactory::createBase58();
        $base64 = EncoderFactory::createBase64();

        // Create ID generators via factory
        $nanoid = IdGeneratorFactory::createNanoid();
        $snowflake = IdGeneratorFactory::createSnowflake(1, 1);
        $uuid = IdGeneratorFactory::createUUID();
        $ulid = IdGeneratorFactory::createULID();

        // Base32
        $base32Encoded = $base32->encode('Hello');
        echo "Base32 Encoded: $base32Encoded\n";
        echo 'Base32 Decoded: ' . $base32->decode($base32Encoded) . "\n";
        echo PHP_EOL;

        // Base58
        $base58Encoded = $base58->encode('Hello');
        echo "Base58 Encoded: $base58Encoded\n";
        echo 'Base58 Decoded: ' . $base58->decode($base58Encoded) . "\n";
        echo PHP_EOL;

        // Base64
        $base64Encoded = $base64->urlEncode('Hello');
        echo "Base64 URL Encoded: $base64Encoded\n";
        echo 'Base64 URL Decoded: ' . $base64->urlDecode($base64Encoded) . "\n";
        echo PHP_EOL;

        // Nanoid
        echo 'Nanoid: ' . $nanoid->generate() . "\n";
        echo PHP_EOL;

        // Snowflake ID
        $snowflakeId = $snowflake->generate();
        echo "Snowflake ID: $snowflakeId\n";
        echo 'Snowflake ID (Base58): ' . $snowflake->toEncoded($base58) . "\n";
        echo PHP_EOL;

        // UUID
        $uuidValue = $uuid->generate();
        echo "UUID: $uuidValue\n";
        $uuidBase64 = $uuid->toBase64($uuidValue, $base64);
        echo "UUID Base64: $uuidBase64\n";
        echo 'UUID from Base64: ' . $uuid->fromBase64($uuidBase64, $base64) . "\n";
        echo PHP_EOL;

        // ULID
        $ulidValue = $ulid->generate();
        echo "ULID: $ulidValue\n";
        echo 'ULID Timestamp: ' . $ulid->decodeTimestamp($ulidValue) . "\n";
        echo 'ULID Base32: ' . $ulid->toEncoded($base32) . "\n";
        echo PHP_EOL;

        // ULID
        $ulidValue = $ulid->generate(1761168799791);
        echo "ULID 2: $ulidValue\n";
        echo 'ULID 2 Timestamp: ' . $ulid->decodeTimestamp($ulidValue) . "\n";
        echo 'ULID 2 Base32: ' . $ulid->toEncoded($base32) . "\n";
    } catch (Exception $e) {
        echo 'Error: ' . $e->getMessage() . "\n";
    }

    // Add a New Encoder:
    class Base16Encoder extends AbstractEncoder {
        public function __construct() {
            parent::__construct(new EncoderConfig('0123456789ABCDEF'));
        }

        public function encode(string $data): string {
            return strtoupper(bin2hex($data));
        }

        public function decode(string $data): string {
            return hex2bin($data);
        }
    }

    class Encoder2Factory {
        public static function createBase16(): Base16Encoder {
            return new Base16Encoder();
        }
    }

    $base16 = Encoder2Factory::createBase16()->encode('Hello');
    $text = Encoder2Factory::createBase16()->decode($base16);
    $line("Base16:encoded: $base16");
    $line("Base16:decoded: $text");
    // EncoderFactory::createBase16 = fn() => new Base16Encoder();


    // Add a New ID Generator:
    class CustomIdGenerator extends AbstractIdGenerator {
        public function generate(): string {
            $timestamp = $this->getTimestamp();
            $random = $this->getRandomBytes(8);
            return bin2hex($timestamp . $random);
        }
    }

    class IdGenerator2Factory {
        public static function createCustomId(): CustomIdGenerator {
            return new CustomIdGenerator();
        }
    }

    $customId = IdGenerator2Factory::createCustomId()->generate();
    $line("CustomID: $customId");

    // IdGeneratorFactory::createCustomId = fn() => new CustomIdGenerator();

    // Customize Snowflake Configuration:
    $customConfig = new SnowflakeConfig(1640995200000, 4, 4, 10); // Custom epoch, fewer bits
    $snowflake = IdGeneratorFactory::createSnowflake(1, 1, $customConfig);

    // Custom Alphabet for Nanoid:
    $nanoid = IdGeneratorFactory::createNanoid('0123456789abcdef', 12); // Hex-only, shorter length

# Error handling

- Most decoders will throw
  `Inane\Stdlib\Exception\InvalidArgumentException` when the input
  contains invalid characters or cannot be parsed.

- `SnowflakeIdGenerator::generate()` can throw
  `Inane\Stdlib\Exception\RuntimeException` if it detects a system clock
  moving backwards.

- Generators that rely on randomness may throw `Random\RandomException`
  from PHP core when entropy is not available.

# When to use which generator

- UUIDv4: Standard interoperable identifiers, not sortable, 36 chars.

- ULID: 26-char, lexicographically sortable, timestamp + randomness;
  good for DB keys and logs.

- Nanoid: Short, URL-friendly IDs with controllable alphabet and size.

- Snowflake: Numeric IDs composed of timestamp + worker/datacenter +
  sequence; good for distributed systems that need k-sortable numbers.
