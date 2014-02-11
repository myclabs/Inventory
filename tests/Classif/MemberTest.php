<?php

namespace Tests\Classif;

use Classif\Domain\IndicatorAxis;
use Classif\Domain\AxisMember;
use Core\Test\TestCase;
use PHPUnit_Framework_TestSuite;

class MemberTest
{
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite();
        $suite->addTestSuite(MemberSetUp::class);
        $suite->addTestSuite(MemberOther::class);
        return $suite;
    }

    /**
     * Generation de l'objet de test.
     * @param string $ref
     * @param string $label
     * @param IndicatorAxis $axis
     * @return AxisMember
     */
    public static function generateObject($ref = null, $label = null, $axis = null)
    {
        $o = new AxisMember();
        $o->setRef(($ref ===null) ? 'ref' : $ref);
        $o->setLabel(($label ===null) ? 'label' : $label);
        $o->setAxis(($axis ===null) ? AxisTest::generateObject($o->getRef(), $o->getLabel()) : $axis);
        $o->save();
        \Core\ContainerSingleton::getEntityManager()->flush();
        return $o;
    }

    /**
     * Suppression d'un objet cree avec generateObject
     * @param AxisMember $o
     * @param bool                 $deleteAxis
     */
    public static function deleteObject(AxisMember $o, $deleteAxis = true)
    {
        if ($deleteAxis === true) {
            $o->getAxis()->delete();
        }
        $o->delete();
        \Core\ContainerSingleton::getEntityManager()->flush();
    }
}

class MemberSetUp extends TestCase
{
    public static function setUpBeforeClass()
    {
        if (AxisMember::countTotal() > 0) {
            echo PHP_EOL . 'Des Classif_Member restants ont été trouvé avant les tests, suppression en cours !';
            foreach (AxisMember::loadList() as $member) {
                $member->delete();
            }
            self::getEntityManager()->flush();
        }
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
        $axis = AxisTest::generateObject('MemberSetUpTest', 'MemberSetUpTest');
        $o = new AxisMember();
        $o->setRef('RefMemberTest');
        $o->setLabel('LabelMemberTest');
        $o->setAxis($axis);
        $this->assertEquals(array(), $o->getKey());
        $o->save();
        $this->entityManager->flush();
        $this->assertNotEquals(array(), $o->getKey());
        return $o;
    }

    /**
     * @depends testConstruct
     * @param AxisMember $o
     * @return AxisMember
     */
    public function testLoad(AxisMember $o)
    {
         $oLoaded = AxisMember::load($o->getKey());
         $this->assertInstanceOf(AxisMember::class, $o);
         $this->assertEquals($oLoaded->getKey(), $o->getKey());
         $this->assertEquals($oLoaded->getRef(), $o->getRef());
         $this->assertSame($oLoaded->getAxis(), $o->getAxis());
         $this->assertEquals($oLoaded->getLabel(), $o->getLabel());
         return $oLoaded;
    }

    /**
     * @depends testLoad
     * @param AxisMember $o
     */
    public function testDelete(AxisMember $o)
    {
        $o->delete();
        $this->entityManager->flush();
        $this->assertEquals(array(), $o->getKey());
        AxisTest::deleteObject($o->getAxis());
    }

    public static function tearDownAfterClass()
    {
        if (AxisMember::countTotal() > 0) {
            echo PHP_EOL . 'Des Classif_Member restants ont été trouvé après les tests, suppression en cours !';
            foreach (AxisMember::loadList() as $member) {
                $member->delete();
            }
            self::getEntityManager()->flush();
        }
        if (IndicatorAxis::countTotal() > 0) {
            echo PHP_EOL . 'Des Classif_Axis restants ont été trouvé après les tests, suppression en cours !';
            foreach (IndicatorAxis::loadList() as $axis) {
                $axis->delete();
            }
            self::getEntityManager()->flush();
        }
    }
}

class MemberOther extends TestCase
{
    /**
     * @var \Classif\Domain\AxisMember
     */
    protected $member;

    public static function setUpBeforeClass()
    {
        if (AxisMember::countTotal() > 0) {
            echo PHP_EOL . 'Des Classif_Member restants ont été trouvé avant les tests, suppression en cours !';
            foreach (AxisMember::loadList() as $member) {
                $member->delete();
            }
            self::getEntityManager()->flush();
        }
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
        $this->member = MemberTest::generateObject();
    }

