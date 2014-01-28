<?php

namespace Tests\Algo\Numeric;

use Algo_Model_Algo;
use Algo_Model_InputSet;
use Algo_Model_Numeric_Constant;
use Algo_Model_Set;
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
        $set = new Algo_Model_Set();
        $set->save();
        self::getEntityManager()->flush();

        $o = new Algo_Model_Numeric_Constant();
        $o->setSet($set);
        $o->setRef(strtolower(Core_Tools::generateString(20)));
        $o->setUnitValue(self::generateUnitValue());
        $o->setContextIndicator(self::generateContextIndicator());
        $o->setLabel('labelNumericConstant');
        $o->save();
        self::getEntityManager()->flush();
        return $o;
    }

    public static function deleteObject(Algo_Model_Numeric_Constant $o)
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
        return new Calc_UnitValue(new UnitAPI('m'), 2, 0.1);
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
        $indicator->setUnit(new UnitAPI('m'));
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
        $set = new Algo_Model_Set();
        $set->save();
        $this->entityManager->flush();
        $unitValue = ConstantTest::generateUnitValue();

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
    public function testLoad(Algo_Model_Numeric_Constant $o)
    {
        $this->entityManager->clear();
        /** @var $oLoaded Algo_Model_Numeric_Constant */
        $oLoaded = Algo_Model_Numeric_Constant::load($o->getKey());
        $this->assertInstanceOf(Algo_Model_Numeric_Constant::class, $oLoaded);
        $this->assertEquals($o->getUnit()->getRef(), $oLoaded->getUnit()->getRef());
        return $oLoaded;
    }

    /**
     * @depends testLoad
     * @param Algo_Model_Numeric_Constant $o
     */
    public function testDelete(Algo_Model_Numeric_Constant $o)
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
        $inputSet = $this->getMockForAbstractClass(Algo_Model_InputSet::class);
        $result = $numericConstant->execute($inputSet);
        $this->assertTrue($result instanceof Calc_UnitValue);
    }
}
