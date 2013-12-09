<?php

use Core\Test\TestCase;
use Doctrine\ORM\UnitOfWork;
use Keyword\Domain\KeywordRepository;
use Techno\Domain\Family\CoeffFamily;
use Techno\Domain\Meaning;
use Techno\Domain\Tag;
use Techno\Domain\Component;
use Unit\UnitAPI;

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
     * @return CoeffFamily
     */
    public static function generateObject()
    {
        // Fixtures
        $baseUnit = new UnitAPI('m');
        $unit = new UnitAPI('km');

        $o = new CoeffFamily();
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
     * @param CoeffFamily $o
     */
    public static function deleteObject($o)
    {
        $o->delete();
        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->flush();
    }
}

class Techno_Test_Family_CoeffSetUpTest extends TestCase
{
    public static function setUpBeforeClass()
    {
        /** @var \Doctrine\ORM\EntityManager $entityManager */
        $entityManager = Zend_Registry::get('EntityManagers')['default'];
        // Vérification qu'il ne reste aucun objet en base, sinon suppression
        foreach (Component::loadList() as $o) {
            $o->delete();
        }
        foreach (Tag::loadList() as $o) {
            $o->delete();
        }
        foreach (Meaning::loadList() as $o) {
            $o->delete();
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
     * @return CoeffFamily
     */
    public function testConstruct()
    {
        // Fixtures
        $baseUnit = new UnitAPI('m');
        $unit = new UnitAPI('km');

        $o = new CoeffFamily();
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
     * @param CoeffFamily $o
     * @return CoeffFamily
     */
    public function testLoad($o)
    {
        $this->entityManager->clear('Techno\Domain\Component');
        /** @var $oLoaded CoeffFamily */
        $oLoaded = CoeffFamily::load($o->getKey());

        $this->assertInstanceOf('Techno\Domain\Family\CoeffFamily', $oLoaded);
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
     * @param CoeffFamily $o
     */
    public function testDelete($o)
    {
        $o->delete();
        $this->assertEquals(UnitOfWork::STATE_REMOVED, $this->entityManager->getUnitOfWork()->getEntityState($o));
        $this->entityManager->flush();
        $this->assertEquals(UnitOfWork::STATE_NEW, $this->entityManager->getUnitOfWork()->getEntityState($o));
    }
}

class Techno_Test_Family_CoeffMetierTest extends TestCase
{
    public static function setUpBeforeClass()
    {
        /** @var \Doctrine\ORM\EntityManager $entityManager */
        $entityManager = Zend_Registry::get('EntityManagers')['default'];
        // Vérification qu'il ne reste aucun objet en base, sinon suppression
        foreach (Component::loadList() as $o) {
            $o->delete();
        }
        foreach (Tag::loadList() as $o) {
            $o->delete();
        }
        foreach (Meaning::loadList() as $o) {
            $o->delete();
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
     * Teste les champs qui peuvent être vides
     */
    public function testNullableFields()
    {
        // Fixtures
        $baseUnit = new UnitAPI('m');
        $unit = new UnitAPI('km');

        $o = new CoeffFamily();
        $o->setRef('family');
        $o->setBaseUnit($baseUnit);
        $o->setUnit($unit);
        $o->save();
        $this->entityManager->flush();
        $o->delete();
        $this->entityManager->flush();
    }
}
