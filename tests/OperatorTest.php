<?php

declare(strict_types=1);

/*
 * This file is part of the "Expressions Parser" package.
 * (c) Skoropadskyi Roman <zipo.ckorop@gmail.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zippovich2\ExpressionsParser\Test;

use PHPUnit\Framework\TestCase;
use Zippovich2\ExpressionsParser\Operator;

class OperatorTest extends TestCase
{
    public function reservedSymbolsProvider(): array
    {
        return [
            ['('],
            [')'],
            [','],
        ];
    }

    /**
     * @dataProvider reservedSymbolsProvider
     */
    public function testReservedSymbolException(string $symbol): void
    {
        static::expectException(\InvalidArgumentException::class);
        static::expectExceptionMessage(\sprintf('The symbols "%s" are reserved.', \implode('", "', Operator::RESERVED_OPERATOR_SYMBOLS)));

        new Operator($symbol);
    }
}
