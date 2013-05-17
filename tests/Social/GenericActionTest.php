<?php
/**
 * @author joseph.rouffet
 * @package Social
 * @subpackage Test
 */

/**
 * Creation of the Test Suite
 * @package Social
 * @subpackage Test
 */
class Social_Model_GenericActionTest
{

    /**
     * Creation of the test suite
     */
     public static function suite()
     {
         $suite = new PHPUnit_Framework_TestSuite();
         $suite->addTestSuite('Social_Model_GenericActionSetUpTest');
         $suite->addTestSuite('Social_Model_GenericActionOtherTest');
         return $suite;
     }

    /**
     * Generation of a test object
     * @return Social_Model_GenericAction
     */
     public static function generateObject()
     {
         $theme = new Social_Model_Theme();
         $theme->setLabel('generateObject');
         $theme->save();

         $o = new Social_Model_GenericAction($theme);
         $o->setLabel("generateObject");
         $o->save();
         return $o;
     }

    /**
     * Deletion of an object created with generateObject
     * @param Social_Model_GenericAction $o
     */
     public static function deleteObject(Social_Model_GenericAction $o)
     {

         $theme = $o->getTheme();

         $o->delete();

         if ($theme !== null)
             $theme->delete();
     }

}


/**
 * Test of the creation/modification/deletion of the entity
 * @package Social
 * @subpackage Test
 */
class Social_Model_GenericActionSetUpTest extends PHPUnit_Framework_TestCase
{

    /**
     * Function called once, before all the tests
     */
     public static function setUpBeforeClass()
     {
     // Empty related tables
//         Social_Model_DAO_GenericAction::getInstance()->unitTestsClearTable();
//         Social_Model_DAO_ContextAction::getInstance()->unitTestsClearTable();
//         Social_Model_DAO_Theme::getInstance()->unitTestsClearTable();
//         Social_Model_DAO_Action::getInstance()->unitTestsClearTable();
//         Social_Model_DAO_CommentSet::getInstance()->unitTestsClearTable();

     }

    /**
     * Constructor test
     */
     function testConstruct()
     {
         $this->markTestIncomplete("TODO");
        $theme = new Social_Model_Theme();
        $theme->setLabel('testConstruct');
        $theme->save();

        $o = new Social_Model_GenericAction($theme);
        $o->setLabel("testConstruct");

        return $o;
     }

    /**
     * Save test
     * @param Social_Model_GenericAction $o
     * @depends testConstruct
     */
     function testSave(Social_Model_GenericAction $o)
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
     * Test of load
     * @param Social_Model_GenericAction $o
     * @depends testSave
	 */
     function testLoad(Social_Model_GenericAction $o)
     {
         $a = Social_Model_GenericAction::load($o->getKey());
         $this->assertTrue($a instanceof Social_Model_GenericAction);
         $this->assertEquals($a->getKey(), $o->getKey());
     }

    /**
     * Test de loadByCommentSet
     * @param Social_Model_GenericAction $o
     * @depends testSave
     */
    public function testLoadByCommentSet(Social_Model_GenericAction $o)
    {
        $commentSet = $o->getCommentSet();
        $a = Social_Model_GenericAction::loadByCommentSet($commentSet);
        $this->assertEquals($a->getKey(), $o->getKey());
        $this->assertTrue($a instanceof Social_Model_GenericAction);
    }

    /**
     * Deletion test
     * @param Social_Model_GenericAction $o
     * @depends testSave
     */
    function testDelete(Social_Model_GenericAction $o)
    {
        $theme = $o->getTheme();
        $theme->removeGenericAction($o);
        $theme->save();

        $o->delete();

        $theme->delete();

        $this->assertNull($o->getKey());
    }

}


/**
 * Tests of Social_Model_GenericAction class
 * @package Social
 * @subpackage Test
 */
class Social_Model_GenericActionOtherTest extends PHPUnit_Framework_TestCase
{

     // Test objects
     protected $_genericAction;


    /**
     * Function called once, before all the tests
     */
     public static function setUpBeforeClass()
     {
         // Empty related tables
//         Social_Model_DAO_GenericAction::getInstance()->unitTestsClearTable();
//         Social_Model_DAO_Action::getInstance()->unitTestsClearTable();
//         Social_Model_DAO_Theme::getInstance()->unitTestsClearTable();
//         Social_Model_DAO_ContextAction::getInstance()->unitTestsClearTable();
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
             $this->_genericAction = Social_Model_GenericActionTest::generateObject();
         } catch (Exception $e) {
             $this->fail($e);
         }
     }


    /**
     * test de get / set
     */
     public function testGetSet()
     {
        $nTheme = new Social_Model_Theme();
        $nTheme->setLabel('testGetSet');
        $nTheme->save();

        $nCommentSet = new Social_Model_CommentSet();
        $nCommentSet->save();

        //on test en lui mettant un theme null
        $this->_genericAction->setTheme();
        $t0 = $this->_genericAction->getTheme();
        $this->assertEquals(null, $t0);


        $this->_genericAction->setTheme($nTheme);
        $this->_genericAction->setLabel("testGetSet");
        $this->_genericAction->setDescription("testGetSet");
        $this->_genericAction->setCommentSet($nCommentSet);
        $this->_genericAction->save();

        $t1 = $this->_genericAction->getTheme();
        $t2 = $this->_genericAction->getLabel();
        $t3 = $this->_genericAction->getDescription();
        $t4 = $this->_genericAction->getCommentSet();

        $this->assertSame($nTheme, $t1);
        $this->assertEquals("testGetSet", $t2);
        $this->assertEquals("testGetSet", $t3);
        $this->assertSame($nCommentSet, $t4);

        //on test en lui remettant le meme theme
        $this->_genericAction->setTheme($nTheme);
        $t5 = $this->_genericAction->getTheme();
        $this->assertSame($nTheme, $t5);
     }


    /**
     * Function called after each test
     */
     protected function tearDown()
     {
         try {
             Social_Model_GenericActionTest::deleteObject($this->_genericAction);
         } catch (Exception $e) {
             $this->fail($e);
         }
     }

}


