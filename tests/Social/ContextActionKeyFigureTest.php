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
class Social_Model_ContextActionKeyFigureTest
{

    /**
     * Creation of the test suite
     * @return PHPUnit_Framework_TestSuite
     */
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite();
        $suite->addTestSuite('Social_Model_ContextActionKeyFigureSetUpTest');
        return $suite;
    }

}


/**
 * Test of the creation/modification/deletion of the entity
 * @package    Social
 * @subpackage Test
 */
class Social_Model_ContextActionKeyFigureSetUpTest extends Core_Test_TestCase
{

    /**
     * Function called once, before all the tests
     */
    public static function setUpBeforeClass()
    {
        // Vérification qu'il ne reste aucun objet en base, sinon suppression
        foreach (Social_Model_ContextActionKeyFigure::loadList() as $o) {
            $o->delete();
        }
        foreach (Social_Model_Action::loadList() as $o) {
            $o->delete();
        }
        foreach (Social_Model_ActionKeyFigure::loadList() as $o) {
            $o->delete();
        }
        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->flush();
    }

    /**
     * @return Social_Model_ContextActionKeyFigure
     */
    function testConstruct()
    {
        $actionKeyFigure = Social_Model_ActionKeyFigureTest::generateObject();
        $contextAction = Social_Model_ContextActionTest::generateObject();

        $o = new Social_Model_ContextActionKeyFigure($actionKeyFigure, $contextAction);
        $o->setValue(10.);

        $o->save();
        $this->entityManager->flush();

        return $o;
    }

    /**
     * @depends testConstruct
     * @param Social_Model_ContextActionKeyFigure $o
     * @return Social_Model_ContextActionKeyFigure
     */
    function testLoad(Social_Model_ContextActionKeyFigure $o)
    {
        $oLoaded = Social_Model_ContextActionKeyFigure::loadByKey($o->getActionKeyFigure(), $o->getContextAction());

        $this->assertSame($o, $oLoaded);
        $this->assertEquals(10., $oLoaded->getValue());

        return $o;
    }

    /**
     * @depends testLoad
     * @param Social_Model_ContextActionKeyFigure $o
     */
    function testDelete(Social_Model_ContextActionKeyFigure $o)
    {
        $o->delete();
        $this->assertEquals(\Doctrine\ORM\UnitOfWork::STATE_REMOVED,
                            $this->entityManager->getUnitOfWork()->getEntityState($o));
        $this->entityManager->flush();
        $this->assertEquals(\Doctrine\ORM\UnitOfWork::STATE_NEW,
                            $this->entityManager->getUnitOfWork()->getEntityState($o));

        Social_Model_ActionKeyFigureTest::deleteObject($o->getActionKeyFigure());
        Social_Model_ContextActionTest::deleteObject($o->getContextAction());
    }

}
