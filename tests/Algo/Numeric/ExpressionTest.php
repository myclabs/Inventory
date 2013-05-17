<?php
/**
 * @author hugo.charbonnier
 * @author yoann.croizer
 * @package Algo
 */

/**
 * @package Algo
 */
class Numeric_ExpressionTest
{
    /**
     * Lance les autre classe de tests.
     */
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite();
//        $suite->addTestSuite('Numeric_ExpressionSetUpTest');
//        $suite->addTestSuite('Numeric_ExpressionLogiqueMetierTest');
        return $suite;
    }//end suite()

    /**
     * Permet de générer un objet de base sur lequel on pourra travailler
     */
    public static function generateExpression()
    {
        $o = new TEC_Model_Expression('algo1+algo2');
        $o->type = TEC_Model_Expression::TYPE_NUMERIC;
        $o->save();

        return $o;
    }//end generateExpression()

    /**
     * Permet de générer un objet de base sur lequel on pourra travailler
     */
    public static function generateObjet()
    {
        $expression = self::generateExpression();

        $o = new Algo_Model_Numeric_Expression();
        $o->ref = 'NumericExpression';
        $o->unit = new Unit_Model_APIUnit('test');
        $o->setExpression($expression);
        $o->setClassifIndicator("1");
        $o->setLabel('labelNumericExpression');
        $o->save();

        return $o;
    }//end generateObject()

    /**
     * Supprime un objet utilisé dans les tests
     * @param Algo_Model_Numeric_Expression $o
     */
    public static function deleteObject(Algo_Model_Numeric_Expression $o)
    {
        $expression = TEC_Model_Expression::getMapper()->load($o->getExpression());
        $expression->delete();
        $o->delete();
    }

}//end class Numeric_ExpressionTest

/**
 * expressionSetUpTest
 * @package Algo
 */
class Numeric_ExpressionSetUpTest extends PHPUnit_Framework_TestCase
{
    protected $_expression;
    /**
     * Méthode appelée avant l'appel à la classe de test
     */
    public static function setUpBeforeClass()
    {
        Algo_Model_DAO_Algo::getInstance()->unitTestsClearTable();
        Algo_Model_DAO_Numeric::getInstance()->unitTestsClearTable();
        Algo_Model_Numeric_DAO_Expression::getInstance()->unitTestsClearTable();
        Algo_Model_Numeric_DAO_Constant::getInstance()->unitTestsClearTable();

        TEC_Model_DAO_Expression::getInstance()->unitTestsClearTable();
        TEC_Model_DAO_Leaf::getInstance()->unitTestsClearTable();
        TEC_Model_DAO_Composite::getInstance()->unitTestsClearTable();
    }

    /**
     * Méthode appelée avant chaque méthode de test.
     */
    function setUp()
    {
        $this->_expression = Numeric_ExpressionTest::generateExpression();
    }

    /**
     * Enter description here ...
     */
    function testConstruct()
    {
        $o = new Algo_Model_Numeric_Expression();
        $this->assertTrue($o instanceof Algo_Model_Numeric_Expression);
        $this->assertEquals('Algo_Model_Numeric_Expression', $o->type);
    }

    /**
     * Test de la sauvegarde en bdd
     * @return Algo_Model_Numeric_Constant $o
     */
    function testSave()
    {
      // Test de l'insertion
        $o = new Algo_Model_Numeric_Expression();
        $o->ref = 'ExpressionSave';
        $o->setExpression($this->_expression);
        $o->setClassifIndicator("1");
        $o->setLabel("labelExpressionSave");

       // Test de l'erreur lors de la sauvegarde d'un Numeric_Expression sans unité.
        try {
            $o->save();
        } catch (Core_Exception_InvalidArgument $e) {
            $this->assertEquals(null, $o->id);
        }

        // Test de l'erreur lors de la sauvegarde d'un Numeric_Expression avec une unité incorrecte.
        try {
            $o->unit = 'test';
            $o->save();
        } catch (Core_Exception_InvalidArgument $e) {
            $this->assertEquals(null, $o->id);
        }

        // Test de l'erreur lors de la sauvegarde d'un Numeric_Expression avec une unité incorrecte.
        try {
            $a = new Algo_Model_Numeric_Expression();
            $a->ref = 'ExpressionSave';
            $a->setClassifIndicator("1");
            $a->unit = new Unit_Model_APIUnit('test');
            $a->setLabel("labelExpressionSave");
            $a->save();
        } catch (Core_Exception_InvalidArgument $e) {
            $this->assertEquals(null, $o->id);
        }

        $o->unit = new Unit_Model_APIUnit('test');
        $o->save();

        $firstId = $o->id;
        $firstRef = $o->ref;

        $this->assertTrue($firstId > 0);

        // Test de l'update
        $o->ref = 'ExpressionUpdate';
        $o->save();

        $secondId = $o->id;
        $secondRef = $o->ref;

        $this->assertTrue($firstId === $secondId && $firstRef !== $secondRef);
        $this->assertTrue($secondRef === 'ExpressionUpdate');

        return $o;
    }


