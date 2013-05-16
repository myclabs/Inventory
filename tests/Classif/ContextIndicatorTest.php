<?php
/**
 * Classe ContextIndicatorTest
 * @author     valentin.claras
 * @author     cyril.perraud
 * @package    Classif
 * @subpackage Test
 */

/**
 * Creation of the Test Suite
 * @package    Classif
 */
class Classif_Test_ContextIndicatorTest
{
    /**
     * Creation of the test suite
     */
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite();
        $suite->addTestSuite('Classif_Test_ContextIndicatorSetUp');
        $suite->addTestSuite('Classif_Test_ContextIndicatorOther');
        return $suite;
    }

    /**
     * Generation of a test object
     *
     * @param Classif_Model_Context $context
     * @param Classif_Model_Indicator $indicator
     *
     * @return Classif_Model_ContextIndicator
     */
    public static function generateObject($context=null, $indicator=null)
    {
        $o = new Classif_Model_ContextIndicator();
        $o->setContext(($context ===null) ? Classif_Test_ContextTest::generateObject() : $context);
        $o->setIndicator(($indicator ===null) ? Classif_Test_IndicatorTest::generateObject() : $indicator);
        $o->save();
        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->flush();
        return $o;
    }

    /**
     * Deletion of an object created with generateObject
     * @param Classif_Model_ContextIndicator $o
     * @param bool $deleteContext
     * @param bool $deleteIndicator
     */
    public static function deleteObject($o, $deleteContext=true, $deleteIndicator=true)
    {
        if ($deleteContext) {
            $o->getContext()->delete();
        }
        if ($deleteIndicator) {
            $o->getIndicator()->delete();
        }
        $o->delete();
        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->flush();
    }
}

/**
 * Test of the creation/modification/deletion of the entity
 * @package Classif
 */
class Classif_Test_ContextIndicatorSetUp extends PHPUnit_Framework_TestCase
{

    /**
     * Function called once, before all the tests
     */
    public static function setUpBeforeClass()
    {
        // Vérification qu'il ne reste aucun Classif_Model_ContextIndicator en base, sinon suppression !
        if (Classif_Model_ContextIndicator::countTotal() > 0) {
            echo PHP_EOL . 'Des Classif_ContextIndicator restants ont été trouvé avant les tests, suppression en cours !';
            foreach (Classif_Model_ContextIndicator::loadList() as $contextIndicator) {
                $contextIndicator->delete();
            }
            $entityManagers = Zend_Registry::get('EntityManagers');
            $entityManagers['default']->flush();
        }
        // Vérification qu'il ne reste aucun Classif_Model_Context en base, sinon suppression !
        if (Classif_Model_Context::countTotal() > 0) {
            echo PHP_EOL . 'Des Classif_Context restants ont été trouvé avant les tests, suppression en cours !';
            foreach (Classif_Model_Context::loadList() as $context) {
                $context->delete();
            }
            $entityManagers = Zend_Registry::get('EntityManagers');
            $entityManagers['default']->flush();
        }
        // Vérification qu'il ne reste aucun Classif_Model_Indicator en base, sinon suppression !
        if (Classif_Model_Indicator::countTotal() > 0) {
            echo PHP_EOL . 'Des Classif_Indicator restants ont été trouvé avant les tests, suppression en cours !';
            foreach (Classif_Model_Indicator::loadList() as $indicator) {
                $indicator->delete();
            }
            $entityManagers = Zend_Registry::get('EntityManagers');
            $entityManagers['default']->flush();
        }
    }

    /**
     * Test le constructeur
     * @return Classif_Model_ContextIndicator
     */
    function testConstruct()
    {
        $context = Classif_Test_ContextTest::generateObject('ContextIndicatorSetUpTest');
        $indicator = Classif_Test_IndicatorTest::generateObject('ContextIndicatorSetUpTest');
        $o = new Classif_Model_ContextIndicator();
        $this->assertInstanceOf('Classif_Model_ContextIndicator', $o);
        $o->setContext($context);
        $o->setIndicator($indicator);
        try {
            Classif_Model_ContextIndicator::load($o->getKey());
            $this->assertTrue(false);
        } catch (Core_Exception_NotFound $e) {
            $this->assertTrue(true);
        }
        $o->save();
        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->flush();
        $this->assertNotEquals(array(), $o->getKey());
        return $o;
    }

    /**
     * @depends testConstruct
     * @param Classif_Model_ContextIndicator $o
     */
    function testLoad(Classif_Model_ContextIndicator $o)
    {

         $oLoaded = Classif_Model_ContextIndicator::load($o->getKey());
         $this->assertInstanceOf('Classif_Model_ContextIndicator', $o);
         $this->assertEquals($oLoaded->getKey(), $o->getKey());
         $this->assertEquals($oLoaded->getContext(), $o->getContext());
         $this->assertEquals($oLoaded->getIndicator(), $o->getIndicator());
         return $oLoaded;
    }

    /**
     * @depends testLoad
     * @param Classif_Model_ContextIndicator $o
     */
    function testDelete(Classif_Model_ContextIndicator $o)
    {
        $o->delete();
        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->flush();
        try {
            Classif_Model_ContextIndicator::load($o->getKey());
            $this->assertTrue(false);
        } catch (Core_Exception_NotFound $e) {
            $this->assertTrue(true);
        }
        Classif_Test_ContextTest::deleteObject($o->getContext());
        Classif_Test_IndicatorTest::deleteObject($o->getIndicator());
    }

