<?php
/**
 * @author yoann.croizer
 * @author hugo.charbonnier
 * @package Algo
 */

/**
 * @package Algo
 */
class MainTest
{
    /**
     * Lance les autre classe de tests.
     */
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite();
//        $suite->addTestSuite('MainSetUpTest');
//        $suite->addTestSuite('MainLogiqueMetierTest');
        return $suite;
    }

    /**
     * Permet de générer un objet de base sur lequel on pourra travailler
     */
    public static function generateObjet()
    {

    }

    /**
     * Permet de générer un objet de base sur lequel on pourra travailler
     */
    public static function generateExpression()
    {
        $o = new TEC_Model_Expression('algo1+algo2');
        $o->type = TEC_Model_Expression::TYPE_NUMERIC;
        $o->save();

        return $o;
    }
}//end class MainTest

/**
 * MainSetUpTest
 * @package Algo
 */
class MainSetUpTest extends PHPUnit_Framework_TestCase
{
    // Attributs protégés.
    protected $_expression;

    /**
     * Méthode appelée avant l'appel à la classe de test
     */
    public static function setUpBeforeClass()
    {
        Algo_Model_DAO_Algo::getInstance()->unitTestsClearTable();
        Algo_Model_DAO_Execution::getInstance()->unitTestsClearTable();

        TEC_Model_DAO_Expression::getInstance()->unitTestsClearTable();
    }// end setUpBeforeClass()

    /**
     * Méthode appelée avant chaque méthode de test.
     */
    function setUp()
    {

    }//end setUp()


    /**
     * Test du constructeur
     * @return Algo_Model_Selection_Main $o
     */
    function testConstruct()
    {
        $o = new Algo_Model_Selection_Main();
        $this->assertTrue($o instanceof Algo_Model_Selection_Main);

        return $o;
    }// end testConstruct()


    /**
     * Test de la fonction save()
     * @depends testConstruct
     * @param Algo_Model_Selection_Main $o
     * @return int $id
     */
    function testSave(Algo_Model_Selection_Main $o)
    {

        $expression = new TEC_Model_Expression('algo1+algo2');
        $expression->type = TEC_Model_Expression::TYPE_NUMERIC;
        $expression->save();

        $o->ref = 'test';
        $o->setExpression($expression);
        $id = $o->save();
        $this->assertNotNull($o->id, 'Object id is not defined');

        return $o;
    }// end testSave()

    /**
     * Test de la fonction load
     * @depends testSave
     * @param Algo_Model_Selection_Main $o
     * @return Algo_Model_Selection_Main $o
     */
    function testLoad($o)
    {
        $id = $o->id;
        $o = Algo_Model_Selection_Main::load($id);
        $this->assertTrue($o instanceof Algo_Model_Selection_Main);
        $this->assertEquals($id, $o->id);

        return $o;
    }// end testLoad()


    /**
     * Test de la fonction delete
     * @depends testLoad
     * @param Algo_Model_Selection_Main $o
     * @expectedException Core_Exception_NotFound
     */
    function testDelete(Algo_Model_Selection_Main $o)
    {
        $expression = TEC_Model_Expression::load($o->getExpression());
        $expression->delete();

        $id = $o->id;
        $o->delete();
        $this->assertEquals(null, $o->id);
        // Test si le load échoue
        Algo_Model_Selection_Main::load($id);
    }// end testDelete()

    /**
     * Méthode appelée à la fin de chaque test
     */
    protected function tearDown()
    {

    }// end tearDown()

    /**
     * Méthode appelée à la fin de la classe de test
     */
    public static function tearDownAfterClass()
    {
        if (! Algo_Model_DAO_Algo::getInstance()->unitTestsIsTableEmpty()) {
            echo "\nLa table Algo n'est pas vide après les tests\n";
        }
        if (! Algo_Model_DAO_Execution::getInstance()->unitTestsIsTableEmpty() ) {
            echo "\nLa table Execution n'est pas vide après les tests\n";
        }
        if (! TEC_Model_DAO_Expression::getInstance()->unitTestsIsTableEmpty() ) {
            echo "\nLa table Expression n'est pas vide après les tests\n";
        }
    }// end tearDownAfterClass

}//end class MainSetUpTest

/**
 * MainLogiqueMetierTest
 * @package Algo
 */
class MainLogiqueMetierTest extends PHPUnit_Framework_TestCase
{
    protected $_systemeUnite;
    protected $_grandeurPhysiqueLongueur;
    protected $_uniteRefLongueur;

