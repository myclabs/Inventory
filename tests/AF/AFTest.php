<?php
/**
 * @author matthieu.napoli
 * @author hugo.charbonnier
 * @author thibaud.rolland
 * @author yoann.croizer
 * @package AF
 */

/**
 * @package Algo
 */
class AFTest
{
    public static $numContextLabel = 1;

    /**
     * Lance les autre classe de tests.
     */
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite();
        $suite->addTestSuite('AFSetUpTest');
        return $suite;
    }

    /**
     * Permet de générer un objet de base sur lequel on pourra travailler
     * @return AF_Model_AF
     */
    public static function generateObject()
    {
        $group = Form_GroupTest::generateObject();

        $context = new Classif_Model_Context();
        $context->setLabel('testlabel'.self::$numContextLabel);
        self::$numContextLabel++;
        $context->save();

        $o = new AF_Model_AF();
        $o->setRef('test');
        $o->setContext($context);
        $o->setRootGroup($group);
        $o->save();
        return ($o);
    }


    /**
     * Supprime un objet utilisé dans les tests
     * @param AF_Model_AF $o
     */
    public static function deleteObject(AF_Model_AF $o)
    {
        // On ne peut pas supprimer un AF possédant des inputs donc on les supprimes
        foreach ($o->getInputSets() as $inputSet ) {
            $inputSet->delete();
        }
        $o->delete();
    }

}

/**
 * AFSetUpTest
 * @package Algo
 */
class AFSetUpTest extends PHPUnit_Framework_TestCase
{

    /**
     * Test du constructeur de AF
     * @return AF_Model_AF
     */
    function testConstruct()
    {
        $o = new AF_Model_AF(strtolower(Core_Tools::generateString(20)));
        $this->assertTrue($o instanceof AF_Model_AF);
        return $o;
    }

    /**
     * @depends testConstruct
     * @param AF_Model_AF $o
     * @return AF_Model_AF
     */
    function testLoad(AF_Model_AF $o)
    {
        $this->assertTrue($o instanceof AF_Model_AF);
        return $o;
    }

    /**
     * @depends testLoad
     * @param AF_Model_AF $o
     */
    function testDelete(AF_Model_AF $o)
    {
        $this->assertTrue($o instanceof AF_Model_AF);
    }

}
