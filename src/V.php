<?php

declare(strict_types=1);

namespace Validate;

use function array_key_exists;
use function count;
use function is_array;
use function is_bool;
use function is_float;
use function is_int;
use function is_string;
use function preg_match;
use function strlen;

final class V
{
    ////////// Combinator \\\\\\\\\\

    /**
     * Lazy Boolean `&&` over each validators
     */
    public static function and(callable ...$validators): callable
    {
        return static function ($input) use ($validators): bool {
            foreach ($validators as $validator) {
                if ($validator($input) === false) {
                    return false;
                }
            }

            return true;
        };
    }

    /**
     * Lazy Boolean `||` over each validators
     */
    public static function or(callable ...$validators): callable
    {
        return static function ($input) use ($validators): bool {
            foreach ($validators as $validator) {
                if ($validator($input) === true) {
                    return true;
                }
            }

            return false;
        };
    }

    /**
     * Negates a validator
     */
    public static function not(callable $validator): callable
    {
        return static fn ($input): bool => ! $validator($input);
    }

    /**
     * Allow null value or validator
     */
    public static function opt(callable $validator): callable
    {
        return static fn ($input): bool => $input === null || $validator($input);
    }

    ////////// Field level: Value \\\\\\\\\\

    /**
     * Test that the value is similar (`==`)
     *
     * @param mixed $similar The value it should be similar to
     */
    public static function similar($similar): callable
    {
        // phpcs:ignore SlevomatCodingStandard.Operators.DisallowEqualOperators.DisallowedEqualOperator
        return static fn ($input): bool => $input == $similar;
    }

    /**
     * Test that the value is exactly the same (`===`)
     *
     * @param mixed $exact The value it should be exactly equal to
     */
    public static function exact($exact): callable
    {
        return static fn ($input): bool => $input === $exact;
    }

    ////////// Field level: Types \\\\\\\\\\

    /**
     * Test that the value is an integer
     */
    public static function integer(): callable
    {
        return static fn ($input): bool => is_int($input);
    }

    /**
     * Test that the value is a float
     */
    public static function float(): callable
    {
        return static fn ($input): bool => is_float($input);
    }

    /**
     * Test that the value is a string
     */
    public static function string(): callable
    {
        return static fn ($input): bool => is_string($input);
    }

    /**
     * Test that the value is a boolean
     */
    public static function boolean(): callable
    {
        return static fn ($input): bool => is_bool($input);
    }

    /**
     * Test that the value is null
     */
    public static function null(): callable
    {
        return static fn ($input): bool => null === $input;
    }

    /**
     * Test that the value is an array
     */
    public static function array(): callable
    {
        return static fn ($input): bool => is_array($input);
    }

    ////////// Field level: Pattern \\\\\\\\\\

    /**
     * Test that the value match the provided regex pattern
     */
    public static function pattern(string $regex): callable
    {
        return static fn ($input): bool => preg_match($regex, $input) === 1;
    }

    /**
     * Test that the value is lowercase
     */
    public static function lowercase(): callable
    {
        return static fn ($input): bool => self::pattern('/^([a-z]+\s*)+$/')($input);
    }

    /**
     * Test that the value is uppercase
     */
    public static function uppercase(): callable
    {
        return static fn ($input): bool => self::pattern('/^([A-Z]+\s*)+$/')($input);
    }

    /**
     * Test that the value is a vowel
     */
    public static function vowel(): callable
    {
        return static fn ($input): bool => self::pattern('/^[aeiouy]+$/i')($input);
    }

    /**
     * Test that the value is a consonant
     */
    public static function consonant(): callable
    {
        return static function ($input): bool {
            return self::pattern('/^[a-z]+$/i')($input) && ! self::pattern('/[aeiouy]/i')($input);
        };
    }

    ////////// Field level: Arrays \\\\\\\\\\

    /**
     * Test that every value of the array match the given predicate
     */
    public static function every(callable $validator): callable
    {
        return static function ($input) use ($validator): bool {
            foreach ($input as $item) {
                if ($validator($item) === false) {
                    return false;
                }
            }

            return true;
        };
    }

    /**
     * Test that any value of the array match the given predicate
     */
    public static function any(callable $validator): callable
    {
        return static function ($input) use ($validator): bool {
            foreach ($input as $item) {
                if ($validator($item) === true) {
                    return true;
                }
            }

            return false;
        };
    }

    /**
     * Test that the nth value of the array match the given predicate
     *
     * @param string|int $position
     */
    public static function at($position, callable $validator): callable
    {
        return static fn ($input): bool => array_key_exists($position, $input) && $validator($input[$position]);
    }

    ////////// Field level: Length \\\\\\\\\\

    /**
     * Test that the value is `empty`
     */
    public static function empty(): callable
    {
        return static fn ($input): bool => empty($input);
    }

    /**
     * Available on arrays and string. Test that the value length is between $min and $max
     */
    public static function length(int $min, int $max): callable
    {
        return static function ($input) use ($min, $max): bool {
            return ($length = self::extractLength($input)) !== false && $min <= $length && $length <= $max;
        };
    }

    /**
     * Available on arrays and string. Test that the value length is greater than $min
     */
    public static function minLength(int $min): callable
    {
        return static fn ($input): bool => ($length = self::extractLength($input)) !== false && $min <= $length;
    }

    /**
     * Available on arrays and string. Test that the value length is lesser than $max
     */
    public static function maxLength(int $max): callable
    {
        return static fn ($input): bool => ($length = self::extractLength($input)) !== false && $max >= $length;
    }

    ////////// Field level: Ranges \\\\\\\\\\

    /**
     * Tests that the value is negative
     */
    public static function negative(): callable
    {
        return static fn ($input): bool => $input < 0;
    }

    /**
     * Tests that the value is positive
     */
    public static function positive(): callable
    {
        return static fn ($input): bool => $input >= 0;
    }

    /**
     * Tests that the value is in the specified range (inclusive)
     *
     * @param int|float $min
     * @param int|float $max
     */
    public static function range($min, $max): callable
    {
        return static fn ($input): bool => $min <= $input && $input <= $max;
    }

    /**
     * Tests that the value is less than $max
     *
     * @param int|float $max
     */
    public static function lessThan($max): callable
    {
        return static fn ($input): bool => $input < $max;
    }

    /**
     * Tests that the value is less than or equal to $max
     *
     * @param int|float $max
     */
    public static function lessThanOrEqual($max): callable
    {
        return static fn ($input): bool => $input <= $max;
    }

    /**
     * Tests that the value is greater than $min
     *
     * @param int|float $min
     */
    public static function greaterThan($min): callable
    {
        return static fn ($input): bool => $input > $min;
    }

    /**
     * Tests that the value is greater than or equal to $min
     *
     * @param int|float $min
     */
    public static function greaterThanOrEqual($min): callable
    {
        return static fn ($input): bool => $input >= $min;
    }

    ////////// Field level: Divisible \\\\\\\\\\

    /**
     * Test that the value is even
     */
    public static function even(): callable
    {
        return static fn ($input): bool => $input % 2 === 0;
    }

    /**
     * Test that the value is odd
     */
    public static function odd(): callable
    {
        return static fn ($input): bool => $input % 2 !== 0;
    }

    ////////// Internal Utils \\\\\\\\\\

    /**
     * Utils to extract the length from a string or an array
     *
     * @param mixed $input The input to extract length from
     *
     * @return int|false
     */
    private static function extractLength($input)
    {
        if (is_array($input)) {
            return count($input);
        }

        if (is_string($input)) {
            return strlen($input);
        }

        return false;
    }
}
