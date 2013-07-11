<?php
/**
 * Test de l'objet métier Unit_System
 * @author valentin.claras
 * @author hugo.charboniere
 * @author yoann.croizer
 * @package Unit
 * @subpackage Test
 */
use Unit\Domain\UnitSystem;

/**
 * UnitSystemTest
 * @package Unit
 * @subpackage Test
 */
class Unit_Test_UnitSystemTest
{
    /**
     * lance les autres classes de test
     */
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite();
        $suite->addTestSuite('Unit_Test_UnitSystemSetUp');
        $suite->addTestSuite('Unit_Test_UnitSystemOthers');
        return $suite;
    }

    /**
     * Génere un objet pret à l'emploi pour les tests
     * @param string $ref
     * @return UnitSystem $o
     */
    public static function generateObject($ref='UnitSystemTest')
    {
        $o = new UnitSystem();
        $o->setRef('Ref'.$ref);
        $o->setName('Name'.$ref);
        $o->save();
        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->flush();
        return $o;
    }

    /**
     * Supprime un objet utilisé dans les tests
     * @param UnitSystem $o
     */
    public static function deleteObject(UnitSystem $o)
    {
        $o->delete();
        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->flush();
    }
}


/**
 * UnitSystemSetUpTest
 * @package Unit
 */
class Unit_Test_UnitSystemSetUp extends PHPUnit_Framework_TestCase
{
    /**
     * Méthode appelée avant l'appel à la classe de test
     */
    public static function setUpBeforeClass()
    {
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
     * @return UnitSystem
     */
    function testConstruct()
    {
        $o = new UnitSystem();
        $this->assertInstanceOf('Unit\Domain\UnitSystem', $o);
        $o->setRef('RefSystemeUniteTestSave');
        $o->setName('NamzSystemeUniteTestSave');
        $this->assertEquals(array(), $o->getKey());
        $o->save();
        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->flush();
        $this->assertNotEquals(array(), $o->getKey());
        return $o;
    }

    /**
     * @depends testConstruct
     * @param UnitSystem $o
     */
    function testLoad($o)
    {
        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->clear($o);
        $oLoaded = UnitSystem::load($o->getKey());
        $this->assertInstanceOf('Unit\Domain\UnitSystem', $o);
        $this->assertEquals($oLoaded->getKey(), $o->getKey());
        $this->assertEquals($oLoaded->getRef(), $o->getRef());
        $this->assertEquals($oLoaded->getName(), $o->getName());
        return $oLoaded;
    }

    /**
     * @depends testLoad
     * @param UnitSystem $o
     */
    function testDelete(UnitSystem $o)
    {
        $o->delete();
        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->flush();
        $this->assertEquals(array(), $o->getKey());
    }

    /**
     * Méthode appelée à la fin des test
     */
    protected function tearDown()
    {
    }

    /**
     * Function called once, after all the tests
     */
    public static function tearDownAfterClass()
    {
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
 * UnitSystemOthersTest
 * @package Unit
 */
class Unit_Test_UnitSystemOthers extends PHPUnit_Framework_TestCase
{
    protected $unitSystem;

    /**
     * fonction apellé avant les test de la classe
     */
    public static function setUpBeforeClass()
    {
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
        $this->unitSystem = Unit_Test_UnitSystemTest::generateObject();
    }

    /**
     * test de la fonction loadByRef()
     */
    function testLoadByRef()
    {
        $o = UnitSystem::loadByRef('RefUnitSystemTest');
        $this->assertInstanceOf('Unit\Domain\UnitSystem', $o);
        $this->assertSame($o, $this->unitSystem);
    }

    /**
     * Méthode appelée à la fin des test
     */
    protected function tearDown()
    {
        Unit_Test_UnitSystemTest::deleteObject($this->unitSystem);
    }


    /**
     * Function called once, after all the tests
     */
    public static function tearDownAfterClass()
    {
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