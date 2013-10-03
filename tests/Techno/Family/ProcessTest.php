<?php
/**
 * @package Techno
 */
use Techno\Domain\Family\ProcessFamily;
use Techno\Domain\Meaning;
use Techno\Domain\Tag;
use Techno\Domain\Component;
use Unit\UnitAPI;

/**
 * Test Family Process Class
 * @package Techno
 */
class Techno_Test_Family_ProcessTest
{
    /**
     * Creation of the test suite
     */
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite();
        $suite->addTestSuite('Techno_Test_Family_ProcessSetUpTest');
        $suite->addTestSuite('Techno_Test_Family_ProcessMetierTest');
        return $suite;
    }

    /**
     * Generation of a test object
     * @return ProcessFamily
     */
    public static function generateObject()
    {
        // Fixtures
        $baseUnit = new UnitAPI('m');
        $unit = new UnitAPI('km');

        $o = new ProcessFamily();
        $o->setRef(Core_Tools::generateString(10));
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
     * @param ProcessFamily $o
     */
    public static function deleteObject($o)
    {
        $o->delete();
        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->flush();
    }
}

class Techno_Test_Family_ProcessSetUpTest extends PHPUnit_Framework_TestCase
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
        if (Component::countTotal() > 0) {
            foreach (Component::loadList() as $o) {
                $o->delete();
            }
        }
        if (Tag::countTotal() > 0) {
            foreach (Tag::loadList() as $o) {
                $o->delete();
            }
        }
        if (Meaning::countTotal() > 0) {
            foreach (Meaning::loadList() as $o) {
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
     * @return ProcessFamily
     */
    function testConstruct()
    {
        // Fixtures
        $baseUnit = new UnitAPI('m');
        $unit = new UnitAPI('km');

        $o = new ProcessFamily();
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
     * @param ProcessFamily $o
     * @return ProcessFamily
     */
    public function testLoad($o)
    {
        $this->entityManager->clear('Techno\Domain\Component');
        /** @var $oLoaded ProcessFamily */
        $oLoaded = ProcessFamily::load($o->getKey());

        $this->assertInstanceOf('Techno\Domain\Family\ProcessFamily', $oLoaded);
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
     * @param ProcessFamily $o
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

class Techno_Test_Family_ProcessMetierTest extends PHPUnit_Framework_TestCase
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
        if (Component::countTotal() > 0) {
            foreach (Component::loadList() as $o) {
                $o->delete();
            }
        }
        if (Tag::countTotal() > 0) {
            foreach (Tag::loadList() as $o) {
                $o->delete();
            }
        }
        if (Meaning::countTotal() > 0) {
            foreach (Meaning::loadList() as $o) {
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

        $o = new ProcessFamily();
        $o->setRef('family');
        $o->setBaseUnit($baseUnit);
        $o->setUnit($unit);
        $o->save();
        $this->entityManager->flush();
        $o->delete();
        $this->entityManager->flush();
    }

}
