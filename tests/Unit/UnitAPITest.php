<?php

use Core\Test\TestCase;
use Unit\Domain\Unit\DiscreteUnit;
use Unit\Domain\Unit\ExtendedUnit;
use Unit\Domain\Unit\StandardUnit;
use Unit\Domain\PhysicalQuantity;
use Unit\Domain\UnitSystem;
use Unit\Domain\UnitExtension;
use Unit\IncompatibleUnitsException;
use Unit\Domain\Unit\Unit;
use Unit\UnitAPI;

/**
 * @author valentin.claras
 * @author hugo.charboniere
 * @author yoann.croizer
 */
class Unit_Test_UnitAPITest extends TestCase
{
    protected $massStandardUnit;
    protected $timeStandardUnit;
    protected $lengthStandardUnit;
    protected $cashStandardUnit;

    protected $lengthPhysicalQuantity;
    protected $massPhysicalQuantity;
    protected $timePhysicalQuantity;
    protected $cashPhysicalQuantity;

    /**
     * @var UnitExtension
     */
    protected $extension;
    /**
     * @var UnitExtension
     */
    protected $extension2;

    /**
     * @var UnitSystem
     */
    protected $unitSystem;
    /**
     * @var DiscreteUnit
     */
    protected $unit1;
    /**
     * @var StandardUnit
     */
    protected $unit2;
    /**
     * @var StandardUnit
     */
    protected $unit3;
    /**
     * @var ExtendedUnit
     */
    protected $unit4;
    /**
     * @var ExtendedUnit
     */
    protected $unit5;
    /**
     * @var StandardUnit
     */
    protected $unit6;
    /**
     * @var ExtendedUnit
     */
    protected $unit7;

    protected $physicalQuantity1;

    public static function setUpBeforeClass()
    {
        $em = \Core\ContainerSingleton::getEntityManager();

        if (Unit::countTotal() > 0) {
            foreach (Unit::loadList() as $unit) {
                $unit->delete();
            }
            $em->flush();
        }
        if (UnitExtension::countTotal() > 0) {
            foreach (UnitExtension::loadList() as $extensionunit) {
                $extensionunit->delete();
            }
            $em->flush();
        }
        if (PhysicalQuantity::countTotal() > 0) {
            foreach (PhysicalQuantity::loadList() as $physicalQuantity) {
                $physicalQuantity->delete();
            }
            $em->flush();
        }
        if (UnitSystem::countTotal() > 0) {
            foreach (UnitSystem::loadList() as $systemunit) {
                $systemunit->delete();
            }
            $em->flush();
        }
    }

