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
use Zippovich2\ExpressionsParser\Parser\ArithmeticalParser;

class ArithmeticalParserTest extends TestCase
{
    /**
     * @var ArithmeticalParser
     */
    private $parser;

    protected function setUp(): void
    {
        $this->parser = new ArithmeticalParser();
    }

    public function arithmeticalExpressionsProvider(): array
    {
        return [
            ['1 + 2', 3],
            ['3 - 1', 2],
            ['3 * 2', 6],
            ['6 / 2', 3],
            ['5 % 2', 1],
            ['2 ^ 3', 8],
        ];
    }

    /**
     * @dataProvider arithmeticalExpressionsProvider
     */
    public function testArithmeticalParser($expression, $expectedResult): void
    {
        static::assertEquals($expectedResult, $this->parser->eval($expression));
    }

    public function testDividingByZeroException(): void
    {
        static::expectException(\LogicException::class);
        static::expectExceptionMessage('Division by zero error detected.');

        $this->parser->eval('3 / 0');
    }
}
