<?php

declare(strict_types=1);

/*
 * This file is part of the "Expressions Parser" package.
 * (c) Skoropadskyi Roman <zipo.ckorop@gmail.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zippovich2\ExpressionsParser\Test\Parser;

use PHPUnit\Framework\TestCase;
use Zippovich2\ExpressionsParser\Parser\LogicalParser;

class LogicalParserTest extends TestCase
{
    /**
     * @var LogicalParser
     */
    private $parser;

    protected function setUp(): void
    {
        $this->parser = new LogicalParser();
    }

    public function booleanParseValuesProvider(): array
    {
        return [
            ['true', true],
            ['false', false],
            ['0', false],
            ['1', true],
        ];
    }

    public function logicalExpressionsProvider(): array
    {
        return [
            ['true || false', true],
            ['false or true', true],
            ['true && false', false],
            ['false and true', false],
            ['true and true', true],
            ['true xor false', true],
            ['true xor true', false],
            ['false xor false', false],
            ['2 > 3', false],
            ['2 >= 3', false],
            ['3 > 2', true],
            ['2 >= 2', true],
            ['2 < 3', true],
            ['2 <= 3', true],
            ['3 < 2', false],
            ['2 <= 2', true],
            ['2 > 3 and 2 < 5', false],
            ['2 = 3', false],
            ['2 = 2', true],
            ['not(false)', true],
            ['not(true)', false],
            ['not(3 > 1)', false],
            ['not(3 < 1)', true],
        ];
    }

    /**
     * @dataProvider booleanParseValuesProvider
     */
    public function testParseBoolean($value, $expectedResult): void
    {
        static::assertEquals($expectedResult, LogicalParser::parseBoolean($value));
    }

    /**
     * @dataProvider logicalExpressionsProvider
     */
    public function testLogicalParser($expression, $expectedResult): void
    {
        static::assertEquals($expectedResult, $this->parser->eval($expression));
    }
}
