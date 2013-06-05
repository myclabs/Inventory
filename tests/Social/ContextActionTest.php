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
class Social_Model_ContextActionTest
{

    /**
     * Creation of the test suite
     * @return PHPUnit_Framework_TestSuite
     */
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite();
        $suite->addTestSuite('Social_Model_ContextActionUnitTest');
        return $suite;
    }

    /**
     * @return Social_Model_ContextAction
     */
    public static function generateObject()
    {
        $theme = new Social_Model_Theme();
        $theme->save();

        $genericAction = new Social_Model_GenericAction($theme);
        $genericAction->save();

        $o = new Social_Model_ContextAction($genericAction);
        $o->save();

        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->flush();

        return $o;
    }

    /**
     * @param Social_Model_ContextAction $o
     */
    public static function deleteObject(Social_Model_ContextAction $o)
    {
        $o->delete();
        $o->getGenericAction()->delete();
        $o->getGenericAction()->getTheme()->delete();
        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->flush();
    }

}


/**
 * @package    Social
 * @subpackage Test
 */
class Social_Model_ContextActionUnitTest extends PHPUnit_Framework_TestCase
{

    public function testInitialProgress()
    {
        $theme = new Social_Model_Theme();
        $genericAction = new Social_Model_GenericAction($theme);
        $contextAction = new Social_Model_ContextAction($genericAction);
        $this->assertEquals(Social_Model_ContextAction::PROGRESS_PLANNED, $contextAction->getProgress());
    }

    /**
     * @expectedException Core_Exception_InvalidArgument
     */
    public function testInvalidProgress()
    {
        $theme = new Social_Model_Theme();
        $genericAction = new Social_Model_GenericAction($theme);
        $contextAction = new Social_Model_ContextAction($genericAction);
        $contextAction->setProgress('foo');
    }

}
