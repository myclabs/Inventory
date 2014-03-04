<?php

namespace Tests\Classif;

use Classif_Model_Axis;
use Classif_Model_Context;
use Classif_Model_ContextIndicator;
use Classif_Model_Indicator;
use Core\Test\TestCase;
use Core_Exception_NotFound;
use PHPUnit_Framework_TestSuite;

class ContextIndicatorTest
{
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite();
        $suite->addTestSuite(ContextIndicatorSetUp::class);
        $suite->addTestSuite(ContextIndicatorOther::class);
        return $suite;
    }

    /**
     * Generation of a test object
     * @param Classif_Model_Context $context
     * @param Classif_Model_Indicator $indicator
     * @return Classif_Model_ContextIndicator
     */
    public static function generateObject($context = null, $indicator = null)
    {
        $o = new Classif_Model_ContextIndicator();
        $o->setContext(($context ===null) ? ContextTest::generateObject() : $context);
        $o->setIndicator(($indicator ===null) ? IndicatorTest::generateObject() : $indicator);
        $o->save();
        \Core\ContainerSingleton::getEntityManager()->flush();
        return $o;
    }

    /**
     * Deletion of an object created with generateObject
     * @param Classif_Model_ContextIndicator $o
     * @param bool $deleteContext
     * @param bool $deleteIndicator
     */
    public static function deleteObject($o, $deleteContext = true, $deleteIndicator = true)
    {
        if ($deleteContext) {
            $o->getContext()->delete();
        }
        if ($deleteIndicator) {
            $o->getIndicator()->delete();
        }
        $o->delete();
        \Core\ContainerSingleton::getEntityManager()->flush();
    }
}

class ContextIndicatorSetUp extends TestCase
{
    public static function setUpBeforeClass()
    {
        if (Classif_Model_ContextIndicator::countTotal() > 0) {
            echo PHP_EOL . 'Des ContextIndicator restants ont été trouvé avant les tests, suppression en cours !';
            foreach (Classif_Model_ContextIndicator::loadList() as $contextIndicator) {
                $contextIndicator->delete();
            }
            self::getEntityManager()->flush();
        }
        if (Classif_Model_Context::countTotal() > 0) {
            echo PHP_EOL . 'Des Classif_Context restants ont été trouvé avant les tests, suppression en cours !';
            foreach (Classif_Model_Context::loadList() as $context) {
                $context->delete();
            }
            self::getEntityManager()->flush();
        }
        if (Classif_Model_Indicator::countTotal() > 0) {
            echo PHP_EOL . 'Des Classif_Indicator restants ont été trouvé avant les tests, suppression en cours !';
            foreach (Classif_Model_Indicator::loadList() as $indicator) {
                $indicator->delete();
            }
            self::getEntityManager()->flush();
        }
    }

    public function testConstruct()
    {
        $context = ContextTest::generateObject('ContextIndicatorSetUpTest');
        $indicator = IndicatorTest::generateObject('ContextIndicatorSetUpTest');
        $o = new Classif_Model_ContextIndicator();
        $o->setContext($context);
        $o->setIndicator($indicator);
        try {
            Classif_Model_ContextIndicator::load($o->getKey());
            $this->assertTrue(false);
        } catch (Core_Exception_NotFound $e) {
            $this->assertTrue(true);
        }
        $o->save();
        $this->entityManager->flush();
        $this->assertNotEquals(array(), $o->getKey());
        return $o;
    }

    /**
     * @depends testConstruct
     * @param Classif_Model_ContextIndicator $o
     * @return Classif_Model_ContextIndicator
     */
    public function testLoad(Classif_Model_ContextIndicator $o)
    {
         $oLoaded = Classif_Model_ContextIndicator::load($o->getKey());
         $this->assertInstanceOf(Classif_Model_ContextIndicator::class, $o);
         $this->assertEquals($oLoaded->getKey(), $o->getKey());
         $this->assertEquals($oLoaded->getContext(), $o->getContext());
         $this->assertEquals($oLoaded->getIndicator(), $o->getIndicator());
         return $oLoaded;
    }

    /**
     * @depends testLoad
     * @param Classif_Model_ContextIndicator $o
     */
    public function testDelete(Classif_Model_ContextIndicator $o)
    {
        $o->delete();
        $this->entityManager->flush();
        try {
            Classif_Model_ContextIndicator::load($o->getKey());
            $this->assertTrue(false);
        } catch (Core_Exception_NotFound $e) {
            $this->assertTrue(true);
        }
        ContextTest::deleteObject($o->getContext());
        IndicatorTest::deleteObject($o->getIndicator());
    }

    public static function tearDownAfterClass()
    {
        if (Classif_Model_ContextIndicator::countTotal() > 0) {
            echo PHP_EOL . 'Des ContextIndicator restants ont été trouvé après les tests, suppression en cours !';
            foreach (Classif_Model_ContextIndicator::loadList() as $contextIndicator) {
                $contextIndicator->delete();
            }
            self::getEntityManager()->flush();
        }
        if (Classif_Model_Context::countTotal() > 0) {
            echo PHP_EOL . 'Des Classif_Context restants ont été trouvé après les tests, suppression en cours !';
            foreach (Classif_Model_Context::loadList() as $context) {
                $context->delete();
            }
            self::getEntityManager()->flush();
        }
        if (Classif_Model_Indicator::countTotal() > 0) {
            echo PHP_EOL . 'Des Classif_Indicator restants ont été trouvé après les tests, suppression en cours !';
            foreach (Classif_Model_Indicator::loadList() as $indicator) {
                $indicator->delete();
            }
            self::getEntityManager()->flush();
        }
    }
}

