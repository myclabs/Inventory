<?php
/**
 * @author  matthieu.napoli
 * @author  hugo.charbonnier
 * @author  yoann.croizer
 * @package Algo
 */

use Core\Test\TestCase;
use Keyword\Domain\Keyword;
use Unit\UnitAPI;

/**
 * @package Algo
 */
class Numeric_ConstantTest
{

    /**
     * Lance les autre classe de tests.
     */
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite();
        $suite->addTestSuite('Numeric_ConstantSetUpTest');
        $suite->addTestSuite('Numeric_ConstantLogiqueMetierTest');
        return $suite;
    }

    /**
     * Permet de générer un objet de base sur lequel on pourra travailler
     */
    public static function generateObject()
    {
        $set = new Algo_Model_Set();
        $set->save();
        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->flush();

        $o = new Algo_Model_Numeric_Constant();
        $o->setSet($set);
        $o->setRef(strtolower(Core_Tools::generateString(20)));
        $o->setUnitValue(self::generateUnitValue());
        $o->setContextIndicator(self::generateContextIndicator());
        $o->setLabel('labelNumericConstant');
        $o->save();
        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->flush();
        return $o;
    }

    /**
     * Supprime un objet utilisé dans les tests
     * @param Algo_Model_Numeric_Constant $o
     */
    public static function deleteObject(Algo_Model_Numeric_Constant $o)
    {
        $o->delete();
        $o->getSet()->delete();
        self::deleteContextIndicator($o->getContextIndicator());
        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->flush();
    }

    /**
     * @return Calc_UnitValue
     */
    public static function generateUnitValue()
    {
        return new Calc_UnitValue(new UnitAPI('g'), 2, 0.1);
    }

    /**
     * @return Classif_Model_ContextIndicator
     */
    public static function generateContextIndicator()
    {
        $context = new Classif_Model_Context();
        $context->setRef(Core_Tools::generateString(20));
        $context->setLabel('Classif context');
        $context->save();
        $indicator = new Classif_Model_Indicator();
        $indicator->setRef(Core_Tools::generateString(20));
        $indicator->setLabel('Classif indicator');
        $indicator->setUnit(new UnitAPI('g'));
        $indicator->setRatioUnit($indicator->getUnit());
        $indicator->save();
        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->flush();
        $contextIndicator = new Classif_Model_ContextIndicator();
        $contextIndicator->setContext($context);
        $contextIndicator->setIndicator($indicator);
        $contextIndicator->save();
        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->flush();
        return $contextIndicator;
    }

    /**
     * @param Classif_Model_ContextIndicator $contextIndicator
     */
    public static function deleteContextIndicator(Classif_Model_ContextIndicator $contextIndicator)
    {
        $contextIndicator->delete();
        $contextIndicator->getIndicator()->delete();
        $contextIndicator->getContext()->delete();
        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->flush();
    }

}


/**
 * constantSetUpTest
 * @package Algo
 */
class Numeric_ConstantSetUpTest extends TestCase
{

    /**
     * Méthode appelée avant l'appel à la classe de test
     */
    public static function setUpBeforeClass()
    {
        /** @var \Doctrine\ORM\EntityManager $entityManager */
        $entityManager = Zend_Registry::get('EntityManagers')['default'];
        // Vérification qu'il ne reste aucun objet en base, sinon suppression
        foreach (Algo_Model_Set::loadList() as $o) {
            $o->delete();
        }
        foreach (Algo_Model_Algo::loadList() as $o) {
            $o->delete();
        }
        /** @var KeywordRepository $keywordRepository */
        $keywordRepository = $entityManager->getRepository('\Keyword\Domain\Keyword');
        if ($keywordRepository->count() > 0) {
            foreach ($keywordRepository->getAll() as $o) {
                $keywordRepository->remove($o);
            }
        }
        foreach (Classif_Model_Context::loadList() as $o) {
            $o->delete();
        }
        foreach (Classif_Model_Indicator::loadList() as $o) {
            $o->delete();
        }
        foreach (Classif_Model_ContextIndicator::loadList() as $o) {
            $o->delete();
        }
        $entityManager->flush();
    }

    /**
     * @return Algo_Model_Numeric_Constant $o
     */
    function testConstruct()
    {
        $set = new Algo_Model_Set();
        $set->save();
        $this->entityManager->flush();
        $unitValue = Numeric_ConstantTest::generateUnitValue();

        $o = new Algo_Model_Numeric_Constant();
        $o->setSet($set);
        $o->setRef(strtolower(Core_Tools::generateString(20)));
        $o->setUnitValue($unitValue);
        $o->save();
        $this->entityManager->flush();

        $this->assertSame($unitValue->getUnit(), $o->getUnit());

        return $o;
    }

    /**
     * @depends testConstruct
     * @param Algo_Model_Numeric_Constant $o
     * @return Algo_Model_Numeric_Constant $o
     */
    function testLoad(Algo_Model_Numeric_Constant $o)
    {
        $this->entityManager->clear();
        /** @var $oLoaded Algo_Model_Numeric_Constant */
        $oLoaded = Algo_Model_Numeric_Constant::load($o->getKey());
        $this->assertInstanceOf('Algo_Model_Numeric_Constant', $oLoaded);
        $this->assertEquals($o->getUnit()->getRef(), $oLoaded->getUnit()->getRef());
        return $oLoaded;
    }

    /**
     * @depends testLoad
     * @param Algo_Model_Numeric_Constant $o
     */
    function testDelete(Algo_Model_Numeric_Constant $o)
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
 * Numeric_ConstantLogiqueMetierTest
 * @package Algo
 */
class Numeric_ConstantLogiqueMetierTest extends PHPUnit_Framework_TestCase
{

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;

    /**
     * Méthode appelée avant l'appel à la classe de test
     */
    public static function setUpBeforeClass()
    {
        /** @var \Doctrine\ORM\EntityManager $entityManager */
        $entityManager = Zend_Registry::get('EntityManagers')['default'];
        // Vérification qu'il ne reste aucun objet en base, sinon suppression
        foreach (Algo_Model_Set::loadList() as $o) {
            $o->delete();
        }
        foreach (Algo_Model_Algo::loadList() as $o) {
            $o->delete();
        }
        /** @var KeywordRepository $keywordRepository */
        $keywordRepository = $entityManager->getRepository('\Keyword\Domain\Keyword');
        if ($keywordRepository->count() > 0) {
            foreach ($keywordRepository->getAll() as $o) {
                $keywordRepository->remove($o);
            }
        }
        foreach (Classif_Model_Context::loadList() as $o) {
            $o->delete();
        }
        $entityManager->flush();
    }

    /**
     * Set up
     */
    function setUp()
    {
        $entityManagers = Zend_Registry::get('EntityManagers');
        $this->entityManager = $entityManagers['default'];
    }

    /**
     * Test de la méthode execute()
     */
    function testExecute()
    {
        $numericConstant = Numeric_ConstantTest::generateObject();
        $inputSet = $this->getMockForAbstractClass('Algo_Model_InputSet');
        $result = $numericConstant->execute($inputSet);
        $this->assertTrue($result instanceof Calc_UnitValue);
    }

}