    protected $_calcValue1;
    protected $_calcValue2;
    protected $_calcValue3;
    protected $_unitValue1;
    protected $_unitValue2;
    protected $_unitValue3;

    protected $_algoSet;

    protected $_expressionSelectMulti;
    protected $_algoExecMulti;

    protected $_expressionSelectSimple;
    protected $_algoExecSimple;

    protected $_expressionCalculMulti1;
    protected $_algoCalculMulti1;

    protected $_expressionCalculMulti2;
    protected $_algoCalculMulti2;

    protected $_algoNumeric1;
    protected $_algoNumeric2;
    protected $_algoNumeric3;
    /**
     * Méthode appelée avant l'appel à la classe de test
     */
    public static function setUpBeforeClass()
    {
       Algo_Model_DAO_Set::getInstance()->unitTestsClearTable();
       Algo_Model_DAO_Algo::getInstance()->unitTestsClearTable();
       Algo_Model_DAO_Numeric::getInstance()->unitTestsClearTable();
       Algo_Model_DAO_Execution::getInstance()->unitTestsClearTable();
       Algo_Model_Numeric_DAO_Expression::getInstance()->unitTestsClearTable();
       Algo_Model_Numeric_DAO_NumericInput::getInstance()->unitTestsClearTable();

       Classif_Model_DAO_Indicator::getInstance()->unitTestsClearTable();
       Classif_Model_DAO_Version::getInstance()->unitTestsClearTable();
       Classif_Model_DAO_Context::getInstance()->unitTestsClearTable();

       TEC_Model_DAO_Composite::getInstance()->unitTestsClearTable();
       TEC_Model_DAO_Expression::getInstance()->unitTestsClearTable();
       TEC_Model_DAO_Leaf::getInstance()->unitTestsClearTable();

       Unit_Model_DAO_GrandeurPhysique::getInstance()->unitTestsClearTable();
       Unit_Model_PhysicalQuantity_DAO_ComposantGrandeurPhysique::getInstance()->unitTestsClearTable();
       Unit_Model_Unit_DAO_SystemeUnite::getInstance()->unitTestsClearTable();
       Unit_Model_Unit_DAO_UniteStandard::getInstance()->unitTestsClearTable();

    }// end setUpBeforeClass()

