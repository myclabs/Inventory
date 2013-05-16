<?php
/**
 * Test de l'API Unit.
 * @author valentin.claras
 * @author hugo.charboniere
 * @author yoann.croizer
 * @package Unit
 * @subpackage Test
 */

/**
 * UnitAPITest
 * @package Unit
 * @subpackage Test
 */
class Unit_Test_UnitAPITest
{
    /**
     * Enter description here ...
     */
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite();
        $suite->addTestSuite('Unit_Test_UnitAPISetUp');
        $suite->addTestSuite('Unit_Test_UnitAPILogiqueMetier');
        return $suite;
    }
}

/**
 * Enter description here ...
 * @package Unit
 * @subpackage Test
 */
class Unit_Test_UnitAPISetUp extends PHPUnit_Framework_TestCase
{
    /**
     * Test le constructeur
     */
    function testConstruct()
    {
        $o = new Unit_API('m^2.animal^-1.m^-2.g.g_co2e^2');
        $this->assertInstanceOf('Unit_API', $o);
    }
}

/**
 * Enter description here ...
 * @package Unit
 */
class Unit_Test_UnitAPILogiqueMetier extends PHPUnit_Framework_TestCase
{
    protected $_massStandardUnit;
    protected $_timeStandardUnit;
    protected $_lengthStandardUnit;
    protected $_cashStandardUnit;

    protected $_lengthPhysicalQuantity;
    protected $_massPhysicalQuantity;
    protected $_timePhysicalQuantity;
    protected $_cashPhysicalQuantity;

    protected $extension;
    protected $extension2;

    protected $unitSystem;
    protected $_unit1;
    protected $_unit2;
    protected $_unit3;
    protected $_unit4;
    protected $_unit5;
    protected $_unit6;
    protected $_unit7;

    protected $physicalQuantity1;

