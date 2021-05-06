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
use Zippovich2\ExpressionsParser\OperatorFactory;
use Zippovich2\ExpressionsParser\OperatorsList;
use Zippovich2\ExpressionsParser\Parser;

class ParserTest extends TestCase
{
    /**
     * @var Parser
     */
    private $parser;

    protected function setUp(): void
    {
        $operatorsList = new OperatorsList();
        $operatorsList->add(OperatorFactory::leftAssociative('+', function ($a, $b) {
            return $a + $b;
        }));
        $operatorsList->add(OperatorFactory::leftAssociative('-', function ($a, $b) {
            return $a - $b;
        }));
        $operatorsList->add(OperatorFactory::leftAssociative('*', null, 2));
        $operatorsList->add(OperatorFactory::leftAssociative('/', null, 2));
        $operatorsList->add(OperatorFactory::rightAssociative('^'));
        $operatorsList->add(OperatorFactory::func('max'));
        $operatorsList->add(OperatorFactory::constant('e', \M_E));

        $this->parser = new Parser($operatorsList);
    }

    public function expressionsTokenProvider(): array
    {
        return [
            ['1+2+3', ['1', '+', '2', '+', '3']],
            ['1+ 2 + 3', ['1', '+', '2', '+', '3']],
            ['1+e*(2-1)', ['1', '+', 'e', '*', '(', '2', '-', '1', ')']],
            ['2 * max(1,2, 8, 1 )  ', ['2', '*', 'max', '(', '1', ',', '2', ',', '8', ',', '1', ')']],
            ['1 * 22 + 3*(1   -13*(1+2))', ['1', '*', '22', '+', '3', '*', '(', '1', '-', '13', '*', '(', '1', '+', '2', ')', ')']],
            ['3 + 4 * 2 / ( 1 - 5 ) ^ 2 ^ 3', ['3', '+', '4', '*', '2', '/', '(', '1', '-', '5', ')', '^', '2', '^', '3']],
        ];
    }

    public function RPNExpressionsProvider(): array
    {
        return [
            ['1+2+3', ['1', '2', '+', '3', '+']],
            ['1+ 2 + 3', ['1', '2', '+', '3', '+']],
            ['1+e*(2-1)', ['1', 'e', '2', '1', '-', '*', '+']],
            ['2 * max(1,2, 8, 1 )  ', ['2', '1', '2', '8', '1', ',', ',', ',', 'max', '*']],
            ['1 * 22 + 3*(1   -13*(1+2))', ['1', '22', '*', '3', '1', '13', '1', '2', '+', '*', '-', '*', '+']],
            ['3 + 4 * 2 / ( 1 - 5 ) ^ 2 ^ 3', ['3', '4', '2', '*', '1', '5', '-', '2', '3', '^', '^', '/', '+']],
        ];
    }

    public function processRPNProvider(): array
    {
        return [
            [['1', '2', '+', '3', '+'], 6],
            [['2', '1', '2', '8', '1', ',', ',', ',', 'max', '*'], 16],
            [['1', 'e', '2', '1', '-', '*', '+'], 3.718281828459],
            [['1', '22', '*', '3', '1', '13', '1', '2', '+', '*', '-', '*', '+'], -92],
            [['3', '4', '2', '*', '1', '5', '-', '2', '3', '^', '^', '/', '+'], 3.0001220703125],
        ];
    }

    public function parseAndProcessRPNProvider(): array
    {
        return [
            ['1+2+3', 6],
            ['2 * max(1,2, 8, 1 )  ', 16],
            ['1+e*(2-1)', 3.718281828459],
            ['1 * 22 + 3*(1   -13*(1+2))', -92],
            ['3 + 4 * 2 / ( 1 - 5 ) ^ 2 ^ 3', 3.0001220703125],
        ];
    }