    public function setUp()
    {
        parent::setUp();

        // On créer un système d'unité (obligatoire pour une unité standard).
        $this->unitSystem = new UnitSystem();
        $this->unitSystem->setRef('international');
        $this->unitSystem->save();

        // On créer les grandeurs physiques de base.
        $this->lengthPhysicalQuantity = new PhysicalQuantity();
        $this->lengthPhysicalQuantity->setRef('l');
        $this->lengthPhysicalQuantity->setSymbol('L');
        $this->lengthPhysicalQuantity->setIsBase(true);
        $this->lengthPhysicalQuantity->save();

        $this->massPhysicalQuantity = new PhysicalQuantity();
        $this->massPhysicalQuantity->setRef('m');
        $this->massPhysicalQuantity->setSymbol('M');
        $this->massPhysicalQuantity->setIsBase(true);
        $this->massPhysicalQuantity->save();

        $this->timePhysicalQuantity = new PhysicalQuantity();
        $this->timePhysicalQuantity->setRef('t');
        $this->timePhysicalQuantity->setSymbol('T');
        $this->timePhysicalQuantity->setIsBase(true);
        $this->timePhysicalQuantity->save();

        $this->cashPhysicalQuantity = new PhysicalQuantity();
        $this->cashPhysicalQuantity->setRef('numeraire');
        $this->cashPhysicalQuantity->setSymbol('$');
        $this->cashPhysicalQuantity->setIsBase(true);
        $this->cashPhysicalQuantity->save();

        // On créer une grandeur physique composée de grandeur physique de base.
        $this->physicalQuantity1 = new PhysicalQuantity();
        $this->physicalQuantity1->setRef('ml2/t2');
        $this->physicalQuantity1->setSymbol('M.L2/T2');
        $this->physicalQuantity1->setIsBase(false);
        $this->physicalQuantity1->save();

        $this->entityManager->flush();

        $this->lengthPhysicalQuantity->addPhysicalQuantityComponent($this->lengthPhysicalQuantity, 1);
        $this->massPhysicalQuantity->addPhysicalQuantityComponent($this->massPhysicalQuantity, 1);
        $this->timePhysicalQuantity->addPhysicalQuantityComponent($this->timePhysicalQuantity, 1);
        $this->cashPhysicalQuantity->addPhysicalQuantityComponent($this->cashPhysicalQuantity, 1);

        $this->physicalQuantity1->addPhysicalQuantityComponent($this->lengthPhysicalQuantity, 2);
        $this->physicalQuantity1->addPhysicalQuantityComponent($this->massPhysicalQuantity, 1);
        $this->physicalQuantity1->addPhysicalQuantityComponent($this->timePhysicalQuantity, -2);
        $this->physicalQuantity1->addPhysicalQuantityComponent($this->cashPhysicalQuantity, 0);

        // On crée les unités standards.
        $this->lengthStandardUnit = new StandardUnit();
        $this->lengthStandardUnit->setMultiplier(1);
        $this->lengthStandardUnit->getName()->set('Metre', 'fr');
        $this->lengthStandardUnit->getSymbol()->set('m', 'fr');
        $this->lengthStandardUnit->setRef('m');
        $this->lengthStandardUnit->setPhysicalQuantity($this->lengthPhysicalQuantity);
        $this->lengthStandardUnit->setUnitSystem($this->unitSystem);
        $this->lengthStandardUnit->save();
        $this->entityManager->flush();
        $this->lengthPhysicalQuantity->setReferenceUnit($this->lengthStandardUnit);

        $this->massStandardUnit = new StandardUnit();
        $this->massStandardUnit->setMultiplier(1);
        $this->massStandardUnit->getName()->set('Kilogramme', 'fr');
        $this->massStandardUnit->getSymbol()->set('kg', 'fr');
        $this->massStandardUnit->setRef('kg');
        $this->massStandardUnit->setPhysicalQuantity($this->massPhysicalQuantity);
        $this->massStandardUnit->setUnitSystem($this->unitSystem);
        $this->massStandardUnit->save();
        $this->entityManager->flush();
        $this->massPhysicalQuantity->setReferenceUnit($this->massStandardUnit);

        $this->timeStandardUnit = new StandardUnit();
        $this->timeStandardUnit->setMultiplier(1);
        $this->timeStandardUnit->getName()->set('Seconde', 'fr');
        $this->timeStandardUnit->getSymbol()->set('s', 'fr');
        $this->timeStandardUnit->setRef('s');
        $this->timeStandardUnit->setPhysicalQuantity($this->timePhysicalQuantity);
        $this->timeStandardUnit->setUnitSystem($this->unitSystem);
        $this->timeStandardUnit->save();
        $this->entityManager->flush();
        $this->timePhysicalQuantity->setReferenceUnit($this->timeStandardUnit);

        $this->cashStandardUnit = new StandardUnit();
        $this->cashStandardUnit->setMultiplier(1);
        $this->cashStandardUnit->getName()->set('Euro', 'fr');
        $this->cashStandardUnit->getSymbol()->set('€', 'fr');
        $this->cashStandardUnit->setRef('e');
        $this->cashStandardUnit->setPhysicalQuantity($this->cashPhysicalQuantity);
        $this->cashStandardUnit->setUnitSystem($this->unitSystem);
        $this->cashStandardUnit->save();
        $this->entityManager->flush();
        $this->cashPhysicalQuantity->setReferenceUnit($this->cashStandardUnit);

        $this->entityManager->flush();

        // On créer deux extensions.
        $this->extension = new UnitExtension();
        $this->extension->setRef('co2e');
        $this->extension->getSymbol()->set('equ. CO2', 'fr');
        $this->extension->setMultiplier(1);
        $this->extension->save();

        $this->extension2 = new UnitExtension();
        $this->extension2->setRef('ce');
        $this->extension2->getSymbol()->set('equ. C', 'fr');
        $this->extension2->setMultiplier(3.7);
        $this->extension2->save();

        //on créer plusieurs unités :
        $this->unit1 = new DiscreteUnit();
        $this->unit1->getName()->set('Animal', 'fr');
        $this->unit1->getSymbol()->set('animal', 'fr');
        $this->unit1->setRef('animal');
        $this->unit1->save();

        $this->unit2 = new StandardUnit();
        $this->unit2->setMultiplier(0.001);
        $this->unit2->getName()->set('gramme', 'fr');
        $this->unit2->getSymbol()->set('g', 'fr');
        $this->unit2->setRef('g');
        $this->unit2->setPhysicalQuantity($this->massPhysicalQuantity);
        $this->unit2->setUnitSystem($this->unitSystem);
        $this->unit2->save();

        $this->unit3 = new StandardUnit();
        $this->unit3->setMultiplier(1);
        $this->unit3->getName()->set('Joule', 'fr');
        $this->unit3->getSymbol()->set('J', 'fr');
        $this->unit3->setRef('j');
        $this->unit3->setPhysicalQuantity($this->physicalQuantity1);
        $this->unit3->setUnitSystem($this->unitSystem);
        $this->unit3->save();

        $this->unit4 = new ExtendedUnit();
        $this->unit4->setRef('g_co2e');
        $this->unit4->getName()->set('gramme équivalent CO2', 'fr');
        $this->unit4->getSymbol()->set('g equ. CO2', 'fr');
        $this->unit4->setMultiplier(0.001);
        $this->unit4->setExtension($this->extension);
        $this->unit4->setStandardUnit($this->massStandardUnit);
        $this->unit4->save();

        $this->unit5 = new ExtendedUnit();
        $this->unit5->setRef('kg_ce');
        $this->unit5->getName()->set('kilogramme équivalent carbone', 'fr');
        $this->unit5->getSymbol()->set('kg.equ. C', 'fr');
        $this->unit5->setMultiplier(3.7);
        $this->unit5->setExtension($this->extension2);
        $this->unit5->setStandardUnit($this->massStandardUnit);
        $this->unit5->save();

        $this->unit6 = new StandardUnit();
        $this->unit6->setMultiplier(3.15576e+007);
        $this->unit6->getName()->set('an', 'fr');
        $this->unit6->getSymbol()->set('an', 'fr');
        $this->unit6->setRef('an');
        $this->unit6->setPhysicalQuantity($this->timePhysicalQuantity);
        $this->unit6->setUnitSystem($this->unitSystem);
        $this->unit6->save();

        $this->unit7 = new ExtendedUnit();
        $this->unit7->setRef('kg_co2e');
        $this->unit7->getName()->set('kilogramme équivalent CO2', 'fr');
        $this->unit7->getSymbol()->set('kg.equ. CO2', 'fr');
        $this->unit7->setMultiplier(0.001);
        $this->unit7->setExtension($this->extension);
        $this->unit7->setStandardUnit($this->massStandardUnit);
        $this->unit7->save();

        $this->entityManager->flush();
    }

