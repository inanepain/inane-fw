<?php

/**
 * inane-fw
 *
 * Inane Framework
 *
 * $Id$
 * $Date$
 *
 * PHP version 8.5
 *
 * @author   Philip Michael Raab <philip@cathedral.co.za>
 * @package  inanepain\inane-fw
 * @category inane-fw
 *
 * @license  UNLICENSE
 * @license  https://unlicense.org/UNLICENSE UNLICENSE
 *
 * _version_ $version
 */

declare(strict_types = 1);

if (!function_exists('is_whole_float')) {
    /**
     * Checks if the given value is a whole number, even when provided as a float.
     *
     * @param int|float $value The number to be checked.
     *
     * @return bool Returns true if the number is a whole number, otherwise false.
     */
    function is_whole_float(int|float $value): bool {
        return floor($value) === $value;
    }
}

if (!function_exists('normalise_number')) {
    /**
     * Normalises a given value into either an integer or a floating-point number.
     *
     * If the input is numeric but in string form, it will be converted to a numeric type.
     * Whole floats are cast to integers if within the range of PHP_INT_MIN and PHP_INT_MAX.
     *
     * @param int|float|string $value The value to be normalised. It can be an integer,
     *                                a floating-point number, or a numeric string.
     *
     * @return int|float The normalised value as either an integer or a floating-point number.
     *
     * @throws InvalidArgumentException If the provided value is not numeric.
     */
    function normalise_number(int|float|string $value): int|float {
        if (is_int($value) || is_float($value)) {
            $number = (float)$value;
        } else {
            $value = trim($value);

            if (!is_numeric($value)) {
                throw new InvalidArgumentException('Value is not numeric.');
            }

            $number = (float)$value;
        }

        // Convert whole floats to int
        if (
            $number >= PHP_INT_MIN &&
            $number <= PHP_INT_MAX &&
            fmod($number, 1.0) === 0.0
        ) {
            return (int)$number;
        }

        return $number;
    }
}

if (!function_exists('clamp')) {
    /**
     * Clamps a given value within the inclusive range defined by a minimum and maximum boundary.
     *
     * @param int|float $value The value to be clamped.
     * @param int|float $min   The minimum boundary.
     * @param int|float $max   The maximum boundary.
     *
     * @return int|float The clamped value within the specified range.
     */
    function clamp(int|float $value, int|float $min, int|float $max): int|float {
        return max($min, min($value, $max));
    }
}
