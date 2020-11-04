<?php

declare(strict_types=1);

namespace Validate;

use PHPUnit\Framework\TestCase;

class VTest extends TestCase
{
    public function testAnd(): void
    {
        self::assertTrue(V::and(V::float(), V::negative())(-0.1));
        self::assertFalse(V::and(V::float(), V::negative())(-1));
        self::assertFalse(V::and(V::float(), V::negative())(0.1));
    }

    public function testOr(): void
    {
        self::assertTrue(V::or(V::float(), V::integer())(0.1));
        self::assertTrue(V::or(V::float(), V::integer())(1));
        self::assertFalse(V::or(V::float(), V::integer())('notFloatNorInt'));
    }

    public function testNot(): void
    {
        $validator = V::positive();
        self::assertTrue($validator(0.1));
        self::assertFalse(V::not($validator)(0.1));
    }

    public function testOpt(): void
    {
        self::assertTrue(V::opt(V::positive())(null));
        self::assertTrue(V::opt(V::positive())(0.1));
        self::assertFalse(V::opt(V::positive())(-1));
    }

    public function testSimilar(): void
    {
        self::assertTrue(V::similar(12)('12'));
        self::assertTrue(V::similar(12)(12));
        self::assertFalse(V::similar(12)(3));
    }

    public function testExact(): void
    {
        self::assertTrue(V::exact(12)(12));
        self::assertFalse(V::exact(12)('12'));
        self::assertFalse(V::exact(12)(3));
    }

    public function testInteger(): void
    {
        self::assertTrue(V::integer()(12));
        self::assertFalse(V::integer()('12'));
        self::assertFalse(V::integer()(12.1));
    }

    public function testFloat(): void
    {
        self::assertTrue(V::float()(12.1));
        self::assertFalse(V::float()('12'));
        self::assertFalse(V::float()(12));
    }

    public function testString(): void
    {
        self::assertTrue(V::string()('test'));
        self::assertFalse(V::string()(12));
        self::assertFalse(V::string()([]));
    }

    public function testBoolean(): void
    {
        self::assertTrue(V::boolean()(true));
        self::assertFalse(V::boolean()(12));
        self::assertFalse(V::boolean()(null));
    }

    public function testNull(): void
    {
        self::assertTrue(V::null()(null));
        self::assertFalse(V::null()(! null));
    }

    public function testArray(): void
    {
        self::assertTrue(V::array()([]));
        self::assertFalse(V::array()(! []));
    }

    public function testPattern(): void
    {
        self::assertTrue(V::pattern('/^\d{2}$/')('12'));
        self::assertFalse(V::pattern('/^\d{2}$/')('1'));
    }

    public function testLowercase(): void
    {
        self::assertTrue(V::lowercase()('abcd'));
        self::assertFalse(V::lowercase()('aBcd'));
    }

    public function testUppercase(): void
    {
        self::assertTrue(V::uppercase()('ABCD'));
        self::assertFalse(V::uppercase()('AbCD'));
    }

    public function testVowel(): void
    {
        self::assertTrue(V::vowel()('aEiOuY'));
        self::assertFalse(V::vowel()('aediou'));
    }

    public function testConsonant(): void
    {
        self::assertTrue(V::consonant()('bCdFgHjKlMnPqRsTvWxZ'));
        self::assertFalse(V::consonant()('abcd'));
    }

    public function testEvery(): void
    {
        self::assertTrue(V::every(V::integer())([1, 2, 3]));
        self::assertFalse(V::every(V::integer())([1, 2.1, 3]));
    }

    public function testAny(): void
    {
        self::assertTrue(V::any(V::integer())([1.1, 2.2, 3]));
        self::assertFalse(V::any(V::integer())([1.1, 2.2, 3.3]));
    }

