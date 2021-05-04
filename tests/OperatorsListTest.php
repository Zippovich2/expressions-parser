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
use Zippovich2\ExpressionsParser\OperatorsList;

class OperatorsListTest extends TestCase
{
    /**
     * @var OperatorsList
     */
    private $list;

    protected function setUp(): void
    {
        $this->list = new OperatorsList();

        $this->list->add(new Operator('+'));
        $this->list->add(new Operator('-'));
    }

    public function addArithmeticOperatorProvider(): array
    {
        return [
            ['+', 1, Operator::TYPE_LEFT_ASSOCIATIVE, 2],
            ['-', 1, Operator::TYPE_LEFT_ASSOCIATIVE, 2],
            ['*', 2, Operator::TYPE_LEFT_ASSOCIATIVE, 3],
            ['/', 2, Operator::TYPE_LEFT_ASSOCIATIVE, 3],
            ['^', 2, Operator::TYPE_RIGHT_ASSOCIATIVE, 3],
        ];
    }

    /**
     * @dataProvider addArithmeticOperatorProvider
     */
    public function testAddNewElements(string $symbol, int $precedence, int $type, int $expectedCount): void
    {
        $this->list->add(new Operator($symbol, $precedence, null, $type));

        static::assertCount($expectedCount, $this->list);
    }

    public function testFindOperatorBySymbol(): void
    {
        static::assertNull($this->list->findOperatorBySymbol('*'));
        static::assertNull($this->list->findOperatorBySymbol('/'));
        static::assertNull($this->list->findOperatorBySymbol('^'));
        static::assertInstanceOf(Operator::class, $this->list->findOperatorBySymbol('+'));
        static::assertInstanceOf(Operator::class, $this->list->findOperatorBySymbol('-'));
    }

    public function testRemoveOperatorUsingStringSymbol(): void
    {
        static::assertCount(2, $this->list);

        $this->list->remove('+');

        static::assertCount(1, $this->list);
        static::assertNotNull($this->list->findOperatorBySymbol('-'));

        $this->list->remove('-');

        static::assertCount(0, $this->list);
    }

    public function testRemoveOperatorUsingObject(): void
    {
        static::assertCount(2, $this->list);

        $this->list->remove(new Operator('+'));

        static::assertCount(1, $this->list);
        static::assertNotNull($this->list->findOperatorBySymbol('-'));

        $this->list->remove(new Operator('-'));

        static::assertCount(0, $this->list);
    }

    public function testContains(): void
    {
        static::assertTrue($this->list->contains('+'));
        static::assertTrue($this->list->contains('-'));
        static::assertTrue($this->list->contains(new Operator('+')));
        static::assertTrue($this->list->contains(new Operator('-')));

        static::assertFalse($this->list->contains('*'));
        static::assertFalse($this->list->contains('/'));
        static::assertFalse($this->list->contains(new Operator('*')));
        static::assertFalse($this->list->contains(new Operator('/')));
    }

    public function testGetSymbolsArray(): void
    {
        static::assertSame(['+', '-'], \array_values($this->list->getSymbolsArray()));

        $this->list->add(new Operator('*'));

        static::assertSame(['+', '-', '*'], \array_values($this->list->getSymbolsArray()));

        $this->list->remove('-');

        static::assertSame(['+', '*'], \array_values($this->list->getSymbolsArray()));
    }

    public function testGetPrecedence(): void
    {
        $this->list->add(new Operator('*', 2));
        $this->list->add(new Operator('/', 2));

        static::assertEquals(1, $this->list->getPrecedence('-'));
        static::assertEquals(1, $this->list->getPrecedence('+'));
        static::assertEquals(2, $this->list->getPrecedence('*'));
        static::assertEquals(2, $this->list->getPrecedence('/'));
        static::assertEquals(0, $this->list->getPrecedence('^'));
    }

    public function testGetOperatorType(): void
    {
        $this->list->add(new Operator('*', 2));
        $this->list->add(new Operator('/', 2));
        $this->list->add(new Operator('^', 3, null, Operator::TYPE_RIGHT_ASSOCIATIVE));
        $this->list->add(new Operator('max', 2, null, Operator::TYPE_FUNCTION));

        static::assertEquals(Operator::TYPE_LEFT_ASSOCIATIVE, $this->list->getOperatorType('-'));
        static::assertEquals(Operator::TYPE_LEFT_ASSOCIATIVE, $this->list->getOperatorType('+'));
        static::assertEquals(Operator::TYPE_LEFT_ASSOCIATIVE, $this->list->getOperatorType('*'));
        static::assertEquals(Operator::TYPE_LEFT_ASSOCIATIVE, $this->list->getOperatorType('/'));
        static::assertEquals(Operator::TYPE_RIGHT_ASSOCIATIVE, $this->list->getOperatorType('^'));
        static::assertEquals(Operator::TYPE_FUNCTION, $this->list->getOperatorType('max'));
        static::assertNull($this->list->getOperatorType('avg'));
    }

    /**
     * Just for coverage.
     */
    public function testGetIterator(): void
    {
        static::assertInstanceOf(\ArrayIterator::class, $this->list->getIterator());
    }
}
