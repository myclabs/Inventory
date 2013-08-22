<?php

use Unit\Domain\Unit\Unit;
use Unit\Domain\Unit\StandardUnit;
use Unit\Domain\PhysicalQuantity;
use Unit\Domain\UnitSystem;

/**
 * Test de l'objet métier StandardUnit
 * @author valentin.claras
 * @author hugo.charboniere
 * @author yoann.croizer
 */
class Unit_Test_StandardUnitTest
{
    /**
     * Lance les autres classes de test
     */
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite();
        $suite->addTestSuite('Unit_Test_StandardUnitSetUp');
        $suite->addTestSuite('Unit_Test_StandardUnitOthers');
        return $suite;
    }

    /**
     * Génere un objet pret à l'emploi pour les tests
     * @param string $ref
     * @param int $multiplier
     * @param PhysicalQuantity $physicalQuantity
     * @param UnitSystem $unitSystem
     * @return StandardUnit $o
     */
    public static function generateObject($ref='StandardUnitTest', $multiplier=1, $physicalQuantity=null, $unitSystem=null)
    {
        $o = new StandardUnit();
        $o->setRef('Ref'.$ref);
        $o->setName('Name'.$ref);
        $o->setSymbol('Symbol'.$ref);
        $o->setMultiplier($multiplier);
        if ($physicalQuantity == null) {
            $physicalQuantity = Unit_Test_PhysicalQuantityTest::generateObject($ref);
        }
        $o->setPhysicalQuantity($physicalQuantity);
        if ($unitSystem == null) {
            $unitSystem = Unit_Test_UnitSystemTest::generateObject($ref);
        }
        $o->setUnitSystem($unitSystem);
        $o->save();
        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->flush();
        return $o;
    }

    /**
     * Supprime un objet utilisé dans les tests
     * @param StandardUnit $o
     * @param bool $deletePhysicalQuantity
     * @param bool $deleteUnitSystem
     */
    public static function deleteObject(StandardUnit $o, $deletePhysicalQuantity=true, $deleteUnitSystem=true)
    {
        $o->delete();
        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->flush();
        if ($deletePhysicalQuantity == true) {
            Unit_Test_PhysicalQuantityTest::deleteObject($o->getPhysicalQuantity());
        }
        if ($deleteUnitSystem == true) {
            Unit_Test_UnitSystemTest::deleteObject($o->getUnitSystem());
        }
    }

}

/**
 * Test de la construction/sauvegarde de l'objet métier
 * @package Unit
 */
class Unit_Test_StandardUnitSetUp extends PHPUnit_Framework_TestCase
{
    /**
     * Méthode appelée avant l'appel à la classe de test
     */
    public static function setUpBeforeClass()
    {
        $entityManagers = Zend_Registry::get('EntityManagers');
        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $entityManagers['default'];
        if (Unit::countTotal() > 0) {
            foreach (Unit::loadList() as $o) {
                $o->delete();
            }
            $em->flush();
        }
        if (PhysicalQuantity::countTotal() > 0) {
            foreach (PhysicalQuantity::loadList() as $o) {
                $o->delete();
            }
            $em->flush();
        }
        if (UnitSystem::countTotal() > 0) {
            foreach (UnitSystem::loadList() as $o) {
                $o->delete();
            }
            $em->flush();
        }
    }

    /**
     * Test le constructeur
     * @return \Unit\Domain\Unit\StandardUnit
     */
    function testConstruct()
    {
        $unitSystem = Unit_Test_UnitSystemTest::generateObject();
        $physicalQuantity = Unit_Test_PhysicalQuantityTest::generateObject();

        $o = new StandardUnit();
        $o->setRef('RefStandardUnit');
        $o->setName('NameStandardUnit');
        $o->setSymbol('StandardUnit');
        $o->setMultiplier(1);
        $o->setUnitSystem($unitSystem);
        $o->setPhysicalQuantity($physicalQuantity);

        $this->assertEquals(array(), $o->getKey());
        $o->save();
        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->flush();
        $this->assertNotEquals(array(), $o->getKey());

        return $o;
    }