    public function processRPNExceptionProvider(): array
    {
        return [
            [['1', '2', '*'], 'No callback was provided for "*" operator.'],
            [['1', '+', '+', '3'], 'Invalid expression.'],
            [['1', '3', '+', '3'], 'Invalid expression.'],
        ];
    }

    /**
     * @dataProvider expressionsTokenProvider
     */
    public function testParseTokens(string $expression, array $expectedTokens): void
    {
        static::assertSame($expectedTokens, $this->parser->parseTokens($expression));
    }

    /**
     * @dataProvider RPNExpressionsProvider
     */
    public function testConvertToRPN(string $expression, array $expectedRPN): void
    {
        static::assertSame($expectedRPN, $this->parser->convertToRPN($expression));
    }

    public function testConvertToRPNMissedLeftParenthesis(): void
    {
        static::expectException(\RuntimeException::class);
        static::expectExceptionMessage('Missed "(".');

        $this->parser->convertToRPN('2*3-1)');
    }

    public function testConvertToRPNMissedRightParenthesis(): void
    {
        static::expectException(\RuntimeException::class);
        static::expectExceptionMessage('Missed ")".');

        $this->parser->convertToRPN('2*(3-1');
    }

    /**
     * @dataProvider processRPNProvider
     */
    public function testProcessRPN(array $rpn, $result): void
    {
        $defaultCallback = function ($operator, ...$tokens) {
            switch ($operator) {
                case '*':
                    return $tokens[0] * $tokens[1];
                case '/':
                    return $tokens[0] / $tokens[1];
                case '^':
                    return $tokens[0] ** $tokens[1];
                case 'max':
                    return \max(...$tokens);
            }

            throw new \LogicException('Unreachable code.');
        };

        static::assertEquals($result, $this->parser->processRPN($rpn, $defaultCallback));
    }

    /**
     * @dataProvider parseAndProcessRPNProvider
     */
    public function testEval(string $expression, $result): void
    {
        $defaultCallback = function ($operator, ...$tokens) {
            switch ($operator) {
                case '*':
                    return $tokens[0] * $tokens[1];
                case '/':
                    return $tokens[0] / $tokens[1];
                case '^':
                    return $tokens[0] ** $tokens[1];
                case 'max':
                    return \max(...$tokens);
            }

            throw new \LogicException('Unreachable code.');
        };

        static::assertEquals($result, $this->parser->eval($expression, $defaultCallback));
    }

    public function testSetOperators(): void
    {
        $newOperators = new OperatorsList();
        $newOperators->add(new Operator('&', 1, function ($a, $b) {
            return $a && $b;
        }));

        $this->parser->setOperators($newOperators);

        $tokens = $this->parser->parseTokens('1 & 0');
        $rpn = $this->parser->convertToRPN('1 & 0');
        $result = $this->parser->processRPN($rpn);

        static::assertEquals(['1', '&', '0'], $tokens);
        static::assertEquals(['1', '0', '&'], $rpn);
        static::assertFalse($result);
    }

    public function testAddOperator(): void
    {
        $this->parser->addOperator(new Operator('&', 1, function ($a, $b) {
            return $a && $b;
        }));

        $tokens = $this->parser->parseTokens('1 & 0');
        $rpn = $this->parser->convertToRPN('1 & 0');
        $result = $this->parser->processRPN($rpn);

        static::assertEquals(['1', '&', '0'], $tokens);
        static::assertEquals(['1', '0', '&'], $rpn);
        static::assertFalse($result);
    }

    public function testGetOperators(): void
    {
        static::assertInstanceOf(OperatorsList::class, $this->parser->getOperators());
    }

    /**
     * @dataProvider processRPNExceptionProvider
     */
    public function testProcessRPNExceptions(array $rpn, string $expectedExceptionMessage): void
    {
        static::expectException(\RuntimeException::class);
        static::expectExceptionMessage($expectedExceptionMessage);

        $this->parser->processRPN($rpn);
    }
}
