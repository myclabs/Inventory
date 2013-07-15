<?php
/**
 * Test de l'objet métier Unit_Extended.
 * @author valentin.claras
 * @author hugo.charboniere
 * @author yoann.croizer
 * @package Unit
 * @subpackage Test
 */
use Unit\Domain\Unit\ExtendedUnit;
use Unit\Domain\Unit\Unit;
use Unit\Domain\Unit\StandardUnit;
use Unit\Domain\PhysicalQuantity;
use Unit\Domain\UnitSystem;
use Unit\Domain\UnitExtension;

/**
 * ExtendedUnitTest
 * @package Unit
 * @subpackage Test
 */
class Unit_Test_ExtendedUnitTest
{

    /**
     * Lance les autres classes de tests
     */
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite();
        $suite->addTestSuite('Unit_Test_ExtendedUnitSetUp');
        $suite->addTestSuite('Unit_Test_ExtendedUnitOther');
        return $suite;
    }

    /**
     * Génère une unité étendue qui sera utilisée dans les tests
     * @return \Unit\Domain\Unit\ExtendedUnit $o
     */
    public static function generateUnitEtendue()
    {
        $standardUnit = Unit_Test_ExtendedUnitTest::generateUnitStandard();
        $extension    = Unit_Test_ExtendedUnitTest::genererExtension();
        $o = new ExtendedUnit();
        $o->setStandardUnit($standardUnit);
        $o->save();
        return $o;
    }

    /**
     * Génere un objet pret à l'emploi pour les tests
     * @param string $ref
     * @param int $multiplier
     * @param \Unit\Domain\UnitExtension $extension
     * @param \Unit\Domain\Unit\StandardUnit $standardUnit
     * @return \Unit\Domain\UnitExtension $o
     */
    public static function generateObject($ref='StandardUnitTest', $multiplier=1, $extension=null, $standardUnit=null)
    {
        $o = new ExtendedUnit();
        $o->setRef('Ref'.$ref);
        $o->setName('Name'.$ref);
        $o->setSymbol('Symbol'.$ref);
        $o->setMultiplier($multiplier);
        if ($extension == null) {
            $extension = Unit_Test_UnitExtensionTest::generateObject($ref);
        }
        $o->setExtension($extension);
        if ($standardUnit == null) {
            $standardUnit = Unit_Test_StandardUnitTest::generateObject($ref);
        }
        $o->setStandardUnit($standardUnit);
        $o->save();
        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->flush();
        return $o;
    }

    /**
     * Supprime un objet utilisé dans les tests.
     * @param \Unit\Domain\Unit\ExtendedUnit $o
     * @param bool $deleteExtension
     * @param bool $deleteStandardUnit
     */
    public static function deleteObject(ExtendedUnit $o, $deleteExtension=true, $deleteStandardUnit=true)
    {
        $o->delete();
        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->flush();
        if ($deleteExtension == true) {
            Unit_Test_UnitExtensionTest::deleteObject($o->getExtension());
        }
        if ($deleteStandardUnit == true) {
            Unit_Test_StandardUnitTest::deleteObject($o->getStandardUnit());
        }
    }
}


/**
 * ExtendedUnitSetUpTest
 * @package Unit
 */
class Unit_Test_ExtendedUnitSetUp extends PHPUnit_Framework_TestCase
{
    /**
     * Méthode appelé avant le lancement des tests de la classe
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
        // Vérification qu'il ne reste aucun UnitExtension en base, sinon suppression !
        if (UnitExtension::countTotal() > 0) {
            echo PHP_EOL . 'Des Unit_System restants ont été trouvé avant les tests, suppression en cours !';
            foreach (UnitExtension::loadList() as $extensionunit) {
                $extensionunit->delete();
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
     * @return \Unit\Domain\Unit\ExtendedUnit
     */
    function testConstruct()
    {
        $unitExtension = Unit_Test_UnitExtensionTest::generateObject();
        $standardUnit = Unit_Test_StandardUnitTest::generateObject();

        $o = new ExtendedUnit();
        $this->assertInstanceOf('Unit\Domain\Unit\ExtendedUnit', $o);
        $o->setRef('RefExtendedUnit');
        $o->setName('NameExtendedUnit');
        $o->setSymbol('ExtendedUnit');
        $o->setMultiplier(1);
        $o->setExtension($unitExtension);
        $o->setStandardUnit($standardUnit);

        $this->assertEquals(array(), $o->getKey());
        $o->save();
        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->flush();
        $this->assertNotEquals(array(), $o->getKey());

        return $o;
    }

