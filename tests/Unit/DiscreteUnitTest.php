<?php
/**
 * Test de l'objet métier Unit_Discrete.
 * @author valentin.claras
 * @author hugo.charboniere
 * @author yoann.croizer
 * @package Unit
 * @subpackage Test
 */

/**
 * DiscreteUnit
 * @package Unit
 * @subpackage Test
 */
class Unit_Test_DiscreteUnitTest
{
    /**
     * Lance les autres classes de test
     */
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite();
        $suite->addTestSuite('Unit_Test_DiscreteUnitSetUp');
        $suite->addTestSuite('Unit_Test_DiscreteUnitOthers');
        return $suite;
    }

    /**
     * Génere un objet pret à l'emploi pour les tests
     * @param string $ref
     * @return Unit_Model_Unit_Discrete $o
     */
    public static function generateObject($ref='DiscreteUnitTest')
    {
        $o = new Unit_Model_Unit_Discrete();
        $o->setRef('Ref'.$ref);
        $o->setName('Name'.$ref);
        $o->setSymbol('Symbol'.$ref);
        $o->save();
        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->flush();

        return $o;
    }


    /**
     * supprime un objet
     * @param Unit_Model_Unit_Discrete $o
     */
    public static function deleteObject(Unit_Model_Unit_Discrete $o)
    {
        $o->delete();
        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->flush();
    }
}

/**
 * DiscreteUnit
 * @package Unit
 */
class Unit_Test_DiscreteUnitSetUp extends PHPUnit_Framework_TestCase
{
    /**
     * Méthode appelée avant l'appel à la classe de test
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
    }

    /**
     *  Méthode apellée avant chaque test
     */
    protected function setUp()
    {

    }

    /**
     * Test du constructeur
     */
    function testConstruct()
    {
        $o = new Unit_Model_Unit_Discrete();
        $this->assertInstanceOf('Unit_Model_Unit_Discrete', $o);
        $o->setRef('RefDiscreteUnit');
        $o->setName('NameDiscreteUnit');
        $o->setSymbol('SymbolDiscreteUnit');
        $this->assertEquals(array(), $o->getKey());
        $o->save();
        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->flush();
        $this->assertNotEquals(array(), $o->getKey());

        return $o;
    }

    /**
     * @depends testConstruct
     * @param Unit_Model_Unit_Discrete $o
     */
    function testLoad(Unit_Model_Unit_Discrete $o)
    {
        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->clear($o);
        // On tente de charger l'unité enregistrée dans la base lors du test de la méthode save().
        $oLoaded = Unit_Model_Unit_Discrete::load($o->getKey());
        $this->assertInstanceOf('Unit_Model_Unit_Discrete', $oLoaded);
        $this->assertEquals($oLoaded->getKey(), $o->getKey());
        $this->assertEquals($oLoaded->getRef(), $o->getRef());
        $this->assertEquals($oLoaded->getName(), $o->getName());
        $this->assertEquals($oLoaded->getSymbol(), $o->getSymbol());

        return $oLoaded;
    }


    /**
     * @depends testLoad
     * @param Unit_Model_Unit_Discrete $o
     */
    function testDelete(Unit_Model_Unit_Discrete $o)
    {
        $o->delete();
        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->flush();
        $this->assertEquals(array(), $o->getKey());
    }

    /**
     * Méthode appelé à la fin de chaque test
     */
    protected function tearDown()
    {

    }

    /**
     * Métode appelée à la fin de la classe de test
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
    }

}


/**
 * DiscreteUnit
 * @package Unit
 */
class Unit_Test_DiscreteUnitOthers extends PHPUnit_Framework_TestCase
{

    protected $_discreteUnit;

    /**
     * méthode apellée avant le lancement des tests de la classe
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
    }

    /**
     * méthode appelée avant chaque test
     */
    protected function setUp()
    {
        $this->_discreteUnit = Unit_Test_DiscreteUnitTest::generateObject();

    }

    /**
     * test de la fonction loadByRef()
     */
    function testLoadByRef()
    {
        $o = Unit_Model_Unit_Discrete::loadByRef('RefDiscreteUnitTest');
        $this->assertInstanceOf('Unit_Model_Unit_Discrete', $o);
        $this->assertSame($o, $this->_discreteUnit);
    }

    /**
     * Test la méthode GetRefeerenceUnit
     */
    function testGetReferenceUnit()
    {
        //L'unité de référence d'une unité discrete est elle même.
         $this->assertSame($this->_discreteUnit->getReferenceUnit(), $this->_discreteUnit);
    }

    /**
     * Test la méthode getConversionFactor
     */
    function testGetConversionFactor()
    {
        //Le facteur de conversion d'une unité discrète par rapport à elle même est 1.
        $this->assertEquals($this->_discreteUnit->getConversionFactor($this->_discreteUnit), 1);

        // Test de l'exception levée lorsque l'on essaye de récupérer le facteur de conversion entre deux unités
        // discrètes différentes.
        $b = new Unit_Model_Unit_Discrete();

        try {
            $this->assertEquals($this->_discreteUnit->getConversionFactor($b), 1);
        } catch (Core_Exception_InvalidArgument $e) {
            $this->assertEquals('Units need to be the same', $e->getMessage());
        }
        $b->delete();
    }

    /**
     * Méthode appelé à la fin de chaque test
     */
    protected function tearDown()
    {
        Unit_Test_DiscreteUnitTest::deleteObject($this->_discreteUnit);
    }

    /**
     * Métode appelée à la fin de la classe de test
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
    }
}