    /**
     * @depends testConstruct
     * @param StandardUnit $o
     */
    function testLoad(StandardUnit $o)
    {
        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->clear($o);
        // On tente de charger l'unité enregistrée dans la base lors du test de la méthode save().
        $oLoaded = StandardUnit::load($o->getKey());
        $this->assertInstanceOf('Unit\Domain\Unit\StandardUnit', $oLoaded);
        $this->assertEquals($oLoaded->getKey(), $o->getKey());
        $this->assertEquals($oLoaded->getRef(), $o->getRef());
        $this->assertEquals($oLoaded->getName(), $o->getName());
        $this->assertEquals($oLoaded->getSymbol(), $o->getSymbol());
        $this->assertEquals($oLoaded->getMultiplier(), $o->getMultiplier());
        $this->assertEquals($oLoaded->getUnitSystem()->getKey(), $o->getUnitSystem()->getKey());
        $this->assertEquals($oLoaded->getPhysicalQuantity()->getKey(), $o->getPhysicalQuantity()->getKey());

        return $oLoaded;
    }

    /**
     * @depends testLoad
     * @param StandardUnit $o
     */
    function testDelete(StandardUnit $o)
    {
        $o->delete();
        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->flush();
        $this->assertEquals(array(), $o->getKey());

        Unit_Test_UnitSystemTest::deleteObject($o->getUnitSystem());
        Unit_Test_PhysicalQuantityTest::deleteObject($o->getPhysicalQuantity());
    }

    /**
     * Function called once, after all the tests
     */
    public static function tearDownAfterClass()
    {
        $entityManagers = Zend_Registry::get('EntityManagers');
        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $entityManagers['default'];
        if (Unit::countTotal() > 0) {
            echo PHP_EOL . 'Des Unit restants ont été trouvé après les tests, suppression en cours !';
            foreach (Unit::loadList() as $unit) {
                $unit->delete();
            }
            $em->flush();
        }
        if (PhysicalQuantity::countTotal() > 0) {
            echo PHP_EOL . 'Des PhysicalQuantity restants ont été trouvé après les tests, suppression en cours !';
            foreach (PhysicalQuantity::loadList() as $physicalQuantity) {
                $physicalQuantity->delete();
            }
            $em->flush();
        }
        if (UnitSystem::countTotal() > 0) {
            echo PHP_EOL . 'Des UnitSystem restants ont été trouvé après les tests, suppression en cours !';
            foreach (UnitSystem::loadList() as $systemunit) {
                $systemunit->delete();
            }
            $em->flush();
        }
    }
}

/**
 * StandardUnitOthersTest
 * @package Unit
 */
class Unit_Test_StandardUnitOthers extends PHPUnit_Framework_TestCase
{
    /**
     * @var UnitSystem
     */
    protected $unitSystem1;
    /**
     * @var UnitSystem
     */
    protected $unitSystem2;

    /**
     * @var PhysicalQuantity
     */
    protected $_lengthPhysicalQuantity;
    /**
     * @var PhysicalQuantity
     */
    protected $_massPhysicalQuantity;
    /**
     * @var PhysicalQuantity
     */
    protected $_timePhysicalQuantity;
    /**
     * @var PhysicalQuantity
     */
    protected $_cashPhysicalQuantity;

    /**
     * @var StandardUnit
     */
    protected $_lengthStandardUnit;
    /**
     * @var StandardUnit
     */
    protected $_cashStandardUnit;
    /**
     * @var StandardUnit
     */
    protected $_timeStandardUnit;
    /**
     * @var StandardUnit
     */
    protected $_massStandardUnit;
    /**
     * @var StandardUnit
     */
    protected $_massStandardUnit2;

