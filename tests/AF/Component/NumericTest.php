<?php
/**
 * @author  matthieu.napoli
 * @author  hugo.charbonnier
 * @author  thibaud.rolland
 * @package AF
 */

/**
 * @package AF
 */
class Form_NumericTest
{

    /**
     * Creation of the test suite.
     */
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite();
        $suite->addTestSuite('Form_NumericSetUpTest');
        //        $suite->addTestSuite('Form_NumericOtherTest');
        return $suite;
    }

    /**
     * Generation of a test object.
     * @return AF_Model_Component_Numeric
     */
    public static function generateObject()
    {
        $o = new AF_Model_Component_Numeric();
        $value = new Calc_Value();
        $value->relativeUncertainty = 1;
        $value->digitalValue = 1;
        $o->setDefaultValue($value);
        $o->setRef('save');
        $o->setLabel('label');
        $o->setHelp('help');
        $o->setUnit(new Unit_API("kg"));
        $o->save();
        return $o;
    }

    /**
     * Deletion of an object created with generateObject.
     * @param AF_Model_Component_Numeric $o
     */
    public static function deleteObject($o)
    {
        $o->delete();
    }

}

/**
 * Test of the creation/modification/deletion of the entity.
 * @package AF
 */
class Form_NumericSetUpTest extends PHPUnit_Framework_TestCase
{

    /**
     * @return AF_Model_Component_Numeric
     */
    function testConstruct()
    {
        $value = new Calc_Value();
        $value->relativeUncertainty = 1;
        $value->digitalValue = 1;

        $o = new AF_Model_Component_Numeric();
        $o->setRef('save');
        $o->setDefaultValue($value);
        $o->setLabel('label');
        $o->setHelp('help');
        $o->setUnit(new Unit_API("kg"));
        $this->assertEquals("save", $o->getRef());

        return $o;
    }

    /**
     * @depends testConstruct
     * @param AF_Model_Component_Numeric $o
     * @return AF_Model_Component_Numeric
     */
    function testLoad(AF_Model_Component_Numeric $o)
    {
        return $o;
    }

    /**
     * @depends testLoad
     * @param AF_Model_Component_Numeric $o
     */
    function testDelete(AF_Model_Component_Numeric $o)
    {
    }

}

/**
 * @package AF
 */
class Form_NumericOtherTest extends PHPUnit_Framework_TestCase
{

    protected $_numeric;

    /**
     * Function called once, before all the tests.
     */
    public static function setUpBeforeClass()
    {
        // Empty related tables
        AF_Model_DAO_Version::getInstance()->unitTestsClearTable();
        AF_Model_DAO_Branch::getInstance()->unitTestsClearTable();
        Classif_Model_DAO_Version::getInstance()->unitTestsClearTable();
        Classif_Model_DAO_Context::getInstance()->unitTestsClearTable();

        AF_Model_DAO_AF::getInstance()->unitTestsClearTable();
        AF_Model_Form_DAO_Component::getInstance()->unitTestsClearTable();
        AF_Model_Form_DAO_Element::getInstance()->unitTestsClearTable();
        AF_Model_Form_DAO_Group::getInstance()->unitTestsClearTable();
        AF_Model_Form_Element_DAO_Numeric::getInstance()->unitTestsClearTable();
        AF_Model_DAO_AF::getInstance()->unitTestsClearTable();
    }

    /**
     * Function called before each test.
     */
    protected function setUp()
    {
        try {
            // Create a test object
            $this->_numeric = Form_NumericTest::generateObject();
        } catch (Exception $e) {
            $this->fail($e);
        }
    }

    // @todo il faut créer une unité pour que le generate fonctionne
    //     /**
    //      * Test of generate.
    //      */
    //      function testGenerate()
    //      {
    //         $af = AFTest::generateObject();
    //         $this->assertTrue($this->_numeric->generate($af) instanceof UI_Form_Element_Pattern_Value);
    //         AFTest::deleteObject($af);
    //      }

    /**
     * Test of setValue et getValue.
     */
    function testSetGetValue()
    {
        $value = new Calc_Value();
        $value->relativeUncertainty = 1;
        $value->digitalValue = 1;

        $this->_numeric->setValue($value);
        $this->assertEquals($value, $this->_numeric->getValue());
    }

    /**
     * Function called after each test.
     */
    protected function tearDown()
    {
        try {
            // Delete the test object
            Form_NumericTest::deleteObject($this->_numeric);
        } catch (Exception $e) {
            $this->fail($e);
        }
    }

    /**
     * Function called once, after all the tests.
     */
    public static function tearDownAfterClass()
    {
        // Check tables are empty
        if (!AF_Model_Form_Element_DAO_Numeric::getInstance()->unitTestsIsTableEmpty()) {
            echo "\nLa table Numeric n'est pas vide après les tests de la classe NumericInput\n";
        }
        if (!AF_Model_Form_DAO_Element::getInstance()->unitTestsIsTableEmpty()) {
            echo "\nLa table Element n'est pas vide après les tests de la classe NumericInput\n";
        }
        if (!AF_Model_Form_DAO_Component::getInstance()->unitTestsIsTableEmpty()) {
            echo "\nLa table Component n'est pas vide après les tests de la classe NumericInput\n";
        }
        if (!AF_Model_DAO_AF::getInstance()->unitTestsIsTableEmpty()) {
            echo "\nLa table AF n'est pas vide après les tests de la classe NumericInput\n";
        }
    }
    //end tearDownAfterClass()

}//end class Form_NumericInputOtherTest