    /**
     * @depends testSave
     * @param Algo_Model_Numeric_Expression $o
     * @expectedException Core_Exception_NotFound
     */
    function testLoad(Algo_Model_Numeric_Expression $o)
    {
        $a = Algo_Model_Numeric_Expression::getMapper()->load($o->id);
        $this->assertEquals($a, $o);

        // Test erreur de chargement
        $b = Algo_Model_Numeric_Expression::getMapper()->load(0);
    }

    /**
     * @depends testSave
     * @param Algo_Model_Numeric_Expression $o
     * @expectedException Core_Exception_Systeme
     */
    function testDelete(Algo_Model_Numeric_Expression $o)
    {
        $o->delete();
        $this->assertEquals(null, $o->id);
        // Test de l'exception.
        $o->delete();
    }


    /**
     * Méthode appelée à la fin de chaque test
     */
    protected function tearDown()
    {
        $this->_expression->delete();
    }


    /**
     * Méthode appelée à la fin de la classe de test
     */
    public static function tearDownAfterClass()
    {
        // Check tables are empty
        if (! Algo_Model_DAO_Algo::getInstance()->unitTestsIsTableEmpty()) {
            echo "\nLa table Algo_Algo n'est pas vide après les tests\n";
        }
        if (! Algo_Model_DAO_Numeric::getInstance()->unitTestsIsTableEmpty()) {
            echo "\nLa table Algo_Numeric n'est pas vide après les tests\n";
        }
        if (! Algo_Model_Numeric_DAO_Expression::getInstance()->unitTestsIsTableEmpty()) {
            echo "\nLa table Algo_Numeric_Expression n'est pas vide après les tests\n";
        }

        // Tables de TEC
        if (! TEC_Model_DAO_Expression::getInstance()->unitTestsIsTableEmpty()) {
            echo "\nLa table Expression n'est pas vide après les tests\n";
        }
        if (! TEC_Model_DAO_Leaf::getInstance()->unitTestsIsTableEmpty()) {
            echo "\nLa table Leaf n'est pas vide après les tests\n";
        }
        if (! TEC_Model_DAO_Composite::getInstance()->unitTestsIsTableEmpty()) {
            echo "\nLa table Composite n'est pas vide après les tests\n";
        }
    }

}//end class Numeric_ExpressionSetUpTest

/**
 * Numeric_ExpressionLogiqueMetierTest
 * @package Algo
 */
class Numeric_ExpressionLogiqueMetierTest extends PHPUnit_Framework_TestCase
{
    protected $_algoSet;

    protected $_expressionNumeric;

    protected $_numericConstant;
    protected $_numericNumericInput;

    protected $_input;

    protected $_grandeurPhysiqueMasse;
    protected $_uniteRefMasse;
    protected $_unite1;
    protected $_systemeUnite;

    /**
     * Méthode appelée avant l'appel à la classe de test
     */
    public static function setUpBeforeClass()
    {
         Algo_Model_DAO_Set::getInstance()->unitTestsClearTable();

         Algo_Model_DAO_Algo::getInstance()->unitTestsClearTable();
         Algo_Model_DAO_Numeric::getInstance()->unitTestsClearTable();
         Algo_Model_Numeric_DAO_Expression::getInstance()->unitTestsClearTable();
         Algo_Model_Numeric_DAO_NumericInput::getInstance()->unitTestsClearTable();
         Algo_Model_Numeric_DAO_Constant::getInstance()->unitTestsClearTable();

         TEC_Model_DAO_Expression::getInstance()->unitTestsClearTable();
         TEC_Model_DAO_Leaf::getInstance()->unitTestsClearTable();
         TEC_Model_DAO_Composite::getInstance()->unitTestsClearTable();

         Unit_Model_DAO_GrandeurPhysique::getInstance()->unitTestsClearTable();
         Unit_Model_PhysicalQuantity_DAO_ComposantGrandeurPhysique::getInstance()->unitTestsClearTable();
         Unit_Model_Unit_DAO_SystemeUnite::getInstance()->unitTestsClearTable();
         Unit_Model_Unit_DAO_UniteStandard::getInstance()->unitTestsClearTable();
         Unit_Model_DAO_Unit::getInstance()->unitTestsClearTable();

         Classif_Model_DAO_Context::getInstance()->unitTestsClearTable();
         Classif_Model_DAO_Version::getInstance()->unitTestsClearTable();
    }