    /**
     * @var StandardUnit
     */
    protected $standardUnit1;
    /**
     * @var StandardUnit
     */
    protected $standardUnit2;
    /**
     * @var PhysicalQuantity
     */
    protected $physicalQuantity1;
    /**
     * @var PhysicalQuantity
     */
    protected $physicalQuantity2;

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
     * Méthode appelée avant l'exécution des tests
     */
    protected function setUp()
    {
        $entityManagers = Zend_Registry::get('EntityManagers');

        //On créer un système d'unité (obligatoire pour une unité standard).
        $this->unitSystem1 = new UnitSystem();
        $this->unitSystem1->setRef('international');
        $this->unitSystem1->setName('International');
        $this->unitSystem1->save();

        $this->unitSystem2 = new UnitSystem();
        $this->unitSystem2->setRef('francais');
        $this->unitSystem2->setName('Francais');
        $this->unitSystem2->save();

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

        $this->physicalQuantity2 = new PhysicalQuantity();
        $this->physicalQuantity2->setName('masse2');
        $this->physicalQuantity2->setRef('m2');
        $this->physicalQuantity2->setSymbol('M2');
        $this->physicalQuantity2->setIsBase(false);
        $this->physicalQuantity2->save();

        $entityManagers['default']->flush();

        $this->_lengthPhysicalQuantity->addPhysicalQuantityComponent($this->_lengthPhysicalQuantity, 1);
        $this->_massPhysicalQuantity->addPhysicalQuantityComponent($this->_massPhysicalQuantity, 1);
        $this->_timePhysicalQuantity->addPhysicalQuantityComponent($this->_timePhysicalQuantity, 1);
        $this->_cashPhysicalQuantity->addPhysicalQuantityComponent($this->_cashPhysicalQuantity, 1);

        $this->physicalQuantity1->addPhysicalQuantityComponent($this->_lengthPhysicalQuantity, 2);
        $this->physicalQuantity1->addPhysicalQuantityComponent($this->_massPhysicalQuantity, 1);
        $this->physicalQuantity1->addPhysicalQuantityComponent($this->_timePhysicalQuantity, -2);
        $this->physicalQuantity1->addPhysicalQuantityComponent($this->_cashPhysicalQuantity, 0);

        $this->physicalQuantity2->addPhysicalQuantityComponent($this->_massPhysicalQuantity, 1);
        $this->physicalQuantity2->addPhysicalQuantityComponent($this->_lengthPhysicalQuantity, 0);
        $this->physicalQuantity2->addPhysicalQuantityComponent($this->_timePhysicalQuantity, 0);
        $this->physicalQuantity2->addPhysicalQuantityComponent($this->_cashPhysicalQuantity, 0);

        // On crée les unités standards.
        $this->_lengthStandardUnit = new StandardUnit();
        $this->_lengthStandardUnit->setMultiplier(1);
        $this->_lengthStandardUnit->setName('Metre');
        $this->_lengthStandardUnit->setSymbol('m');
        $this->_lengthStandardUnit->setRef('m');
        $this->_lengthStandardUnit->setPhysicalQuantity($this->_lengthPhysicalQuantity);
        $this->_lengthStandardUnit->setUnitSystem($this->unitSystem1);
        $this->_lengthStandardUnit->save();
        $entityManagers['default']->flush();
        $this->_lengthPhysicalQuantity->setReferenceUnit($this->_lengthStandardUnit);

        $this->_massStandardUnit = new StandardUnit();
        $this->_massStandardUnit->setMultiplier(1);
        $this->_massStandardUnit->setName('Kilogramme');
        $this->_massStandardUnit->setSymbol('kg');
        $this->_massStandardUnit->setRef('kg');
        $this->_massStandardUnit->setPhysicalQuantity($this->_massPhysicalQuantity);
        $this->_massStandardUnit->setUnitSystem($this->unitSystem1);
        $this->_massStandardUnit->save();
        $entityManagers['default']->flush();
        $this->_massPhysicalQuantity->setReferenceUnit($this->_massStandardUnit);

        $this->_massStandardUnit2 = new StandardUnit();
        $this->_massStandardUnit2->setMultiplier(1000);
        $this->_massStandardUnit2->setName('Ton');
        $this->_massStandardUnit2->setSymbol('t');
        $this->_massStandardUnit2->setRef('t');
        $this->_massStandardUnit2->setPhysicalQuantity($this->_massPhysicalQuantity);
        $this->_massStandardUnit2->setUnitSystem($this->unitSystem1);
        $this->_massStandardUnit2->save();
        $entityManagers['default']->flush();
        $this->_massPhysicalQuantity->setReferenceUnit($this->_massStandardUnit);

        $this->_timeStandardUnit = new StandardUnit();
        $this->_timeStandardUnit->setMultiplier(1);
        $this->_timeStandardUnit->setName('Seconde');
        $this->_timeStandardUnit->setSymbol('s');
        $this->_timeStandardUnit->setRef('s');
        $this->_timeStandardUnit->setPhysicalQuantity($this->_timePhysicalQuantity);
        $this->_timeStandardUnit->setUnitSystem($this->unitSystem1);
        $this->_timeStandardUnit->save();
        $entityManagers['default']->flush();
        $this->_timePhysicalQuantity->setReferenceUnit($this->_timeStandardUnit);

        $this->_cashStandardUnit = new StandardUnit();
        $this->_cashStandardUnit->setMultiplier(1);
        $this->_cashStandardUnit->setName('Euro');
        $this->_cashStandardUnit->setSymbol('€');
        $this->_cashStandardUnit->setRef('e');
        $this->_cashStandardUnit->setPhysicalQuantity($this->_cashPhysicalQuantity);
        $this->_cashStandardUnit->setUnitSystem($this->unitSystem1);
        $this->_cashStandardUnit->save();
        $entityManagers['default']->flush();
        $this->_cashPhysicalQuantity->setReferenceUnit($this->_cashStandardUnit);

        $this->standardUnit1 = new StandardUnit();
        $this->standardUnit1->setMultiplier(1);
        $this->standardUnit1->setName('Joule');
        $this->standardUnit1->setSymbol('J');
        $this->standardUnit1->setRef('j');
        $this->standardUnit1->setPhysicalQuantity($this->physicalQuantity1);
        $this->standardUnit1->setUnitSystem($this->unitSystem1);
        $this->standardUnit1->save();

        $this->standardUnit2 = new StandardUnit();
        $this->standardUnit2->setMultiplier(0.001);
        $this->standardUnit2->setName('Gramme');
        $this->standardUnit2->setSymbol('g');
        $this->standardUnit2->setRef('g');
        $this->standardUnit2->setPhysicalQuantity($this->physicalQuantity2);
        $this->standardUnit2->setUnitSystem($this->unitSystem1);
        $this->standardUnit2->save();

        $entityManagers['default']->flush();

    }

