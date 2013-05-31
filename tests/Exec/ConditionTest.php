<?php
/**
 * @author valentin.claras
 * @author hugo.charbonnier
 * @author yoann.croizer
 * @package Exec
 */

/**
 * ConditionTest
 * @package Exec
 */
class Exec_Test_ConditionTest
{
    /**
     * lance les autre classe de tests
     */
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite();
        $suite->addTestSuite('Exec_Test_ConditionSetUp');
        $suite->addTestSuite('Exec_Test_ConditionOthers');
        return $suite;
    }

}

/**
 * conditionSetUpTest
 * @package Exec
 */
class Exec_Test_ConditionSetUp extends PHPUnit_Framework_TestCase
{
    /**
     * test si l'objet renvoyé est bien du type demandé
     */
    function testConstruct()
    {
        $tecExpression = new TEC_Model_Expression();
        $executionCondition = new Exec_Execution_Condition($tecExpression);
        $this->assertInstanceOf('Exec_Execution_Condition', $executionCondition);
    }

}

/**
 * conditionLogiqueMetierTest
 * @package Exec
 */
class Exec_Test_ConditionOthers extends PHPUnit_Framework_TestCase
{
    protected $expression1;
    protected $expression2;
    protected $expression3;
    protected $expression4;

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
        $this->expression1 = new TEC_Model_Expression('a&(b|c)');
        $this->expression1->buildTree();
        $this->expression2 = new TEC_Model_Expression('vrai&faux');
        $this->expression2->buildTree();
        $this->expression3 = new TEC_Model_Expression('un|un|zero');
        $this->expression3->buildTree();
        $this->expression4 = new TEC_Model_Expression('a&(b|c)&!(b|!c|(a&b))');
        $this->expression4->buildTree();
    }


    /**
     * Test de la méthode executeComponent
     */
    function testExecuteComponent()
    {
        $valueProvider1 = new Inventory_Model_ValueProviderEntity(array('a' => true, 'b' => false, 'c' => true));
        $expressionCondition1 = new Exec_Execution_Condition($this->expression1);
        $this->assertTrue($expressionCondition1->executeExpression($valueProvider1));

        $valueProvider2 = new Inventory_Model_ValueProviderEntity(array('vrai' => true, 'faux' => false));
        $expressionCondition2 = new Exec_Execution_Condition($this->expression2);
        $this->assertFalse($expressionCondition2->executeExpression($valueProvider2));

        $valueProvider3 = new Inventory_Model_ValueProviderEntity(array('un' => false, 'zero' => true));
        $expressionCondition3 = new Exec_Execution_Condition($this->expression3);
        $this->assertTrue($expressionCondition3->executeExpression($valueProvider3));

        $valueProvider4 = new Inventory_Model_ValueProviderEntity(array('a' => true, 'b' => true, 'c' => false));
        $expressionCondition4 = new Exec_Execution_Condition($this->expression4);
        $this->assertTrue($expressionCondition4->executeExpression($valueProvider4));
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