    /**
     * Méthode appelée avant chaque méthode de test.
     */
    function setUp()
    {

        $this->_systemeUnite = new Unit_Model_Unit_SystemeUnite();
        $this->_systemeUnite->ref = "international";
        $this->_systemeUnite->nom = "International";
        $this->_systemeUnite->save();

        $this->_grandeurPhysiqueLongueur = new Unit_Model_GrandeurPhysique();
        $this->_grandeurPhysiqueLongueur->nom = "longueur";
        $this->_grandeurPhysiqueLongueur->ref = "l";
        $this->_grandeurPhysiqueLongueur->symbole = "L";
        $this->_grandeurPhysiqueLongueur->isBase = "1";
        $this->_grandeurPhysiqueLongueur->save();

        $this->_uniteRefLongueur = new Unit_Model_Unit_UniteStandard();
        $this->_uniteRefLongueur->coeffMultiplicateur = 1;
        $this->_uniteRefLongueur->nom = "kilomètre";
        $this->_uniteRefLongueur->symbole = "km";
        $this->_uniteRefLongueur->ref = "km";
        $this->_uniteRefLongueur->setGrandeurPhysique($this->_grandeurPhysiqueLongueur);
        $this->_uniteRefLongueur->setSystemeUnite($this->_systemeUnite);
        $this->_uniteRefLongueur->save();

        $this->_grandeurPhysiqueLongueur->setCompositionGrandeurPhysique($this->_grandeurPhysiqueLongueur, 1);
        $this->_grandeurPhysiqueLongueur->setUniteReference($this->_uniteRefLongueur);
        $this->_grandeurPhysiqueLongueur->save();

        // Définition des calc_value.
        $this->_calcValue1 = new Calc_Value();
        $this->_calcValue1->digitalValue = 1;
        $this->_calcValue1->relativeUncertainty = 1;

        $this->_calcValue2 = new Calc_Value();
        $this->_calcValue2->digitalValue = 2;
        $this->_calcValue2->relativeUncertainty = 1;

        $this->_calcValue3 = new Calc_Value();
        $this->_calcValue3->digitalValue = 3;
        $this->_calcValue3->relativeUncertainty = 1;


        $calcValue1 = new Calc_Value();
        $calcValue1->digitalValue = 1;
        $calcValue1->relativeUncertainty = 1;
        $unit1 = new Unit_Model_APIUnit('km^2');
        $this->_unitValue1 = new Calc_UnitValue();
        $this->_unitValue1->unit = $unit1;
        $this->_unitValue1->value = $calcValue1;

        $calcValue2 = new Calc_Value();
        $calcValue2->digitalValue = 2;
        $calcValue2->relativeUncertainty = 1;
        $unit2 = new Unit_Model_APIUnit('km^2');
        $this->_unitValue2 = new Calc_UnitValue();
        $this->_unitValue2->unit = $unit2;
        $this->_unitValue2->value = $calcValue2;

        $calcValue3 = new Calc_Value();
        $calcValue3->digitalValue = 3;
        $calcValue3->relativeUncertainty = 1;
        $unit3 = new Unit_Model_APIUnit('km^2');
        $this->_unitValue3 = new Calc_UnitValue();
        $this->_unitValue3->unit = $unit3;
        $this->_unitValue3->value = $calcValue3;


        // Définitition des expressions de sélection et de leurs algos.
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

        // Selection avec deux conditions et deux actions.
        $this->_expressionSelectMulti = new TEC_Model_Expression_Select('1:calcul1; 1:calcul2');
        $this->_expressionSelectMulti->save();

        $this->_algoExecMulti = new Algo_Model_Selection_Main();
        $this->_algoExecMulti->ref = 'testExec';
        $this->_algoExecMulti->setExpression($this->_expressionSelectMulti);
        $this->_algoExecMulti->setSet($this->_algoSet);
        $this->_algoExecMulti->save();


            // Selection avec une condition et une action.
        $this->_expressionSelectSimple = new TEC_Model_Expression_Select('1:calcul2');
        $this->_expressionSelectSimple->save();

        $this->_algoExecSimple = new Algo_Model_Selection_Main();
        $this->_algoExecSimple->ref = 'testExec2';
        $this->_algoExecSimple->setExpression($this->_expressionSelectSimple);
        $this->_algoExecSimple->setSet($this->_algoSet);
        $this->_algoExecSimple->save();

        // Définition des expressions de calculs et de leurs algos.
        $this->_expressionCalculMulti1 = new TEC_Model_Expression_Numeric('Input3*Input2-Input1*Input2*Input3/Input3+Input1*Input1');
        $this->_expressionCalculMulti1->save();

        $date = new Core_Date();
        $this->_classifVersion = new Classif_Model_Version();
        $this->_classifVersion->setRef(Core_Tools::generateString());
        $this->_classifVersion->setLabel('Label_generate_classif_version');
        $this->_classifVersion->setCreationDate($date->now()->get('YYYY-MM-dd HH:mm:ss'));
        $this->_classifVersion->save();

        $this->_classifIndicator = new Classif_Model_Indicator();
        $this->_classifIndicator->setLabel('Label_generate_classif_indicator');
        $this->_classifIndicator->setRef(Core_Tools::generateString());
        $apiUnit1 = new Unit_Model_APIUnit("km^2");
        $this->_classifIndicator->setUnit($apiUnit1);
        $this->_classifIndicator->setUnitRatio($apiUnit1);
        $this->_classifIndicator->setVersion($this->_classifVersion);
        $this->_classifIndicator->save();

        $this->_classifIndicator2 = new Classif_Model_Indicator();
        $this->_classifIndicator2->setLabel('Label_generate_classif_indicator2');
        $this->_classifIndicator2->setRef(Core_Tools::generateString());
        $apiUnit2 = new Unit_Model_APIUnit("km^4");
        $this->_classifIndicator2->setUnit($apiUnit2);
        $this->_classifIndicator2->setUnitRatio($apiUnit2);
        $this->_classifIndicator2->setVersion($this->_classifVersion);
        $this->_classifIndicator2->save();

        $this->_algoCalculMulti1 = new Algo_Model_Numeric_Expression();
        $this->_algoCalculMulti1->ref = 'calcul1';
        $this->_algoCalculMulti1->unit = new Unit_Model_APIUnit('testUnit');
        $this->_algoCalculMulti1->setExpression($this->_expressionCalculMulti1);
        $this->_algoCalculMulti1->setSet($this->_algoSet);
        $this->_algoCalculMulti1->setClassifIndicator($this->_classifIndicator2->getKey());
        $this->_algoCalculMulti1->setLabel('labelcalcul1');
        $this->_algoCalculMulti1->save();

        $this->_expressionCalculMulti2 = new TEC_Model_Expression_Numeric('Input1+Input2');
        $this->_expressionCalculMulti2->save();

        $this->_algoCalculMulti2 = new Algo_Model_Numeric_Expression();
        $this->_algoCalculMulti2->ref = 'calcul2';
        $this->_algoCalculMulti2->unit = new Unit_Model_APIUnit('testUnit');
        $this->_algoCalculMulti2->setExpression($this->_expressionCalculMulti2);
        $this->_algoCalculMulti2->setSet($this->_algoSet);
        $this->_algoCalculMulti2->setClassifIndicator($this->_classifIndicator->getKey());
        $this->_algoCalculMulti2->setLabel('labelcalcul2');
        $this->_algoCalculMulti2->save();


//         Algo Numerique Input utilisé à l'interieur des algo de calcul.
        $this->_algoNumeric1 = new Algo_Model_Numeric_Input();
        $this->_algoNumeric1->inputRef = 'Input1';
        $this->_algoNumeric1->ref = 'Input1';
        $this->_algoNumeric1->unit = new Unit_Model_APIUnit('kg');
        $this->_algoNumeric1->setClassifIndicator('3');
        $this->_algoNumeric1->setSet($this->_algoSet);
        $this->_algoNumeric1->setLabel('LabelInput1');
        $this->_algoNumeric1->save();

        $this->_algoNumeric2 = new Algo_Model_Numeric_Input();
        $this->_algoNumeric2->inputRef = 'Input2';
        $this->_algoNumeric2->ref = 'Input2';
        $this->_algoNumeric2->unit = new Unit_Model_APIUnit('kg');
        $this->_algoNumeric2->setClassifIndicator('2');
        $this->_algoNumeric2->setSet($this->_algoSet);
        $this->_algoNumeric2->setLabel('LabelInput2');
        $this->_algoNumeric2->save();

        $this->_algoNumeric3 = new Algo_Model_Numeric_Input();
        $this->_algoNumeric3->inputRef = 'Input3';
        $this->_algoNumeric3->ref = 'Input3';
        $this->_algoNumeric3->unit = new Unit_Model_APIUnit('kg');
        $this->_algoNumeric3->setClassifIndicator('2');
        $this->_algoNumeric3->setSet($this->_algoSet);
        $this->_algoNumeric3->setLabel('LabelInput3');
        $this->_algoNumeric3->save();

        $this->_algoSet->addAlgo($this->_algoExecMulti);
        $this->_algoSet->addAlgo($this->_algoExecSimple);
        $this->_algoSet->addAlgo($this->_algoCalculMulti1);
        $this->_algoSet->addAlgo($this->_algoCalculMulti2);
        $this->_algoSet->addAlgo($this->_algoNumeric1);
        $this->_algoSet->addAlgo($this->_algoNumeric2);
        $this->_algoSet->addAlgo($this->_algoNumeric3);
        $this->_algoSet->save();

    }//end setUp()

