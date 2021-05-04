<?php

declare(strict_types=1);

/*
 * This file is part of the "Expressions Parser" package.
 * (c) Skoropadskyi Roman <zipo.ckorop@gmail.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zippovich2\ExpressionsParser;

class Operator
{
    public const TYPE_LEFT_ASSOCIATIVE = 0;
    public const TYPE_RIGHT_ASSOCIATIVE = 1;
    public const TYPE_FUNCTION = 2;

    /**
     * @var string
     */
    private $symbol;

    /**
     * @var int
     */
    private $precedence;

    /**
     * @var int
     */
    private $type;

    /**
     * @var \Closure|null
     */
    private $callback;

    public function __construct(
        string $symbol,
        int $precedence = 1,
        ?\Closure $callback = null,
        int $type = self::TYPE_LEFT_ASSOCIATIVE
    ) {
        $this->symbol = $symbol;
        $this->precedence = $precedence;
        $this->type = $type;
        $this->callback = $callback;
    }

    public function getSymbol(): string
    {
        return $this->symbol;
    }

    public function getPrecedence(): int
    {
        return $this->precedence;
    }

    public function getType(): int
    {
        return $this->type;
    }

    public function getCallback(): ?\Closure
    {
        return $this->callback;
    }
}
