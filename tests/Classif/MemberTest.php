<?php
/**
 * @author     valentin.claras
 * @package    Classif
 * @subpackage Test
 */

/**
 * Creation of the Test Suite
 * @package    Classif
 */
class Classif_Test_MemberTest
{
    /**
     * Creation of the test suite
     */
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite();
        $suite->addTestSuite('Classif_Test_MemberSetUp');
        $suite->addTestSuite('Classif_Test_MemberOther');
        return $suite;
    }

    /**
     * Generation de l'objet de test.
     *
     * @param string $ref
     * @param string $label
     * @param Classif_Model_Axis $axis
     *
     * @return Classif_Model_Member
     */
    public static function generateObject($ref=null, $label=null, $axis=null)
    {
        $o = new Classif_Model_Member();
        $o->setRef(($ref ===null) ? 'ref' : $ref);
        $o->setLabel(($label ===null) ? 'label' : $label);
        $o->setAxis(($axis ===null) ? Classif_Test_AxisTest::generateObject($o->getRef(), $o->getLabel()) : $axis);
        $o->save();
        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->flush();
        return $o;
    }

    /**
     * Suppression d'un objet cree avec generateObject
     * @param Classif_Model_Member $o
     * @param bool $deleteAxis
     */
    public static function deleteObject(Classif_Model_Member $o, $deleteAxis=true)
    {
        if ($deleteAxis === true) {
            $o->getAxis()->delete();
        }
        $o->delete();
        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->flush();
    }

}

/**
 * Test of the creation/modification/deletion of the entity
 * @package    Classif
 */
class Classif_Test_MemberSetUp extends PHPUnit_Framework_TestCase
{

    /**
     * Function called once, before all the tests
     */
    public static function setUpBeforeClass()
    {
        // Vérification qu'il ne reste aucun Classif_Model_Member en base, sinon suppression !
        if (Classif_Model_Member::countTotal() > 0) {
            echo PHP_EOL . 'Des Classif_Member restants ont été trouvé avant les tests, suppression en cours !';
            foreach (Classif_Model_Member::loadList() as $member) {
                $member->delete();
            }
            $entityManagers = Zend_Registry::get('EntityManagers');
            $entityManagers['default']->flush();
        }
        // Vérification qu'il ne reste aucun Classif_Model_Axis en base, sinon suppression !
        if (Classif_Model_Axis::countTotal() > 0) {
            echo PHP_EOL . 'Des Classif_Axis restants ont été trouvé avant les tests, suppression en cours !';
            foreach (Classif_Model_Axis::loadList() as $axis) {
                $axis->delete();
            }
            $entityManagers = Zend_Registry::get('EntityManagers');
            $entityManagers['default']->flush();
        }
    }

    /**
     * Test le constructeur
     * @return Classif_Model_Member
     */
    function testConstruct()
    {
        $axis = Classif_Test_AxisTest::generateObject('MemberSetUpTest', 'MemberSetUpTest');
        $o = new Classif_Model_Member();
        $this->assertInstanceOf('Classif_Model_Member', $o);
        $o->setRef('RefMemberTest');
        $o->setLabel('LabelMemberTest');
        $o->setAxis($axis);
        $this->assertEquals(array(), $o->getKey());
        $o->save();
        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->flush();
        $this->assertNotEquals(array(), $o->getKey());
        return $o;
    }

    /**
     * @depends testConstruct
     * @param Classif_Model_Member $o
     */
    function testLoad(Classif_Model_Member $o)
    {
         $oLoaded = Classif_Model_Member::load($o->getKey());
         $this->assertInstanceOf('Classif_Model_Member', $o);
         $this->assertEquals($oLoaded->getKey(), $o->getKey());
         $this->assertEquals($oLoaded->getRef(), $o->getRef());
         $this->assertSame($oLoaded->getAxis(), $o->getAxis());
         $this->assertEquals($oLoaded->getLabel(), $o->getLabel());
         return $oLoaded;
    }

    /**
     * @depends testLoad
     * @param Classif_Model_Member $o
     */
    function testDelete(Classif_Model_Member $o)
    {
        $o->delete();
        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->flush();
        $this->assertEquals(array(), $o->getKey());
        Classif_Test_AxisTest::deleteObject($o->getAxis());
    }

    /**
     * Function called once, after all the tests
     */
    public static function tearDownAfterClass()
    {
        // Vérification qu'il ne reste aucun Classif_Model_Member en base, sinon suppression !
        if (Classif_Model_Member::countTotal() > 0) {
            echo PHP_EOL . 'Des Classif_Member restants ont été trouvé après les tests, suppression en cours !';
            foreach (Classif_Model_Member::loadList() as $member) {
                $member->delete();
            }
            $entityManagers = Zend_Registry::get('EntityManagers');
            $entityManagers['default']->flush();
        }
        // Vérification qu'il ne reste aucun Classif_Model_Axis en base, sinon suppression !
        if (Classif_Model_Axis::countTotal() > 0) {
            echo PHP_EOL . 'Des Classif_Axis restants ont été trouvé après les tests, suppression en cours !';
            foreach (Classif_Model_Axis::loadList() as $axis) {
                $axis->delete();
            }
            $entityManagers = Zend_Registry::get('EntityManagers');
            $entityManagers['default']->flush();
        }
    }

}


