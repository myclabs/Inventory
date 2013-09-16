<?php
/**
 * @author benjamin.bertin
 * @package Algo
 * @subpackage Test
 */

require_once dirname(__FILE__).'/../Numeric/ConstantTest.php';

/**
 * Index_AlgoSetUpTest
 * @package Algo
 */
class Index_AlgoSetUpTest extends PHPUnit_Framework_TestCase
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
        foreach (Classif_Model_Indicator::loadList() as $o) {
            $o->delete();
        }
        foreach (Classif_Model_ContextIndicator::loadList() as $o) {
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
     * @return Algo_Model_Index_Algo $o
     */
    function testConstruct()
    {
        // Fixtures
        $classifAxis = new Classif_Model_Axis();
        $classifAxis->setRef(Core_Tools::generateString(20));
        $classifAxis->setLabel('Classif Axis');
        $classifAxis->save();
        $algoNumeric = Numeric_ConstantTest::generateObject();
        $algoKeyword = TextKey_InputTest::generateObject();

        $o = new Algo_Model_Index_Algo($classifAxis, $algoNumeric);
        $o->setAlgo($algoKeyword);
        $o->save();
        $this->entityManager->flush();

        $this->assertSame($classifAxis, $o->getClassifAxis());
        $this->assertSame($algoNumeric, $o->getAlgoNumeric());
        $this->assertSame($algoKeyword, $o->getAlgo());

        return $o;
    }

    /**
     * @depends testConstruct
     * @param Algo_Model_Index_Algo $o
     * @return Algo_Model_Index_Algo $o
     */
    function testLoad(Algo_Model_Index_Algo $o)
    {
        $this->entityManager->clear();
        /** @var $oLoaded Algo_Model_Index_Algo */
        $oLoaded = Algo_Model_Index_Algo::load($o->getKey());
        $this->assertInstanceOf('Algo_Model_Index_Algo', $oLoaded);
        $this->assertEquals($o->getClassifAxis()->getKey(), $oLoaded->getClassifAxis()->getKey());
        $this->assertEquals($o->getAlgoNumeric()->getKey(), $oLoaded->getAlgoNumeric()->getKey());
        $this->assertEquals($o->getAlgo()->getKey(), $oLoaded->getAlgo()->getKey());
        return $oLoaded;
    }

    /**
     * @depends testLoad
     * @param Algo_Model_Index_Algo $o
     */
    function testDelete(Algo_Model_Index_Algo $o)
    {
        $o->delete();
        $o->getClassifAxis()->delete();
        $this->assertEquals(\Doctrine\ORM\UnitOfWork::STATE_REMOVED,
                            $this->entityManager->getUnitOfWork()->getEntityState($o));
        $this->entityManager->flush();
        $this->assertEquals(\Doctrine\ORM\UnitOfWork::STATE_NEW,
                            $this->entityManager->getUnitOfWork()->getEntityState($o));
        Numeric_ConstantTest::deleteObject($o->getAlgoNumeric());
        TextKey_InputTest::deleteObject($o->getAlgo());
    }

}