    /**
     * Function called once, after all the tests
     */
    public static function tearDownAfterClass()
    {
        // Vérification qu'il ne reste aucun Classif_Model_ContextIndicator en base, sinon suppression !
        if (Classif_Model_ContextIndicator::countTotal() > 0) {
            echo PHP_EOL . 'Des Classif_ContextIndicator restants ont été trouvé après les tests, suppression en cours !';
            foreach (Classif_Model_ContextIndicator::loadList() as $contextIndicator) {
                $contextIndicator->delete();
            }
            $entityManagers = Zend_Registry::get('EntityManagers');
            $entityManagers['default']->flush();
        }
        // Vérification qu'il ne reste aucun Classif_Model_Context en base, sinon suppression !
        if (Classif_Model_Context::countTotal() > 0) {
            echo PHP_EOL . 'Des Classif_Context restants ont été trouvé après les tests, suppression en cours !';
            foreach (Classif_Model_Context::loadList() as $context) {
                $context->delete();
            }
            $entityManagers = Zend_Registry::get('EntityManagers');
            $entityManagers['default']->flush();
        }
        // Vérification qu'il ne reste aucun Classif_Model_Indicator en base, sinon suppression !
        if (Classif_Model_Indicator::countTotal() > 0) {
            echo PHP_EOL . 'Des Classif_Indicator restants ont été trouvé après les tests, suppression en cours !';
            foreach (Classif_Model_Indicator::loadList() as $indicator) {
                $indicator->delete();
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
class Classif_Test_ContextIndicatorOther extends PHPUnit_Framework_TestCase
{

    /**
     * @var Classif_Model_ContextIndicator
     */
    protected $contextIndicator;


    /**
     * Function called once, before all the tests
     */
    public static function setUpBeforeClass()
    {
        // Vérification qu'il ne reste aucun Classif_Model_ContextIndicator en base, sinon suppression !
        if (Classif_Model_ContextIndicator::countTotal() > 0) {
            echo PHP_EOL . 'Des Classif_ContextIndicator restants ont été trouvé avant les tests, suppression en cours !';
            foreach (Classif_Model_ContextIndicator::loadList() as $contextIndicator) {
                $contextIndicator->delete();
            }
            $entityManagers = Zend_Registry::get('EntityManagers');
            $entityManagers['default']->flush();
        }
        // Vérification qu'il ne reste aucun Classif_Model_Context en base, sinon suppression !
        if (Classif_Model_Context::countTotal() > 0) {
            echo PHP_EOL . 'Des Classif_Context restants ont été trouvé avant les tests, suppression en cours !';
            foreach (Classif_Model_Context::loadList() as $context) {
                $context->delete();
            }
            $entityManagers = Zend_Registry::get('EntityManagers');
            $entityManagers['default']->flush();
        }
        // Vérification qu'il ne reste aucun Classif_Model_Indicator en base, sinon suppression !
        if (Classif_Model_Indicator::countTotal() > 0) {
            echo PHP_EOL . 'Des Classif_Indicator restants ont été trouvé avant les tests, suppression en cours !';
            foreach (Classif_Model_Indicator::loadList() as $indicator) {
                $indicator->delete();
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
        $this->contextIndicator = Classif_Test_ContextIndicatorTest::generateObject();
    }

    /**
     * Lance removeAllAxes
     */
    function testManageAxes()
    {
        $axis1 = Classif_Test_AxisTest::generateObject('axis1');
        $axis2 = Classif_Test_AxisTest::generateObject('axis2');
        $axis3 = Classif_Test_AxisTest::generateObject('axis3');

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

        Classif_Test_AxisTest::deleteObject($axis1);
        Classif_Test_AxisTest::deleteObject($axis2);
        Classif_Test_AxisTest::deleteObject($axis3);
    }

    /**
     * Teste loadByRef
     */
    public function loadByRef()
    {
        $contextIndicator = Classif_Model_ContextIndicator::loadByRef(
            $this->contextIndicator->getContext()->getRef(),
            $this->contextIndicator->getIndicator()->getRef()
        );
        $this->assertSame($this->contextIndicator, $contextIndicator);
    }

    /**
     * Function called after each test
     */
    protected function tearDown()
    {
        Classif_Test_ContextIndicatorTest::deleteObject($this->contextIndicator);
    }

    /**
     * Function called once, after all the tests
     */
    public static function tearDownAfterClass()
    {
        // Vérification qu'il ne reste aucun Classif_Model_ContextIndicator en base, sinon suppression !
        if (Classif_Model_ContextIndicator::countTotal() > 0) {
            echo PHP_EOL . 'Des Classif_ContextIndicator restants ont été trouvé après les tests, suppression en cours !';
            foreach (Classif_Model_ContextIndicator::loadList() as $contextIndicator) {
                $contextIndicator->delete();
            }
            $entityManagers = Zend_Registry::get('EntityManagers');
            $entityManagers['default']->flush();
        }
        // Vérification qu'il ne reste aucun Classif_Model_Context en base, sinon suppression !
        if (Classif_Model_Context::countTotal() > 0) {
            echo PHP_EOL . 'Des Classif_Context restants ont été trouvé après les tests, suppression en cours !';
            foreach (Classif_Model_Context::loadList() as $context) {
                $context->delete();
            }
            $entityManagers = Zend_Registry::get('EntityManagers');
            $entityManagers['default']->flush();
        }
        // Vérification qu'il ne reste aucun Classif_Model_Indicator en base, sinon suppression !
        if (Classif_Model_Indicator::countTotal() > 0) {
            echo PHP_EOL . 'Des Classif_Indicator restants ont été trouvé après les tests, suppression en cours !';
            foreach (Classif_Model_Indicator::loadList() as $indicator) {
                $indicator->delete();
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