    /**
     * Test de la fonction getSymbol()
     * On vérigfie que le symbol est bien le bon
     */
    function testGetSymbol()
    {
        //Traitement d'un cas assez complexe utilisant tout les types d'unité (discrète, étendue et standard)
        $o = new UnitAPI('m^2.animal^-1.m^-2.g.g_co2e^2');
        $this->assertSame('m2.g.g equ. CO22/animal.m2', $o->getSymbol()->get('fr'));
    }


    /**
     * Test de la fonction getNormalizedUnit()
     * On vérigfie que le symbol est bien le bon
     */
    function testGetNormalizedUnit()
    {
        //Traitement d'un cas assez complexe utilisant tout les types d'unité (discrète, étendue et standard)
        $o = new UnitAPI('g.An');
        $result = $o->getNormalizedUnit();
        $this->assertTrue($result instanceof UnitAPI);
        $this->assertSame('kg.s', $result->getRef());
    }

    /**
     * Test de la fonction isEquivalent()
     * On vérifie que deux unités equivalentes le son bien et inversement
     */
    function testIsEquivalent()
    {
        //Cas ou l'on mélange plusieurs type d'unité.
        $unit1 = new UnitAPI('m^2.animal^-1.m^-2.kg.m^2.J^-5.kg_co2e^2');
        $unit2 = new UnitAPI('animal^-1.g.m^2.J^-5.g_co2e^2');
        $this->assertTrue($unit1->isEquivalent($unit2->getRef()));

        $unit3 = new UnitAPI('animal^-1.g.m');
        $this->assertFalse($unit1->isEquivalent($unit3->getRef()));

        // Cas ou l'on compare seulement des unités standard
        $unit4 = new UnitAPI('g');
        $this->assertTrue($unit4->isEquivalent($unit4->getRef()));

        // Cas ou l'on compare seulement des unités pas standard.
        $unit5 = new UnitAPI('animal');
        $this->assertTrue($unit5->isEquivalent($unit5->getRef()));

        $this->assertFalse($unit1->isEquivalent(''));
    }

