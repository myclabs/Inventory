<?php

namespace Tests\TEC;

use Core\Test\TestCase;
use TEC\Expression;

class ExpressionTest extends TestCase
{
    /**
     * Vérifie que les types d'expression sont correctement détectés.
     */
    public function testTypeDetection()
    {
        $expression = new Expression('a+b');
        $this->assertEquals($expression->getType(), Expression::TYPE_NUMERIC);

        $expression = new Expression('a-b');
        $this->assertEquals($expression->getType(), Expression::TYPE_NUMERIC);

        $expression = new Expression('a*b');
        $this->assertEquals($expression->getType(), Expression::TYPE_NUMERIC);

        $expression = new Expression('a/b');
        $this->assertEquals($expression->getType(), Expression::TYPE_NUMERIC);

        $expression = new Expression('a&b');
        $this->assertEquals($expression->getType(), Expression::TYPE_LOGICAL);

        $expression = new Expression('a|b');
        $this->assertEquals($expression->getType(), Expression::TYPE_LOGICAL);

        $expression = new Expression('!a');
        $this->assertEquals($expression->getType(), Expression::TYPE_LOGICAL);

        $expression = new Expression('a:b');
        $this->assertEquals($expression->getType(), Expression::TYPE_SELECT);
    }

    /**
     * @dataProvider provideInvalidExpressions
     * @expectedException \TEC\Exception\InvalidExpressionException
     */
    public function testInvalidExpression($expression)
    {
        new Expression($expression);
    }

    public function provideInvalidExpressions()
    {
        return [
            [''],
            ['a'],
            ['a b'],
            ['a + b & c'],
            ['a * b : c'],
            ['a | b : c'],
            ['!a - b / c'],
        ];
    }
}
