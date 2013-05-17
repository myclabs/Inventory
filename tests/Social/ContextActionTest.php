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
        $suite->addTestSuite('Social_Model_ContextActionSetUpTest');
        $suite->addTestSuite('Social_Model_ContextActionOtherTest');
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


/**
 * Test of the creation/modification/deletion of the entity
 * @package    Social
 * @subpackage Test
 */
class Social_Model_ContextActionSetUpTest extends PHPUnit_Framework_TestCase
{

    /**
     * Function called once, before all the tests
     */
    public static function setUpBeforeClass()
    {
        // Empty related tables
        //         Social_Model_DAO_GenericAction::getInstance()->unitTestsClearTable();
        //         Social_Model_DAO_ContextAction::getInstance()->unitTestsClearTable();
        //         Social_Model_DAO_Action::getInstance()->unitTestsClearTable();
        //         Social_Model_DAO_ContextActionKeyFigure::getInstance()->unitTestsClearTable();
        //         Social_Model_DAO_CommentSet::getInstance()->unitTestsClearTable();

    }

    /**
     * @return Social_Model_Theme
     */
    function testConstruct()
    {
        $this->markTestIncomplete("TODO");
        $theme = new Social_Model_Theme('testConstruct');
        $theme->setLabel("testConstruct");
        $theme->save();

        $genericAction = new Social_Model_GenericAction($theme);
        $genericAction->setLabel("testConstruct");
        $genericAction->save();

        $o = new Social_Model_ContextAction($genericAction);
        $o->setLabel("testConstruct");
        return $o;
    }

    /**
     * @depends testConstruct
     * @param Social_Model_ContextAction $o
     * @return Social_Model_Theme
     */
    function testSave(Social_Model_ContextAction $o)
    {
        $o->save();
        $this->assertNotNull($o->getKey(), "Object id is not defined");

        $doc = new Doc_Model_Document();
        $doc->setName('test');
        $doc->addRessource($o);
        $doc->setDescription('document test');
        $doc->setReferenceYear(2000);
        $doc->setFileExists(false);
        $doc->save();

        return $o;
    }

    /**
     * @param Social_Model_ContextAction $o
     * @depends testSave
     */
    function testLoad(Social_Model_ContextAction $o)
    {
        $a = Social_Model_ContextAction::load($o->getKey());
        $this->assertTrue($a instanceof Social_Model_ContextAction);
        $this->assertSame($a, $o);
    }

    /**
     * Deletion test
     * @param Social_Model_ContextAction $o
     * @depends testSave
     */
    function testDelete(Social_Model_ContextAction $o)
    {
        $actionKeyFigure = new Social_Model_ActionKeyFigure();
        $actionKeyFigure->save();

        $cle = array(
            'idContextAction'   => $o->getKey(),
            'idActionKeyFigure' => $actionKeyFigure->getKey()
        );

        $contextActionKeyFigure = new Social_Model_ContextActionKeyFigure();
        $contextActionKeyFigure->setKey($cle);
        $contextActionKeyFigure->save();

        $genericAction = $o->getGenericAction();

        $o->delete();
        $genericAction->delete();
        $actionKeyFigure->delete();

        $this->assertNull($o->getKey());

    }

}


/**
 * Tests of Social_Model_ContextAction class
 * @package    Social
 * @subpackage Test
 */
class Social_Model_ContextActionOtherTest extends PHPUnit_Framework_TestCase
{

    // Test objects
    protected $_contextAction;


    /**
     * Function called once, before all the tests
     */
    public static function setUpBeforeClass()
    {
        // Empty related tables
        //         Social_Model_DAO_ContextAction::getInstance()->unitTestsClearTable();
        //         Social_Model_DAO_GenericAction::getInstance()->unitTestsClearTable();
        //         Social_Model_DAO_Action::getInstance()->unitTestsClearTable();
        //         Social_Model_DAO_ContextActionKeyFigure::getInstance()->unitTestsClearTable();
        //         Social_Model_DAO_CommentSet::getInstance()->unitTestsClearTable();
    }

    /**
     * Function called before each test
     */
    protected function setUp()
    {
        $this->markTestIncomplete("TODO");
        try {
            // Create a test object
            $this->_contextAction = Social_Model_ContextActionTest::generateObject();
        } catch (Exception $e) {
            $this->fail($e);
        }
    }


    /**
     * test de get / set
     */
    public function testGetSet()
    {
        //Test retour null
        //remise a null;
        $this->_contextAction->setGenericAction();
        $this->_contextAction->setCommentSet();
        $this->_contextAction->setLaunchDate();
        $this->_contextAction->setTargetDate();
        $this->_contextAction->setAchievementDate();

        $t1 = $this->_contextAction->getGenericAction();
        $t2 = $this->_contextAction->getLaunchDate();
        $t3 = $this->_contextAction->getTargetDate();
        $t4 = $this->_contextAction->getAchievementDate();

        $this->assertNull($t1);
        $this->assertNull($t2);
        $this->assertNull($t3);
        $this->assertNull($t4);

        // Tests Normaux
        $theme = new Social_Model_Theme('testGetSet');
        $theme->setLabel("testGetSet");
        $theme->save();

        $genericAction = new Social_Model_GenericAction($theme);
        $genericAction->setLabel("testGetSet");
        $genericAction->save();

        $launchDate = Core_Date::now();
        $targetDate = Core_Date::now();
        $achievementDate = Core_Date::now();

        $progress = Social_Model_ContextAction::PROGRESS_LAUNCHED;
        $nCommentSet = new Social_Model_CommentSet();
        $nCommentSet->save();

        $this->_contextAction->setGenericAction($genericAction);
        $this->_contextAction->setLaunchDate($launchDate);
        $this->_contextAction->setTargetDate($targetDate);
        $this->_contextAction->setAchievementDate($achievementDate);
        $this->_contextAction->setPersonInCharge('quidam');
        $this->_contextAction->setProgress($progress);
        $this->_contextAction->setLabel("testGetSet");
        $this->_contextAction->setDescription("testGetSet");
        $this->_contextAction->setCommentSet($nCommentSet);
        $this->_contextAction->save();

        $t1 = $this->_contextAction->getGenericAction();
        $t2 = $this->_contextAction->getLaunchDate();
        $t3 = $this->_contextAction->getTargetDate();
        $t4 = $this->_contextAction->getAchievementDate();
        $t5 = $this->_contextAction->getPersonInCharge();
        $t6 = $this->_contextAction->getProgress();
        $t7 = $this->_contextAction->getLabel();
        $t8 = $this->_contextAction->getDescription();
        $t9 = $this->_contextAction->getCommentSet();

        $this->assertEquals($genericAction->getKey(), $t1->getKey());
        $this->assertEquals($launchDate->toString('y-MM-dd'), $t2->toString('y-MM-dd'));
        $this->assertSame($targetDate->toString('y-MM-dd'), $t3->toString('y-MM-dd'));
        $this->assertSame($achievementDate->toString('y-MM-dd'), $t4->toString('y-MM-dd'));
        $this->assertEquals('quidam', $t5);
        $this->assertEquals($progress, $t6);
        $this->assertEquals("testGetSet", $t7);
        $this->assertEquals("testGetSet", $t8);
        $this->assertSame($nCommentSet, $t9);

    }

    /**
     * Function called after each test
     */
    protected function tearDown()
    {
        try {
            // Delete the test object
            Social_Model_ContextActionTest::deleteObject($this->_contextAction);

        } catch (Exception $e) {
            $this->fail($e);
        }
    }

}