    /**
     * Test de la fonction getConversionFactor()
     * On test si les facteurs de conversion retournés sont justes
     */
    function testGetConversionFactor()
    {
        $unit1 = new UnitAPI('m^2.animal^-1.m^-2.kg.kg_ce');
        $result = $unit1->getConversionFactor();
        $this->assertEquals(true, $result == 3.7);

        $unit1 = new UnitAPI('kg^2.g');
        $result = $unit1->getConversionFactor();
        $this->assertEquals(true, $result == 0.001);

        //Test de l'exception levée lorsque le coefficient multiplicateur d'une extension est null.
        $this->extension->setMultiplier(null);

        $this->unit7->setExtension($this->extension);

        $unit1 = new UnitAPI('kg_co2e');
        try {
            $result = $unit1->getConversionFactor();
        } catch (Core_Exception_UndefinedAttribute $e) {
            $this->assertEquals('Multiplier has not be defined', $e->getMessage());
        }

        //Test de l'exception levée lorsque le coefficient multiplicateur d'une extension est null.
        $this->unit2->setMultiplier(null);

        $unit1 = new UnitAPI('g');
        try {
            $result = $unit1->getConversionFactor();
        } catch (Core_Exception_UndefinedAttribute $e) {
            $this->assertEquals('Multiplier has not be defined', $e->getMessage());
        }

    }

    /**
     * Test de la fonction testMultiply()
     * On vérifie que le résultat d'une multiplication est correcte
     * pour une multiplication
     * pour une division
     */
    function testMultiply()
    {
        $operande[0]['unit'] = new UnitAPI('g.animal^-1.kg.kg_ce');
        $operande[0]['signExponent'] = 1;
        $operande[1]['unit'] = new UnitAPI('animal.s.an.kg^-1');
        $operande[1]['signExponent'] = -1;

        $result = UnitAPI::multiply($operande);
        $this->assertTrue($result instanceof UnitAPI);
        $this->assertEquals('kg^3.kg_co2e.s^-2.animal^-2', $result->getRef());
    }