/**
 * Tests of User class
 * @package    Classif
 */
class Classif_Test_MemberOther extends PHPUnit_Framework_TestCase
{
    // Test objects
    protected $_member;


    /**
     * Function called once, before all the tests
     */
    public static function setUpBeforeClass()
    {
        // Vérification qu'il ne reste aucun Classif_Model_Member en base, sinon suppression !
        if (Classif_Model_Member::countTotal() > 0) {
            echo PHP_EOL . 'Des Classif_Member restants ont été trouvé avant les tests, suppression en cours !';
            foreach (Classif_Model_Member::loadList() as $member) {
                $member->delete();
            }
            $entityManagers = Zend_Registry::get('EntityManagers');
            $entityManagers['default']->flush();
        }
        // Vérification qu'il ne reste aucun Classif_Model_Axis en base, sinon suppression !
        if (Classif_Model_Axis::countTotal() > 0) {
            echo PHP_EOL . 'Des Classif_Axis restants ont été trouvé avant les tests, suppression en cours !';
            foreach (Classif_Model_Axis::loadList() as $axis) {
                $axis->delete();
            }
            $entityManagers = Zend_Registry::get('EntityManagers');
            $entityManagers['default']->flush();
        }
    }

    /**
     * Function called before each test
     */
    protected function setUp()
    {
        $this->_member = Classif_Test_MemberTest::generateObject();
    }

    /**
     * Test d'ajout d'un parent
     */
    public function testManageChild()
    {
        $child1 = Classif_Test_MemberTest::generateObject('child1');
        $child11 = Classif_Test_MemberTest::generateObject('child11');
        $child2 = Classif_Test_MemberTest::generateObject('child2');
        $child3 = Classif_Test_MemberTest::generateObject('child3');

        $this->assertFalse($this->_member->hasDirectChildren());
        $this->assertFalse($this->_member->hasDirectChild($child1));
        $this->assertFalse($this->_member->hasDirectChild($child11));
        $this->assertFalse($this->_member->hasDirectChild($child2));
        $this->assertFalse($this->_member->hasDirectChild($child3));
        $this->assertEmpty($this->_member->getDirectChildren());
        $this->assertEmpty($this->_member->getAllChildren());

        $this->_member->addDirectChild($child1);
        $this->_member->addDirectChild($child2);

        $this->assertTrue($this->_member->hasDirectChildren());
        $this->assertTrue($this->_member->hasDirectChild($child1));
        $this->assertFalse($this->_member->hasDirectChild($child11));
        $this->assertTrue($this->_member->hasDirectChild($child2));
        $this->assertFalse($this->_member->hasDirectChild($child3));
        $this->assertEquals(array($child1, $child2), $this->_member->getDirectChildren());
        $this->assertEquals(array($child1, $child2), $this->_member->getAllChildren());

        $child1->addDirectChild($child11);

        $this->assertTrue($this->_member->hasDirectChildren());
        $this->assertTrue($this->_member->hasDirectChild($child1));
        $this->assertFalse($this->_member->hasDirectChild($child11));
        $this->assertTrue($this->_member->hasDirectChild($child2));
        $this->assertFalse($this->_member->hasDirectChild($child3));
        $this->assertEquals(array($child1, $child2), $this->_member->getDirectChildren());
        $this->assertEquals(array($child1, $child2, $child11), $this->_member->getAllChildren());

        $this->_member->removeDirectChild($child2);

        $this->assertTrue($this->_member->hasDirectChildren());
        $this->assertTrue($this->_member->hasDirectChild($child1));
        $this->assertFalse($this->_member->hasDirectChild($child11));
        $this->assertFalse($this->_member->hasDirectChild($child2));
        $this->assertFalse($this->_member->hasDirectChild($child3));
        $this->assertEquals(array($child1), $this->_member->getDirectChildren());
        $this->assertEquals(array($child1, $child11), $this->_member->getAllChildren());

        Classif_Test_MemberTest::deleteObject($child3);
        Classif_Test_MemberTest::deleteObject($child2);
        Classif_Test_MemberTest::deleteObject($child11);
        Classif_Test_MemberTest::deleteObject($child1);

        $this->assertFalse($this->_member->hasDirectChildren());
        $this->assertFalse($this->_member->hasDirectChild($child1));
        $this->assertFalse($this->_member->hasDirectChild($child11));
        $this->assertFalse($this->_member->hasDirectChild($child2));
        $this->assertFalse($this->_member->hasDirectChild($child3));
        $this->assertEmpty($this->_member->getDirectChildren());
        $this->assertEmpty($this->_member->getAllChildren());
    }

