<?php

declare(strict_types=1);

/*
 * This file is part of the "Expressions Parser" package.
 * (c) Skoropadskyi Roman <zipo.ckorop@gmail.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zippovich2\ExpressionsParser;

class Parser
{
    /**
     * @var OperatorsList
     */
    private $operators;

    public function __construct(OperatorsList $operators)
    {
        $this->operators = $operators;
    }

    public function setOperators(OperatorsList $operators): void
    {
        $this->operators = $operators;
    }

    public function getOperators(): OperatorsList
    {
        return $this->operators;
    }

    public function parseTokens(string $expression): array
    {
        $operatorsSymbols = \array_merge($this->operators->getSymbolsArray(), ['(', ')', ',']);

        $preparedOperators = \array_map(function ($operatorSymbol) {
            return \preg_quote($operatorSymbol, '/');
        }, $operatorsSymbols);

        $tokens = [];
        $operatorsRegex = \sprintf('/^(%s).*/i', \implode('|', $preparedOperators));
        $notOperatorString = '';

        while (\strlen($expression) > 0) {
            \preg_match($operatorsRegex, $expression, $matches);

            if (null !== $matches && \count($matches) > 1) {
                if (\strlen($notOperatorString) > 0) {
                    $preparedString = \trim($notOperatorString);

                    if (\strlen($preparedString) > 0) {
                        $tokens[] = \trim($notOperatorString);
                    }

                    $notOperatorString = '';
                }

                $tokens[] = $matches[1];
                $expression = \substr($expression, \strlen($matches[1]));
            } else {
                $notOperatorString .= $expression[0];
                $expression = \substr($expression, 1);
            }
        }

        if (\strlen($notOperatorString) > 0) {
            $preparedString = \trim($notOperatorString);

            if (\strlen($preparedString) > 0) {
                $tokens[] = \trim($notOperatorString);
            }
        }

        return $tokens;
    }

    public function convertToRPN(string $expression): array
    {
        $tokens = $this->parseTokens($expression);
        $operatorsSymbols = \array_merge($this->operators->getSymbolsArray(), ['(', ')', ',']);
        $output = [];
        $operatorStack = [];

        foreach ($tokens as $token) {
            if (!\in_array($token, $operatorsSymbols, true)) {
                $output[] = $token;

                continue;
            }

            if (!\in_array($token, ['(', ')'], true)) {
                if (0 === \count($operatorStack)) {
                    \array_unshift($operatorStack, $token);

                    continue;
                }

                $lastOperatorInStackPrecedence = $this->operators->getPrecedence($operatorStack[0]);
                $currentOperatorPrecedence = $this->operators->getPrecedence($token);
                $operatorType = $this->operators->getOperatorType($token);

                while (
                    0 !== \count($operatorStack)
                    && (
                        ($lastOperatorInStackPrecedence > $currentOperatorPrecedence)
                        || (($lastOperatorInStackPrecedence === $currentOperatorPrecedence) && Operator::TYPE_LEFT_ASSOCIATIVE === $operatorType)
                    )
                    && '(' !== $operatorStack[0]
                ) {
                    $output[] = \array_shift($operatorStack);

                    if (\count($operatorStack) > 0) {
                        $lastOperatorInStackPrecedence = $this->operators->getPrecedence($operatorStack[0]);
                    }
                }

                \array_unshift($operatorStack, $token);
            } elseif ('(' === $token) {
                \array_unshift($operatorStack, $token);
            } elseif (')' === $token) {
                if (!\in_array('(', $operatorStack, true)) {
                    throw new \RuntimeException('Missed "(".');
                }

                while ('(' !== $operatorStack[0]) {
                    $output[] = \array_shift($operatorStack);
                }

                \array_shift($operatorStack);
            }
        }

        if (\in_array('(', $operatorStack, true)) {
            throw new \RuntimeException('Missed ")".');
        }

        foreach ($operatorStack as $operatorInStack) {
            $output[] = $operatorInStack;
        }

        return $output;
    }

    /**
     * @return mixed
     */
    public function processRPN(array $rpn, ?\Closure $defaultCallback = null)
    {
        $stack = [];
        $operatorsSymbols = $this->operators->getSymbolsArray();

        foreach ($rpn as $token) {
            $stack[] = $token;

            if (\in_array($token, $operatorsSymbols, true)) {
                $numberOfTokens = 2;
                $operatorSymbol = \array_pop($stack);
                $tokens = [];

                if (Operator::TYPE_FUNCTION === $this->operators->getOperatorType($operatorSymbol)) {
                    $numberOfTokens = 0;
                    $lastToken = \array_pop($stack);

                    while (',' === $lastToken) {
                        ++$numberOfTokens;
                        $lastToken = \array_pop($stack);
                    }

                    $tokens[] = $lastToken;
                }

                while ($numberOfTokens > 0) {
                    $tokens[] = \array_pop($stack);
                    --$numberOfTokens;
                }
                $tokens = \array_reverse($tokens);

                foreach ($tokens as $item) {
                    if (null === $item
                        || null === $operatorSymbol
                        || \in_array($item, $operatorsSymbols, true)
                    ) {
                        throw new \RuntimeException('Invalid expression.');
                    }
                }

                $operator = $this->operators->findOperatorBySymbol($operatorSymbol);

                if (null !== $operator->getCallback()) {
                    $stack[] = $operator->getCallback()(...$tokens);
                } elseif (null !== $defaultCallback) {
                    $stack[] = $defaultCallback($operatorSymbol, ...$tokens);
                } else {
                    throw new \RuntimeException(\sprintf('No callback was provided for "%s" operator.', $operatorSymbol));
                }
            }
        }

        if (1 !== \count($stack)) {
            throw new \RuntimeException('Invalid expression.');
        }

        return $stack[0];
    }

    /**
     * @return mixed
     */
    public static function parseAndProcess(string $expression, OperatorsList $operatorsList, ?\Closure $defaultCallback = null)
    {
        $parser = new self($operatorsList);
        $rpn = $parser->convertToRPN($expression);

        return $parser->processRPN($rpn, $defaultCallback);
    }
}
