<?php

use Exec\Execution\Select;
use TEC\Expression;

/**
 * @author valentin.claras
 * @author hugo.charbonnier
 * @author yoann.croizer
 */
class Exec_Test_SelectTest
{
    /**
     * lance les autre classe de tests
     */
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite();
        $suite->addTestSuite('Exec_Test_SelectOthers');
        return $suite;
    }
}

class Exec_Test_SelectOthers extends PHPUnit_Framework_TestCase
{
    /**
     * Test de la méthode executeComponent
     */
    public function testExecuteExpression()
    {
        $expression = new Expression('a:(b:(c:d;:e);:f)');
        $valueProvider = new Inventory_Model_ValueProviderEntity(
            array(
                 "a" => true,
                 "b" => true,
                 "c" => true,
                 "d" => 'action1',
                 "e" => 'action2',
                 "f" => 'action3',
            )
        );
        $selection = new Select($expression);

        $result = $selection->executeExpression($valueProvider);

        $this->assertEquals(3, count($result));
    }
}
