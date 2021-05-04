<?php

declare(strict_types=1);

/*
 * This file is part of the "Expressions Parser" package.
 * (c) Skoropadskyi Roman <zipo.ckorop@gmail.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zippovich2\ExpressionsParser;

class OperatorsList implements \IteratorAggregate, \Countable
{
    /**
     * @var Operator[]
     */
    private $operators = [];

    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->operators);
    }

    public function count(): int
    {
        return \count($this->operators);
    }

    public function add(Operator $operator): void
    {
        if (!$this->contains($operator)) {
            $this->operators[] = $operator;
        }
    }

    public function findOperatorBySymbol(string $symbol): ?Operator
    {
        foreach ($this->operators as $operator) {
            if ($symbol === $operator->getSymbol()) {
                return $operator;
            }
        }

        return null;
    }

    /**
     * @param Operator|string $item
     */
    public function remove($item): void
    {
        $symbol = $item;

        if ($item instanceof Operator) {
            $symbol = $item->getSymbol();
        }

        foreach ($this->operators as $key => $operator) {
            if ($symbol === $operator->getSymbol()) {
                unset($this->operators[$key]);

                break;
            }
        }
    }

    /**
     * @param Operator|string $item
     */
    public function contains($item): bool
    {
        $symbol = $item instanceof Operator ? $item->getSymbol() : $item;

        foreach ($this->operators as $operator) {
            if ($symbol === $operator->getSymbol()) {
                return true;
            }
        }

        return false;
    }

    public function getSymbolsArray(): array
    {
        return \array_map(function (Operator $operator) {
            return $operator->getSymbol();
        }, $this->operators);
    }

    /**
     * @param Operator|string $item
     */
    public function getPrecedence($item): int
    {
        $symbol = $item instanceof Operator ? $item->getSymbol() : $item;

        foreach ($this->operators as $operator) {
            if ($symbol === $operator->getSymbol()) {
                return $operator->getPrecedence();
            }
        }

        return 0;
    }

    /**
     * @param Operator|string $item
     */
    public function getOperatorType($item): ?int
    {
        $symbol = $item instanceof Operator ? $item->getSymbol() : $item;

        foreach ($this->operators as $operator) {
            if ($symbol === $operator->getSymbol()) {
                return $operator->getType();
            }
        }

        return null;
    }
}
