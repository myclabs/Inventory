<?php
/**
 * @author matthieu.napoli
 * @author yoann.croizer
 * @author hugo.charbonnier
 * @package Algo
 */

use Keyword\Domain\Keyword;
use TEC\Expression;

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
        return $suite;
    }

	/**
	 * Permet de générer un objet de base sur lequel on pourra travailler
	 * @return Expression $o
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
        foreach (Keyword::loadList() as $o) {
            $o->delete();
        }
        foreach (Classif_Model_ContextIndicator::loadList() as $o) {
            $o->delete();
        }
        foreach (Classif_Model_Context::loadList() as $o) {
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
		$this->assertEquals($expression, str_replace(' ', '', $o->getExpression()));

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
