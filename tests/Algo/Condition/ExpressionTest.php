<?php
/**
 * @author  matthieu.napoli
 * @author  yoann.croizer
 * @author  hugo.charbonnier
 * @package Algo
 */

/**
 * @package    Algo
 * @subpackage Condition
 */
class Condition_ExpressionTest
{

    /**
     * Lance les autres classe de tests.
     */
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite();
        //        $suite->addTestSuite('Condition_ExpressionSetUpTest');
        //        $suite->addTestSuite('Condition_ExpressionLogiqueMetierTest');
        return $suite;
    }

    /**
     * Permet de générer un objet de base sur lequel on pourra travailler
     */
    public static function generateObject()
    {
        $expression = self::generateExpression();

        $o = new Algo_Model_Condition_Expression();
        $o->ref = 'ConditionExpression';
        $o->setExpression($expression);
        $o->save();
        return $o;
    }

    /**
     * Permet de générer un objet de base sur lequel on pourra travailler
     * @return TEC_Model_Expression $o
     */
    public static function generateExpression()
    {
        $o = new TEC_Model_Expression('ConditionMulti&ConditionCheckbox');
        $o->type = TEC_Model_Expression::TYPE_LOGIC;
        $o->save();
        return $o;
    }

    /**
     * Supprime un objet utilisé dans les tests
     * @param Algo_Model_Condition_Expression $o
     */
    public static function deleteObject(Algo_Model_Condition_Expression $o)
    {
        $expression = TEC_Model_Expression::getMapper()->load($o->getExpression());
        $expression->delete();
        $o->delete();
    }

}

/**
 * Condition_ExpressionSetUpTest
 * @package Algo
 */
class Condition_ExpressionSetUpTest extends Core_Test_TestCase
{

    /**
     * Méthode appelée avant l'appel à la classe de test
     */
    public static function setUpBeforeClass()
    {
        // Vérification qu'il ne reste aucun objet en base, sinon suppression
        foreach (Algo_Model_Set::loadList() as $o) {
            $o->delete();
        }
        foreach (Algo_Model_Algo::loadList() as $o) {
            $o->delete();
        }
        foreach (Classif_Model_Context::loadList() as $o) {
            $o->delete();
        }
        foreach (TEC_Model_Expression::loadList() as $o) {
            $o->delete();
        }
        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->flush();
    }

    /**
     * @return Algo_Model_Condition_Expression $o
     */
    function testConstruct()
    {
        // TODO
        $o = new Algo_Model_Condition_Expression();
        $this->assertTrue($o instanceof Algo_Model_Condition_Expression);
        $this->assertTrue($o->type === 'Algo_Model_Condition_Expression');

        $o->ref = 'testExpression';
        $o->setExpression($this->_expression);
        $id = $o->save();

        $this->assertNotNull($o->id, 'Object id is not defined');
        $this->assertEquals($o->getExpression(), 2);

        // Test erreur généré si l'expression associé à l'algo n'a pas d'id
        $expression = new TEC_Model_Expression('la');
        $a = new Algo_Model_Condition_Expression();
        $a->ref = 'testExpression2';
        $a->setExpression($expression);
        try {
            $a->save();
        } catch (Core_Exception_InvalidArgument $e) {
            $this->assertEquals(null, $a->id);
        }
        return $o;
    }

    /**
     * @depends testConstruct
     * @param Algo_Model_Condition_Expression $o
     */
    function testLoad(Algo_Model_Condition_Expression $o)
    {
        $id = $o->id;
        $expression = Algo_Model_Condition_Expression::load($id);
        $this->assertTrue($expression instanceof Algo_Model_Condition_Expression);
        $this->assertEquals($expression, $o);
    }

    /**
     * Test la supression de l'objet.
     * @depends testSave
     * @param Algo_Model_Condition_Expression $o
     * @expectedException Core_Exception_NotFound
     */
    function testDelete(Algo_Model_Condition_Expression $o)
    {
        $id = $o->id;
        $o->delete();
        $this->assertEquals($o->id, null);
        // Doit lancer une exception
        $expression = Algo_Model_Condition_Expression::load($id);
    }

}


/**
 * Elementary_ExpressionLogiqueMetierTest
 * @package Algo
 */
class Condition_ExpressionLogiqueMetierTest extends PHPUnit_Framework_TestCase
{

    protected $_algoSet;

    protected $_expressionCondition;

    protected $_conditionCheckbox;
    protected $_conditionMulti;

    protected $_input;

