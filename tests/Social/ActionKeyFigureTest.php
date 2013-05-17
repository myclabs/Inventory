<?php
/**
 * @author     joseph.rouffet
 * @package    Social
 * @subpackage Test
 */

/**
 * Creation of the Test Suite
 * @package    Social
 * @subpackage Test
 */
class Social_Model_ActionKeyFigureTest
{

    /**
     * @return PHPUnit_Framework_TestSuite
     */
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite();
        $suite->addTestSuite('Social_Model_ActionKeyFigureSetUpTest');
        return $suite;
    }

    /**
     * Generation of a test object
     * @return Social_Model_ActionKeyFigure
     */
    public static function generateObject()
    {
        $unit = new Unit_API('m');
        $o = new Social_Model_ActionKeyFigure($unit, 'test');
        $o->save();
        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->flush();
        return $o;
    }

    /**
     * Deletion of an object created with generateObject
     * @param Social_Model_ActionKeyFigure $o
     */
    public static function deleteObject(Social_Model_ActionKeyFigure $o)
    {
        $o->delete();
        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->flush();
    }

}


/**
 * Test of the creation/modification/deletion of the entity
 * @package    Social
 * @subpackage Test
 */
class Social_Model_ActionKeyFigureSetUpTest extends Core_Test_TestCase
{

    public static function setUpBeforeClass()
    {
        // Vérification qu'il ne reste aucun objet en base, sinon suppression
        foreach (Social_Model_ActionKeyFigure::loadList() as $o) {
            $o->delete();
        }
        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->flush();
    }

    /**
     * @return Social_Model_ActionKeyFigure
     */
    function testConstruct()
    {
        $unit = new Unit_API('m');
        $o = new Social_Model_ActionKeyFigure($unit, 'test');

        $o->save();
        $this->entityManager->flush();

        $this->assertNotNull($o->getId(), "Object id is not defined");

        return $o;
    }


    /**
     * @depends testConstruct
     * @param Social_Model_ActionKeyFigure $o
     * @return Social_Model_ActionKeyFigure
     */
    function testLoad(Social_Model_ActionKeyFigure $o)
    {
        $oLoaded = Social_Model_ActionKeyFigure::load($o->getId());
        $this->assertSame($oLoaded, $o);
        return $oLoaded;
    }

    /**
     * @depends testLoad
     * @param Social_Model_ActionKeyFigure $o
     */
    function testDelete(Social_Model_ActionKeyFigure $o)
    {
        $o->delete();
        $this->assertEquals(\Doctrine\ORM\UnitOfWork::STATE_REMOVED,
                            $this->entityManager->getUnitOfWork()->getEntityState($o));
        $this->entityManager->flush();
        $this->assertEquals(\Doctrine\ORM\UnitOfWork::STATE_NEW,
                            $this->entityManager->getUnitOfWork()->getEntityState($o));
    }

}
