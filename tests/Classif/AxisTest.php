<?php

namespace Tests\Classif;

use Classif\Domain\IndicatorAxis;
use Core\Test\TestCase;
use PHPUnit_Framework_TestSuite;

class AxisTest
{
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite();
        $suite->addTestSuite(AxisSetUp::class);
        $suite->addTestSuite(AxisOther::class);
        return $suite;
    }

    /**
     * Generation de l'objet de test.
     * @param string $ref
     * @param string $label
     * @return \Classif\Domain\IndicatorAxis
     */
    public static function generateObject($ref = null, $label = null)
    {
        $o = new IndicatorAxis();
        $o->setRef(($ref ===null) ? 'ref' : $ref);
        $o->setLabel(($label ===null) ? 'label' : $label);
        $o->save();
        \Core\ContainerSingleton::getEntityManager()->flush();
        return $o;
    }

    /**
     * Suppression d'un objet cree avec generateObject
     * @param \Classif\Domain\IndicatorAxis $o
     */
    public static function deleteObject($o)
    {
        $o->delete();
        \Core\ContainerSingleton::getEntityManager()->flush();
    }
}

class AxisSetUp extends TestCase
{
    public static function setUpBeforeClass()
    {
        if (IndicatorAxis::countTotal() > 0) {
            echo PHP_EOL . 'Des Classif_Axis restants ont été trouvé avant les tests, suppression en cours !';
            foreach (IndicatorAxis::loadList() as $axis) {
                $axis->delete();
            }
            self::getEntityManager()->flush();
        }
    }

    public function testConstruct()
    {
        $o = new IndicatorAxis();
        $o->setRef('RefAxisTest');
        $o->setLabel('LabelAxisTest');
        $this->assertEquals(array(), $o->getKey());
        $o->save();
        \Core\ContainerSingleton::getEntityManager()->flush();
        $this->assertNotEquals(array(), $o->getKey());
        return $o;
    }

    /**
     * @depends testConstruct
     * @param \Classif\Domain\IndicatorAxis $o
     */
    public function testLoad(IndicatorAxis $o)
    {
         $oLoaded = IndicatorAxis::load($o->getKey());
        $this->assertInstanceOf(IndicatorAxis::class, $o);
         $this->assertEquals($oLoaded->getKey(), $o->getKey());
         $this->assertEquals($oLoaded->getRef(), $o->getRef());
         $this->assertEquals($oLoaded->getLabel(), $o->getLabel());
         return $oLoaded;
    }

    /**
     * @depends testLoad
     * @param \Classif\Domain\IndicatorAxis $o
     */
    public function testDelete(IndicatorAxis $o)
    {
        $o->delete();
        \Core\ContainerSingleton::getEntityManager()->flush();
        $this->assertEquals(array(), $o->getKey());
    }

    public static function tearDownAfterClass()
    {
        if (IndicatorAxis::countTotal() > 0) {
            echo PHP_EOL . 'Des Classif_Axis restants ont été trouvé après les tests, suppression en cours !';
            foreach (IndicatorAxis::loadList() as $axis) {
                $axis->delete();
            }
            self::getEntityManager()->flush();
        }
    }
}

class AxisOther extends TestCase
{
    /**
     * @var \Classif\Domain\IndicatorAxis
     */
    protected $axis;

    public static function setUpBeforeClass()
    {
        if (IndicatorAxis::countTotal() > 0) {
            echo PHP_EOL . 'Des Classif_Axis restants ont été trouvé avant les tests, suppression en cours !';
            foreach (IndicatorAxis::loadList() as $axis) {
                $axis->delete();
            }
            self::getEntityManager()->flush();
        }
    }

    public function setUp()
    {
        parent::setUp();
        $this->axis = AxisTest::generateObject();
    }