    /**
     * Méthode appelée avant l'appel à la classe de test
     */
    public static function setUpBeforeClass()
    {
        Algo_Model_DAO_Algo::getInstance()->unitTestsClearTable();
        Algo_Model_Condition_DAO_Expression::getInstance()->unitTestsClearTable();
        Algo_Model_Condition_DAO_Elementary::getInstance()->unitTestsClearTable();
        Algo_Model_Condition_Elementary_DAO_Multi::getInstance()->unitTestsClearTable();
        Algo_Model_Condition_Elementary_DAO_Checkbox::getInstance()->unitTestsClearTable();

        Classif_Model_DAO_Version::getInstance()->unitTestsClearTable();
        Classif_Model_DAO_Context::getInstance()->unitTestsClearTable();

        TEC_Model_DAO_Expression::getInstance()->unitTestsClearTable();
        TEC_Model_DAO_Leaf::getInstance()->unitTestsClearTable();
        TEC_Model_DAO_Composite::getInstance()->unitTestsClearTable();
    }

    /**
     * Méthode appelée avant chaque méthode de test.
     */
    function setUp()
    {
        $this->_algoSet = new Algo_Model_Set();
        $version = new Classif_Model_Version();
        $version->setLabel('test');
        $version->setRef('testRef');
        $version->setCreationDate('2012-04-24 09:41:36');
        $version->save();
        $context = new Classif_Model_Context();
        $context->setRef('test');
        $context->setLabel('test');
        $context->setVersion($version);
        $context->save();
        $this->_algoSet->setClassifContext($context);
        $this->_algoSet->setClassifVersion($version);
        $this->_algoSet->save();

        $this->_expressionCondition = Condition_ExpressionTest::generateObject();
        $this->_expressionCondition->setSet($this->_algoSet);
        $this->_expressionCondition->save();

        $this->_conditionMulti = Condition_Elementary_Select_MultiTest::generateObject();
        $this->_conditionMulti->setSet($this->_algoSet);
        $this->_conditionMulti->save();

        $this->_conditionCheckbox = Condition_Elementary_BooleanTest::generateObject();
        $this->_conditionCheckbox->setSet($this->_algoSet);
        $this->_conditionCheckbox->save();

        $this->_algoSet->addAlgo($this->_expressionCondition);
        $this->_algoSet->addAlgo($this->_conditionMulti);
        $this->_algoSet->addAlgo($this->_conditionCheckbox);
        $this->_algoSet->save();

        $this->_input = array(
            'algoCheckBox'  => true,
            'algoMulti'     => 'testEqual',
        );
    }

    /**
     * Test de la méthode execute()
     */
    function testExecute()
    {
        $result = $this->_expressionCondition->execute($this->_input);
        $this->assertTrue(is_bool($result));
        $this->assertFalse($result);
    }

    /**
     * Méthode appelée à la fin de chaque test
     */
    protected function tearDown()
    {
        Condition_ExpressionTest::deleteObject($this->_expressionCondition);
        Condition_Elementary_Select_MultiTest::deleteObject($this->_conditionMulti);
        Condition_Elementary_BooleanTest::deleteObject($this->_conditionCheckbox);
    }

    /**
     * Méthode appelée à la fin de la classe de test
     */
    public static function tearDownAfterClass()
    {
        if (!Algo_Model_DAO_Algo::getInstance()->unitTestsIsTableEmpty()) {
            echo "\nLa table Algo n'est pas vide après les tests\n";
        }
        if (!Algo_Model_Condition_DAO_Expression::getInstance()->unitTestsIsTableEmpty()) {
            echo "\nLa table Expression n'est pas vide après les tests\n";
        }

        if (!Algo_Model_Condition_DAO_Elementary::getInstance()->unitTestsIsTableEmpty()) {
            echo "\nLa table Elementary n'est pas vide après les tests\n";
        }
        if (!Algo_Model_Condition_Elementary_DAO_Checkbox::getInstance()->unitTestsIsTableEmpty()) {
            echo "\nLa table Checkbox n'est pas vide après les tests\n";
        }
        if (!Algo_Model_Condition_Elementary_DAO_Multi::getInstance()->unitTestsIsTableEmpty()) {
            echo "\nLa table Multi n'est pas vide après les tests\n";
        }

        // Tables de TEC
        if (!TEC_Model_DAO_Expression::getInstance()->unitTestsIsTableEmpty()) {
            echo "\nLa table Expression n'est pas vide après les tests\n";
        }
        if (!TEC_Model_DAO_Leaf::getInstance()->unitTestsIsTableEmpty()) {
            echo "\nLa table Leaf n'est pas vide après les tests\n";
        }
        if (!TEC_Model_DAO_Composite::getInstance()->unitTestsIsTableEmpty()) {
            echo "\nLa table Composite n'est pas vide après les tests\n";
        }

    }

}//end class Condition_ExpressionLogiqueMetierTest
