<?php

declare(strict_types=1);

/*
 * This file is part of the "Expressions Parser" package.
 * (c) Skoropadskyi Roman <zipo.ckorop@gmail.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zippovich2\ExpressionsParser\Parser;

use Zippovich2\ExpressionsParser\Operator;
use Zippovich2\ExpressionsParser\OperatorsList;
use Zippovich2\ExpressionsParser\Parser;

class ArithmeticalParser extends Parser
{
    public function __construct()
    {
        $operators = new OperatorsList();
        $operators->add(new Operator('+', 1, function ($a, $b) { return $a + $b; }));
        $operators->add(new Operator('-', 1, function ($a, $b) { return $a - $b; }));
        $operators->add(new Operator('*', 2, function ($a, $b) { return $a * $b; }));
        $operators->add(new Operator('/', 2, function ($a, $b) {
            if (0 == $b) {
                throw new \LogicException('Division by zero error detected.');
            }

            return $a / $b;
        }));
        $operators->add(new Operator('%', 2, function ($a, $b) { return $a % $b; }));
        $operators->add(new Operator('^', 3, function ($a, $b) { return $a ** $b; }, Operator::TYPE_RIGHT_ASSOCIATIVE));

        parent::__construct($operators);
    }
}