    /**
     * @depends testConstruct
     * @param \Unit\Domain\Unit\ExtendedUnit $o
     */
    function testLoad($o)
    {
        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->clear($o);
        // On tente de charger l'unité enregistrée dans la base lors du test de la méthode save().
        $oLoaded = ExtendedUnit::load($o->getKey());
        $this->assertInstanceOf('Unit\Domain\Unit\ExtendedUnit', $oLoaded);
        $this->assertEquals($oLoaded->getKey(), $o->getKey());
        $this->assertEquals($oLoaded->getRef(), $o->getRef());
        $this->assertEquals($oLoaded->getName(), $o->getName());
        $this->assertEquals($oLoaded->getSymbol(), $o->getSymbol());
        $this->assertEquals($oLoaded->getMultiplier(), $o->getMultiplier());
        $this->assertEquals($oLoaded->getExtension()->getKey(), $o->getExtension()->getKey());
        $this->assertEquals($oLoaded->getStandardUnit()->getKey(), $o->getStandardUnit()->getKey());

        return $oLoaded;
    }

    /**
     * @depends testLoad
     * @param \Unit\Domain\Unit\ExtendedUnit $o
     */
    function testDelete(ExtendedUnit $o)
    {
        $o->delete();
        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->flush();
        $this->assertEquals(array(), $o->getKey());

        Unit_Test_UnitExtensionTest::deleteObject($o->getExtension());
        Unit_Test_StandardUnitTest::deleteObject($o->getStandardUnit());
    }

    /**
     * Méthode appelée à la fin de chaque test
     */
    protected function tearDown()
    {
    }


    /**
     * On verifie que les tables soient vides après les tests
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
        // Vérification qu'il ne reste aucun UnitExtension en base, sinon suppression !
        if (UnitExtension::countTotal() > 0) {
            echo PHP_EOL . 'Des Unit_System restants ont été trouvé après les tests, suppression en cours !';
            foreach (UnitExtension::loadList() as $extensionunit) {
                $extensionunit->delete();
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
 * ExtendedUnitOtherTest
 * @package Unit
 */
class Unit_Test_ExtendedUnitOther extends PHPUnit_Framework_TestCase
{
    protected $extension;
    protected $standardUnit;
    protected $_extendedUnit;
    protected $_lengthPhysicalQuantity;
    protected $_massPhysicalQuantity;
    protected $_timePhysicalQuantity;
    protected $_cashPhysicalQuantity;
    protected $physicalQuantity1;
    protected $unitSystem;

