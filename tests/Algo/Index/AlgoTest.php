<?php

namespace Tests\Algo\Index;

use AF\Domain\Algorithm\Algo;
use AF\Domain\Algorithm\Index\AlgoResultIndex;
use AF\Domain\Algorithm\AlgoSet;
use Classification\Domain\IndicatorAxis;
use Classification\Domain\Context;
use Classification\Domain\ContextIndicator;
use Classification\Domain\Indicator;
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
        foreach (AlgoSet::loadList() as $o) {
            $o->delete();
        }
        foreach (Algo::loadList() as $o) {
            $o->delete();
        }
        foreach (Context::loadList() as $o) {
            $o->delete();
        }
        foreach (Indicator::loadList() as $o) {
            $o->delete();
        }
        foreach (ContextIndicator::loadList() as $o) {
            $o->delete();
        }
        self::getEntityManager()->flush();
    }

    public function testConstruct()
    {
        // Fixtures
        $classifAxis = new IndicatorAxis();
        $classifAxis->setRef(Core_Tools::generateString(20));
        $classifAxis->setLabel('Classification Axis');
        $classifAxis->save();
        $algoNumeric = ConstantTest::generateObject();
        $selectionAlgo = InputTest::generateObject();

        $o = new AlgoResultIndex($classifAxis, $algoNumeric);
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
     * @param \AF\Domain\Algorithm\Index\AlgoResultIndex $o
     * @return \AF\Domain\Algorithm\Index\AlgoResultIndex $o
     */
    public function testLoad(AlgoResultIndex $o)
    {
        $this->entityManager->clear();
        /** @var $oLoaded \AF\Domain\Algorithm\Index\AlgoResultIndex */
        $oLoaded = AlgoResultIndex::load($o->getKey());
        $this->assertInstanceOf(AlgoResultIndex::class, $oLoaded);
        $this->assertEquals($o->getClassifAxis()->getKey(), $oLoaded->getClassifAxis()->getKey());
        $this->assertEquals($o->getAlgoNumeric()->getKey(), $oLoaded->getAlgoNumeric()->getKey());
        $this->assertEquals($o->getAlgo()->getKey(), $oLoaded->getAlgo()->getKey());
        return $oLoaded;
    }

    /**
     * @depends testLoad
     * @param \AF\Domain\Algorithm\Index\AlgoResultIndex $o
     */
    public function testDelete(AlgoResultIndex $o)
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
