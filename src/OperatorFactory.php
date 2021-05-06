<?php

declare(strict_types=1);

/*
 * This file is part of the "Expressions Parser" package.
 * (c) Skoropadskyi Roman <zipo.ckorop@gmail.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zippovich2\ExpressionsParser;

class OperatorFactory
{
    public static function leftAssociative(string $symbol, ?\Closure $callback = null, int $precedence = 1): Operator
    {
        return new Operator($symbol, $precedence, $callback, Operator::TYPE_LEFT_ASSOCIATIVE);
    }

    public static function rightAssociative(string $symbol, ?\Closure $callback = null, int $precedence = 3): Operator
    {
        return new Operator($symbol, $precedence, $callback, Operator::TYPE_RIGHT_ASSOCIATIVE);
    }

    public static function func(string $name, ?\Closure $callback = null): Operator
    {
        return new Operator($name, 1, $callback, Operator::TYPE_FUNCTION);
    }

    /**
     * @param mixed $value
     */
    public static function constant(string $name, $value): Operator
    {
        return new Operator($name, 1, function () use ($value) {
            return $value;
        }, Operator::TYPE_CONSTANT);
    }
}
