<?php
/**
 * @author benjamin.bertin
 * @package Algo
 * @subpackage Test
 */

/**
 * Index_FixedSetUpTest
 * @package Algo
 */
//class Index_FixedSetUpTest extends PHPUnit_Framework_TestCase
//{
//    /**
//     * Méthode appelée avant l'appel à la classe de test
//     */
//    public static function setUpBeforeClass()
//    {
//        Algo_Model_DAO_Algo::getInstance()->unitTestsClearTable();
//        Algo_Model_DAO_Numeric::getInstance()->unitTestsClearTable();
//        Algo_Model_Numeric_DAO_Constant::getInstance()->unitTestsClearTable();
//
//        Algo_Model_Index_DAO_Index::getInstance()->unitTestsClearTable();
//        Algo_Model_Index_Index_DAO_Fixed::getInstance()->unitTestsClearTable();
//
//        Classif_Model_DAO_Axis::getInstance()->unitTestsClearTable();
//        Classif_Model_DAO_Member::getInstance()->unitTestsClearTable();
//        Classif_Model_DAO_Version::getInstance()->unitTestsClearTable();
//
//    }

//    /**
//     * Test du constructeur
//     * @return Algo_Model_Index_Fixed $o
//     */
//    function testConstruct()
//    {
//        $algo = Numeric_ConstantTest::generateObjet();
//        $o    = new Algo_Model_Index_Fixed(1, $algo->id);
//        $this->assertTrue($o instanceof Algo_Model_Index_Fixed);
//
//        return $o;
//    }
//
//    /**
//     * Test de la sauvegarde de l'objet.
//     * @depends testConstruct
//     * @param Algo_Model_Index_Fixed $o
//     * @return Algo_Model_Index_Fixed $o
//     */
//    function testSave(Algo_Model_Index_Fixed $o)
//    {
//        $date = new Core_Date();
//
//        $classifVersion = new Classif_Model_Version();
//        $classifVersion->setRef(Core_Tools::generateString());
//        $classifVersion->setLabel('Label_generate_classif_version');
//        $classifVersion->setCreationDate($date->now()->get('YYYY-MM-dd HH:mm:ss'));
//        $classifVersion->save();
//
//        $axis = new Classif_Model_Axis();
//        $axis->setRef(Core_Tools::generateString());
//        $axis->setLabel('test');
//        $axis->setVersion($classifVersion);
//        $axis->save();
//
//        $classifMember = new Classif_Model_Member($axis->getKey());
//        $classifMember->setLabel('test');
//        $classifMember->setRef('refTest');
//        $classifMember->save();
//
//        $o->setClassifMember($classifMember);
//        $o->save();
//
//        $this->assertEquals($o->id, 1);
//        return $o;
//    }
//
//    /**
//     * Test du chargement de l'objet.
//     * @depends testSave
//     * @param Algo_Model_Index_Fixed $o
//     * @return Algo_Model_Index_Fixed $o
//     */
//    function testLoad(Algo_Model_Index_Fixed $o)
//    {
//        $id = $o->id;
//        $indexIndexFixed = Algo_Model_Index_Fixed::load($id);
//        $this->assertTrue($indexIndexFixed instanceof Algo_Model_Index_Fixed);
//        $this->assertEquals($o, $indexIndexFixed);
//        return $o;
//    }
//
//    /**
//     * Test de la suppression de l'objet.
//     * @depends testLoad
//     * @param Algo_Model_Index_Fixed $o
//     * @expectedException Core_Exception_NotFound
//     */
//    function testDelete(Algo_Model_Index_Fixed $o)
//    {
//        $classifMember = $o->getClassifMember();
//        $axis          = $classifMember->getAxis();
//        $version       = $axis->getVersion();
//
//        $id = $o->id;
//        $numeric = $o->getAlgoNumeric();
//        $o->delete();
//
//        $classifMember->delete();
//        $axis->delete();
//
//        $this->assertEquals(null, $o->id);
//
//        $numeric->delete();
//        $version->delete();
//
//        // Doit lever une exception
//        $o = Algo_Model_TextKey_Fixed::load($id);
//    }

//    /**
//     * Méthode appelée à la fin de la classe de test
//     */
//    public static function tearDownAfterClass()
//    {
//        if (! Algo_Model_DAO_Algo::getInstance()->unitTestsIsTableEmpty()) {
//            echo "\nLa table Algo_Algo n'est pas vide après les tests\n";
//        }
//        if (! Algo_Model_DAO_Numeric::getInstance()->unitTestsIsTableEmpty()) {
//            echo "\nLa table Algo_Numeric n'est pas vide après les tests\n";
//        }
//        if (! Algo_Model_Numeric_DAO_Constant::getInstance()->unitTestsIsTableEmpty()) {
//            echo "\nLa table Algo_Numeric_Constant n'est pas vide après les tests\n";
//        }
//        if (! Algo_Model_Index_DAO_Index::getInstance()->unitTestsIsTableEmpty()) {
//            echo "\nLa table Index_Index n'est pas vide après les tests\n";
//        }
//        if (! Algo_Model_Index_Index_DAO_Fixed::getInstance()->unitTestsIsTableEmpty()) {
//            echo "\nLa table Index_Index_Fixed n'est pas vide après les tests\n";
//        }
//        if (! Classif_Model_DAO_Axis::getInstance()->unitTestsIsTableEmpty()) {
//            echo "\nLa table Classif_Axis n'est pas vide après les tests\n";
//        }
//        if (! Classif_Model_DAO_Member::getInstance()->unitTestsIsTableEmpty()) {
//            echo "\nLa table Classif_Member n'est pas vide après les tests\n";
//        }
//        if (! Classif_Model_DAO_Version::getInstance()->unitTestsIsTableEmpty()) {
//            echo "\nLa table Classif_Version n'est pas vide après les tests\n";
//        }
//    }
//}