    /**
     * Méthode appelée avant l'exécution des tests
     */
    public static function setUpBeforeClass()
    {
        // Vérification qu'il ne reste aucun Unit_Model_Unit en base, sinon suppression !
        if (Unit_Model_Unit::countTotal() > 0) {
            echo PHP_EOL . 'Des Unit_System restants ont été trouvé avant les tests, suppression en cours !';
            foreach (Unit_Model_Unit::loadList() as $unit) {
                $unit->delete();
            }
            $entityManagers = Zend_Registry::get('EntityManagers');
            $entityManagers['default']->flush();
        }
        // Vérification qu'il ne reste aucun Unit_Model_Unit_Extension en base, sinon suppression !
        if (Unit_Model_Unit_Extension::countTotal() > 0) {
            echo PHP_EOL . 'Des Unit_System restants ont été trouvé avant les tests, suppression en cours !';
            foreach (Unit_Model_Unit_Extension::loadList() as $extensionunit) {
                $extensionunit->delete();
            }
            $entityManagers = Zend_Registry::get('EntityManagers');
            $entityManagers['default']->flush();
        }
        // Vérification qu'il ne reste aucun Unit_Model_PhysicalQuantity en base, sinon suppression !
        if (Unit_Model_PhysicalQuantity::countTotal() > 0) {
            echo PHP_EOL . 'Des Unit_System restants ont été trouvé avant les tests, suppression en cours !';
            foreach (Unit_Model_PhysicalQuantity::loadList() as $physicalQuantity) {
                $physicalQuantity->delete();
            }
            $entityManagers = Zend_Registry::get('EntityManagers');
            $entityManagers['default']->flush();
        }
        // Vérification qu'il ne reste aucun Unit_Model_Unit_System en base, sinon suppression !
        if (Unit_Model_Unit_System::countTotal() > 0) {
            echo PHP_EOL . 'Des Unit_System restants ont été trouvé avant les tests, suppression en cours !';
            foreach (Unit_Model_Unit_System::loadList() as $systemunit) {
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

        // On créer un système d'unité (obligatoire pour une unité standard).
        $this->unitSystem = new Unit_Model_Unit_System();
        $this->unitSystem->setRef('international');
        $this->unitSystem->setName('International');
        $this->unitSystem->save();

        // On créer les grandeurs physiques de base.
        $this->_lengthPhysicalQuantity = new Unit_Model_PhysicalQuantity();
        $this->_lengthPhysicalQuantity->setName('longueur');
        $this->_lengthPhysicalQuantity->setRef('l');
        $this->_lengthPhysicalQuantity->setSymbol('L');
        $this->_lengthPhysicalQuantity->setIsBase(true);
        $this->_lengthPhysicalQuantity->save();

        $this->_massPhysicalQuantity = new Unit_Model_PhysicalQuantity();
        $this->_massPhysicalQuantity->setName('masse');
        $this->_massPhysicalQuantity->setRef('m');
        $this->_massPhysicalQuantity->setSymbol('M');
        $this->_massPhysicalQuantity->setIsBase(true);
        $this->_massPhysicalQuantity->save();

        $this->_timePhysicalQuantity = new Unit_Model_PhysicalQuantity();
        $this->_timePhysicalQuantity->setName('temps');
        $this->_timePhysicalQuantity->setRef('t');
        $this->_timePhysicalQuantity->setSymbol('T');
        $this->_timePhysicalQuantity->setIsBase(true);
        $this->_timePhysicalQuantity->save();

        $this->_cashPhysicalQuantity = new Unit_Model_PhysicalQuantity();
        $this->_cashPhysicalQuantity->setName('numéraire');
        $this->_cashPhysicalQuantity->setRef('numeraire');
        $this->_cashPhysicalQuantity->setSymbol('$');
        $this->_cashPhysicalQuantity->setIsBase(true);
        $this->_cashPhysicalQuantity->save();

        // On créer une grandeur physique composée de grandeur physique de base.
        $this->physicalQuantity1 = new Unit_Model_PhysicalQuantity();
        $this->physicalQuantity1->setName('energie');
        $this->physicalQuantity1->setRef('ml2/t2');
        $this->physicalQuantity1->setSymbol('M.L2/T2');
        $this->physicalQuantity1->setIsBase(false);
        $this->physicalQuantity1->save();

        $entityManagers['default']->flush();

        $this->_lengthPhysicalQuantity->addPhysicalQuantityComponent($this->_lengthPhysicalQuantity, 1);
        $this->_massPhysicalQuantity->addPhysicalQuantityComponent($this->_massPhysicalQuantity, 1);
        $this->_timePhysicalQuantity->addPhysicalQuantityComponent($this->_timePhysicalQuantity, 1);
        $this->_cashPhysicalQuantity->addPhysicalQuantityComponent($this->_cashPhysicalQuantity, 1);

        $this->physicalQuantity1->addPhysicalQuantityComponent($this->_lengthPhysicalQuantity, 2);
        $this->physicalQuantity1->addPhysicalQuantityComponent($this->_massPhysicalQuantity, 1);
        $this->physicalQuantity1->addPhysicalQuantityComponent($this->_timePhysicalQuantity, -2);
        $this->physicalQuantity1->addPhysicalQuantityComponent($this->_cashPhysicalQuantity, 0);

        // On crée les unités standards.
        $this->_lengthStandardUnit = new Unit_Model_Unit_Standard();
        $this->_lengthStandardUnit->setMultiplier(1);
        $this->_lengthStandardUnit->setName('Metre');
        $this->_lengthStandardUnit->setSymbol('m');
        $this->_lengthStandardUnit->setRef('m');
        $this->_lengthStandardUnit->setPhysicalQuantity($this->_lengthPhysicalQuantity);
        $this->_lengthStandardUnit->setUnitSystem($this->unitSystem);
        $this->_lengthStandardUnit->save();
        $entityManagers['default']->flush();
        $this->_lengthPhysicalQuantity->setReferenceUnit($this->_lengthStandardUnit);

        $this->_massStandardUnit = new Unit_Model_Unit_Standard();
        $this->_massStandardUnit->setMultiplier(1);
        $this->_massStandardUnit->setName('Kilogramme');
        $this->_massStandardUnit->setSymbol('kg');
        $this->_massStandardUnit->setRef('kg');
        $this->_massStandardUnit->setPhysicalQuantity($this->_massPhysicalQuantity);
        $this->_massStandardUnit->setUnitSystem($this->unitSystem);
        $this->_massStandardUnit->save();
        $entityManagers['default']->flush();
        $this->_massPhysicalQuantity->setReferenceUnit($this->_massStandardUnit);

        $this->_timeStandardUnit = new Unit_Model_Unit_Standard();
        $this->_timeStandardUnit->setMultiplier(1);
        $this->_timeStandardUnit->setName('Seconde');
        $this->_timeStandardUnit->setSymbol('s');
        $this->_timeStandardUnit->setRef('s');
        $this->_timeStandardUnit->setPhysicalQuantity($this->_timePhysicalQuantity);
        $this->_timeStandardUnit->setUnitSystem($this->unitSystem);
        $this->_timeStandardUnit->save();
        $entityManagers['default']->flush();
        $this->_timePhysicalQuantity->setReferenceUnit($this->_timeStandardUnit);

        $this->_cashStandardUnit = new Unit_Model_Unit_Standard();
        $this->_cashStandardUnit->setMultiplier(1);
        $this->_cashStandardUnit->setName('Euro');
        $this->_cashStandardUnit->setSymbol('€');
        $this->_cashStandardUnit->setRef('e');
        $this->_cashStandardUnit->setPhysicalQuantity($this->_cashPhysicalQuantity);
        $this->_cashStandardUnit->setUnitSystem($this->unitSystem);
        $this->_cashStandardUnit->save();
        $entityManagers['default']->flush();
        $this->_cashPhysicalQuantity->setReferenceUnit($this->_cashStandardUnit);

        $entityManagers['default']->flush();

        // On créer deux extensions.
        $this->extension = new Unit_Model_Unit_Extension();
        $this->extension->setRef('co2e');
        $this->extension->setName('équivalent CO2');
        $this->extension->setSymbol('equ. CO2');
        $this->extension->setMultiplier(1);
        $this->extension->save();

        $this->extension2 = new Unit_Model_Unit_Extension();
        $this->extension2->setRef('ce');
        $this->extension2->setName('équivalent carbone');
        $this->extension2->setSymbol('equ. C');
        $this->extension2->setMultiplier(3.7);
        $this->extension2->save();

        //on créer plusieurs unités :
        $this->_unit1 = new Unit_Model_Unit_Discrete();
        $this->_unit1->setName('Animal');
        $this->_unit1->setSymbol('animal');
        $this->_unit1->setRef('animal');
        $this->_unit1->save();

        $this->_unit2 = new Unit_Model_Unit_Standard();
        $this->_unit2->setMultiplier(0.001);
        $this->_unit2->setName('gramme');
        $this->_unit2->setSymbol('g');
        $this->_unit2->setRef('g');
        $this->_unit2->setPhysicalQuantity($this->_massPhysicalQuantity);
        $this->_unit2->setUnitSystem($this->unitSystem);
        $this->_unit2->save();

        $this->_unit3 = new Unit_Model_Unit_Standard();
        $this->_unit3->setMultiplier(1);
        $this->_unit3->setName('Joule');
        $this->_unit3->setSymbol('J');
        $this->_unit3->setRef('j');
        $this->_unit3->setPhysicalQuantity($this->physicalQuantity1);
        $this->_unit3->setUnitSystem($this->unitSystem);
        $this->_unit3->save();

        $this->_unit4 = new Unit_Model_Unit_Extended();
        $this->_unit4->setRef('g_co2e');
        $this->_unit4->setName('gramme équivalent CO2');
        $this->_unit4->setSymbol('g equ. CO2');
        $this->_unit4->setMultiplier(0.001);
        $this->_unit4->setExtension($this->extension);
        $this->_unit4->setStandardUnit($this->_massStandardUnit);
        $this->_unit4->save();

        $this->_unit7 = new Unit_Model_Unit_Extended();
        $this->_unit7->setRef('kg_co2e');
        $this->_unit7->setName('kilogramme équivalent CO2');
        $this->_unit7->setSymbol('kg.equ. CO2');
        $this->_unit7->setMultiplier(0.001);
        $this->_unit7->setExtension($this->extension);
        $this->_unit7->setStandardUnit($this->_massStandardUnit);
        $this->_unit7->save();

        $this->_unit5 = new Unit_Model_Unit_Extended();
        $this->_unit5->setRef('kg_ce');
        $this->_unit5->setName('kilogramme équivalent carbone');
        $this->_unit5->setSymbol('kg.equ. C');
        $this->_unit5->setMultiplier(3.7);
        $this->_unit5->setExtension($this->extension2);
        $this->_unit5->setStandardUnit($this->_massStandardUnit);
        $this->_unit5->save();

        $this->_unit6 = new Unit_Model_Unit_Standard();
        $this->_unit6->setMultiplier(3.15576e+007);
        $this->_unit6->setName('an');
        $this->_unit6->setSymbol('an');
        $this->_unit6->setRef('an');
        $this->_unit6->setPhysicalQuantity($this->_timePhysicalQuantity);
        $this->_unit6->setUnitSystem($this->unitSystem);
        $this->_unit6->save();

        $entityManagers['default']->flush();
    }

    /**
     * Test de la fonction getSymbol()
     * On vérigfie que le symbol est bien le bon
     */
    function testGetSymbol()
    {
        //Traitement d'un cas assez complexe utilisant tout les types d'unité (discrète, étendue et standard)
        $o = new Unit_API('m^2.animal^-1.m^-2.g.g_co2e^2');
        $this->assertSame('m2.g.g equ. CO22/animal.m2', $o->getSymbol());
    }


    /**
     * Test de la fonction getNormalizedUnit()
     * On vérigfie que le symbol est bien le bon
     */
    function testGetNormalizedUnit()
    {
        //Traitement d'un cas assez complexe utilisant tout les types d'unité (discrète, étendue et standard)
        $o = new Unit_API('g.An');
        $result = $o->getNormalizedUnit();
        $this->assertTrue($result instanceof Unit_API);
        $this->assertSame('kg.s', $result->getRef());
    }

    /**
     * Test de la fonction isEquivalent()
     * On vérifie que deux unités equivalentes le son bien et inversement
     */
    function testIsEquivalent()
    {
        //Cas ou l'on mélange plusieurs type d'unité.
        $unit1 = new Unit_API('m^2.animal^-1.m^-2.kg.m^2.J^-5.kg_co2e^2');
        $unit2 = new Unit_API('animal^-1.g.m^2.J^-5.g_co2e^2');
        $this->assertEquals(true, $unit1->isEquivalent($unit2->getRef()));

        $unit3 = new Unit_API('animal^-1.g.m');
        $this->assertEquals(false, $unit1->isEquivalent($unit3->getRef()));

        // Cas ou l'on compare seulement des unités standard
        $unit4 = new Unit_API('g');
        $this->assertEquals(true, $unit4->isEquivalent($unit4->getRef()));

        // Cas ou l'on compare seulement des unités pas standard.
        $unit5 = new Unit_API('animal');
        $this->assertEquals(true, $unit5->isEquivalent($unit5->getRef()));

        // Test de l'expression levée lorsque l'on cherche à comparer une unité qui n'existe pas
        $unit6 = new Unit_API('');
        try {
            $unit1->isEquivalent($unit6->getRef());
        } catch (Core_Exception_NotFound $e) {
            $this->assertEquals('The unit does not exist', $e->getMessage());
        }
    }

    /**
     * Test de la fonction getConversionFactor()
     * On test si les facteurs de conversion retournés sont justes
     */
    function testGetConversionFactor()
    {
        $unit1 = new Unit_API('m^2.animal^-1.m^-2.kg.kg_ce');
        $result = $unit1->getConversionFactor();
        $this->assertEquals(true, $result == 3.7);

        $unit1 = new Unit_API('kg^2.g');
        $result = $unit1->getConversionFactor();
        $this->assertEquals(true, $result == 0.001);

        //Test de l'exception levée lorsque le coefficient multiplicateur d'une extension est null.
        $this->extension->setMultiplier(null);

        $this->_unit7->setExtension($this->extension);

        $unit1 = new Unit_API('kg_co2e');
        try {
            $result = $unit1->getConversionFactor();
        } catch (Core_Exception_UndefinedAttribute $e) {
            $this->assertEquals('Multiplier has not be defined', $e->getMessage());
        }

        //Test de l'exception levée lorsque le coefficient multiplicateur d'une extension est null.
        $this->_unit2->setMultiplier(null);

        $unit1 = new Unit_API('g');
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
        $operande[0]['unit'] = new Unit_API('g.animal^-1.kg.kg_ce');
        $operande[0]['signExponent'] = 1;
        $operande[1]['unit'] = new Unit_API('animal.s.an.kg^-1');
        $operande[1]['signExponent'] = -1;

        $result = Unit_API::multiply($operande);
        $this->assertTrue($result instanceof Unit_API);
        $this->assertEquals('kg_co2e.kg^3.animal^-2.s^-2', $result->getRef());
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
        $unit = new Unit_API();

        try {
            $result = $unit->calculateSum($operande);
        } catch (Unit_Exception_IncompatibleUnits $e) {
            $this->assertEquals('Units for the sum are incompatible', $e->getMessage());
        }

        $operande = null;
        $operande[] = 'g.animal^-1.kg.an^2';
        $operande[] = 'animal^-1.s.an.kg^2';

        $result = $unit->calculateSum($operande);

        $this->assertTrue($result instanceof Unit_API);
        $this->assertEquals('kg^2.s^2.animal^-1', $result->getRef());
    }

    /**
     * Test de la fonction getSamePhysicalQuantityUnits()
     * @expectedException Core_Exception_NotFound
     */
    function testGetSamePhysicalQuantityUnits()
    {
         // Cas ou la méthode fonctionne correctement.
         $unit1 = new Unit_API('kg');
         $results = $unit1->getSamePhysicalQuantityUnits();
         $this->assertTrue(count($results) >= 1);

         // Test de l'erreur générée dans le cas ou l'on cherche à récupérer les unités
         // compatibles avec une unité non standard.
         $unit2 = new Unit_API('kg.s^2');
         $results = $unit2->getSamePhysicalQuantityUnits();
         $this->assertTrue(count($results) == 0);
    }

    /**
     * Méthode appelée à la fin de chaque test
     */
    protected function tearDown()
    {
        $entityManagers = Zend_Registry::get('EntityManagers');

        $this->_lengthPhysicalQuantity->setReferenceUnit(null);
        $this->_massPhysicalQuantity->setReferenceUnit(null);
        $this->_timePhysicalQuantity->setReferenceUnit(null);
        $this->_cashPhysicalQuantity->setReferenceUnit(null);

        $this->_unit1->delete();
        $this->_unit2->delete();
        $this->_unit3->delete();
        $this->_unit4->delete();
        $this->_unit5->delete();
        $this->_unit6->delete();
        $this->_unit7->delete();

        $this->_lengthStandardUnit->delete();
        $this->_massStandardUnit->delete();
        $this->_timeStandardUnit->delete();
        $this->_cashStandardUnit->delete();

        $entityManagers['default']->flush();

        $this->physicalQuantity1->delete();

        $this->_lengthPhysicalQuantity->delete();
        $this->_massPhysicalQuantity->delete();
        $this->_timePhysicalQuantity->delete();
        $this->_cashPhysicalQuantity->delete();

        $this->extension->delete();
        $this->extension2->delete();

        $this->unitSystem->delete();

        $entityManagers['default']->flush();
    }

    /**
     * On verifie que les tables soientt vides après les tests
     */
    public static function tearDownAfterClass()
    {
        // Vérification qu'il ne reste aucun Unit_Model_Unit en base, sinon suppression !
        if (Unit_Model_Unit::countTotal() > 0) {
            echo PHP_EOL . 'Des Unit_System restants ont été trouvé après les tests, suppression en cours !';
            foreach (Unit_Model_Unit::loadList() as $unit) {
                $unit->delete();
            }
            $entityManagers = Zend_Registry::get('EntityManagers');
            $entityManagers['default']->flush();
        }
        // Vérification qu'il ne reste aucun Unit_Model_Unit_Extension en base, sinon suppression !
        if (Unit_Model_Unit_Extension::countTotal() > 0) {
            echo PHP_EOL . 'Des Unit_System restants ont été trouvé après les tests, suppression en cours !';
            foreach (Unit_Model_Unit_Extension::loadList() as $extensionunit) {
                $extensionunit->delete();
            }
            $entityManagers = Zend_Registry::get('EntityManagers');
            $entityManagers['default']->flush();
        }
        // Vérification qu'il ne reste aucun Unit_Model_PhysicalQuantity en base, sinon suppression !
        if (Unit_Model_PhysicalQuantity::countTotal() > 0) {
            echo PHP_EOL . 'Des Unit_System restants ont été trouvé après les tests, suppression en cours !';
            foreach (Unit_Model_PhysicalQuantity::loadList() as $physicalQuantity) {
                $physicalQuantity->delete();
            }
            $entityManagers = Zend_Registry::get('EntityManagers');
            $entityManagers['default']->flush();
        }
        // Vérification qu'il ne reste aucun Unit_Model_Unit_System en base, sinon suppression !
        if (Unit_Model_Unit_System::countTotal() > 0) {
            echo PHP_EOL . 'Des Unit_System restants ont été trouvé après les tests, suppression en cours !';
            foreach (Unit_Model_Unit_System::loadList() as $systemunit) {
                $systemunit->delete();
            }
            $entityManagers = Zend_Registry::get('EntityManagers');
            $entityManagers['default']->flush();
        }
    }

}