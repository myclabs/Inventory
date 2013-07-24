<?php
/**
 * @author valentin.claras
 * @author hugo.charbonnier
 * @author yoann.croizer
 * @package Exec
 */

use TEC\Expression;

/**
 * @package Exec
 */
class Exec_Test_SelectTest
{
    /**
     * lance les autre classe de tests
     */
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite();
        $suite->addTestSuite('Exec_Test_SelectSetUp');
        $suite->addTestSuite('Exec_Test_SelectOthers');
        return $suite;
    }

}

/**
 * @package Exec
 */
class Exec_Test_SelectSetUp extends PHPUnit_Framework_TestCase
{
    /**
     * test si l'objet renvoyé est bien du type demandé
     */
    function testConstruct()
    {
        $tecExpression = new Expression('foo:bar');
        $executionSelection = new Exec_Execution_Select($tecExpression);
        $this->assertInstanceOf('Exec_Execution_Select', $executionSelection);
    }

}

/**
 * @package Exec
 */
class Exec_Test_SelectOthers extends PHPUnit_Framework_TestCase
{
    /**
     * @var Expression
     */
     protected $expression;
     /**
      * @var Inventory_Model_ValueProviderEntity
      */
     protected $_valueProvider;

    /**
     * Méthode appelée avant l'appel à la classe de test
     */
    public static function setUpBeforeClass()
    {
    }

    /**
     * Méthode appelée avant chaque test
     */
    protected function setUp()
    {
        $this->expression = new Expression('a:(b:(c:d;:e);:f)');
        $this->_valueProvider = new Inventory_Model_ValueProviderEntity(
                                        array(
                                           "a" => true,
                                           "b" => true,
                                           "c" => true,
                                           "d" => 'action1',
                                           "e" => 'action2',
                                           "f" => 'action3',
                                        )
                                   );
    }

    /**
     * Test de la méthode executeComponent
     */
    function testExecuteExpression()
    {
        $selection = new Exec_Execution_Select($this->expression);

        $result = $selection->executeExpression($this->_valueProvider);

        $count = count($result);

        $this->assertEquals(3, $count);
    }


    /**
     * Méthode appelée à la fin de chaque test
     */
    protected function tearDown()
    {
    }

    /**
     * Méthode appelée à la fin de la classe de test
     */
    public static function tearDownAfterClass()
    {
    }

}