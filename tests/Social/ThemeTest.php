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
class Social_Model_ThemeTest
{

    /**
     * Creation of the test suite
     * @return PHPUnit_Framework_TestSuite
     */
     public static function suite()
     {
         $suite = new PHPUnit_Framework_TestSuite();
         $suite->addTestSuite('Social_Model_ThemeSetUpTest');
         $suite->addTestSuite('Social_Model_ThemeOtherTest');
         return $suite;
     }

    /**
     * Generation of a test object
     * @return Social_Model_Theme
     */
     public static function generateObject()
     {
         $o = new Social_Model_Theme();
         $o->setLabel('test');
         $o->save();
         return $o;
     }

    /**
     * Deletion of an object created with generateObject
     * @param Social_Model_Theme $o
     */
     public static function deleteObject(Social_Model_Theme $o)
     {
         $o->delete();
     }

}


/**
 * Test of the creation/modification/deletion of the entity
 * @package Social
 * @subpackage Test
 */
class Social_Model_ThemeSetUpTest extends PHPUnit_Framework_TestCase
{

     public static function setUpBeforeClass()
     {
     // Empty related tables
//         Social_Model_DAO_GenericAction::getInstance()->unitTestsClearTable();
//         Social_Model_DAO_Action::getInstance()->unitTestsClearTable();
//         Social_Model_DAO_Theme::getInstance()->unitTestsClearTable();
     }

    /**
     * @return Social_Model_Theme
     */
     function testConstruct()
     {
         $this->markTestIncomplete("TODO");
         $o = new Social_Model_Theme();
         $o->setLabel('testConstruct');
         $o->save();
         return $o;
     }

    /**
     * Save test
     * @param Social_Model_Theme $o
     * @depends testConstruct
     */
     function testSave(Social_Model_Theme $o)
     {
         $o->save();
         $this->assertNotNull($o->getKey(), "Object id is not defined");

         return $o;
     }


    /**
     * Test of load
     * @param Social_Model_Theme $o
     * @depends testSave
     */
     function testLoad(Social_Model_Theme $o)
     {
         $a = Social_Model_Theme::load($o->getKey());
         $this->assertTrue($a instanceof Social_Model_Theme);
         $this->assertSame($a, $o);
     }

    /**
     * Deletion test
     * @param Social_Model_Theme $o
     * @depends testSave
     */
     function testDelete(Social_Model_Theme $o)
     {
         $o->delete();
         $this->assertNull($o->getKey());
     }

}


/**
 * Tests of Social_Model_Theme class
 * @package Social
 * @subpackage Test
 */
class Social_Model_ThemeOtherTest extends PHPUnit_Framework_TestCase
{

     // Test objects
     protected $_theme;

    /**
     * Function called once, before all the tests
     */
     public static function setUpBeforeClass()
     {
         // Empty related tables
//         Social_Model_DAO_GenericAction::getInstance()->unitTestsClearTable();
//         Social_Model_DAO_Action::getInstance()->unitTestsClearTable();
//         Social_Model_DAO_Theme::getInstance()->unitTestsClearTable();
     }

    /**
     * Function called before each test
     */
     protected function setUp()
     {
         $this->markTestIncomplete("TODO");
         try {
             // Create a test object
             $this->_theme = Social_Model_ThemeTest::generateObject();
         } catch (Exception $e) {
             $this->fail($e);
         }
     }


    /**
     * test de get / set
     */
     public function testGetSet()
     {
        $this->_theme->setLabel('TestGetSet');
        $this->_theme->save();

        $t1 = $this->_theme->getLabel();

        $this->assertEquals('TestGetSet', $t1);
     }


     /**
      * test d'ajout de GenericAction
      */
     public function testAddGenericAction()
     {
        //Test si le tableau de genericAction est vide
        $this->assertEquals(count($this->_theme->getGenericActions()), 0);

        $genericAction = new Social_Model_GenericAction();
        $genericAction->setLabel("testAddGenericAction");
        $genericAction->save();

        $genericAction1 = new Social_Model_GenericAction();
        $genericAction1->setLabel("testAddGenericAction1");
        $genericAction1->save();


        $this->_theme->addGenericAction($genericAction);
        $this->_theme->addGenericAction($genericAction1);
        $this->_theme->save();

        //Test l'ajout
        $this->assertEquals(count($this->_theme->getGenericActions()), 2);
        $this->assertTrue($this->_theme->hasGenericAction($genericAction));
        $this->assertTrue($this->_theme->hasGenericAction($genericAction1));

        // Test l'ajout d'un élément deja associé
        $this->_theme->addGenericAction($genericAction);
        $this->_theme->save();

        $this->assertEquals(count($this->_theme->getGenericActions()), 2);

        $genericAction->delete();
        $genericAction1->delete();

     }