class ContextIndicatorOther extends TestCase
{
    /**
     * @var Classif_Model_ContextIndicator
     */
    protected $contextIndicator;

    public static function setUpBeforeClass()
    {
        if (Classif_Model_ContextIndicator::countTotal() > 0) {
            echo PHP_EOL . 'Des ContextIndicator restants ont été trouvé avant les tests, suppression en cours !';
            foreach (Classif_Model_ContextIndicator::loadList() as $contextIndicator) {
                $contextIndicator->delete();
            }
            self::getEntityManager()->flush();
        }
        if (Classif_Model_Context::countTotal() > 0) {
            echo PHP_EOL . 'Des Classif_Context restants ont été trouvé avant les tests, suppression en cours !';
            foreach (Classif_Model_Context::loadList() as $context) {
                $context->delete();
            }
            self::getEntityManager()->flush();
        }
        if (Classif_Model_Indicator::countTotal() > 0) {
            echo PHP_EOL . 'Des Classif_Indicator restants ont été trouvé avant les tests, suppression en cours !';
            foreach (Classif_Model_Indicator::loadList() as $indicator) {
                $indicator->delete();
            }
            self::getEntityManager()->flush();
        }
        if (Classif_Model_Axis::countTotal() > 0) {
            echo PHP_EOL . 'Des Classif_Axis restants ont été trouvé avant les tests, suppression en cours !';
            foreach (Classif_Model_Axis::loadList() as $axis) {
                $axis->delete();
            }
            self::getEntityManager()->flush();
        }
    }

    public function setUp()
    {
        parent::setUp();
        $this->contextIndicator = ContextIndicatorTest::generateObject();
    }

    public function testManageAxes()
    {
        $axis1 = AxisTest::generateObject('axis1');
        $axis2 = AxisTest::generateObject('axis2');
        $axis3 = AxisTest::generateObject('axis3');

        $this->assertFalse($this->contextIndicator->hasAxes());
        $this->assertFalse($this->contextIndicator->hasAxis($axis1));
        $this->assertFalse($this->contextIndicator->hasAxis($axis2));
        $this->assertFalse($this->contextIndicator->hasAxis($axis3));
        $this->assertEmpty($this->contextIndicator->getAxes());

        $this->contextIndicator->addAxis($axis1);
        $this->contextIndicator->addAxis($axis2);

        $this->assertTrue($this->contextIndicator->hasAxes());
        $this->assertTrue($this->contextIndicator->hasAxis($axis1));
        $this->assertTrue($this->contextIndicator->hasAxis($axis2));
        $this->assertFalse($this->contextIndicator->hasAxis($axis3));
        $this->assertEquals(array($axis1, $axis2), $this->contextIndicator->getAxes());

        $this->contextIndicator->removeAxis($axis1);

        $this->assertTrue($this->contextIndicator->hasAxes());
        $this->assertFalse($this->contextIndicator->hasAxis($axis1));
        $this->assertTrue($this->contextIndicator->hasAxis($axis2));
        $this->assertFalse($this->contextIndicator->hasAxis($axis3));
        $this->assertEquals(array(1 => $axis2), $this->contextIndicator->getAxes());

        $this->contextIndicator->removeAxis($axis2);

        $this->assertFalse($this->contextIndicator->hasAxes());
        $this->assertFalse($this->contextIndicator->hasAxis($axis1));
        $this->assertFalse($this->contextIndicator->hasAxis($axis2));
        $this->assertFalse($this->contextIndicator->hasAxis($axis3));
        $this->assertEmpty($this->contextIndicator->getAxes());

        AxisTest::deleteObject($axis1);
        AxisTest::deleteObject($axis2);
        AxisTest::deleteObject($axis3);
    }

    public function loadByRef()
    {
        $contextIndicator = Classif_Model_ContextIndicator::loadByRef(
            $this->contextIndicator->getContext()->getRef(),
            $this->contextIndicator->getIndicator()->getRef()
        );
        $this->assertSame($this->contextIndicator, $contextIndicator);
    }

    protected function tearDown()
    {
        if ($this->contextIndicator) {
            ContextIndicatorTest::deleteObject($this->contextIndicator);
        }
    }

    public static function tearDownAfterClass()
    {
        if (Classif_Model_ContextIndicator::countTotal() > 0) {
            echo PHP_EOL . 'Des ContextIndicator restants ont été trouvé après les tests, suppression en cours !';
            foreach (Classif_Model_ContextIndicator::loadList() as $contextIndicator) {
                $contextIndicator->delete();
            }
            self::getEntityManager()->flush();
        }
        if (Classif_Model_Context::countTotal() > 0) {
            echo PHP_EOL . 'Des Classif_Context restants ont été trouvé après les tests, suppression en cours !';
            foreach (Classif_Model_Context::loadList() as $context) {
                $context->delete();
            }
            self::getEntityManager()->flush();
        }
        if (Classif_Model_Indicator::countTotal() > 0) {
            echo PHP_EOL . 'Des Classif_Indicator restants ont été trouvé après les tests, suppression en cours !';
            foreach (Classif_Model_Indicator::loadList() as $indicator) {
                $indicator->delete();
            }
            self::getEntityManager()->flush();
        }
        if (Classif_Model_Axis::countTotal() > 0) {
            echo PHP_EOL . 'Des Classif_Axis restants ont été trouvé après les tests, suppression en cours !';
            foreach (Classif_Model_Axis::loadList() as $axis) {
                $axis->delete();
            }
            self::getEntityManager()->flush();
        }
    }
}
