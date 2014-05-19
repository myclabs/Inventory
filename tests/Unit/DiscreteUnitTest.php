<?php
/**
 * Test de l'objet métier Unit_Discrete.
 * @author valentin.claras
 * @author hugo.charboniere
 * @author yoann.croizer
 * @package Unit
 * @subpackage Test
 */
use Unit\Domain\Unit\Unit;
use Unit\Domain\Unit\DiscreteUnit;

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
        $suite->addTestSuite('Unit_Test_DiscreteUnitOthers');
        return $suite;
    }

    /**
     * Génere un objet pret à l'emploi pour les tests
     * @param string $ref
     * @return \Unit\Domain\Unit\DiscreteUnit $o
     */
    public static function generateObject($ref='DiscreteUnitTest')
    {
        $o = new DiscreteUnit();
        $o->setRef('Ref'.$ref);
        $o->getName()->set('Name'.$ref, 'fr');
        $o->getSymbol()->set('Symbol'.$ref, 'fr');
        $o->save();
        \Core\ContainerSingleton::getEntityManager()->flush();

        return $o;
    }


    /**
     * supprime un objet
     * @param \Unit\Domain\Unit\DiscreteUnit $o
     */
    public static function deleteObject(DiscreteUnit $o)
    {
        $o->delete();
        \Core\ContainerSingleton::getEntityManager()->flush();
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
        // Vérification qu'il ne reste aucun Unit en base, sinon suppression !
        if (Unit::countTotal() > 0) {
            echo PHP_EOL . 'Des Unit_System restants ont été trouvé avant les tests, suppression en cours !';
            foreach (Unit::loadList() as $unit) {
                $unit->delete();
            }
            \Core\ContainerSingleton::getEntityManager()->flush();
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
        $o = DiscreteUnit::loadByRef('RefDiscreteUnitTest');
        $this->assertInstanceOf('Unit\Domain\Unit\DiscreteUnit', $o);
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
        $b = new DiscreteUnit();

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
        // Vérification qu'il ne reste aucun Unit en base, sinon suppression !
        if (Unit::countTotal() > 0) {
            echo PHP_EOL . 'Des Unit_System restants ont été trouvé après les tests, suppression en cours !';
            foreach (Unit::loadList() as $unit) {
                $unit->delete();
            }
            \Core\ContainerSingleton::getEntityManager()->flush();
        }
    }
}
