<?php

namespace Tests\Algo\Numeric;

use AF\Domain\Algorithm\Algo;
use AF\Domain\Algorithm\InputSet;
use AF\Domain\Algorithm\Numeric\NumericConstantAlgo;
use AF\Domain\Algorithm\AlgoSet;
use Calc_UnitValue;
use Classif_Model_Context;
use Classif_Model_ContextIndicator;
use Classif_Model_Indicator;
use Core\Test\TestCase;
use Core_Tools;
use Doctrine\ORM\UnitOfWork;
use Unit\UnitAPI;

class ConstantTest extends TestCase
{
    public static function generateObject()
    {
        $set = new AlgoSet();
        $set->save();
        self::getEntityManager()->flush();

        $o = new NumericConstantAlgo();
        $o->setSet($set);
        $o->setRef(strtolower(Core_Tools::generateString(20)));
        $o->setUnitValue(self::generateUnitValue());
        $o->setContextIndicator(self::generateContextIndicator());
        $o->setLabel('labelNumericConstant');
        $o->save();
        self::getEntityManager()->flush();
        return $o;
    }

    public static function deleteObject(NumericConstantAlgo $o)
    {
        $o->delete();
        $o->getSet()->delete();
        self::deleteContextIndicator($o->getContextIndicator());
        self::getEntityManager()->flush();
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
        self::getEntityManager()->flush();
        $contextIndicator = new Classif_Model_ContextIndicator();
        $contextIndicator->setContext($context);
        $contextIndicator->setIndicator($indicator);
        $contextIndicator->save();
        self::getEntityManager()->flush();
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
        self::getEntityManager()->flush();
    }

    public static function setUpBeforeClass()
    {
        foreach (AlgoSet::loadList() as $o) {
            $o->delete();
        }
        foreach (Algo::loadList() as $o) {
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
        $set = new AlgoSet();
        $set->save();
        $this->entityManager->flush();
        $unitValue = ConstantTest::generateUnitValue();

        $o = new NumericConstantAlgo();
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
     * @param \AF\Domain\Algorithm\Numeric\NumericConstantAlgo $o
     * @return NumericConstantAlgo $o
     */
    public function testLoad(NumericConstantAlgo $o)
    {
        $this->entityManager->clear();
        /** @var $oLoaded \AF\Domain\Algorithm\Numeric\NumericConstantAlgo */
        $oLoaded = NumericConstantAlgo::load($o->getKey());
        $this->assertInstanceOf(NumericConstantAlgo::class, $oLoaded);
        $this->assertEquals($o->getUnit()->getRef(), $oLoaded->getUnit()->getRef());
        return $oLoaded;
    }

    /**
     * @depends testLoad
     * @param \AF\Domain\Algorithm\Numeric\NumericConstantAlgo $o
     */
    public function testDelete(NumericConstantAlgo $o)
    {
        $o->delete();
        $o->getSet()->delete();
        $this->assertEquals(UnitOfWork::STATE_REMOVED, $this->entityManager->getUnitOfWork()->getEntityState($o));
        $this->entityManager->flush();
        $this->assertEquals(UnitOfWork::STATE_NEW, $this->entityManager->getUnitOfWork()->getEntityState($o));
    }

    public function testExecute()
    {
        $numericConstant = ConstantTest::generateObject();
        $inputSet = $this->getMockForAbstractClass(InputSet::class);
        $result = $numericConstant->execute($inputSet);
        $this->assertTrue($result instanceof Calc_UnitValue);
    }
}