    /**
     * Méthode appelée avant chaque méthode de test.
     */
    function setUp()
    {
        $this->_algoSet = new Algo_Model_Set();
        $version = new Classif_Model_Version();
        $version->setLabel('test');
        $version->setRef('testRef');
        $version->setCreationDate('2012-04-24 09:41:36');
        $version->save();
        $context = new Classif_Model_Context();
        $context->setRef('test');
        $context->setLabel('test');
        $context->setVersion($version);
        $context->save();
        $this->_algoSet->setClassifContext($context);
        $this->_algoSet->setClassifVersion($version);
        $this->_algoSet->save();

        $value2 = new Calc_Value();
        $value2->digitalValue = 1;
        $value2->relativeUncertainty = 0.1;

        $unit2 = new Unit_Model_APIUnit('g');

        $unitValue2 = new Calc_UnitValue();
        $unitValue2->value = $value2;
        $unitValue2->unit = $unit2;

        $this->_input = array(
            'NumericInput' => $unitValue2
        );

        // Géneration de l'algorythme de resultType NumericExpression.
        $this->_expressionNumeric = Numeric_ExpressionTest::generateObjet();
        $this->_expressionNumeric->setSet($this->_algoSet);
        $this->_expressionNumeric->save();

        // Géneration des algorythme "feuilles" de l'algo ci dessus.

        $value = new Calc_Value();
        $value->digitalValue = 2;
        $value->relativeUncertainty = 0.1;

        $unit = new Unit_Model_APIUnit('g');

        $unitValue = new Calc_UnitValue();
        $unitValue->value = $value;
        $unitValue->unit = $unit;

        $this->_numericConstant = new Algo_Model_Numeric_Constant();
        $this->_numericConstant->ref = 'algo1';
        $this->_numericConstant->unitValue = $unitValue;
        $this->_numericConstant->setClassifIndicator('1');
        $this->_numericConstant->setSet($this->_algoSet);
        $this->_numericConstant->setLabel('labelAlgo1');
        $this->_numericConstant->save();

        $this->_numericNumericInput = new Algo_Model_Numeric_Input();
        $this->_numericNumericInput->ref = 'algo2';
        $this->_numericNumericInput->unit = new Unit_Model_APIUnit('kg');
        $this->_numericNumericInput->inputRef = 'NumericInput';
        $this->_numericNumericInput->setClassifIndicator('1');
        $this->_numericNumericInput->setSet($this->_algoSet);
        $this->_numericNumericInput->setLabel('labelAlgo2');
        $this->_numericNumericInput->save();

        $this->_algoSet->addAlgo($this->_expressionNumeric);
        $this->_algoSet->addAlgo($this->_numericConstant);
        $this->_algoSet->addAlgo($this->_numericNumericInput);
        $this->_algoSet->save();

        // On créer l'unité ayant pour reference 'g' car elle sera utilisée dans le execute.

        //On créer la grandeurs physiques de base.
        $this->_grandeurPhysiqueMasse = new Unit_Model_GrandeurPhysique();
        $this->_grandeurPhysiqueMasse->nom = "masse";
        $this->_grandeurPhysiqueMasse->ref = "m";
        $this->_grandeurPhysiqueMasse->symbole = "M";
        $this->_grandeurPhysiqueMasse->isBase = "1";
        $this->_grandeurPhysiqueMasse->save();

        //On créer un système d'unité (obligatoire pour une unité standard).
        $this->_systemeUnite = new Unit_Model_Unit_SystemeUnite();
        $this->_systemeUnite->ref = "international";
        $this->_systemeUnite->nom = "International";
        $this->_systemeUnite->save();

        //On créer les unités de références des grandeurs physique de base
        $this->_uniteRefMasse = new Unit_Model_Unit_UniteStandard();
        $this->_uniteRefMasse->coeffMultiplicateur = 1;
        $this->_uniteRefMasse->nom = "kilogramme";
        $this->_uniteRefMasse->symbole = "kg";
        $this->_uniteRefMasse->ref = "kg";
        $this->_uniteRefMasse->setGrandeurPhysique($this->_grandeurPhysiqueMasse);
        $this->_uniteRefMasse->setSystemeUnite($this->_systemeUnite);
        $this->_uniteRefMasse->save();

        $this->_unite1 = new Unit_Model_Unit_UniteStandard();
        $this->_unite1->coeffMultiplicateur = 0.001;
        $this->_unite1->nom = "gramme";
        $this->_unite1->symbole = "g";
        $this->_unite1->ref = "g";
        $this->_unite1->setGrandeurPhysique($this->_grandeurPhysiqueMasse);
        $this->_unite1->setSystemeUnite($this->_systemeUnite);
        $this->_unite1->save();


        $this->_grandeurPhysiqueMasse->setCompositionGrandeurPhysique($this->_grandeurPhysiqueMasse, 1);
        $this->_grandeurPhysiqueMasse->setUniteReference($this->_uniteRefMasse);
        $this->_grandeurPhysiqueMasse->save();
    }

