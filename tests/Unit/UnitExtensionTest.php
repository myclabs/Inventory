<?php
/**
 * Test de l'objet métier Unit_Extension
 * @author valentin.claras
 * @author hugo.charboniere
 * @author yoann.croizer
 * @package Unit
 * @subpackage Test
 */
use Unit\Domain\UnitExtension;

/**
 * UnitExtensionTest
 * @package Unit
 * @subpackage Test
 */
class Unit_Test_UnitExtensionTest
{
    /**
     * lance les autre classe de tests
     */
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite();
        $suite->addTestSuite('Unit_Test_UnitExtensionSetUp');
        $suite->addTestSuite('Unit_Test_UnitExtensionOthers');
        return $suite;
    }

    /**
     * Permet de générer un objet de base sur lequel on pourra travailler
     * @param string $ref
     * @param int $multiplier
     * @return \Unit\Domain\UnitExtension $o
     */
    public static function generateObject($ref='UnitExtensionTest', $multiplier=1)
    {
        $o = new UnitExtension();
        $o->setRef('Ref'.$ref);
        $o->setName('Name'.$ref);
        $o->setSymbol('Symbol'.$ref);
        $o->setMultiplier($multiplier);
        $o->save();
        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->flush();

        return $o;
    }

    /**
     * Permet de supprimer un objet de base sur lequel on a travaillé
     * @param \Unit\Domain\UnitExtension $o
     */
    public static function deleteObject(UnitExtension $o)
    {
        $o->delete();
        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->flush();
    }
}

/**
 * ExtensionSetUpTest
 * @package Unit
 */
class Unit_Test_UnitExtensionSetUp extends PHPUnit_Framework_TestCase
{
    /**
     * Méthode appelée avant l'appel à la classe de test
     */
    public static function setUpBeforeClass()
    {
        // Vérification qu'il ne reste aucun UnitExtension en base, sinon suppression !
        if (UnitExtension::countTotal() > 0) {
            echo PHP_EOL . 'Des Unit_Extention restants ont été trouvé avant les tests, suppression en cours !';
            foreach (UnitExtension::loadList() as $extensionunit) {
                $extensionunit->delete();
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
     * test si l'objet renvoyé est bien dy type demandé
     * @return $o
     */
    function testConstruct()
    {
        $o = new UnitExtension();
        $this->assertInstanceOf('Unit\Domain\UnitExtension', $o);
        $o->setRef('RefUnitExtensionTest');
        $o->setName('NameUnitExtensionTest');
        $o->setSymbol('Ext');
        $o->setMultiplier(1);
        $this->assertEquals(array(), $o->getKey());
        $o->save();
        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->flush();
        $this->assertNotEquals(array(), $o->getKey());
        return $o;
    }

    /**
     * @depends testConstruct
     * @param \Unit\Domain\UnitExtension $o
     */
    function testLoad($o)
    {
        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->clear($o);
        $oLoaded = UnitExtension::load($o->getKey());
        $this->assertInstanceOf('Unit\Domain\UnitExtension', $o);
        $this->assertEquals($oLoaded->getKey(), $o->getKey());
        $this->assertEquals($oLoaded->getRef(), $o->getRef());
        $this->assertEquals($oLoaded->getName(), $o->getName());
        $this->assertEquals($oLoaded->getSymbol(), $o->getSymbol());
        $this->assertEquals($oLoaded->getMultiplier(), $o->getMultiplier());
        return $oLoaded;
    }

    /**
     * @depends testLoad
     * @param \Unit\Domain\UnitExtension $o
     */
    function testDelete(UnitExtension $o)
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
     * Méthode appelée à la fin de la classe de test
     */
    public static function tearDownAfterClass()
    {
        // Vérification qu'il ne reste aucun UnitExtension en base, sinon suppression !
        if (UnitExtension::countTotal() > 0) {
            echo PHP_EOL . 'Des Unit_Extension restants ont été trouvé après les tests, suppression en cours !';
            foreach (UnitExtension::loadList() as $extensionunit) {
                $extensionunit->delete();
            }
            $entityManagers = Zend_Registry::get('EntityManagers');
            $entityManagers['default']->flush();
        }
    }
}

/**
 * extensionOthersTest
 * @package Unit
 */
class Unit_Test_UnitExtensionOthers extends PHPUnit_Framework_TestCase
{
    protected $extension;

     /**
      * Méthode appelée avant l'appel à la classe de test
      */
     public static function setUpBeforeClass()
     {
        // Vérification qu'il ne reste aucun UnitExtension en base, sinon suppression !
        if (UnitExtension::countTotal() > 0) {
            echo PHP_EOL . 'Des Unit_Extension restants ont été trouvé avant les tests, suppression en cours !';
            foreach (UnitExtension::loadList() as $extensionunit) {
                $extensionunit->delete();
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
        $this->extension = Unit_Test_UnitExtensionTest::generateObject();

     }

     /**
      * test de la fonction loadByRef
      */
     function testLoadByRef()
     {
        $o = UnitExtension::loadByRef('RefUnitExtensionTest');
        $this->assertInstanceOf('Unit\Domain\UnitExtension', $o);
        $this->assertSame($o, $this->extension);
     }

    /**
     * Méthode appelée à la fin de chaque test
     */
    protected function tearDown()
    {
        Unit_Test_UnitExtensionTest::deleteObject($this->extension);
    }

    /**
     * Méthode appelée à la fin de la classe de test
     */
    public static function tearDownAfterClass()
    {
        // Vérification qu'il ne reste aucun UnitExtension en base, sinon suppression !
        if (UnitExtension::countTotal() > 0) {
            echo PHP_EOL . 'Des Unit_Extension restants ont été trouvé après les tests, suppression en cours !';
            foreach (UnitExtension::loadList() as $extensionunit) {
                $extensionunit->delete();
            }
            $entityManagers = Zend_Registry::get('EntityManagers');
            $entityManagers['default']->flush();
        }
    }
}