    /**
     * Test d'ajout d'un parent
     */
    public function testManageChild()
    {
        $child1 = MemberTest::generateObject('child1');
        $child11 = MemberTest::generateObject('child11');
        $child2 = MemberTest::generateObject('child2');
        $child3 = MemberTest::generateObject('child3');

        $this->assertFalse($this->member->hasDirectChildren());
        $this->assertFalse($this->member->hasDirectChild($child1));
        $this->assertFalse($this->member->hasDirectChild($child11));
        $this->assertFalse($this->member->hasDirectChild($child2));
        $this->assertFalse($this->member->hasDirectChild($child3));
        $this->assertEmpty($this->member->getDirectChildren());
        $this->assertEmpty($this->member->getAllChildren());

        $this->member->addDirectChild($child1);
        $this->member->addDirectChild($child2);

        $this->assertTrue($this->member->hasDirectChildren());
        $this->assertTrue($this->member->hasDirectChild($child1));
        $this->assertFalse($this->member->hasDirectChild($child11));
        $this->assertTrue($this->member->hasDirectChild($child2));
        $this->assertFalse($this->member->hasDirectChild($child3));
        $this->assertEquals(array($child1, $child2), $this->member->getDirectChildren());
        $this->assertEquals(array($child1, $child2), $this->member->getAllChildren());

        $child1->addDirectChild($child11);

        $this->assertTrue($this->member->hasDirectChildren());
        $this->assertTrue($this->member->hasDirectChild($child1));
        $this->assertFalse($this->member->hasDirectChild($child11));
        $this->assertTrue($this->member->hasDirectChild($child2));
        $this->assertFalse($this->member->hasDirectChild($child3));
        $this->assertEquals(array($child1, $child2), $this->member->getDirectChildren());
        $this->assertEquals(array($child1, $child2, $child11), $this->member->getAllChildren());

        $this->member->removeDirectChild($child2);

        $this->assertTrue($this->member->hasDirectChildren());
        $this->assertTrue($this->member->hasDirectChild($child1));
        $this->assertFalse($this->member->hasDirectChild($child11));
        $this->assertFalse($this->member->hasDirectChild($child2));
        $this->assertFalse($this->member->hasDirectChild($child3));
        $this->assertEquals(array($child1), $this->member->getDirectChildren());
        $this->assertEquals(array($child1, $child11), $this->member->getAllChildren());

        MemberTest::deleteObject($child3);
        MemberTest::deleteObject($child2);
        MemberTest::deleteObject($child11);
        MemberTest::deleteObject($child1);

        $this->assertFalse($this->member->hasDirectChildren());
        $this->assertFalse($this->member->hasDirectChild($child1));
        $this->assertFalse($this->member->hasDirectChild($child11));
        $this->assertFalse($this->member->hasDirectChild($child2));
        $this->assertFalse($this->member->hasDirectChild($child3));
        $this->assertEmpty($this->member->getDirectChildren());
        $this->assertEmpty($this->member->getAllChildren());
    }

    /**
     * Test d'ajout d'un parent
     */
    public function testManageParents()
    {
        $parent1 = MemberTest::generateObject('parent1');
        $parent11 = MemberTest::generateObject('parent11');
        $parent2 = MemberTest::generateObject('parent2');
        $parent3 = MemberTest::generateObject('parent3');

        $this->assertFalse($this->member->hasDirectParents());
        $this->assertFalse($this->member->hasDirectParent($parent1));
        $this->assertFalse($this->member->hasDirectParent($parent11));
        $this->assertFalse($this->member->hasDirectParent($parent2));
        $this->assertFalse($this->member->hasDirectParent($parent3));
        $this->assertEmpty($this->member->getDirectParents());
        $this->assertEmpty($this->member->getAllParents());

        $this->member->addDirectParent($parent1);
        $this->member->addDirectParent($parent2);

        $this->assertTrue($this->member->hasDirectParents());
        $this->assertTrue($this->member->hasDirectParent($parent1));
        $this->assertFalse($this->member->hasDirectParent($parent11));
        $this->assertTrue($this->member->hasDirectParent($parent2));
        $this->assertFalse($this->member->hasDirectParent($parent3));
        $this->assertEquals(array($parent1, $parent2), $this->member->getDirectParents());
        $this->assertEquals(array($parent1, $parent2), $this->member->getAllParents());

        $parent1->addDirectParent($parent11);

        $this->assertTrue($this->member->hasDirectParents());
        $this->assertTrue($this->member->hasDirectParent($parent1));
        $this->assertFalse($this->member->hasDirectParent($parent11));
        $this->assertTrue($this->member->hasDirectParent($parent2));
        $this->assertFalse($this->member->hasDirectParent($parent3));
        $this->assertEquals(array($parent1, $parent2), $this->member->getDirectParents());
        $this->assertEquals(array($parent1, $parent2, $parent11), $this->member->getAllParents());

        $this->member->removeDirectParent($parent2);

        $this->assertTrue($this->member->hasDirectParents());
        $this->assertTrue($this->member->hasDirectParent($parent1));
        $this->assertFalse($this->member->hasDirectParent($parent11));
        $this->assertFalse($this->member->hasDirectParent($parent2));
        $this->assertFalse($this->member->hasDirectParent($parent3));
        $this->assertEquals(array($parent1), $this->member->getDirectParents());
        $this->assertEquals(array($parent1, $parent11), $this->member->getAllParents());

        MemberTest::deleteObject($parent3);
        MemberTest::deleteObject($parent2);
        MemberTest::deleteObject($parent11);
        MemberTest::deleteObject($parent1);

        $this->assertFalse($this->member->hasDirectParents());
        $this->assertFalse($this->member->hasDirectParent($parent1));
        $this->assertFalse($this->member->hasDirectParent($parent11));
        $this->assertFalse($this->member->hasDirectParent($parent2));
        $this->assertFalse($this->member->hasDirectParent($parent3));
        $this->assertEmpty($this->member->getDirectParents());
        $this->assertEmpty($this->member->getAllParents());
    }

    protected function tearDown()
    {
        MemberTest::deleteObject($this->member);
    }

    public static function tearDownAfterClass()
    {
        if (AxisMember::countTotal() > 0) {
            echo PHP_EOL . 'Des Classif_Member restants ont été trouvé après les tests, suppression en cours !';
            foreach (AxisMember::loadList() as $member) {
                $member->delete();
            }
            self::getEntityManager()->flush();
        }
        if (IndicatorAxis::countTotal() > 0) {
            echo PHP_EOL . 'Des Classif_Axis restants ont été trouvé après les tests, suppression en cours !';
            foreach (IndicatorAxis::loadList() as $axis) {
                $axis->delete();
            }
            self::getEntityManager()->flush();
        }
    }
}