    /**
     * Méthode appelée avant le lancement de la classe de test
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
        // Vérification qu'il ne reste aucun UnitExtension en base, sinon suppression !
        if (UnitExtension::countTotal() > 0) {
            echo PHP_EOL . 'Des Unit_System restants ont été trouvé avant les tests, suppression en cours !';
            foreach (UnitExtension::loadList() as $extensionunit) {
                $extensionunit->delete();
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
     * Méthode appelée avant toutes les méthodes de test
     */
    protected function setUp()
    {
        $entityManagers = Zend_Registry::get('EntityManagers');

        //On créer un système d'unité (obligatoire pour une unité standard).
        $this->unitSystem = new UnitSystem();
        $this->unitSystem->setRef('international');
        $this->unitSystem->setName('International');
        $this->unitSystem->save();

        //On créer les grandeurs physiques de base.
        $this->_lengthPhysicalQuantity = new PhysicalQuantity();
        $this->_lengthPhysicalQuantity->setName('longueur');
        $this->_lengthPhysicalQuantity->setRef('l');
        $this->_lengthPhysicalQuantity->setSymbol('L');
        $this->_lengthPhysicalQuantity->setIsBase(true);
        $this->_lengthPhysicalQuantity->save();

        $this->_massPhysicalQuantity = new PhysicalQuantity();
        $this->_massPhysicalQuantity->setName('masse');
        $this->_massPhysicalQuantity->setRef('m');
        $this->_massPhysicalQuantity->setSymbol('M');
        $this->_massPhysicalQuantity->setIsBase(true);
        $this->_massPhysicalQuantity->save();

        $this->_timePhysicalQuantity = new PhysicalQuantity();
        $this->_timePhysicalQuantity->setName('temps');
        $this->_timePhysicalQuantity->setRef('t');
        $this->_timePhysicalQuantity->setSymbol('T');
        $this->_timePhysicalQuantity->setIsBase(true);
        $this->_timePhysicalQuantity->save();

        $this->_cashPhysicalQuantity = new PhysicalQuantity();
        $this->_cashPhysicalQuantity->setName('numéraire');
        $this->_cashPhysicalQuantity->setRef('numeraire');
        $this->_cashPhysicalQuantity->setSymbol('$');
        $this->_cashPhysicalQuantity->setIsBase(true);
        $this->_cashPhysicalQuantity->save();

        //On créer une grandeur physique composée de grandeur physique de base.
        $this->physicalQuantity1 = new PhysicalQuantity();
        $this->physicalQuantity1->setName('energie');
        $this->physicalQuantity1->setRef('ml2/t2');
        $this->physicalQuantity1->setSymbol('M.L2/T2');
        $this->physicalQuantity1->setIsBase(false);
        $this->physicalQuantity1->save();

        $entityManagers['default']->flush();

        $this->physicalQuantity1->addPhysicalQuantityComponent($this->_lengthPhysicalQuantity, 2);
        $this->physicalQuantity1->addPhysicalQuantityComponent($this->_massPhysicalQuantity, 1);
        $this->physicalQuantity1->addPhysicalQuantityComponent($this->_timePhysicalQuantity, -2);
        $this->physicalQuantity1->addPhysicalQuantityComponent($this->_cashPhysicalQuantity, 0);

        $this->extension = new UnitExtension();
        $this->extension->setName('ExtensionTest');
        $this->extension->setRef('ExtensionTest');
        $this->extension->setSymbol('Ext');
        $this->extension->setMultiplier(1);
        $this->extension->save();

        $this->standardUnit = new StandardUnit();
        $this->standardUnit->setRef('j');
        $this->standardUnit->setName('Joule');
        $this->standardUnit->setSymbol('J');
        $this->standardUnit->setMultiplier(1);
        $this->standardUnit->setPhysicalQuantity($this->physicalQuantity1);
        $this->standardUnit->setUnitSystem($this->unitSystem);
        $this->standardUnit->save();

        $entityManagers['default']->flush();

        $this->physicalQuantity1->setReferenceUnit($this->standardUnit);

        $this->_extendedUnit = new ExtendedUnit();
        $this->_extendedUnit->setRef('RefExtendedUnit');
        $this->_extendedUnit->setName('NameExtendedUnit');
        $this->_extendedUnit->setSymbol('SymbolExtendedUnit');
        $this->_extendedUnit->setMultiplier(1);
        $this->_extendedUnit->setExtension($this->extension);
        $this->_extendedUnit->setStandardUnit($this->standardUnit);
        $this->_extendedUnit->save();

        $entityManagers['default']->flush();
    }

