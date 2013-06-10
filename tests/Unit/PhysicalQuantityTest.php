<?php
/**
 * Test de l'objet métier PhysicalQuantity.
 * @author valentin.claras
 * @author hugo.charboniere
 * @author yoann.croizer
 * @package Unit
 * @subpackage Test
 */
use Unit\Domain\Unit\Unit;
use Unit\Domain\PhysicalQuantity;
use Unit\Domain\UnitSystem;

/**
 * PhysicalQuantityTest
 * @package Unit
 * @subpackage Test
 */
class Unit_Test_PhysicalQuantityTest
{
    /**
     * lance les autre classe de tests
     */
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite();
        $suite->addTestSuite('Unit_Test_PhysicalQuantitySetUp');
        $suite->addTestSuite('Unit_Test_PhysicalQuantityOthers');
        return $suite;
    }

    /**
     * Permet de générer un objet de base sur lequel on pourra travailler
     * @param string $ref
     * @return PhysicalQuantity $o
     */
    public static function generateObject($ref='UnitPhysicalQuantityTest')
    {
        $o = new PhysicalQuantity();
        $o->setRef('Ref'.$ref);
        $o->setName('Name'.$ref);
        $o->setSymbol('Symbol'.$ref);
        $o->setIsBase(true);
        $o->save();
        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->flush();

        return $o;
    }

    /**
     * Permet de supprimer un objet de base sur lequel on a travaillé
     * @param PhysicalQuantity $o
     */
    public static function deleteObject(PhysicalQuantity $o)
    {
        $o->delete();
        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->flush();
    }
}

/**
 * PhysicalQuantitySetUpTest
 * @package Unit
 */
