<?php
/**
 * @author matthieu.napoli
 * @author yoann.croizer
 * @author hugo.charbonnier
 * @package Algo
 */

/**
 * Creation of the Test Suite.
 *
 * @package Algo
 * @subpackage Keyword
 */
class TextKey_ExpressionTest
{
    /**
     * Lance les autre classe de tests.
     * @return PHPUnit_Framework_TestSuite
     */
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite();
        $suite->addTestSuite('TextKey_ExpressionSetUpTest');
//        $suite->addTestSuite('TextKey_ExpressionLogiqueMetierTest');
        return $suite;
    }

	/**
	 * Permet de générer un objet de base sur lequel on pourra travailler
	 * @return TEC_Model_Expression $o
	 */
	public static function generateExpression()
	{
		return 'ConditionCheckbox:(:KeywordFixed;ConditionMulti:KeywordOption)';
	}

}


/**
 * TextKey_ExpressionSetUpTest
 * @package Algo
 */
class TextKey_ExpressionSetUpTest extends Core_Test_TestCase
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
        foreach (Keyword_Model_Keyword::loadList() as $o) {
            $o->delete();
        }
        foreach (Classif_Model_ContextIndicator::loadList() as $o) {
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
     * @return Algo_Model_Selection_TextKey_Expression
     */
    function testConstruct()
    {
		$set = new Algo_Model_Set();
		$set->save();
		$expression = TextKey_ExpressionTest::generateExpression();
		$this->entityManager->flush();

		$o = new Algo_Model_Selection_TextKey_Expression();
		$o->setSet($set);
		$o->setRef('test');
		$o->setExpression($expression);
		$o->save();
		$this->entityManager->flush();

		$this->assertEquals('test', $o->getRef());
		$this->assertEquals($expression, $o->getExpression());

		return $o;
    }

    /**
     * @depends testConstruct
	 * @param Algo_Model_Selection_TextKey_Expression $o
	 * @return Algo_Model_Selection_TextKey_Expression
     */
    function testLoad(Algo_Model_Selection_TextKey_Expression $o)
    {
		$this->entityManager->clear();
		/** @var $oLoaded Algo_Model_Selection_TextKey_Expression */
		$oLoaded = Algo_Model_Selection_TextKey_Expression::load($o->getId());

		$this->assertInstanceOf('Algo_Model_Selection_TextKey_Expression', $oLoaded);
		$this->assertNotSame($o, $oLoaded);
		$this->assertEquals($o->getId(), $oLoaded->getId());
		$this->assertEquals($o->getRef(), $oLoaded->getRef());
        $this->assertNotNull($oLoaded->getExpression());
        $this->assertEquals($o->getExpression(), $oLoaded->getExpression());
		return $oLoaded;
    }

    /**
     * @depends testLoad
     * @param Algo_Model_Selection_TextKey_Expression $o
     */
    function testDelete(Algo_Model_Selection_TextKey_Expression $o)
    {
		$o->delete();
		$o->getSet()->delete();
		$this->assertEquals(\Doctrine\ORM\UnitOfWork::STATE_REMOVED,
			$this->entityManager->getUnitOfWork()->getEntityState($o));
		$this->entityManager->flush();
		$this->assertEquals(\Doctrine\ORM\UnitOfWork::STATE_NEW,
			$this->entityManager->getUnitOfWork()->getEntityState($o));
    }

}


/**
 * TextKey_ExpressionLogiqueMetierTest
 * @package Algo
 */
class TextKey_ExpressionLogiqueMetierTest extends PHPUnit_Framework_TestCase
{

    // Attributs privés.
    protected $_algoSet;

    protected $_keywordSelection;
    protected $_keyword;
    protected $_keywordSave;
    protected $_keywordSave2;

    protected $_condition1;
    protected $_condition2;

    protected $_action1;
    protected $_action2;

    protected $_input;

    /**
     * Méthode appelée avant l'appel à la classe de test.
     */
    public static function setUpBeforeClass()
    {
        Algo_Model_DAO_Algo::getInstance()->unitTestsClearTable();
        Algo_Model_Keyword_DAO_Selection::getInstance()->unitTestsClearTable();
        Algo_Model_Keyword_DAO_Fixed::getInstance()->unitTestsClearTable();
        Algo_Model_Keyword_DAO_Option::getInstance()->unitTestsClearTable();

        Algo_Model_Condition_DAO_Elementary::getInstance()->unitTestsClearTable();
        Algo_Model_Condition_Elementary_DAO_Checkbox::getInstance()->unitTestsClearTable();
        Algo_Model_Condition_Elementary_DAO_Multi::getInstance()->unitTestsClearTable();

        Keyword_Model_DAO_Keyword::getInstance()->unitTestsClearTable();

        Classif_Model_DAO_Version::getInstance()->unitTestsClearTable();
        Classif_Model_DAO_Context::getInstance()->unitTestsClearTable();

        TEC_Model_DAO_Expression::getInstance()->unitTestsClearTable();
        TEC_Model_DAO_Leaf::getInstance()->unitTestsClearTable();
        TEC_Model_DAO_Composite::getInstance()->unitTestsClearTable();
    }// end setUpBeforeClass()