    /**
     * Test de la méthode loadByRef
     */
    function testLoadByRef()
    {
        $o = ExtendedUnit::loadByRef('RefExtendedUnit');
        $this->assertInstanceOf('Unit\Domain\Unit\ExtendedUnit', $o);
        $this->assertSame($o, $this->_extendedUnit);
    }

    /**
     * Test les méthodes get et set Extension()
     */
     function testSetGetExtension()
     {
         $unitExtension = Unit_Test_UnitExtensionTest::generateObject('testSetGetExtention');

         $this->_extendedUnit->setExtension($unitExtension);
         $this->assertSame($unitExtension, $this->_extendedUnit->getExtension());
         $this->_extendedUnit->setExtension($this->extension);
         $this->assertSame($this->extension, $this->_extendedUnit->getExtension());

         Unit_Test_UnitExtensionTest::deleteObject($unitExtension);
     }

    /**
     * Test les méthodes set et get uniteStandard()
     */
     function testSetGetStandarUnit()
     {
        $standardUnit = Unit_Test_StandardUnitTest::generateObject('testSetGetStandarUnit');

        $this->_extendedUnit->setStandardUnit($standardUnit);
        $this->assertSame($standardUnit, $this->_extendedUnit->getStandardUnit());
        $this->_extendedUnit->setStandardUnit($this->standardUnit);
        $this->assertSame($this->standardUnit, $this->_extendedUnit->getStandardUnit());

        Unit_Test_StandardUnitTest::deleteObject($standardUnit);
     }

    /**
     * Test de la fonction getConversionFactor
     */
     function testGetFacteurConversion()
     {
        $this->assertEquals($this->_extendedUnit->getConversionFactor($this->_extendedUnit), 1);
     }

    /**
     * Test la fonction getUnitReference()
     */
     function testGetReferenceUnit()
     {
         // Résultat supposé !
        $extendedReferenceUnit = new ExtendedUnit();
        $extendedReferenceUnit->setRef($this->standardUnit->getReferenceUnit()->getRef().'_co2e');
        $extendedReferenceUnit->setName('('.$this->standardUnit->getReferenceUnit()->getName().' equivalent CO2)');
        $extendedReferenceUnit->setSymbol('('.$this->standardUnit->getReferenceUnit()->getSymbol().'.equCO2)');
        $extendedReferenceUnit->setStandardUnit($this->standardUnit->getReferenceUnit());
        $extendedReferenceUnit->setExtension($this->_extendedUnit->getExtension());

        // L'unité étendue servant uniquement de proxy, elle est supprimée de l'entité manager.
        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['unit']->detach($extendedReferenceUnit);

        // On vérifie que ces deux unités sont bien les mêmes
        $this->assertEquals($this->_extendedUnit->getReferenceUnit(), $extendedReferenceUnit);
     }


    /**
     * Méthode appelée à la fin de chaque test
     */
    protected function tearDown()
    {
        $entityManagers = Zend_Registry::get('EntityManagers');

        $this->physicalQuantity1->setReferenceUnit(null);

        $this->_extendedUnit->delete();
        $this->standardUnit->delete();

        $entityManagers['default']->flush();

        $this->physicalQuantity1->delete();

        $this->_lengthPhysicalQuantity->delete();
        $this->_massPhysicalQuantity->delete();
        $this->_timePhysicalQuantity->delete();
        $this->_cashPhysicalQuantity->delete();

        $this->unitSystem->delete();
        $this->extension->delete();

        $entityManagers['default']->flush();
    }


    /**
     * On verifie que les tables soient vides après les tests
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
        // Vérification qu'il ne reste aucun UnitExtension en base, sinon suppression !
        if (UnitExtension::countTotal() > 0) {
            echo PHP_EOL . 'Des Unit_System restants ont été trouvé après les tests, suppression en cours !';
            foreach (UnitExtension::loadList() as $extensionunit) {
                $extensionunit->delete();
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