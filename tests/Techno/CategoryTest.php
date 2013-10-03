<?php
/**
 * @author matthieu.napoli
 * @package Techno
 */
use Techno\Domain\Category;

/**
 * @package Techno
 */
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

/**
 *  @package Techno
 */
class Techno_Test_CategorySetUp extends PHPUnit_Framework_TestCase
{

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;

    /**
     * Fonction appelee une fois, avant tous les tests
     */
    public static function setUpBeforeClass()
    {
        // Vérification qu'il ne reste aucun objet en base, sinon suppression
        if (Category::countTotal() > 0) {
            foreach (Category::loadList() as $o) {
                $o->delete();
            }
        }
        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->flush();
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
        $this->assertEquals(\Doctrine\ORM\UnitOfWork::STATE_REMOVED,
                            $this->entityManager->getUnitOfWork()->getEntityState($o));
        $this->entityManager->flush();
        $this->assertEquals(\Doctrine\ORM\UnitOfWork::STATE_NEW,
                            $this->entityManager->getUnitOfWork()->getEntityState($o));
    }

}