    public function testSetGetNarrower()
    {
        $narrower = AxisTest::generateObject('narrower');
        $this->axis->setDirectNarrower($narrower);
        $this->assertSame($this->axis->getDirectNarrower(), $narrower);
        $this->axis->setDirectNarrower();
        $this->assertNull($this->axis->getDirectNarrower());
        AxisTest::deleteObject($narrower);
    }

    public function testManageBroaders()
    {
        $broader1 = AxisTest::generateObject('broader1');
        $broader11 = AxisTest::generateObject('broader11');
        $broader2 = AxisTest::generateObject('broader2');
        $broader3 = AxisTest::generateObject('broader3');

        $this->assertFalse($this->axis->hasDirectBroaders());
        $this->assertFalse($this->axis->hasDirectBroader($broader1));
        $this->assertFalse($this->axis->hasDirectBroader($broader11));
        $this->assertFalse($this->axis->hasDirectBroader($broader2));
        $this->assertFalse($this->axis->hasDirectBroader($broader3));
        $this->assertEmpty($this->axis->getDirectBroaders());
        $this->assertEmpty($this->axis->getAllBroaders());

        $this->axis->addDirectBroader($broader1);
        $this->axis->addDirectBroader($broader2);

        $this->assertTrue($this->axis->hasDirectBroaders());
        $this->assertTrue($this->axis->hasDirectBroader($broader1));
        $this->assertFalse($this->axis->hasDirectBroader($broader11));
        $this->assertTrue($this->axis->hasDirectBroader($broader2));
        $this->assertFalse($this->axis->hasDirectBroader($broader3));
        $this->assertEquals(array($broader1, $broader2), $this->axis->getDirectBroaders());
        $this->assertEquals(array($broader1, $broader2), $this->axis->getAllBroaders());

        $broader1->addDirectBroader($broader11);

        $this->assertTrue($this->axis->hasDirectBroaders());
        $this->assertTrue($this->axis->hasDirectBroader($broader1));
        $this->assertFalse($this->axis->hasDirectBroader($broader11));
        $this->assertTrue($this->axis->hasDirectBroader($broader2));
        $this->assertFalse($this->axis->hasDirectBroader($broader3));
        $this->assertEquals(array($broader1, $broader2), $this->axis->getDirectBroaders());
        $this->assertEquals(array($broader11, $broader1, $broader2), $this->axis->getAllBroaders());

        $this->axis->removeDirectBroader($broader2);

        $this->assertTrue($this->axis->hasDirectBroaders());
        $this->assertTrue($this->axis->hasDirectBroader($broader1));
        $this->assertFalse($this->axis->hasDirectBroader($broader11));
        $this->assertFalse($this->axis->hasDirectBroader($broader2));
        $this->assertFalse($this->axis->hasDirectBroader($broader3));
        $this->assertEquals(array($broader1), $this->axis->getDirectBroaders());
        $this->assertEquals(array($broader11, $broader1), $this->axis->getAllBroaders());

        AxisTest::deleteObject($broader3);
        AxisTest::deleteObject($broader2);
        AxisTest::deleteObject($broader11);
        AxisTest::deleteObject($broader1);

        $this->assertFalse($this->axis->hasDirectBroaders());
        $this->assertFalse($this->axis->hasDirectBroader($broader1));
        $this->assertFalse($this->axis->hasDirectBroader($broader11));
        $this->assertFalse($this->axis->hasDirectBroader($broader2));
        $this->assertFalse($this->axis->hasDirectBroader($broader3));
        $this->assertEmpty($this->axis->getDirectBroaders());
        $this->assertEmpty($this->axis->getAllBroaders());
    }

    protected function tearDown()
    {
        if ($this->axis) {
            AxisTest::deleteObject($this->axis);
        }
    }

    public static function tearDownAfterClass()
    {
        if (IndicatorAxis::countTotal() > 0) {
            echo PHP_EOL . 'Des Classif_Axis restants ont été trouvé après les tests, suppression en cours !';
            foreach (IndicatorAxis::loadList() as $axis) {
                $axis->delete();
            }
            self::getEntityManager()->flush();
        }
    }
}
