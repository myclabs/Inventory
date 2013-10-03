<?php

use Doctrine\ORM\UnitOfWork;
use Techno\Domain\Category;

class Techno_Test_CategoryTest
{
    /**
     * Creation of the test suite
     */
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite();
        $suite->addTestSuite('Techno_Test_CategorySetUp');
        return $suite;
    }

    /**
     * Génere un objet dérivé prêt à l'emploi pour les tests.
     * @return Category
     */
    public static function generateObject()
    {
        $category = new Category();
        $category->save();
        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->flush();
        return $category;
    }

    /**
     * Deletion of an object created with generateObject
     * @param Category $o
     */
    public static function deleteObject($o)
    {
        $o->delete();
        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->flush();
    }
}

class Techno_Test_CategorySetUp extends Core_Test_TestCase
{
    public static function setUpBeforeClass()
    {
        // Vérification qu'il ne reste aucun objet en base, sinon suppression
        foreach (Category::loadList() as $o) {
            $o->delete();
        }
        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->flush();
    }

    /**
     * @return Category
     */
    function testConstruct()
    {
        $o = new Category("Test");
        $o->save();
        $this->entityManager->flush();

        $this->assertEquals("Test", $o->getLabel());
        return $o;
    }

    /**
     * @depends testConstruct
     * @param Category $o
     * @return Category
     */
    function testLoad($o)
    {
        $this->entityManager->clear('Techno\Domain\Category');
        /** @var $oLoaded Category */
        $oLoaded = Category::load($o->getKey());

        $this->assertInstanceOf('Techno\Domain\Category', $oLoaded);
        $this->assertNotSame($o, $oLoaded);
        $this->assertEquals($o->getKey(), $oLoaded->getKey());
        $this->assertEquals($o->getLabel(), $oLoaded->getLabel());
        return $oLoaded;
    }

    /**
     * @depends testLoad
     * @param Category $o
     */
    function testDelete($o)
    {
        $o->delete();
        $this->assertEquals(UnitOfWork::STATE_REMOVED, $this->entityManager->getUnitOfWork()->getEntityState($o));
        $this->entityManager->flush();
        $this->assertEquals(UnitOfWork::STATE_NEW, $this->entityManager->getUnitOfWork()->getEntityState($o));
    }
}
