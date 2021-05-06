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

class LogicalParser extends Parser
{
    public function __construct()
    {
        $operators = new OperatorsList();
        $operators->add(new Operator('||', 1, function ($a, $b) { return self::parseBoolean($a) || self::parseBoolean($b); }));
        $operators->add(new Operator('or', 1, function ($a, $b) { return self::parseBoolean($a) || self::parseBoolean($b); }));
        $operators->add(new Operator('&&', 2, function ($a, $b) { return self::parseBoolean($a) && self::parseBoolean($b); }));
        $operators->add(new Operator('and', 2, function ($a, $b) { return self::parseBoolean($a) && self::parseBoolean($b); }));
        $operators->add(new Operator('xor', 3, function ($a, $b) { return self::parseBoolean($a) xor self::parseBoolean($b); }));
        $operators->add(new Operator('>', 4, function ($a, $b) { return $a > $b; }));
        $operators->add(new Operator('>=', 4, function ($a, $b) { return $a >= $b; }));
        $operators->add(new Operator('<', 4, function ($a, $b) { return $a < $b; }));
        $operators->add(new Operator('<=', 4, function ($a, $b) { return $a <= $b; }));
        $operators->add(new Operator('=', 4, function ($a, $b) { return $a == $b; }));
        $operators->add(new Operator('not', 1, function ($a) { return !self::parseBoolean($a); }, Operator::TYPE_FUNCTION));

        parent::__construct($operators);
    }

    public static function parseBoolean($value): bool
    {
        switch ($value) {
            case 'true':
                return true;
            case 'false':
                return false;
            default:
                return (bool) $value;
        }
    }
}