    /**
     * test de la méthode loadByRef()
     */
    public function testLoadByRef()
    {
        $o = StandardUnit::loadByRef('j');
        $this->assertInstanceOf('Unit\Domain\Unit\StandardUnit', $o);
        $this->assertSame($o, $this->standardUnit1);
    }

    /**
     * test des méthodes get et set grandeur physique
     */
    public function testSetGetGrandeurPhysique()
    {
        $unit = $this->standardUnit1;
        $this->assertNotEquals(array(), $unit->getPhysicalQuantity()->getKey());

        $grandeurPhysique1 = $this->physicalQuantity1;
        $this->assertEquals($grandeurPhysique1->getKey(), $unit->getPhysicalQuantity()->getKey());

        $grandeurPhysique2 = $this->physicalQuantity2;
        $unit->setPhysicalQuantity($grandeurPhysique2);
        $this->assertEquals($grandeurPhysique2->getKey(), $unit->getPhysicalQuantity()->getKey());

        // Test de l'exception levée lorsque qu'il n'y a pas de grandeur physique
        // associée à une unité standard.
        $unitTest = new StandardUnit();
        try {
            $unitTest->getPhysicalQuantity();
        } catch (Core_Exception_UndefinedAttribute $e) {
            $this->assertEquals('Physical Quantity has not be defined', $e->getMessage());
        }
        $unitTest->delete();
    }


    /**
     * test des méthodes get et set system Unit
     */
    public function testSetGetUnitSystem()
    {
        $unit = $this->standardUnit1;
        $this->assertNotEquals(array(), $unit->getUnitSystem()->getKey());

        $systemeUnite1 = $this->unitSystem1;
        $this->assertEquals($systemeUnite1->getKey(), $unit->getUnitSystem()->getKey());

        $systemeUnite2 = $this->unitSystem2;
        $unit->setUnitSystem($this->unitSystem2);
        $this->assertEquals($systemeUnite2->getKey(), $unit->getUnitSystem()->getKey());

        // Test des cas particuliers :
        $unitTest = new StandardUnit();
        try {
            $unitTest->getUnitSystem();
        } catch (Core_Exception_UndefinedAttribute $e) {
            $this->assertEquals('System Unit has not be defined', $e->getMessage());
        }
        $unitTest->delete();
    }


    /**
     * test des  méthodes set et get ReferenceUnit
     */
    public function testGetReferenceUnit()
    {
        $unit = $this->standardUnit1;
        $referenceUnit = $this->standardUnit2;
        $this->physicalQuantity1->setReferenceUnit($this->standardUnit2);

        $this->assertEquals($unit->getReferenceUnit()->getKey(), $this->standardUnit2->getKey());

        $this->physicalQuantity1->setReferenceUnit(null);
    }