    /**
     * Test de la méthode execution()
     */
    function testExecution()
    {
        $test = array();
        // Test avec des calcValue
        $input = array(
                    'Input1'=>$this->_unitValue1,
                    'Input2'=>$this->_unitValue2,
                    'Input3'=>$this->_unitValue3,
                       );
        $result = $this->_algoExecMulti->execute($input);
        $this->assertEquals(2, count($result));
        $this->assertTrue($result['calcul1'] instanceof Algo_Model_Output);
        $this->assertTrue($result['calcul1']->getSourceValue()->value instanceof Calc_Value);
        $this->assertTrue($result['calcul1']->getValue() instanceof Calc_Value);
        $this->assertEquals(5, $result['calcul1']->getSourceValue()->value->digitalValue);
        $this->assertEquals(5, $result['calcul1']->getValue()->digitalValue);
        $this->assertTrue($result['calcul1']->getSourceValue()->unit instanceof Unit_Model_APIUnit);
        $this->assertEquals('km^4', $result['calcul1']->getSourceValue()->unit->ref);

        $this->assertTrue($result['calcul2'] instanceof Algo_Model_Output);
        $this->assertTrue($result['calcul2']->getSourceValue()->value instanceof Calc_Value);
        $this->assertTrue($result['calcul2']->getValue() instanceof Calc_Value);
        $this->assertEquals(3, $result['calcul2']->getSourceValue()->value->digitalValue);
        $this->assertEquals(3, $result['calcul2']->getValue()->digitalValue);
        $this->assertTrue($result['calcul2']->getSourceValue()->unit instanceof Unit_Model_APIUnit);
        // @todo km est pour l'instant l'unité par défault car l'unité pour un ne marche pas...
        $this->assertEquals('km^2', $result['calcul2']->getSourceValue()->unit->ref);

        // Test avec des unitValue
        $result2 = $this->_algoExecSimple->execute($input);

        $this->assertTrue($result2['calcul2'] instanceof Algo_Model_Output);
        $this->assertTrue($result2['calcul2']->getSourceValue()->value instanceof Calc_Value);
        $this->assertTrue($result2['calcul2']->getValue() instanceof Calc_Value);
        $this->assertEquals(3, $result2['calcul2']->getSourceValue()->value->digitalValue);
        $this->assertEquals(3, $result2['calcul2']->getValue()->digitalValue);
        $this->assertTrue($result2['calcul2']->getSourceValue()->unit instanceof Unit_Model_APIUnit);
        $this->assertEquals('km^2', $result2['calcul2']->getSourceValue()->unit->ref);
    }// end execution()

