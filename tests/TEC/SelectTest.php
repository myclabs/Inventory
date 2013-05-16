<?php
/**
 * Test de l'objet métier Select
 * @author valentin.claras
 * @author hugo.charboniere
 * @author yoann.croizer
 * @package TEC
 */

/**
 * @package TEC
 */
class TEC_Test_SelectTest
{
    /**
     * lance les autre classe de tests
     */
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite();
        $suite->addTestSuite('TEC_Test_AlgoSelect');
        return $suite;
    }

}

/**
 * @package TEC
 */
class TEC_Test_AlgoSelect extends PHPUnit_Framework_TestCase
{
    /**
     * Méthode appelée avant l'appel à la classe de test
     */
    public static function setUpBeforeClass()
    {
    }

    /**
     * Méthode appelée avant chaque test
     */
    protected function setUp()
    {
    }

    /**
     * Test de la méthode getErrors()
     */
    function testCheckSelect()
    {
        $expression = new TEC_Expression_Algo_Select('');
        $errors = $expression->getErrors();
        $this->assertEquals($errors[0], __('TEC', 'syntaxError', 'missingOperator', array('PART' => '')));

        $expression = new TEC_Expression_Algo_Select('a b');
        $erreur = $expression->getErrors();
        $this->assertEquals($erreur[0], __('TEC', 'syntaxError', 'missingOperator', array('PART' => 'a b')));

        $expression = new TEC_Expression_Algo_Select('(a:b');
        $erreur = $expression->getErrors();
        $this->assertEquals($erreur[0], __('TEC', 'syntaxError', 'openingBracketNotClosed'));

        $expression = new TEC_Expression_Algo_Select('a:b)');
        $erreur = $expression->getErrors();
        $this->assertEquals($erreur[0], __('TEC', 'syntaxError', 'closingBracketNotOpened'));


        $expression = new TEC_Expression_Algo_Select('a:b+c;');
        $errors = $expression->getErrors();
        $this->assertEquals($errors[0], __('TEC', 'syntaxError', 'invalidSelection', array('PART' => 'b+c')));

        $expression = new TEC_Expression_Algo_Select('a:;');
        $errors = $expression->getErrors();
        $this->assertEquals($errors[0], __('TEC', 'syntaxError', 'emptySelection', array('PART' => 'a:')));

        $expression = new TEC_Expression_Algo_Select('a:b:c');
        $errors = $expression->getErrors();
        $this->assertEquals($errors[0], __('TEC', 'syntaxError', 'invalidSelection', array('PART' => 'b:c')));

        $expression = new TEC_Expression_Algo_Select('a:b;;');
        $errors = $expression->getErrors();
        $this->assertEquals($errors[0], __('TEC', 'syntaxError', 'missingOperator', array('PART' => '')));
    }

    /**
     * Vérifie le parenthesage de l'expression.
     *
     * @return TEC_Expression_Algo_Select
     */
    public function testCorrectBrackets()
    {
        $expression = new TEC_Expression_Algo_Select('a:(b:(c:o1);d:o2);:o3;:o4;');
        $this->assertEquals($expression->correctBrackets(), 'a:(b:(c:o1);d:o2);:o3;:o4;');
        return $expression;
    }