    /**
     * Test d'ajout d'un parent
     */
    public function testManageParents()
    {
        $parent1 = Classif_Test_MemberTest::generateObject('parent1');
        $parent11 = Classif_Test_MemberTest::generateObject('parent11');
        $parent2 = Classif_Test_MemberTest::generateObject('parent2');
        $parent3 = Classif_Test_MemberTest::generateObject('parent3');

        $this->assertFalse($this->_member->hasDirectParents());
        $this->assertFalse($this->_member->hasDirectParent($parent1));
        $this->assertFalse($this->_member->hasDirectParent($parent11));
        $this->assertFalse($this->_member->hasDirectParent($parent2));
        $this->assertFalse($this->_member->hasDirectParent($parent3));
        $this->assertEmpty($this->_member->getDirectParents());
        $this->assertEmpty($this->_member->getAllParents());

        $this->_member->addDirectParent($parent1);
        $this->_member->addDirectParent($parent2);

        $this->assertTrue($this->_member->hasDirectParents());
        $this->assertTrue($this->_member->hasDirectParent($parent1));
        $this->assertFalse($this->_member->hasDirectParent($parent11));
        $this->assertTrue($this->_member->hasDirectParent($parent2));
        $this->assertFalse($this->_member->hasDirectParent($parent3));
        $this->assertEquals(array($parent1, $parent2), $this->_member->getDirectParents());
        $this->assertEquals(array($parent1, $parent2), $this->_member->getAllParents());

        $parent1->addDirectParent($parent11);

        $this->assertTrue($this->_member->hasDirectParents());
        $this->assertTrue($this->_member->hasDirectParent($parent1));
        $this->assertFalse($this->_member->hasDirectParent($parent11));
        $this->assertTrue($this->_member->hasDirectParent($parent2));
        $this->assertFalse($this->_member->hasDirectParent($parent3));
        $this->assertEquals(array($parent1, $parent2), $this->_member->getDirectParents());
        $this->assertEquals(array($parent1, $parent2, $parent11), $this->_member->getAllParents());

        $this->_member->removeDirectParent($parent2);

        $this->assertTrue($this->_member->hasDirectParents());
        $this->assertTrue($this->_member->hasDirectParent($parent1));
        $this->assertFalse($this->_member->hasDirectParent($parent11));
        $this->assertFalse($this->_member->hasDirectParent($parent2));
        $this->assertFalse($this->_member->hasDirectParent($parent3));
        $this->assertEquals(array($parent1), $this->_member->getDirectParents());
        $this->assertEquals(array($parent1, $parent11), $this->_member->getAllParents());

        Classif_Test_MemberTest::deleteObject($parent3);
        Classif_Test_MemberTest::deleteObject($parent2);
        Classif_Test_MemberTest::deleteObject($parent11);
        Classif_Test_MemberTest::deleteObject($parent1);

        $this->assertFalse($this->_member->hasDirectParents());
        $this->assertFalse($this->_member->hasDirectParent($parent1));
        $this->assertFalse($this->_member->hasDirectParent($parent11));
        $this->assertFalse($this->_member->hasDirectParent($parent2));
        $this->assertFalse($this->_member->hasDirectParent($parent3));
        $this->assertEmpty($this->_member->getDirectParents());
        $this->assertEmpty($this->_member->getAllParents());
    }

    /**
     * Function called after each test
     */
    protected function tearDown()
    {
        Classif_Test_MemberTest::deleteObject($this->_member);
    }

    /**
     * Function called once, after all the tests
     */
    public static function tearDownAfterClass()
    {
        // Vérification qu'il ne reste aucun Classif_Model_Member en base, sinon suppression !
        if (Classif_Model_Member::countTotal() > 0) {
            echo PHP_EOL . 'Des Classif_Member restants ont été trouvé après les tests, suppression en cours !';
            foreach (Classif_Model_Member::loadList() as $member) {
                $member->delete();
            }
            $entityManagers = Zend_Registry::get('EntityManagers');
            $entityManagers['default']->flush();
        }
        // Vérification qu'il ne reste aucun Classif_Model_Axis en base, sinon suppression !
        if (Classif_Model_Axis::countTotal() > 0) {
            echo PHP_EOL . 'Des Classif_Axis restants ont été trouvé après les tests, suppression en cours !';
            foreach (Classif_Model_Axis::loadList() as $axis) {
                $axis->delete();
            }
            $entityManagers = Zend_Registry::get('EntityManagers');
            $entityManagers['default']->flush();
        }
    }

}