    /**
     * Méthode appelée à la fin de chaque test
     */
    protected function tearDown()
    {
        $this->_algoNumeric2->delete();
        $this->_algoNumeric3->delete();
        $this->_algoNumeric1->delete();

        $this->_systemeUnite->delete();
        $this->_grandeurPhysiqueLongueur->delete();
        $this->_uniteRefLongueur->delete();

        $this->_expressionSelectMulti->delete();
        $this->_algoExecMulti->delete();

        $this->_expressionSelectSimple->delete();
        $this->_algoExecSimple->delete();

        $this->_expressionCalculMulti1->delete();
        $this->_algoCalculMulti1->delete();
        $this->_classifIndicator2->delete();

        $this->_expressionCalculMulti2->delete();
        $this->_algoCalculMulti2->delete();
        $this->_classifIndicator->delete();


        $this->_classifVersion->delete();

        $this->_algoSet->delete();
    }// end tearDown()

    /**
     * Méthode appelée à la fin de la classe de test
     */
    public static function tearDownAfterClass()
    {
        if (! Algo_Model_DAO_Set::getInstance()->unitTestsIsTableEmpty()) {
            echo "\nLa table Set n'est pas vide après les tests\n";
        }
        if (! Algo_Model_DAO_Algo::getInstance()->unitTestsIsTableEmpty()) {
            echo "\nLa table Algo n'est pas vide après les tests\n";
        }
        if (! Algo_Model_DAO_Execution::getInstance()->unitTestsIsTableEmpty() ) {
            echo "\nLa table Execution n'est pas vide après les tests\n";
        }
        if (! Algo_Model_Numeric_DAO_Expression::getInstance()->unitTestsIsTableEmpty()) {
            echo "\nLa table Numeric_Expression n'est pas vide après les tests\n";
        }
        if (! Algo_Model_Numeric_DAO_NumericInput::getInstance()->unitTestsIsTableEmpty() ) {
            echo "\nLa table Numeric_NumericInput n'est pas vide après les tests\n";
        }


        if (! TEC_Model_DAO_Composite::getInstance()->unitTestsIsTableEmpty()) {
            echo "\nLa table Algo n'est pas vide après les tests\n";
        }
        if (! TEC_Model_DAO_Expression::getInstance()->unitTestsIsTableEmpty() ) {
            echo "\nLa table Execution n'est pas vide après les tests\n";
        }
        if (! TEC_Model_DAO_Leaf::getInstance()->unitTestsIsTableEmpty()) {
            echo "\nLa table Numeric_Expression n'est pas vide après les tests\n";
        }
        if (! Algo_Model_Numeric_DAO_NumericInput::getInstance()->unitTestsIsTableEmpty() ) {
            echo "\nLa table Numeric_NumericInput n'est pas vide après les tests\n";
        }


        if (! Unit_Model_DAO_GrandeurPhysique::getInstance()->unitTestsIsTableEmpty() ) {
            echo "\nLa table Execution n'est pas vide après les tests\n";
        }
        if (! Unit_Model_PhysicalQuantity_DAO_ComposantGrandeurPhysique::getInstance()->unitTestsIsTableEmpty()) {
            echo "\nLa table Numeric_Expression n'est pas vide après les tests\n";
        }
        if (! Unit_Model_Unit_DAO_SystemeUnite::getInstance()->unitTestsIsTableEmpty() ) {
            echo "\nLa table Numeric_NumericInput n'est pas vide après les tests\n";
        }
        if (! Unit_Model_Unit_DAO_UniteStandard::getInstance()->unitTestsIsTableEmpty() ) {
            echo "\nLa table Numeric_NumericInput n'est pas vide après les tests\n";
        }
    }
}
