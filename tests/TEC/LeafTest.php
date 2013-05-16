<?php
/**
 * Test de l'objet métier Leaf
 * @author valentin.claras
 * @author hugo.charboniere
 * @author yoann.croizer
 * @package TEC
 */

/**
 * leafTest
 * @package TEC
 */
class TEC_Test_LeafTest
{
    /**
     * lance les autres classe de tests
     */
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite();
        $suite->addTestSuite('TEC_Test_LeafSetUp');
        $suite->addTestSuite('TEC_Test_LeafOthers');
        return $suite;
    }

    /**
     * Generation of a test object
     * @param string              $name
     * @param TEC_Model_Composite $parent
     * @return TEC_Model_Leaf
     */
    public static function generateObject($name='test', $parent=null)
    {
        if ($parent === null) {
            $parent = Tree_Test_CompositeTest::generateObject();
        }
        $o = new TEC_Model_Leaf();
        $o->setName($name);
        $o->setParent($parent);
        $o->save();
        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->flush();
        return $o;
    }

    /**
     * Deletion of an object created with generateObject
     * @param TEC_Model_Leaf $o
     * @param bool $deleteParent
     */
    public static function deleteObject(TEC_Model_Leaf $o, $deleteParent=false)
    {
        $o->delete();
        if ($deleteParent === true) {
            $o->getParent()->delete();
        }
        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->flush();
    }

}

/**
 * @package TEC
 */
class TEC_Test_LeafSetUp extends PHPUnit_Framework_TestCase
{

    /**
     * Méthode appelée avant l'appel à la classe de test
     */
    public static function setUpBeforeClass()
    {
        // Vérification qu'il ne reste aucun TEC_Model_Component en base, sinon suppression !
        if (TEC_Model_Component::countTotal() > 0) {
            echo PHP_EOL . 'Des TEC_Component restants ont été trouvé avant les tests, suppression en cours !';
            foreach (TEC_Model_Component::loadList() as $component) {
                $component->delete();
            }
            $entityManagers = Zend_Registry::get('EntityManagers');
            $entityManagers['default']->flush();
        }
    }

    /**
     * Méthode appelée avant chaque test
     */
    protected function setUp()
    {
    }

    /**
     * Test des constructeurs et de la sauvegarde en base de données
     * @return TEC_Model_Leaf
     */
    function testConstruct()
    {
        $parent = TEC_Test_CompositeTest::generateObject();
        $o = new TEC_Model_Leaf();
        $o->setName('testSetUp');
        $o->setParent($parent);
        $o->save();
        $this->assertInstanceOf('TEC_Model_Leaf', $o);
        $this->assertEquals(array(), $o->getKey());
        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->flush();
        $this->assertNotEquals(array(), $o->getKey());
        return $o;
    }

    /**
     * @depends testConstruct
     * @param TEC_Model_Leaf $o
     */
    function testLoad(TEC_Model_Leaf $o)
    {
        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->clear($o);
        $oLoaded = TEC_Model_Component::load($o->getKey());
        $this->assertInstanceOf('TEC_Model_Leaf', $o);
        $this->assertEquals($oLoaded->getKey(), $o->getKey());
        return $oLoaded;
    }

    /**
     * @depends testLoad
     * @param TEC_Model_Leaf $o
     */
    function testDelete(TEC_Model_Leaf $o)
    {
        $o->delete();
        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->flush();
        $this->assertEquals(array(), $o->getKey());
        $o->getParent()->delete();
        $entityManagers['default']->flush();
    }

    /**
     * Méthode appelée à la fin de chaque test
     */
    protected function tearDown()
    {
    }

    /**
     * Méthode appelée à la fin de la classe de test
     */
    public static function tearDownAfterClass()
    {
        // Vérification qu'il ne reste aucun TEC_Model_Component en base, sinon suppression !
        if (TEC_Model_Component::countTotal() > 0) {
            echo PHP_EOL . 'Des TEC_Component restants ont été trouvé après les tests, suppression en cours !';
            foreach (TEC_Model_Component::loadList() as $component) {
                $component->delete();
            }
            $entityManagers = Zend_Registry::get('EntityManagers');
            $entityManagers['default']->flush();
        }
    }

}

/**
 * leafLogiqueMetierTest
 * @package TEC
 */
class TEC_Test_LeafOthers extends PHPUnit_Framework_TestCase
{
    /**
     * Méthode appelée avant l'appel à la classe de test
     */
    public static function setUpBeforeClass()
    {
        // Vérification qu'il ne reste aucun TEC_Model_Component en base, sinon suppression !
        if (TEC_Model_Component::countTotal() > 0) {
            echo PHP_EOL . 'Des TEC_Component restants ont été trouvé avant les tests, suppression en cours !';
            foreach (TEC_Model_Component::loadList() as $component) {
                $component->delete();
            }
            $entityManagers = Zend_Registry::get('EntityManagers');
            $entityManagers['default']->flush();
        }
    }

    /**
     * Méthode appelée avant chaque test
     */
    protected function setUp()
    {
    }

    /**
     * Test des constructeurs et de la sauvegarde en base de données
     * @expectedException Core_Exception_UndefinedAttribute
     */
    function testNoParentException()
    {
        $o = new TEC_Model_Leaf();
        try {
            $o->save();
        } catch (Core_Exception_UndefinedAttribute $e) {
            if ($e->getMessage() === 'A Leaf needs to have a parent element.') {
                throw $e;
            }
        }
    }


    /**
     * Méthode appelée à la fin de chaque test
     */
    protected function tearDown()
    {
    }

    /**
     * Méthode appelée à la fin de la classe de test
     */
    public static function tearDownAfterClass()
    {
        // Vérification qu'il ne reste aucun TEC_Model_Component en base, sinon suppression !
        if (TEC_Model_Component::countTotal() > 0) {
            echo PHP_EOL . 'Des TEC_Component restants ont été trouvé après les tests, suppression en cours !';
            foreach (TEC_Model_Component::loadList() as $component) {
                $component->delete();
            }
            $entityManagers = Zend_Registry::get('EntityManagers');
            $entityManagers['default']->flush();
        }
    }

}