<?php

namespace Tests\Algo\Index;

use Algo_Model_Algo;
use Algo_Model_Index_Algo;
use Algo_Model_Set;
use Classif_Model_Axis;
use Classif_Model_Context;
use Classif_Model_ContextIndicator;
use Classif_Model_Indicator;
use Core\Test\TestCase;
use Core_Tools;
use Doctrine\ORM\UnitOfWork;
use Tests\Algo\Numeric\ConstantTest;
use Tests\Algo\TextKey\InputTest;

class AlgoIndexTest extends TestCase
{
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
        foreach (Classif_Model_Indicator::loadList() as $o) {
            $o->delete();
        }
        foreach (Classif_Model_ContextIndicator::loadList() as $o) {
            $o->delete();
        }
        self::getEntityManager()->flush();
    }

    public function testConstruct()
    {
        // Fixtures
        $classifAxis = new Classif_Model_Axis();
        $classifAxis->setRef(Core_Tools::generateString(20));
        $classifAxis->setLabel('Classif Axis');
        $classifAxis->save();
        $algoNumeric = ConstantTest::generateObject();
        $selectionAlgo = InputTest::generateObject();

        $o = new Algo_Model_Index_Algo($classifAxis, $algoNumeric);
        $o->setAlgo($selectionAlgo);
        $o->save();
        $this->entityManager->flush();

        $this->assertSame($classifAxis, $o->getClassifAxis());
        $this->assertSame($algoNumeric, $o->getAlgoNumeric());
        $this->assertSame($selectionAlgo, $o->getAlgo());

        return $o;
    }

    /**
     * @depends testConstruct
     * @param Algo_Model_Index_Algo $o
     * @return Algo_Model_Index_Algo $o
     */
    public function testLoad(Algo_Model_Index_Algo $o)
    {
        $this->entityManager->clear();
        /** @var $oLoaded Algo_Model_Index_Algo */
        $oLoaded = Algo_Model_Index_Algo::load($o->getKey());
        $this->assertInstanceOf(Algo_Model_Index_Algo::class, $oLoaded);
        $this->assertEquals($o->getClassifAxis()->getKey(), $oLoaded->getClassifAxis()->getKey());
        $this->assertEquals($o->getAlgoNumeric()->getKey(), $oLoaded->getAlgoNumeric()->getKey());
        $this->assertEquals($o->getAlgo()->getKey(), $oLoaded->getAlgo()->getKey());
        return $oLoaded;
    }

    /**
     * @depends testLoad
     * @param Algo_Model_Index_Algo $o
     */
    public function testDelete(Algo_Model_Index_Algo $o)
    {
        $o->delete();
        $o->getClassifAxis()->delete();
        $this->assertEquals(UnitOfWork::STATE_REMOVED, $this->entityManager->getUnitOfWork()->getEntityState($o));
        $this->entityManager->flush();
        $this->assertEquals(UnitOfWork::STATE_NEW, $this->entityManager->getUnitOfWork()->getEntityState($o));
        ConstantTest::deleteObject($o->getAlgoNumeric());
        InputTest::deleteObject($o->getAlgo());
    }
}