    public function testAt(): void
    {
        self::assertTrue(V::at(1, V::integer())([1.1, 2, 3.3]));
        self::assertFalse(V::at(1, V::integer())([1.1, 2.2, 3.3]));
        self::assertTrue(V::at('test', V::integer())([1.1, 'test' => 2, 3.3]));
        self::assertFalse(V::at('test', V::integer())([1.1, 'test' => 2.2, 3.3]));
    }

    public function testEmpty(): void
    {
        self::assertTrue(V::empty()(null));
        self::assertTrue(V::empty()([]));
        self::assertTrue(V::empty()(''));
        self::assertTrue(V::empty()(0));
        self::assertFalse(V::empty()([1]));
    }

    public function testLength(): void
    {
        self::assertTrue(V::length(1, 1)([1]));
        self::assertTrue(V::length(1, 1)('1'));
        self::assertFalse(V::length(1, 1)(''));
        self::assertFalse(V::length(1, 1)('12'));
    }

    public function testMinLength(): void
    {
        self::assertTrue(V::minLength(1)([1]));
        self::assertTrue(V::minLength(1)('1'));
        self::assertFalse(V::minLength(1)(''));
    }

    public function testMaxLength(): void
    {
        self::assertTrue(V::maxLength(1)([1]));
        self::assertTrue(V::maxLength(1)('1'));
        self::assertFalse(V::maxLength(1)('12'));
    }

    public function testNegative(): void
    {
        self::assertTrue(V::negative()(-0.1));
        self::assertTrue(V::negative()(-1));
        self::assertFalse(V::negative()(0));
    }

    public function testPositive(): void
    {
        self::assertTrue(V::positive()(0));
        self::assertTrue(V::positive()(0.1));
        self::assertFalse(V::positive()(-1));
    }

    public function testRange(): void
    {
        self::assertTrue(V::range(0.1, 0.2)(0.1));
        self::assertTrue(V::range(0.1, 0.2)(0.2));
        self::assertFalse(V::range(0.1, 0.2)(0.09));
        self::assertFalse(V::range(0.1, 0.2)(0.21));
        self::assertTrue(V::range(1, 2)(1));
        self::assertTrue(V::range(1, 2)(2));
        self::assertFalse(V::range(1, 2)(0));
        self::assertFalse(V::range(1, 2)(3));
    }

    public function testLessThan(): void
    {
        self::assertTrue(V::lessThan(0.2)(0.1));
        self::assertFalse(V::lessThan(0.2)(0.2));
        self::assertTrue(V::lessThan(2)(1));
        self::assertFalse(V::lessThan(2)(2));
    }

    public function testLessThanOrEqual(): void
    {
        self::assertTrue(V::lessThanOrEqual(0.2)(0.2));
        self::assertFalse(V::lessThanOrEqual(0.2)(0.21));
        self::assertTrue(V::lessThanOrEqual(2)(2));
        self::assertFalse(V::lessThanOrEqual(2)(3));
    }

    public function testGreaterThan(): void
    {
        self::assertTrue(V::greaterThan(0.1)(0.2));
        self::assertFalse(V::greaterThan(0.1)(0.1));
        self::assertTrue(V::greaterThan(1)(2));
        self::assertFalse(V::greaterThan(1)(1));
    }

    public function testGreaterThanOrEqual(): void
    {
        self::assertTrue(V::greaterThanOrEqual(0.1)(0.1));
        self::assertFalse(V::greaterThanOrEqual(0.1)(0.09));
        self::assertTrue(V::greaterThanOrEqual(1)(1));
        self::assertFalse(V::greaterThanOrEqual(1)(0));
    }

    public function testEven(): void
    {
        self::assertTrue(V::even()(2));
        self::assertFalse(V::even()(1));
    }

    public function testOdd(): void
    {
        self::assertTrue(V::odd()(1));
        self::assertFalse(V::odd()(2));
    }

    public function testWrongExtractLength(): void
    {
        self::assertFalse(V::minLength(0)(1));
    }
}