    /**
     * Test la création d'un arbre select.
     *
     * @depends testCorrectBrackets
     *
     * @param TEC_Expression_Algo_Select $expression
     *
     * @return TEC_Expression_Algo_Select
     */
    public function testCreateTree($expression)
    {
        $rootNode = $expression->createTree();

        $rootChildren = $rootNode->getChildren();
        $this->assertInstanceOf('TEC_Model_Composite', $rootNode);
        $this->assertNull($rootNode->getParent());
        $this->assertEquals($rootNode->getOperator(), TEC_Model_Composite::SELECT);
        $this->assertNull($rootNode->getModifier());
        $this->assertEquals(count($rootChildren), 3);

        $a = $rootChildren[0];
        $aChildren = $a->getChildren();
        $this->assertInstanceOf('TEC_Model_Composite', $a);
        $this->assertSame($a->getParent(), $rootNode);
        $this->assertEquals($a->getOperator(), TEC_Model_Composite::SELECT);
        $this->assertEquals($a->getModifier(), 'a');
        $this->assertEquals(count($aChildren), 2);

        $b = $aChildren[0];
        $bChildren = $b->getChildren();
        $this->assertInstanceOf('TEC_Model_Composite', $b);
        $this->assertSame($b->getParent(), $a);
        $this->assertEquals($b->getOperator(), TEC_Model_Composite::SELECT);
        $this->assertEquals($b->getModifier(), 'b');
        $this->assertEquals(count($bChildren), 1);

        $c = $bChildren[0];
        $cChildren = $c->getChildren();
        $this->assertInstanceOf('TEC_Model_Composite', $c);
        $this->assertSame($c->getParent(), $b);
        $this->assertEquals($c->getOperator(), TEC_Model_Composite::SELECT);
        $this->assertEquals($c->getModifier(), 'c');
        $this->assertEquals(count($cChildren), 1);

        $o1 = $cChildren[0];
        $this->assertInstanceOf('TEC_Model_Leaf', $o1);
        $this->assertSame($o1->getParent(), $c);
        $this->assertEquals($o1->getName(), 'o1');

        $d = $aChildren[1];
        $dChildren = $d->getChildren();
        $this->assertInstanceOf('TEC_Model_Composite', $d);
        $this->assertSame($d->getParent(), $a);
        $this->assertEquals($d->getOperator(), TEC_Model_Composite::SELECT);
        $this->assertEquals($d->getModifier(), 'd');
        $this->assertEquals(count($dChildren), 1);

        $o2 = $dChildren[0];
        $this->assertInstanceOf('TEC_Model_Leaf', $o2);
        $this->assertSame($o2->getParent(), $d);
        $this->assertEquals($o2->getName(), 'o2');

        $o3 = $rootChildren[1];
        $this->assertInstanceOf('TEC_Model_Leaf', $o3);
        $this->assertSame($o3->getParent(), $rootNode);
        $this->assertEquals($o3->getName(), 'o3');

        $o4 = $rootChildren[2];
        $this->assertInstanceOf('TEC_Model_Leaf', $o4);
        $this->assertSame($o4->getParent(), $rootNode);
        $this->assertEquals($o4->getName(), 'o4');

        return $expression;
    }

    /**
     * Test la méthode
     *
     * @depends testCreateTree
     *
     * @param TEC_Expression_Algo_Select $expression
     */
    public function testGetTreeAsString(TEC_Expression_Algo_Select $expression)
    {
        $treeAsString = $expression->convertTreeToString();
        $expectedString = 'a : (b : (c : o1) ; d : o2) ; : o3 ; : o4';
        $this->assertEquals($expectedString, $treeAsString);
    }

    /**
     * Test la méthode
     *
     * @depends testCreateTree
     *
     * @param TEC_Expression_Algo_Select $expression
     */
    public function testGetTreeAsGraph(TEC_Expression_Algo_Select $expression)
    {
        $treeAsGraph = $expression->convertTreeToGraph();
        $expectedGraph = '[{v:"-0",f:"a ?"},"",""],'
                        .'[{v:"-0-0",f:"b ?"},"-0",""],'
                        .'[{v:"-0-0-0",f:"c ?"},"-0-0",""],'
                        .'[{v:"-0-0-0-0",f:"o1"},"-0-0-0",""],'
                        .'[{v:"-0-1",f:"d ?"},"-0",""],'
                        .'[{v:"-0-1-0",f:"o2"},"-0-1",""],'
                        .'[{v:"-1",f:"o3"},"",""],'
                        .'[{v:"-2",f:"o4"},"",""],';
        $this->assertEquals($expectedGraph, $treeAsGraph);
    }

    /**
     * Méthode appelée à la fin de chaque test
     */
    protected function tearDown()
    {
    }

    /**
     * Méthode appelée à la fin de la classe de test
     */
    public static function tearDownAfterClass()
    {
    }

}