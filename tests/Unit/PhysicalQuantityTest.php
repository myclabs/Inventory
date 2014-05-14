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
        $suite->addTestSuite('Unit_Test_PhysicalQuantityOthers');
        return $suite;
    }

    /**
     * Permet de générer un objet de base sur lequel on pourra travailler
     * @param string $ref
     * @return PhysicalQuantity $o
     */
    public static function generateObject($ref = 'UnitPhysicalQuantityTest')
    {
        $o = new PhysicalQuantity();
        $o->setRef('Ref'.$ref);
        $o->getName()->set('Name' . $ref, 'fr');
        $o->setSymbol('Symbol'.$ref);
        $o->setIsBase(true);
        $o->save();
        \Core\ContainerSingleton::getEntityManager()->flush();

        return $o;
    }

    /**
     * Permet de supprimer un objet de base sur lequel on a travaillé
     * @param PhysicalQuantity $o
     */
    public static function deleteObject(PhysicalQuantity $o)
    {
        $o->delete();
        \Core\ContainerSingleton::getEntityManager()->flush();
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
            \Core\ContainerSingleton::getEntityManager()->flush();
        }
        // Vérification qu'il ne reste aucun PhysicalQuantity en base, sinon suppression !
        if (PhysicalQuantity::countTotal() > 0) {
            echo PHP_EOL . 'Des Unit_System restants ont été trouvé avant les tests, suppression en cours !';
            foreach (PhysicalQuantity::loadList() as $physicalQuantity) {
                $physicalQuantity->delete();
            }
            \Core\ContainerSingleton::getEntityManager()->flush();
        }
        // Vérification qu'il ne reste aucun UnitSystem en base, sinon suppression !
        if (UnitSystem::countTotal() > 0) {
            echo PHP_EOL . 'Des Unit_System restants ont été trouvé avant les tests, suppression en cours !';
            foreach (UnitSystem::loadList() as $systemunit) {
                $systemunit->delete();
            }
            \Core\ContainerSingleton::getEntityManager()->flush();
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
        \Core\ContainerSingleton::getEntityManager()->flush();
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
            \Core\ContainerSingleton::getEntityManager()->flush();
        }
        // Vérification qu'il ne reste aucun PhysicalQuantity en base, sinon suppression !
        if (PhysicalQuantity::countTotal() > 0) {
            echo PHP_EOL . 'Des Unit_System restants ont été trouvé après les tests, suppression en cours !';
            foreach (PhysicalQuantity::loadList() as $physicalQuantity) {
                $physicalQuantity->delete();
            }
            \Core\ContainerSingleton::getEntityManager()->flush();
        }
        // Vérification qu'il ne reste aucun UnitSystem en base, sinon suppression !
        if (UnitSystem::countTotal() > 0) {
            echo PHP_EOL . 'Des Unit_System restants ont été trouvé après les tests, suppression en cours !';
            foreach (UnitSystem::loadList() as $systemunit) {
                $systemunit->delete();
            }
            \Core\ContainerSingleton::getEntityManager()->flush();
        }
    }
}
