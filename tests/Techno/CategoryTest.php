<?php
/**
 * @author matthieu.napoli
 * @package Techno
 */

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
     * @return Techno_Model_Category
     */
    public static function generateObject()
    {
        $category = new Techno_Model_Category();
        $category->save();
        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->flush();
        return $category;
    }

    /**
     * Deletion of an object created with generateObject
     * @param Techno_Model_Category $o
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
        if (Techno_Model_Category::countTotal() > 0) {
            foreach (Techno_Model_Category::loadList() as $o) {
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
     * @return Techno_Model_Category
     */
    function testConstruct()
    {
        $o = new Techno_Model_Category();
        $o->setLabel("Test");
        $o->save();
        $this->entityManager->flush();

        $this->assertEquals("Test", $o->getLabel());
        return $o;
    }

    /**
     * @depends testConstruct
     * @param Techno_Model_Category $o
     * @return Techno_Model_Category
     */
    function testLoad($o)
    {
        $this->entityManager->clear('Techno_Model_Category');
        /** @var $oLoaded Techno_Model_Category */
        $oLoaded = Techno_Model_Category::load($o->getKey());

        $this->assertInstanceOf('Techno_Model_Category', $oLoaded);
        $this->assertNotSame($o, $oLoaded);
        $this->assertEquals($o->getKey(), $oLoaded->getKey());
        $this->assertEquals($o->getLabel(), $oLoaded->getLabel());
        return $oLoaded;
    }

    /**
     * @depends testLoad
     * @param Techno_Model_Category $o
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