    /**
     * Test de la fonction getCalculateSum()
     * On vérifie que le résultat d'une somme est correcte
     * pour une addition
     * pour une soustraction
     */
    function testCalculateSum()
    {
        $operande[] = 'g.animal^-1.kg.g_co2e';
        $operande[] = 'animal.s.an.kg^-1';
        $unit = new UnitAPI();

        try {
            $result = $unit->calculateSum($operande);
        } catch (IncompatibleUnitsException $e) {
            $this->assertEquals('Units for the sum are incompatible', $e->getMessage());
        }

        $operande = null;
        $operande[] = 'g.animal^-1.kg.an^2';
        $operande[] = 'animal^-1.s.an.kg^2';

        $result = $unit->calculateSum($operande);

        $this->assertTrue($result instanceof UnitAPI);
        $this->assertEquals('kg^2.s^2.animal^-1', $result->getRef());
    }

    /**
     * Test de la fonction getCompatibleUnits()
     */
    public function testGetCompatibleUnitsStandardUnit()
    {
        $unit1 = new UnitAPI('kg');
        $results = $unit1->getCompatibleUnits();
        $this->assertCount(1, $results);
        $this->assertContains(new UnitAPI('g'), $results, null, false, false);
    }

    /**
     * Test de la fonction getCompatibleUnits()
     */
    public function testGetCompatibleUnitsComposedUnit()
    {
        $unit2 = new UnitAPI('kg.s^2');
        $results = $unit2->getCompatibleUnits();
        $this->assertCount(3, $results);
        $this->assertContains(new UnitAPI('kg.an^2'), $results, null, false, false);
        $this->assertContains(new UnitAPI('g.s^2'), $results, null, false, false);
        $this->assertContains(new UnitAPI('g.an^2'), $results, null, false, false);
    }

    protected function tearDown()
    {
        parent::tearDown();

        $this->lengthPhysicalQuantity->setReferenceUnit(null);
        $this->massPhysicalQuantity->setReferenceUnit(null);
        $this->timePhysicalQuantity->setReferenceUnit(null);
        $this->cashPhysicalQuantity->setReferenceUnit(null);

        if (! $this->unit1) {
            return;
        }
        $this->unit1->delete();
        $this->unit2->delete();
        $this->unit3->delete();
        $this->unit4->delete();
        $this->unit5->delete();
        $this->unit6->delete();
        $this->unit7->delete();

        $this->lengthStandardUnit->delete();
        $this->massStandardUnit->delete();
        $this->timeStandardUnit->delete();
        $this->cashStandardUnit->delete();

        $this->entityManager->flush();

        $this->physicalQuantity1->delete();

        $this->lengthPhysicalQuantity->delete();
        $this->massPhysicalQuantity->delete();
        $this->timePhysicalQuantity->delete();
        $this->cashPhysicalQuantity->delete();

        $this->extension->delete();
        $this->extension2->delete();

        $this->unitSystem->delete();

        $this->entityManager->flush();
    }

    /**
     * On verifie que les tables soientt vides après les tests
     */
    public static function tearDownAfterClass()
    {
        $em = \Core\ContainerSingleton::getEntityManager();

        if (Unit::countTotal() > 0) {
            echo PHP_EOL . 'Des Unit_System restants ont été trouvé après les tests, suppression en cours !';
            foreach (Unit::loadList() as $unit) {
                $unit->delete();
            }
            $em->flush();
        }
        if (UnitExtension::countTotal() > 0) {
            echo PHP_EOL . 'Des Unit_System restants ont été trouvé après les tests, suppression en cours !';
            foreach (UnitExtension::loadList() as $extensionunit) {
                $extensionunit->delete();
            }
            $em->flush();
        }
        if (PhysicalQuantity::countTotal() > 0) {
            echo PHP_EOL . 'Des Unit_System restants ont été trouvé après les tests, suppression en cours !';
            foreach (PhysicalQuantity::loadList() as $physicalQuantity) {
                $physicalQuantity->delete();
            }
            $em->flush();
        }
        if (UnitSystem::countTotal() > 0) {
            echo PHP_EOL . 'Des Unit_System restants ont été trouvé après les tests, suppression en cours !';
            foreach (UnitSystem::loadList() as $systemunit) {
                $systemunit->delete();
            }
            $em->flush();
        }
    }

}
