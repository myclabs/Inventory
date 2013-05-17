<?php
/**
 * @author     joseph.rouffet
 * @author     matthieu.napoli
 * @package    Social
 * @subpackage Test
 */

/**
 * Creation of the Test Suite
 * @package    Social
 * @subpackage Test
 */
class Social_Model_NewsTest
{

    /**
     * @return PHPUnit_Framework_TestSuite
     */
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite();
        $suite->addTestSuite('Social_Model_NewsUnitTest');
        $suite->addTestSuite('Social_Model_NewsSetUpTest');
        return $suite;
    }

    /**
     * Generation of a test object
     * @return Social_Model_News
     */
    public static function generateObject()
    {
        $o = new Social_Model_News();
        $o->save();

        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->flush();

        return $o;
    }

    /**
     * Deletion of an object created with generateObject
     * @param Social_Model_News $o
     */
    public static function deleteObject(Social_Model_News $o)
    {
        $o->delete();

        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->flush();
    }

}


/**
 * @package    Social
 * @subpackage Test
 */
class Social_Model_NewsUnitTest extends PHPUnit_Framework_TestCase
{

    public function testInitialCommentCount()
    {
        $news = new Social_Model_News();
        $this->assertCount(0, $news->getComments());
    }

}


/**
 * Test of the creation/modification/deletion of the entity
 * @package    Social
 * @subpackage Test
 */
class Social_Model_NewsSetUpTest extends Core_Test_TestCase
{

    /**
     * Function called once, before all the tests
     */
    public static function setUpBeforeClass()
    {
        // Vérification qu'il ne reste aucun objet en base, sinon suppression
        foreach (Social_Model_News::loadList() as $o) {
            $o->delete();
        }
        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->flush();
    }

    /**
     * @return Social_Model_News
     */
    public function testConstruct()
    {
        $o = new Social_Model_News();
        $o->save();
        $this->entityManager->flush();

        $this->assertNotNull($o->getId());

        return $o;
    }

    /**
     * @depends testConstruct
     * @param Social_Model_News $o
     * @return Social_Model_News
     */
    public function testLoad(Social_Model_News $o)
    {
        $oLoaded = Social_Model_News::load($o->getId());
        $this->assertSame($oLoaded, $o);
        return $oLoaded;
    }

    /**
     * @depends testLoad
     * @param Social_Model_News $o
     */
    public function testDelete(Social_Model_News $o)
    {
        $o->delete();
        $this->assertEquals(\Doctrine\ORM\UnitOfWork::STATE_REMOVED,
                            $this->entityManager->getUnitOfWork()->getEntityState($o));
        $this->entityManager->flush();
        $this->assertEquals(\Doctrine\ORM\UnitOfWork::STATE_NEW,
                            $this->entityManager->getUnitOfWork()->getEntityState($o));
    }

}