     /**
      * test de récupération d'un GenericAction
      *
      */
     public function testGetGenericActions() {
        //Création des genericActions
        $genericAction = new Social_Model_GenericAction();
        $genericAction->setLabel("testGetGenericActions");
        $genericAction->save();

        $genericAction1 = new Social_Model_GenericAction();
        $genericAction1->setLabel("testGetGenericActions");
        $genericAction1->save();

        //On les associe au theme et on teste
        $this->_theme->addGenericAction($genericAction);
        $this->_theme->addGenericAction($genericAction1);
        $this->_theme->save();

        $this->assertEquals(count($this->_theme->getGenericActions()), 2);

        //on associe une genericAction déja associé
        $this->_theme->addGenericAction($genericAction);
        $this->assertEquals(count($this->_theme->getGenericActions()), 2);

        //on réassocie une genericAction avec le status deleted.
        $this->_theme->removeGenericAction($genericAction);
        $this->_theme->addGenericAction($genericAction);
        $this->assertEquals(count($this->_theme->getGenericActions()), 2);

        foreach ($this->_theme->getGenericActions() as $tempGenericAction) {
            $this->assertTrue($this->_theme->hasGenericAction($tempGenericAction));
        }

        $genericAction->delete();
        $genericAction1->delete();
     }

     /**
      * Test la présence d'un GenericAction
      *
      */
     public function testHasGenericAction() {
        $genericAction = new Social_Model_GenericAction();
        $genericAction->setLabel("testHasGenericAction");
        $genericAction->setTheme($this->_theme);
        $genericAction->save();
        $this->_theme = $genericAction->getTheme();

        $this->assertTrue($this->_theme->hasGenericAction($genericAction));

        $this->_theme->addGenericAction($genericAction);

        $this->_theme->save();

        $this->assertTrue($this->_theme->hasGenericAction($genericAction));

        $genericAction->delete();
     }


    /**
     * test la suppression d'un GenericAction
     * @depends testHasGenericAction
     */
     public function testRemoveGenericAction()
     {
       //création des GenericActions
        $genericAction = new Social_Model_GenericAction();
        $genericAction->setLabel("testRemoveGenericAction");
        $genericAction->save();

        $genericAction1 = new Social_Model_GenericAction();
        $genericAction1->setLabel("testRemoveGenericAction");
        $genericAction1->save();

        $genericAction2 = new Social_Model_GenericAction();
        $genericAction2->setLabel("testRemoveGenericAction");
        $genericAction2->save();

        $this->_theme->addGenericAction($genericAction);
        $this->_theme->addGenericAction($genericAction1);
        $this->_theme->save();

        //Test si le theme contient les deux genericActions
        $this->assertEquals(count($this->_theme->getGenericActions()), 2);

        //Test de suppresion des GenericActions
        $this->_theme->removeGenericAction($genericAction);
        $this->_theme->save();
        $this->assertEquals(count($this->_theme->getGenericActions()), 1);

        $this->_theme->removeGenericAction($genericAction1);
        $this->_theme->save();
        $this->assertEquals(count($this->_theme->getGenericActions()), 0);

        //Test la suppression d'un genericAction sans l'avoir enregistrer  dans le theme en base de donnée
        $this->_theme->addGenericAction($genericAction2);
        $this->assertEquals(count($this->_theme->getGenericActions()), 1);
        $this->_theme->removeGenericAction($genericAction2);
        $this->assertEquals(count($this->_theme->getGenericActions()), 0);

        $genericAction->delete();
        $genericAction1->delete();
        $genericAction2->delete();
     }


    /**
     * Function called after each test
     */
     protected function tearDown()
     {
         try {
             // Delete the test object
             Social_Model_ThemeTest::deleteObject($this->_theme);
         } catch (Exception $e) {
             $this->fail($e);
         }
     }

}
