<?php

namespace Tests\Exec;

use Core\Test\TestCase;
use Exec\Execution\Condition;
use Inventory_Model_ValueProviderEntity;
use TEC\Expression;

class ConditionTest extends TestCase
{
    public function testExecuteComponent()
    {
        $valueProvider1 = new Inventory_Model_ValueProviderEntity(['a' => true, 'b' => false, 'c' => true]);
        $expressionCondition1 = new Condition(new Expression('a&(b|c)'));
        $this->assertTrue($expressionCondition1->executeExpression($valueProvider1));

        $valueProvider2 = new Inventory_Model_ValueProviderEntity(['vrai' => true, 'faux' => false]);
        $expressionCondition2 = new Condition(new Expression('vrai&faux'));
        $this->assertFalse($expressionCondition2->executeExpression($valueProvider2));

        $valueProvider3 = new Inventory_Model_ValueProviderEntity(['un' => false, 'zero' => true]);
        $expressionCondition3 = new Condition(new Expression('un|un|zero'));
        $this->assertTrue($expressionCondition3->executeExpression($valueProvider3));

        $valueProvider4 = new Inventory_Model_ValueProviderEntity(['a' => true, 'b' => true, 'c' => false]);
        $expressionCondition4 = new Condition(new Expression('a&(b|c)&!(b|!c|(a&b))'));
        $this->assertTrue($expressionCondition4->executeExpression($valueProvider4));
    }
}
