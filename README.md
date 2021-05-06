# Expressions Parser

Used shunting-yard algorithm to convert any expressions to RPN(Reverse Polish Notations) and process it.

[![Build](https://github.com/zippovich2/expressions-parser/actions/workflows/ci-tests.yaml/badge.svg)](https://github.com/Zippovich2/expressions-parser/actions/workflows/ci-tests.yaml)
[![Packagist](https://img.shields.io/packagist/v/zippovich2/expressions-parser.svg)](https://packagist.org/packages/zippovich2/expressions-parser)

- [Installation](#installation)
- [Predefined parsers](#predefined-parsers)
    * [Arithmetical](#arithmetical-expressions-parser)
    * [Logical](#logical-expressions-parser)
- [Extending](#extending)
- [Custom parser](#custom-parser)
- [Operators](#operators)
    * [Types](#types)
    * [Reserved names](#reserved-names)
- [Callbacks](#callbacks)
- [References](#references)

## Installation

`composer require zippovich2/expressions-parser`

## Predefined parsers

### Arithmetical expressions parser
```php
use Zippovich2\ExpressionsParser\Parser\ArithmeticalParser;

$parser = new ArithmeticalParser();

// 45
$parser->eval('2 + (3^3) + 8 * (3 - 1)'); 

// 3.0001220703125
$parser->eval('3 + 4 * 2 / (1 - 5) ^ 2 ^ 3'); 

// 58
$parser->eval('58+(max(1, 2, 3)/3)'); 
```

### Logical expressions parser
```php
use Zippovich2\ExpressionsParser\Parser\LogicalParser;

$parser = new LogicalParser();

// true
$parser->eval('true || false'); 

// false
$parser->eval('true && false'); 

// true
$parser->eval('true xor false'); 

// false
$parser->eval('true xor true'); 
```

## Extending
```php
use Zippovich2\ExpressionsParser\Parser\LogicalParser;
use Zippovich2\ExpressionsParser\OperatorFactory;

$parser = new LogicalParser();
$parser->addOperator(OperatorFactory::rightAssociative('**', function($a, $b){
    return $a ** $b;
}, 5));
$parser->addOperator(OperatorFactory::func('if', function($condition, $if, $else){
    return $condition ? $if : $else;
}));
$parser->addOperator(OperatorFactory::constant('e', M_E));

// false
$res = $parser->eval('if(2 < 1, true, false)');

// true
$res = $parser->eval('if(2 > 1, true, false)');

// 16
$res = $parser->eval('2**4');

// 3.718281828459
$res = $parser->eval('e+1');
```

## Custom parser

```php
use Zippovich2\ExpressionsParser\Parser;
use Zippovich2\ExpressionsParser\OperatorsList;
use Zippovich2\ExpressionsParser\OperatorFactory;

$operators = new OperatorsList();

$operators->add(OperatorFactory::leftAssociative('AND', function ($a, $b){
    return $a && $b;
}));

$parser = new Parser($operators);

// false
$parser->eval('1 AND 0');

// true
$parser->eval('1 AND 0');
```

## Operators

### Types
1. `Operator::TYPE_LEFT_ASSOCIATIVE` - left associative.
2. `Operator::TYPE_RIGHT_ASSOCIATIVE` - right associative.
3. `Operator::TYPE_FUNCTION` - function.
4. `Operator::TYPE_LEFT_ASSOCIATIVE` - constant.

### Reserved names
An operator cannot be created using the characters `(` and `)` because it is used for groups 
and `,` because it is used for separating function parameters.

## Callbacks

You can provide callback for each operator or create global callback to handle all operators.

```php
use Zippovich2\ExpressionsParser\Parser;
use Zippovich2\ExpressionsParser\OperatorsList;
use Zippovich2\ExpressionsParser\OperatorFactory;

$operators = new OperatorsList();

$operators->add(OperatorFactory::leftAssociative('AND', function ($a, $b){
    return $a && $b;
}));

$operators->add(OperatorFactory::leftAssociative('OR'));

$defaultCallback = function ($operator, ...$parameters){
    switch ($operator){
        case 'OR':
            return $parameters[0] || $parameters[1];
    }
    
    throw new \LogicException('This code should not be reached.');
};

$parser = new Parser($operators);

// true
$parser->eval('1 AND 0 OR 1', $defaultCallback);

// false
$parser->eval('1 AND 0 OR 0', $defaultCallback);

/**
 * @throws \LogicException because no callback was provided for "OR" operator. 
 */
$parser->eval('1 AND 0 OR 0');
```

## References
- [Shunting-yard algorithm](https://en.wikipedia.org/wiki/Shunting-yard_algorithm)
- [Operator](https://en.wikipedia.org/wiki/Operator_(computer_programming))
- [Operator associativity](https://en.wikipedia.org/wiki/Operator_associativity)
- [Order of operations or operator precedence](https://en.wikipedia.org/wiki/Order_of_operations)
- [Reverse Polish notation](https://en.wikipedia.org/wiki/Reverse_Polish_notation)