    /**
     * Test de la fonction getConversionFactor()
     */
    public function testGetConversionFactor()
    {
        //Test bon fonctionnement.
        //Le facteur de conversion d'une unité avec elle même et forcément 1.
        $unit = $this->standardUnit1;
        $result = $unit->getConversionFactor($unit);
        $this->assertEquals(1, $result);

        //Test erreur.
        //Deux unités qui ne sont pas associées à la même grandeur physique n'ont pas de facteur de conversion.
        $unit2 = $this->standardUnit2;

        // $unit et $unit2 ne sont pas associé à la même grandeur physique
        try {
            $result = $unit->getConversionFactor($unit2);
        } catch (Core_Exception_InvalidArgument $e) {
            $this->assertEquals('Units need to have same PhysicalQuantity.', $e->getMessage());
        }
    }


    /**
     * Test de la méthode loadList() et de la méthode countTotal()
     */
    public function testLoadListAndCountTotal()
    {
        $listStandardUnit = StandardUnit::loadList();
        $totalCount = StandardUnit::countTotal();
        $this->assertEquals(7, $totalCount);
        $this->assertEquals($totalCount, count($listStandardUnit));
        $this->assertContains($this->_lengthStandardUnit, $listStandardUnit);
        $this->assertContains($this->_massStandardUnit, $listStandardUnit);
        $this->assertContains($this->_massStandardUnit2, $listStandardUnit);
        $this->assertContains($this->_timeStandardUnit, $listStandardUnit);
        $this->assertContains($this->_cashStandardUnit, $listStandardUnit);
        $this->assertContains($this->standardUnit1, $listStandardUnit);
        $this->assertContains($this->standardUnit2, $listStandardUnit);
    }

    /**
     * Test la méthode getNormalizedUnit().
     */
    public function testGetNormalizedUnit()
    {
        $arrayNormalizedStandardUnit1 = array(
            array('unit' => $this->_lengthStandardUnit, 'exponent' => 2),
            array('unit' => $this->_massStandardUnit, 'exponent' => 1),
            array('unit' => $this->_timeStandardUnit, 'exponent' => -2),
            array('unit' => $this->_cashStandardUnit, 'exponent' => 0),
        );
        $this->assertEquals($this->standardUnit1->getNormalizedUnit(), $arrayNormalizedStandardUnit1);

        $arrayNormalizedStandardUnit2 = array(
            array('unit' => $this->_massStandardUnit, 'exponent' => 1),
            array('unit' => $this->_lengthStandardUnit, 'exponent' => 0),
            array('unit' => $this->_timeStandardUnit, 'exponent' => 0),
            array('unit' => $this->_cashStandardUnit, 'exponent' => 0),
        );
        $this->assertEquals($this->standardUnit2->getNormalizedUnit(), $arrayNormalizedStandardUnit2);
    }

    /**
     * Test la méthode getEquivalentUnits().
     */
    public function testGetEquivalentUnits()
    {
        $results = $this->_massStandardUnit2->getEquivalentUnits();

        $this->assertCount(1, $results);
        $this->assertContains($this->_massStandardUnit, $results);
        $this->assertNotContains($this->_massStandardUnit2, $results);
    }


    /**
     * Méthode appelée à la fin des test
     */
    protected function tearDown()
    {
        $entityManagers = Zend_Registry::get('EntityManagers');

        $this->_lengthPhysicalQuantity->setReferenceUnit(null);
        $this->_massPhysicalQuantity->setReferenceUnit(null);
        $this->_timePhysicalQuantity->setReferenceUnit(null);
        $this->_cashPhysicalQuantity->setReferenceUnit(null);

        $this->standardUnit1->delete();
        $this->standardUnit2->delete();

        $this->_lengthStandardUnit->delete();
        $this->_massStandardUnit->delete();
        $this->_massStandardUnit2->delete();
        $this->_timeStandardUnit->delete();
        $this->_cashStandardUnit->delete();

        $entityManagers['default']->flush();

        $this->physicalQuantity1->delete();
        $this->physicalQuantity2->delete();

        $this->_lengthPhysicalQuantity->delete();
        $this->_massPhysicalQuantity->delete();
        $this->_timePhysicalQuantity->delete();
        $this->_cashPhysicalQuantity->delete();

        $this->unitSystem1->delete();
        $this->unitSystem2->delete();

        $entityManagers['default']->flush();
    }


    /**
     * Function called once, after all the tests
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