    /**
     * Méthode appelée avant chaque méthode de test.
     */
    function setUp()
    {
        $this->_algoSet = new Algo_Model_Set();
        $version = new Classif_Model_Version();
        $version->setLabel('test');
        $version->setRef('refTestVersionSelection');
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

        $this->_keywordSave = new Keyword_Model_Keyword('test2', 'test2');
        $this->_keywordSave->save();
        $this->_keywordSave2 = new Keyword_Model_Keyword('test1', 'test1');
        $this->_keywordSave2->save();

        $this->_keywordSelection = TextKey_ExpressionTest::generateObject();
        $this->_keywordSelection->setSet($this->_algoSet);
        $this->_keywordSelection->save();

        $this->_condition1 = Condition_Elementary_BooleanTest::generateObject();
        $this->_condition1->setSet($this->_algoSet);
        $this->_condition1->save();
        $this->_condition2 = Condition_Elementary_Select_MultiTest::generateObject();
        $this->_condition2->setSet($this->_algoSet);
        $this->_condition2->save();

        $this->_action1    = TextKey_FixedTest::generateObject();
        $this->_action1->setSet($this->_algoSet);
        $this->_action1->save();
        $this->_action2    = TextKey_InputTest::generateObject();
        $this->_action2->setSet($this->_algoSet);
        $this->_action2->save();

        $this->_algoSet->addAlgo($this->_keywordSelection);
        $this->_algoSet->addAlgo($this->_condition1);
        $this->_algoSet->addAlgo($this->_condition2);
        $this->_algoSet->addAlgo($this->_action1);
        $this->_algoSet->addAlgo($this->_action2);
        $this->_algoSet->save();

        $this->_keyword = 'test2';

        $this->_input = array(
           'algoCheckBox' => true,
           'algoMulti'    => 'testNotEquals',
           'algoOption'   =>  $this->_keyword,
        );

    }//end setUp()

    /**
     * Test de la méthode execute.
     */
    function testExecute()
    {
        $result = $this->_keywordSelection->execute($this->_input);
        $this->assertEquals(count($result), 2);
        $this->assertContains($this->_keyword, $result);
        $this->assertContains($this->_action1->value, $result);

    }// end testExecute()


    /**
     * Méthode appelée à la fin de chaque test.
     */
    protected function tearDown()
    {
        Condition_Elementary_BooleanTest::deleteObject($this->_condition1);
        Condition_Elementary_Select_MultiTest::deleteObject($this->_condition2);

        TextKey_FixedTest::deleteObject($this->_action1);
        TextKey_InputTest::deleteObject($this->_action2);

        $idExpression = $this->_keywordSelection->getIdExpression();
        $expression = TEC_Model_Expression::load($idExpression);
        $expression->delete();

        TextKey_ExpressionTest::deleteObject($this->_keywordSelection);
        $this->_keywordSave->delete();
        $this->_keywordSave2->delete();

    }// end tearDown()


    /**
     * Méthode appelée à la fin de la classe de test.
     */
    public static function tearDownAfterClass()
    {
        // Tables de algo
        if (! Algo_Model_DAO_Algo::getInstance()->unitTestsIsTableEmpty()) {
            echo "\nLa table Algo n'est pas vide après les tests\n";
        }
        if (! Algo_Model_Keyword_DAO_Selection::getInstance()->unitTestsIsTableEmpty() ) {
            echo "\nLa table Selection n'est pas vide après les tests\n";
        }
        if (! Algo_Model_Keyword_DAO_Fixed::getInstance()->unitTestsIsTableEmpty()) {
            echo "\nLa table Algo_Keyword_Fixed n'est pas vide après les tests\n";
        }
        if (! Algo_Model_Keyword_DAO_Option::getInstance()->unitTestsIsTableEmpty() ) {
            echo "\nLa table Algo_Keyword_Option n'est pas vide après les tests\n";
        }
        if (! Algo_Model_Condition_DAO_Elementary::getInstance()->unitTestsIsTableEmpty()) {
            echo "\nLa table algo_condition_elementary n'est pas vide après les tests\n";
        }
        if (! Algo_Model_Condition_Elementary_DAO_Checkbox::getInstance()->unitTestsIsTableEmpty() ) {
            echo "\nLa table algo_condition_elementary_checkbox n'est pas vide après les tests\n";
        }
        if (! Algo_Model_Condition_Elementary_DAO_Multi::getInstance()->unitTestsIsTableEmpty() ) {
            echo "\nLa table algo_condition_elementary_multi n'est pas vide après les tests\n";
        }

        // Tables de TEC
        if (! TEC_Model_DAO_Expression::getInstance()->unitTestsIsTableEmpty()) {
            echo "\nLa table Expression n'est pas vide après les tests\n";
        }
        if (! TEC_Model_DAO_Leaf::getInstance()->unitTestsIsTableEmpty()) {
            echo "\nLa table Leaf n'est pas vide après les tests\n";
        }
        if (! TEC_Model_DAO_Composite::getInstance()->unitTestsIsTableEmpty()) {
            echo "\nLa table Composite n'est pas vide après les tests\n";
        }

        // Tables de keyword
        if (! Keyword_Model_DAO_Keyword::getInstance()->unitTestsIsTableEmpty() ) {
            echo "\nLa table keyword n'est pas vide après les tests\n";
        }
    }// end tearDownAfterClass()

}//end class TextKey_ExpressionLogiqueMetierTest