    /**
     * Test de la méthode execute()
     */
    function testExecute()
    {
        // @todo Verifier apres ce test que le context et la version de classif soient bien supprimé
        $result = $this->_expressionNumeric->execute($this->_input);

        $this->assertTrue($result instanceof Calc_UnitValue);
        $this->assertTrue($result->unit instanceof Unit_Model_APIUnit);
        $this->assertTrue($result->value instanceof Calc_Value);

        $this->assertEquals('kg', $result->unit->ref);
        $this->assertEquals(0.003, $result->value->digitalValue);
    }

    /**
     * Méthode appelée à la fin de chaque test
     */
    protected function tearDown()
    {
        $this->_systemeUnite->delete();
        Numeric_ExpressionTest::deleteObject($this->_expressionNumeric);
//
        // On supprime les algorythme "feuilles".
        $this->_numericConstant->delete();
        $this->_numericNumericInput->delete();

        $this->_algoSet->delete();

        // On supprime les unités et grandeurs physiques utilisées.
        $this->_unite1->delete();
        $this->_uniteRefMasse->delete();
        $this->_grandeurPhysiqueMasse->delete();
    }

    /**
     * Méthode appelée à la fin de la classe de test
     */
    public static function tearDownAfterClass()
    {
        if (! Algo_Model_DAO_Set::getInstance()->unitTestsIsTableEmpty()) {
            echo "\nLa table Algo_Set n'est pas vide après les tests\n";
        }
        // Tables de algo
        if (! Algo_Model_DAO_Algo::getInstance()->unitTestsIsTableEmpty()) {
            echo "\nLa table Algo_Algo n'est pas vide après les tests\n";
        }
        if (! Algo_Model_DAO_Numeric::getInstance()->unitTestsIsTableEmpty()) {
            echo "\nLa table Algo_Numeric n'est pas vide après les tests\n";
        }
        if (! Algo_Model_Numeric_DAO_Expression::getInstance()->unitTestsIsTableEmpty()) {
            echo "\nLa table Algo_Numeric_Expression n'est pas vide après les tests\n";
        }
        if (! Algo_Model_Numeric_DAO_Constant::getInstance()->unitTestsIsTableEmpty()) {
            echo "\nLa table Algo_Numeric_Constant n'est pas vide après les tests\n";
        }
        if (! Algo_Model_Numeric_DAO_NumericInput::getInstance()->unitTestsIsTableEmpty()) {
            echo "\nLa table Algo_Numeric_NumericInput n'est pas vide après les tests\n";
        }

        // Tables de TEC
        if (! TEC_Model_DAO_Expression::getInstance()->unitTestsIsTableEmpty()) {
            echo "\nLa table Expression n'est pas vide après les tests\n";
        }
        if (! TEC_Model_DAO_Leaf::getInstance()->unitTestsIsTableEmpty()) {
            echo "\nLa table Leaf n'est pas vide après les tests\n";
        }
        if (! TEC_Model_DAO_Composite::getInstance()->unitTestsIsTableEmpty()) {
            echo "\nLa table Composite n'est pas vide après les tests\n";
        }

        // Tables de Unit
        if (! Unit_Model_DAO_GrandeurPhysique::getInstance()->unitTestsIsTableEmpty()) {
            echo "\nLa table GrandeurPhysique n'est pas vide après les tests\n";
        }
        if (! Unit_Model_PhysicalQuantity_DAO_ComposantGrandeurPhysique::getInstance()->unitTestsIsTableEmpty()) {
            echo "\nLa table ComposantGrandeurPhysique n'est pas vide après les tests\n";
        }
        if (! Unit_Model_Unit_DAO_SystemeUnite::getInstance()->unitTestsIsTableEmpty()) {
            echo "\nLa table SystemeUnite n'est pas vide après les tests\n";
        }
        if (! Unit_Model_Unit_DAO_UniteStandard::getInstance()->unitTestsIsTableEmpty()) {
            echo "\nLa table UniteStandard n'est pas vide après les tests\n";
        }
        if (! Unit_Model_DAO_Unit::getInstance()->unitTestsIsTableEmpty()) {
            echo "\nLa table Unit n'est pas vide après les tests\n";
        }
    }

}//end class Numeric_ExpressionLogiqueMetierTest
