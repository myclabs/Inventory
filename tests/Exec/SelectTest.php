<?php

namespace Tests\Exec;

use Core\Test\TestCase;
use Exec\Execution\Select;
use Inventory_Model_ValueProviderEntity;
use TEC\Expression;

class SelectTest extends TestCase
{
    /**
     * Test de la méthode executeComponent
     */
    public function testExecuteExpression()
    {
        $expression = new Expression('a:(b:(c:d;:e);:f)');
        $valueProvider = new Inventory_Model_ValueProviderEntity([
             "a" => true,
             "b" => true,
             "c" => true,
             "d" => 'action1',
             "e" => 'action2',
             "f" => 'action3',
        ]);
        $selection = new Select($expression);

        $result = $selection->executeExpression($valueProvider);

        $this->assertEquals(3, count($result));
    }
}
