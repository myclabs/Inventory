<?php
/**
* @package Techno
*/
use Unit\UnitAPI;

/**
 * Test Family Coeff Class
 * @package Techno
 */
class Techno_Test_Family_CoeffTest
{
    /**
     * Creation of the test suite
     */
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite();
        $suite->addTestSuite('Techno_Test_Family_CoeffSetUpTest');
        $suite->addTestSuite('Techno_Test_Family_CoeffMetierTest');
        return $suite;
    }

    /**
     * Generation of a test object
     * @return Techno_Model_Family_Coeff
     */
    public static function generateObject()
    {
        // Fixtures
        $baseUnit = new UnitAPI('m');
        $unit = new UnitAPI('km');

        $o = new Techno_Model_Family_Coeff();
        $o->setRef(strtolower(Core_Tools::generateString(10)));
        $o->setBaseUnit($baseUnit);
        $o->setUnit($unit);
        $o->setDocumentation("Documentation");
        $o->setLabel('Label');
        $o->save();

        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->flush();
        return $o;
    }

    /**
     * Deletion of an object created with generateObject
     * @param Techno_Model_Family_Coeff $o
     */
    public static function deleteObject($o)
    {
        $o->delete();
        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->flush();
    }
}

/**
 * Test des méthodes de base de l'objet métier Techno_Model_Family_Coeff
 * @package Techno
 */
class Techno_Test_Family_CoeffSetUpTest extends PHPUnit_Framework_TestCase
{

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;

    /**
     * Méthode appelée avant les tests
     */
    public static  function setUpBeforeClass()
    {
        $entityManager = Zend_Registry::get('EntityManagers')['default'];
        // Vérification qu'il ne reste aucun objet en base, sinon suppression
        if (Techno_Model_Component::countTotal() > 0) {
            foreach (Techno_Model_Component::loadList() as $o) {
                $o->delete();
            }
        }
        if (Techno_Model_Tag::countTotal() > 0) {
            foreach (Techno_Model_Tag::loadList() as $o) {
                $o->delete();
            }
        }
        if (Techno_Model_Meaning::countTotal() > 0) {
            foreach (Techno_Model_Meaning::loadList() as $o) {
                $o->delete();
            }
        }
        /** @var KeywordRepository $keywordRepository */
        $keywordRepository = $entityManager->getRepository('\Keyword\Domain\Keyword');
        if ($keywordRepository->count() > 0) {
            foreach ($keywordRepository->getAll() as $o) {
                $keywordRepository->remove($o);
            }
        }
        $entityManager->flush();
    }

    /**
     * Set up
     */
    public function setUp()
    {
        $entityManagers = Zend_Registry::get('EntityManagers');
        $this->entityManager = $entityManagers['default'];
    }

    /**
     * @return Techno_Model_Family_Coeff
     */
    function testConstruct()
    {
        // Fixtures
        $baseUnit = new UnitAPI('m');
        $unit = new UnitAPI('km');

        $o = new Techno_Model_Family_Coeff();
        $o->setRef('family');
        $o->setBaseUnit($baseUnit);
        $o->setUnit($unit);
        $o->setDocumentation("Documentation");

        $o->save();
        $this->entityManager->flush();

        $this->assertNotEmpty($o->getKey());
        $this->assertSame($baseUnit, $o->getBaseUnit());
        $this->assertSame($unit, $o->getUnit());
        $this->assertEquals("Documentation", $o->getDocumentation());
        return $o;
    }

    /**
     * @depends testConstruct
     * @param Techno_Model_Family_Coeff $o
     * @return Techno_Model_Family_Coeff
     */
    public function testLoad($o)
    {
        $this->entityManager->clear('Techno_Model_Component');
        /** @var $oLoaded Techno_Model_Family_Coeff */
        $oLoaded = Techno_Model_Family_Coeff::load($o->getKey());

        $this->assertInstanceOf('Techno_Model_Family_Coeff', $oLoaded);
        $this->assertNotSame($o, $oLoaded);
        $this->assertEquals($o->getKey(), $oLoaded->getKey());
        // getBaseUnit
        $this->assertEquals($o->getBaseUnit(), $oLoaded->getBaseUnit());
        $this->assertNotSame($o->getBaseUnit(), $oLoaded->getBaseUnit());
        // getUnit
        $this->assertEquals($o->getUnit(), $oLoaded->getUnit());
        $this->assertNotSame($o->getUnit(), $oLoaded->getUnit());
        // getRef
        $this->assertEquals($o->getRef(), $oLoaded->getRef());
        // getLabel
        $this->assertEquals($o->getLabel(), $oLoaded->getLabel());
        // Documentation
        $this->assertEquals("Documentation", $o->getDocumentation());
        return $oLoaded;
    }

     /**
      * @depends testLoad
      * @param Techno_Model_Family_Coeff $o
      */
     function testDelete($o)
     {
         $o->delete();
         $this->assertEquals(\Doctrine\ORM\UnitOfWork::STATE_REMOVED,
                             $this->entityManager->getUnitOfWork()->getEntityState($o));
         $this->entityManager->flush();
         $this->assertEquals(\Doctrine\ORM\UnitOfWork::STATE_NEW,
                             $this->entityManager->getUnitOfWork()->getEntityState($o));
     }

}

/**
 * Test des fonctionnalités de l'objet métier Techno_Model_Family_Coeff
 * @package Techno
 */
class Techno_Test_Family_CoeffMetierTest extends PHPUnit_Framework_TestCase
{

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;

    /**
     * Méthode appelée avant les tests
     */
    public static  function setUpBeforeClass()
    {
        $entityManager = Zend_Registry::get('EntityManagers')['default'];
        // Vérification qu'il ne reste aucun objet en base, sinon suppression
        if (Techno_Model_Component::countTotal() > 0) {
            foreach (Techno_Model_Component::loadList() as $o) {
                $o->delete();
            }
        }
        if (Techno_Model_Tag::countTotal() > 0) {
            foreach (Techno_Model_Tag::loadList() as $o) {
                $o->delete();
            }
        }
        if (Techno_Model_Meaning::countTotal() > 0) {
            foreach (Techno_Model_Meaning::loadList() as $o) {
                $o->delete();
            }
        }
        /** @var KeywordRepository $keywordRepository */
        $keywordRepository = $entityManager->getRepository('\Keyword\Domain\Keyword');
        if ($keywordRepository->count() > 0) {
            foreach ($keywordRepository->getAll() as $o) {
                $keywordRepository->remove($o);
            }
        }
        $entityManager->flush();
    }

    /**
     * Set up
     */
    public function setUp()
    {
        $entityManagers = Zend_Registry::get('EntityManagers');
        $this->entityManager = $entityManagers['default'];
    }

    /**
     * Teste les champs qui peuvent être vides
     */
    function testNullableFields()
    {
        // Fixtures
        $baseUnit = new UnitAPI('m');
        $unit = new UnitAPI('km');

        $o = new Techno_Model_Family_Coeff();
        $o->setRef('family');
        $o->setBaseUnit($baseUnit);
        $o->setUnit($unit);
        $o->save();
        $this->entityManager->flush();
        $o->delete();
        $this->entityManager->flush();
    }

}
