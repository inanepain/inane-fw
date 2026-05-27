<?php

/**
 * AbstractHydratable
 *
 * Inane Library
 *
 * $Id$
 * $Date$
 *
 * PHP version 8.5
 *
 * @author   Philip Michael Raab <philip@cathedral.co.za>
 * @package  inanepain\abstract-hydratable
 * @category abstract-hydratable
 *
 * @license  UNLICENSE
 * @license  https://unlicense.org/UNLICENSE UNLICENSE
 *
 * _version_ $version
 */

declare(strict_types = 1);

namespace Inane\Db\Hydrator;

use Inane\Stdlib\Exception\DateMalformedStringException;
use Inane\Stdlib\Exception\ReflectionException;
use Inane\Stdlib\String\StringCaseConverter;
use ReflectionClass;
use ReflectionProperty;

use function array_key_exists;
use function gettype;
use function is_a;
use function is_int;
use function is_string;
use function json_decode;
use function json_encode;
use function ksort;

use const JSON_NUMERIC_CHECK;

/**
 * Abstract class providing hydration and reverse hydration functionalities.
 *
 * This class allows objects to be created (hydrated) from associative arrays
 * and to convert objects back into associative arrays (unhydrated). It also
 * supports custom alias mappings and basic type coercion for specific data types.
 */
abstract class AbstractHydratable {
    /** @var array<string, array<int, \ReflectionParameter>> */
    protected static array $constructorCache = [];

    /** @var array<string, array<string, string>> */
    protected static array $aliasCache = [];

    /**
     * Populates and returns an instance of the current class using the given data array.
     * The method resolves constructor parameters based on their names, aliases, or various case styles.
     * Performs type coercion for non-builtin types and supports default parameter values if no data is matched.
     *
     * @param array $data Associative array of data used to populate the instance properties.
     *
     * @return static A new instance of the calling class populated with provided data.
     *
     * @throws \ReflectionException Thrown if there is an issue with instantiating the class via reflection.
     * @throws DateMalformedStringException
     */
    public static function hydrate(array $data): static {
        $class = static::class;

        if (!isset(self::$constructorCache[$class])) {
            self::initReflection($class);
        }

        $params = self::$constructorCache[$class];
        $aliases = self::$aliasCache[$class];
        $args = [];

        foreach($params as $param) {
            $name = $param->getName();

            // Resolve value without triggering undefined index warnings.
            $value = null;

            // 1) Exact name
            if (array_key_exists($name, $data)) {
                $value = $data[$name];
            } else {
                // 2) Alias mapping from attribute OR case conversion in later iterations.
                if (isset($aliases[$name]) && array_key_exists($aliases[$name], $data)) {
                    $value = $data[$aliases[$name]];
                } else {
                    // 3) camelCase of name
                    $cc = StringCaseConverter::toCamel($name);
                    if (array_key_exists($cc, $data)) {
                        $value = $data[$cc];
                        // added to alias mapping for later iterations and dehydration.
                        self::$aliasCache[$class][$name] = $cc;
                    } else {
                        // 4) snake_case of name
                        $sc = StringCaseConverter::toSnake($name);
                        if (array_key_exists($sc, $data)) {
                            $value = $data[$sc];
                            // added to alias mapping for later iterations and dehydration.
                            self::$aliasCache[$class][$name] = $sc;
                        }
                    }
                }
            }

            $type = $param->getType();
            $typeName = $type->getName();
            $field = FieldType::tryFrom($typeName);
            if ($field || ($type instanceof \ReflectionNamedType && $value !== null && !$type->isBuiltin())) {
                // Basic type coercion for known non-builtin types
                if (is_a($typeName, \DateTimeInterface::class, true)) {
                    // Convert string dates OR int timestamps to DateTimeImmutable/DateTimeInterface
                    $value = match (true) {
                        is_string($value) => new \DateTimeImmutable($value),
                        is_int($value) => new \DateTimeImmutable('@' . $value),
                        default => $value,
                    };
                } elseif ($field === FieldType::JSON && is_string($value)) {
                    // Convert string dates to DateTimeImmutable/DateTimeInterface
                    $value = json_decode($value, true, JSON_NUMERIC_CHECK);
                }
            }

            // If still null and parameter has a default value, use it
            if ($value === null && $param->isDefaultValueAvailable()) {
                $value = $param->getDefaultValue();
            }

            $args[] = $value;
        }

        return new ReflectionClass(static::class)->newInstanceArgs($args);
    }

    /**
     * Reverse hydration: object → array
     */
    public function dehydrate(): array {
        $result = [];
        $ref = new ReflectionClass($this);

        foreach($ref->getProperties(ReflectionProperty::IS_PUBLIC | ReflectionProperty::IS_PROTECTED) as $prop) {
            if ($prop->isStatic()) continue;

            $aliases = self::$aliasCache[$ref->getName()] ?? [];
            $key = $aliases[$prop->getName()] ?? $prop->getName();
            $value = $prop->getValue($this);

            if ($value instanceof \DateTimeInterface) {
                foreach($prop->getAttributes(HydrateField::class) as $attr) {
                    $field = $attr->newInstance();
                    $value = match ($field->type) {
                        FieldType::Timestamp => $value->getTimestamp(),
                        FieldType::Datetime => $value->format($field->format ?? 'Y-m-d H:i:s'),
                        default => $value,
                    };
                }
            } elseif ($field = FieldType::tryFrom(gettype($value))) {
                $value = match ($field) {
                    FieldType::JSON => json_encode($value),
                    default => $value,
                };
            }

            $result[$key] = $value;
        }

        return $result;
    }

    /**
     * Initializes reflection metadata for the specified class.
     *
     * This method inspects the constructor of the provided class and caches the
     * constructor parameters along with any attributes used for mapping aliases.
     * The collected metadata is used to streamline object hydration processes.
     *
     * @param string $class The fully qualified name of the class to reflect on.
     *
     * @return void
     *
     * @throws ReflectionException If the class does not exist or is not loadable.
     */
    private static function initReflection(string $class): void {
        $ref = new ReflectionClass($class);
        $constructor = $ref->getConstructor();
        if (!$constructor) {
            self::$constructorCache[$class] = [];
            self::$aliasCache[$class] = [];

            return;
        }

        $params = [];
        $aliasesMap = [];

        foreach($constructor->getParameters() as $param) {
            $params[$param->getPosition()] = $param;

            // Read #[HydrateField('column_name')] attributes
            foreach($param->getAttributes(HydrateField::class) as $attr) {
                $name = $param->getName();
                $aliasesMap[$name] = $attr->newInstance()->alias ?? $name;
            }
        }

        // Sort by position to maintain constructor order for newInstanceArgs
        ksort($params);
        self::$constructorCache[$class] = $params;
        self::$aliasCache[$class] = $aliasesMap;
    }
}
