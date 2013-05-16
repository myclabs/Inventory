<?php
/**
 * Test de l'objet métier Composite
 * @author valentin.claras
 * @author hugo.charbonier
 * @author yoann.croizer
 * @package TEC
 */

/**
 * compositeTest
 * @package TEC
 */
class TEC_Test_CompositeTest
{
    /**
     * lance les autre classe de tests
     */
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite();
        $suite->addTestSuite('TEC_Test_CompositeSetUp');
        $suite->addTestSuite('TEC_Test_CompositeOthers');
        return $suite;
    }

    /**
     * Generation of a test object
     * @param const $operator
     * @return TEC_Model_Composite
     */
    public static function generateObject($operator=null)
    {
        if ($operator === null) {
            $operator = TEC_Model_Composite::OPERATOR_SUM;
        }
        $o = new TEC_Model_Composite();
        $o->setOperator($operator);
        $o->save();
        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->flush();
        return $o;
    }

    /**
     * Deletion of an object created with generateObject
     * @param TEC_Model_Composite $o
     */
    public static function deleteObject(TEC_Model_Composite $o)
    {
        $o->delete();
        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->flush();
    }

}

/**
 * @package TEC
 */
class TEC_Test_compositeSetUp extends PHPUnit_Framework_TestCase
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
     * @return TEC_Model_Composite
     */
    function testConstruct()
    {
        $o = new TEC_Model_Composite();
        $o->setOperator(TEC_Model_Composite::OPERATOR_SUM);
        $o->save();
        $this->assertInstanceOf('TEC_Model_Composite', $o);
        $this->assertEquals(array(), $o->getKey());
        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->flush();
        $this->assertNotEquals(array(), $o->getKey());
        return $o;
    }

    /**
     * @depends testConstruct
     * @param TEC_Model_Composite $o
     */
    function testLoad(TEC_Model_Composite $o)
    {
        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->clear($o);
        $oLoaded = TEC_Model_Component::load($o->getKey());
        $this->assertInstanceOf('TEC_Model_Composite', $o);
        $this->assertEquals($oLoaded->getKey(), $o->getKey());
        $this->assertEquals($oLoaded->getOperator(), $o->getOperator());
        return $oLoaded;
    }

    /**
     * @depends testLoad
     * @param TEC_Model_Composite $o
     */
    function testDelete(TEC_Model_Composite $o)
    {
        $o->delete();
        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->flush();
        $this->assertEquals(array(), $o->getKey());
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
 * @package TEC
 */
class TEC_Test_CompositeOthers extends PHPUnit_Framework_TestCase
{
    /**
     * @var TEC_Model_Composite
     */
    protected $_treeComposite;

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
        $this->_treeComposite = TEC_Test_CompositeTest::generateObject();
    }

    /**
     * Test de setIdParent() et getParent()
     */
    function testParent()
    {
        $child = TEC_Test_CompositeTest::generateObject();
        $child->setParent($this->_treeComposite);
        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->flush();
        $this->assertEquals($child->getParent(), $this->_treeComposite);
        $child->delete();
        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->flush();
    }

    /**
     * Tente de créer un cycle dans l'arbre
     * @expectedException Core_Exception_InvalidArgument
     */
    function testParentExceptionCycle()
    {
        $composite = TEC_Test_CompositeTest::generateObject();
        $composite->setParent($this->_treeComposite);
        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->flush();
        try {
            $this->_treeComposite->setParent($composite );
        } catch (Core_Exception_InvalidArgument $e) {
            if ($e->getMessage() === 'Error : Cycle in the Tree detected') {
                throw $e;
            }
        }
    }

    /**
     * Test de getChildren() et hasChild()
     */
    function testChildren()
    {
        $this->assertFalse($this->_treeComposite->hasChildren());

        $composite1 = TEC_Test_CompositeTest::generateObject();
        $composite2 = TEC_Test_CompositeTest::generateObject();

        $composite1->setParent($this->_treeComposite);
        $composite2->setParent($this->_treeComposite);

        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->flush();

        $this->assertTrue($this->_treeComposite->hasChildren());
        $this->assertTrue($this->_treeComposite->hasChild($composite1));
        $this->assertTrue($this->_treeComposite->hasChild($composite2));
        $tempChild = new TEC_Model_Leaf();
        $this->assertFalse($this->_treeComposite->hasChild($tempChild));

        $children = $this->_treeComposite->getChildren();
        $this->assertEquals(count($children), 2);
        $this->assertEquals($children[0], $composite1);
        $this->assertEquals($children[1], $composite2);
        TEC_Test_CompositeTest::deleteObject($composite1);
        TEC_Test_CompositeTest::deleteObject($composite2);
    }

    /**
     * Teste la suppression d'un noeud possèdant des enfants
     */
    function testLoadDeleteExtended()
    {
        $entityManagers = Zend_Registry::get('EntityManagers');
        $root = TEC_Test_CompositeTest::generateObject();

        $composite1 = TEC_Test_CompositeTest::generateObject();
        $composite1->setParent($root);

        $composite11 = TEC_Test_CompositeTest::generateObject();
        $composite11->setParent($composite1);

        $composite2 = TEC_Test_CompositeTest::generateObject();
        $composite2->setParent($root);

        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->flush();
        $entityManagers['default']->clear($root);
        $entityManagers['default']->clear($composite1);
        $entityManagers['default']->clear($composite11);
        $entityManagers['default']->clear($composite2);

        $rootLoaded = TEC_Model_Component::load($root->getKey());
        $composite1Loaded = TEC_Model_Component::load($composite1->getKey());
        $composite11Loaded = TEC_Model_Component::load($composite11->getKey());
        $composite2Loaded = TEC_Model_Component::load($composite2->getKey());

        $this->assertSame($composite11->getParent(), $composite1);

        $rootLoaded->delete();
        $entityManagers['default']->flush();
        $this->assertEquals(array(), $rootLoaded->getKey());
        $this->assertEquals(array(), $composite1Loaded->getKey());
        $this->assertEquals(array(), $composite11Loaded->getKey());
        $this->assertEquals(array(), $composite2Loaded->getKey());
    }

    /**
     * Méthode appelée à la fin de chaque test
     */
    protected function tearDown()
    {
        TEC_Test_CompositeTest::deleteObject($this->_treeComposite);
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