class Unit_Test_PhysicalQuantitySetUp extends PHPUnit_Framework_TestCase
{
    /**
     * Méthode appelée avant l'appel à la classe de test
     */
    public static function setUpBeforeClass()
    {
        // Vérification qu'il ne reste aucun Unit en base, sinon suppression !
        if (Unit::countTotal() > 0) {
            echo PHP_EOL . 'Des Unit_System restants ont été trouvé avant les tests, suppression en cours !';
            foreach (Unit::loadList() as $unit) {
                $unit->delete();
            }
            $entityManagers = Zend_Registry::get('EntityManagers');
            $entityManagers['default']->flush();
        }
        // Vérification qu'il ne reste aucun PhysicalQuantity en base, sinon suppression !
        if (PhysicalQuantity::countTotal() > 0) {
            echo PHP_EOL . 'Des Unit_System restants ont été trouvé avant les tests, suppression en cours !';
            foreach (PhysicalQuantity::loadList() as $physicalQuantity) {
                $physicalQuantity->delete();
            }
            $entityManagers = Zend_Registry::get('EntityManagers');
            $entityManagers['default']->flush();
        }
        // Vérification qu'il ne reste aucun UnitSystem en base, sinon suppression !
        if (UnitSystem::countTotal() > 0) {
            echo PHP_EOL . 'Des Unit_System restants ont été trouvé avant les tests, suppression en cours !';
            foreach (UnitSystem::loadList() as $systemunit) {
                $systemunit->delete();
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
     * Test le constructeur
     */
    function testConstruct()
    {
        $o = new PhysicalQuantity();
        $this->assertInstanceOf('Unit\Domain\PhysicalQuantity', $o);
        $o->setRef('RefPhysicalQuantityTest');
        $o->setName('NamePhysicalQuantityTest');
        $o->setSymbol('physicalQuantity');
        $o->setIsBase(true);
        $this->assertEquals(array(), $o->getKey());
        $o->save();
        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->flush();
        $this->assertNotEquals(array(), $o->getKey());
        return $o;
    }

    /**
     * @depends testConstruct
     * @param PhysicalQuantity $o
     */
    function testLoad($o)
    {
         $oLoaded = PhysicalQuantity::load($o->getKey());
         $this->assertInstanceOf('Unit\Domain\PhysicalQuantity', $o);
         $this->assertEquals($oLoaded->getKey(), $o->getKey());
         $this->assertEquals($oLoaded->getRef(), $o->getRef());
         $this->assertEquals($oLoaded->getName(), $o->getName());
         $this->assertEquals($oLoaded->getSymbol(), $o->getSymbol());
         $this->assertEquals($oLoaded->isBase(), $o->isBase());
         return $oLoaded;
    }

    /**
     * @depends testLoad
     * @param PhysicalQuantity $o
     */
    function testDelete(PhysicalQuantity $o)
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
     * On verifie que les tables soientt vides après les tests
     */
    public static function tearDownAfterClass()
    {
        // Vérification qu'il ne reste aucun Unit en base, sinon suppression !
        if (Unit::countTotal() > 0) {
            echo PHP_EOL . 'Des Unit_System restants ont été trouvé après les tests, suppression en cours !';
            foreach (Unit::loadList() as $unit) {
                $unit->delete();
            }
            $entityManagers = Zend_Registry::get('EntityManagers');
            $entityManagers['default']->flush();
        }
        // Vérification qu'il ne reste aucun PhysicalQuantity en base, sinon suppression !
        if (PhysicalQuantity::countTotal() > 0) {
            echo PHP_EOL . 'Des Unit_System restants ont été trouvé après les tests, suppression en cours !';
            foreach (PhysicalQuantity::loadList() as $physicalQuantity) {
                $physicalQuantity->delete();
            }
            $entityManagers = Zend_Registry::get('EntityManagers');
            $entityManagers['default']->flush();
        }
        // Vérification qu'il ne reste aucun UnitSystem en base, sinon suppression !
        if (UnitSystem::countTotal() > 0) {
            echo PHP_EOL . 'Des Unit_System restants ont été trouvé après les tests, suppression en cours !';
            foreach (UnitSystem::loadList() as $systemunit) {
                $systemunit->delete();
            }
            $entityManagers = Zend_Registry::get('EntityManagers');
            $entityManagers['default']->flush();
        }
    }
}


/**
 * PhysicalQuantityOthersTest
 * @package Unit
 */
class Unit_Test_PhysicalQuantityOthers extends PHPUnit_Framework_TestCase
{
    protected $derivedPhysicalQuantity;
    protected $basePhysicalQuantity;

    /**
     * Méthode appelée avant l'appel à la classe de test
     */
    public static function setUpBeforeClass()
    {
        // Vérification qu'il ne reste aucun Unit en base, sinon suppression !
        if (Unit::countTotal() > 0) {
            echo PHP_EOL . 'Des Unit_System restants ont été trouvé avant les tests, suppression en cours !';
            foreach (Unit::loadList() as $unit) {
                $unit->delete();
            }
            $entityManagers = Zend_Registry::get('EntityManagers');
            $entityManagers['default']->flush();
        }
        // Vérification qu'il ne reste aucun PhysicalQuantity en base, sinon suppression !
        if (PhysicalQuantity::countTotal() > 0) {
            echo PHP_EOL . 'Des Unit_System restants ont été trouvé avant les tests, suppression en cours !';
            foreach (PhysicalQuantity::loadList() as $physicalQuantity) {
                $physicalQuantity->delete();
            }
            $entityManagers = Zend_Registry::get('EntityManagers');
            $entityManagers['default']->flush();
        }
        // Vérification qu'il ne reste aucun UnitSystem en base, sinon suppression !
        if (UnitSystem::countTotal() > 0) {
            echo PHP_EOL . 'Des Unit_System restants ont été trouvé avant les tests, suppression en cours !';
            foreach (UnitSystem::loadList() as $systemunit) {
                $systemunit->delete();
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
        $this->basePhysicalQuantity    = Unit_Test_PhysicalQuantityTest::generateObject('Base');
        $this->derivedPhysicalQuantity = Unit_Test_PhysicalQuantityTest::generateObject('Derived');
    }

    /**
     * test de la méthode loadByRef()
     */
    function testLoadByRef()
    {
        $o = PhysicalQuantity::loadByRef('RefDerived');
        $this->assertInstanceOf('Unit\Domain\PhysicalQuantity', $o);
        $this->assertSame($o, $this->derivedPhysicalQuantity);
    }

    /**
     * @depends testLoadByRef
     */
    function testSetGetReferenceUnit()
    {
        $unit = Unit_Test_StandardUnitTest::generateObject();
        $this->derivedPhysicalQuantity->setReferenceUnit($unit);
        $this->assertEquals($unit->getKey(), $this->derivedPhysicalQuantity->getReferenceUnit()->getKey());
        $this->derivedPhysicalQuantity->setReferenceUnit(null);
        $this->assertEquals($this->derivedPhysicalQuantity->getReferenceUnit(), null);
        Unit_Test_StandardUnitTest::deleteObject($unit);

        $unit = Unit_Test_StandardUnitTest::generateObject('SetGetReferenceUnit', 1, $this->basePhysicalQuantity);
        $this->assertEquals($unit->getPhysicalQuantity()->getKey(), $this->basePhysicalQuantity->getKey());
        $this->basePhysicalQuantity->setReferenceUnit($unit);
        $this->assertEquals($unit->getKey(), $this->basePhysicalQuantity->getReferenceUnit()->getKey());
        $this->basePhysicalQuantity->setReferenceUnit(null);
        Unit_Test_StandardUnitTest::deleteObject($unit, false);
    }

    /**
     * @depends testLoadByRef
     */
    function testAddGetPhysicalQuantityComponents()
    {
        $this->basePhysicalQuantity->addPhysicalQuantityComponent($this->basePhysicalQuantity, 1);

        $components = $this->basePhysicalQuantity->getPhysicalQuantityComponents();
        $this->assertEquals(count($components), 1);
        $this->assertSame($components[0]->getBasePhysicalQuantity(), $this->basePhysicalQuantity);
        $this->assertEquals($components[0]->getExponent(), 1);

        $this->derivedPhysicalQuantity->addPhysicalQuantityComponent($this->basePhysicalQuantity, 10);

        $components = $this->derivedPhysicalQuantity->getPhysicalQuantityComponents();
        $this->assertEquals(count($components), 1);
        $this->assertSame($components[0]->getBasePhysicalQuantity(), $this->basePhysicalQuantity);
        $this->assertEquals($components[0]->getExponent(), 10);
    }


    /**
     * Méthode appelée à la fin de chaque test
     */
    protected function tearDown()
    {
        Unit_Test_PhysicalQuantityTest::deleteObject($this->derivedPhysicalQuantity);
        Unit_Test_PhysicalQuantityTest::deleteObject($this->basePhysicalQuantity);
        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->flush();
    }

    /**
     * On verifie que les tables soientt vides après les tests
     */
    public static function tearDownAfterClass()
    {
        // Vérification qu'il ne reste aucun Unit en base, sinon suppression !
        if (Unit::countTotal() > 0) {
            echo PHP_EOL . 'Des Unit_System restants ont été trouvé après les tests, suppression en cours !';
            foreach (Unit::loadList() as $unit) {
                $unit->delete();
            }
            $entityManagers = Zend_Registry::get('EntityManagers');
            $entityManagers['default']->flush();
        }
        // Vérification qu'il ne reste aucun PhysicalQuantity en base, sinon suppression !
        if (PhysicalQuantity::countTotal() > 0) {
            echo PHP_EOL . 'Des Unit_System restants ont été trouvé après les tests, suppression en cours !';
            foreach (PhysicalQuantity::loadList() as $physicalQuantity) {
                $physicalQuantity->delete();
            }
            $entityManagers = Zend_Registry::get('EntityManagers');
            $entityManagers['default']->flush();
        }
        // Vérification qu'il ne reste aucun UnitSystem en base, sinon suppression !
        if (UnitSystem::countTotal() > 0) {
            echo PHP_EOL . 'Des Unit_System restants ont été trouvé après les tests, suppression en cours !';
            foreach (UnitSystem::loadList() as $systemunit) {
                $systemunit->delete();
            }
            $entityManagers = Zend_Registry::get('EntityManagers');
            $entityManagers['default']->flush();
        }
    }
}