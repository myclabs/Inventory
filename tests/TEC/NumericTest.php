<?php
/**
 * Test de l'objet métier Numeric
 * @author valentin.claras
 * @author hugo.charboniere
 * @author yoann.croizer
 * @package TEC
 */

/**
 * @package TEC
 */
class TEC_Test_NumericTest
{
    /**
     * lance les autre classe de tests
     */
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite();
        $suite->addTestSuite('TEC_Test_AlgoNumeric');
        return $suite;
    }

}

/**
 * numericLogiqueMetierTest
 * @package TEC
 */
class TEC_Test_AlgoNumeric extends PHPUnit_Framework_TestCase
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
    function testCheckNumeric()
    {
        $expression = new TEC_Expression_Algo_Numeric('');
        $erreur = $expression->getErrors();
        $this->assertEquals($erreur[0], __('TEC', 'syntaxError', 'missingOperator', array('PART' => '')));

        $expression = new TEC_Expression_Algo_Numeric('a b');
        $erreur = $expression->getErrors();
        $this->assertEquals($erreur[0], __('TEC', 'syntaxError', 'missingOperator', array('PART' => 'a b')));

        $expression = new TEC_Expression_Algo_Numeric('(a+b');
        $erreur = $expression->getErrors();
        $this->assertEquals($erreur[0], __('TEC', 'syntaxError', 'openingBracketNotClosed'));

        $expression = new TEC_Expression_Algo_Numeric('a+b)');
        $erreur = $expression->getErrors();
        $this->assertEquals($erreur[0], __('TEC', 'syntaxError', 'closingBracketNotOpened'));


        $expression = new TEC_Expression_Algo_Numeric('a&b');
        $erreur = $expression->getErrors();
        $this->assertEquals($erreur[0], __('TEC', 'syntaxError', 'missingOperator', array('PART' => 'a&b')));

        $expression = new TEC_Expression_Algo_Numeric('a+b&c');
        $erreur = $expression->getErrors();
        $this->assertEquals($erreur[0], __('TEC', 'syntaxError', 'invalidOperand', array('PART' => 'b&c')));

        $expression = new TEC_Expression_Algo_Numeric('a++b');
        $erreur = $expression->getErrors();
        $this->assertEquals($erreur[0], __('TEC', 'syntaxError', 'emptyOperand', array('PART' => '')));

        $expression = new TEC_Expression_Algo_Numeric('ab');
        $erreur = $expression->getErrors();
        $this->assertEquals($erreur[0], __('TEC', 'syntaxError', 'missingOperator', array('PART' => 'ab')));

        $expression = new TEC_Expression_Algo_Numeric('a+b+(+)');
        $erreur = $expression->getErrors();
        $this->assertEquals($erreur[0], __('TEC', 'syntaxError', 'emptyOperand', array('PART' => '')));
        $this->assertEquals($erreur[1], __('TEC', 'syntaxError', 'emptyOperand', array('PART' => '')));

        $expression = new TEC_Expression_Algo_Numeric('a+b(a+b)');
        $erreur = $expression->getErrors();
        $this->assertEquals($erreur[0], __('TEC', 'syntaxError', 'missingOperator', array('PART' => 'b(a+b)')));

        $expression = new TEC_Expression_Algo_Numeric('a+(b+c-)');
        $erreur = $expression->getErrors();
        $this->assertEquals($erreur[0], __('TEC', 'syntaxError', 'emptyOperand', array('PART' => '')));

        $expression = new TEC_Expression_Algo_Numeric('a+(*b-c)');
        $erreur = $expression->getErrors();
        $this->assertEquals($erreur[0], __('TEC', 'syntaxError', 'emptyOperand', array('PART' => '')));

        $expression = new TEC_Expression_Algo_Numeric('a*b-');
        $erreur = $expression->getErrors();
        $this->assertEquals($erreur[0], __('TEC', 'syntaxError', 'emptyOperand', array('PART' => '')));

    }

    /**
     * Vérifie le parenthesage de l'expression.
     *
     * @return TEC_Expression_Algo_Select
     */
    public function testCorrectBrackets()
    {
        $expression = new TEC_Expression_Algo_Numeric('a*(b+c)*d');
        $this->assertEquals($expression->correctBrackets(), 'a*(b+c)*d');
        $expression = new TEC_Expression_Algo_Numeric('a/b/c+d-e*f/(g-h)');
        $this->assertEquals($expression->correctBrackets(), '(a/b/c)+d-(e*f/(g-h))');
        $expression = new TEC_Expression_Algo_Numeric('c*(a+b)');
        $this->assertEquals($expression->correctBrackets(), 'c*(a+b)');
        $expression = new TEC_Expression_Algo_Numeric('(a+b)*c');
        $this->assertEquals($expression->correctBrackets(), '(a+b)*c');

        // Expression utilisé pour le test suivant.
        $expression = new TEC_Expression_Algo_Numeric('A+B/C*D-E-F+G/(H+I*J)');
        $this->assertEquals($expression->correctBrackets(), 'A+(B/C*D)-E-F+(G/(H+(I*J)))');
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
        $rootPlusNode = $expression->createTree();

        $rootPlusChildren = $rootPlusNode->getChildren();
        $this->assertInstanceOf('TEC_Model_Composite', $rootPlusNode);
        $this->assertNull($rootPlusNode->getParent());
        $this->assertEquals($rootPlusNode->getOperator(), TEC_Model_Composite::OPERATOR_SUM);
        $this->assertNull($rootPlusNode->getModifier());
        $this->assertEquals(count($rootPlusChildren), 5);

        $a = $rootPlusChildren[0];
        $this->assertInstanceOf('TEC_Model_Leaf', $a);
        $this->assertSame($a->getParent(), $rootPlusNode);
        $this->assertEquals($a->getName(), 'A');
        $this->assertEquals($a->getModifier(), TEC_Model_Component::MODIFIER_ADD);

        $mul1 = $rootPlusChildren[1];
        $mul1Children = $mul1->getChildren();
        $this->assertInstanceOf('TEC_Model_Composite', $mul1);
        $this->assertSame($mul1->getParent(), $rootPlusNode);
        $this->assertEquals($mul1->getOperator(), TEC_Model_Composite::OPERATOR_PRODUCT);
        $this->assertEquals($mul1->getModifier(), TEC_Model_Component::MODIFIER_ADD);
        $this->assertEquals(count($mul1Children), 3);

        $b = $mul1Children[0];
        $this->assertInstanceOf('TEC_Model_Leaf', $b);
        $this->assertSame($b->getParent(), $mul1);
        $this->assertEquals($b->getName(), 'B');
        $this->assertEquals($b->getModifier(), TEC_Model_Component::MODIFIER_ADD);

        $c = $mul1Children[1];
        $this->assertInstanceOf('TEC_Model_Leaf', $c);
        $this->assertSame($c->getParent(), $mul1);
        $this->assertEquals($c->getName(), 'C');
        $this->assertEquals($c->getModifier(), TEC_Model_Component::MODIFIER_SUB);

        $d = $mul1Children[2];
        $this->assertInstanceOf('TEC_Model_Leaf', $d);
        $this->assertSame($d->getParent(), $mul1);
        $this->assertEquals($d->getName(), 'D');
        $this->assertEquals($d->getModifier(), TEC_Model_Component::MODIFIER_ADD);

        $e = $rootPlusChildren[2];
        $this->assertInstanceOf('TEC_Model_Leaf', $e);
        $this->assertSame($e->getParent(), $rootPlusNode);
        $this->assertEquals($e->getName(), 'E');
        $this->assertEquals($e->getModifier(), TEC_Model_Component::MODIFIER_SUB);

        $f = $rootPlusChildren[3];
        $this->assertInstanceOf('TEC_Model_Leaf', $f);
        $this->assertSame($f->getParent(), $rootPlusNode);
        $this->assertEquals($f->getName(), 'F');
        $this->assertEquals($f->getModifier(), TEC_Model_Component::MODIFIER_SUB);

        $mul4 = $rootPlusChildren[4];
        $mul4Children = $mul4->getChildren();
        $this->assertInstanceOf('TEC_Model_Composite', $mul4);
        $this->assertSame($mul4->getParent(), $rootPlusNode);
        $this->assertEquals($mul4->getOperator(), TEC_Model_Composite::OPERATOR_PRODUCT);
        $this->assertEquals($mul4->getModifier(), TEC_Model_Component::MODIFIER_ADD);
        $this->assertEquals(count($mul4Children), 2);

        $g = $mul4Children[0];
        $this->assertInstanceOf('TEC_Model_Leaf', $g);
        $this->assertSame($g->getParent(), $mul4);
        $this->assertEquals($g->getName(), 'G');
        $this->assertEquals($g->getModifier(), TEC_Model_Component::MODIFIER_ADD);

        $sum41 = $mul4Children[1];
        $sum41Children = $sum41->getChildren();
        $this->assertInstanceOf('TEC_Model_Composite', $sum41);
        $this->assertSame($sum41->getParent(), $mul4);
        $this->assertEquals($sum41->getOperator(), TEC_Model_Composite::OPERATOR_SUM);
        $this->assertEquals($sum41->getModifier(), TEC_Model_Component::MODIFIER_SUB);
        $this->assertEquals(count($sum41Children), 2);

        $h = $sum41Children[0];
        $this->assertInstanceOf('TEC_Model_Leaf', $h);
        $this->assertSame($h->getParent(), $sum41);
        $this->assertEquals($h->getName(), 'H');
        $this->assertEquals($h->getModifier(), TEC_Model_Component::MODIFIER_ADD);

        $mul411 = $sum41Children[1];
        $sum411Children = $mul411->getChildren();
        $this->assertInstanceOf('TEC_Model_Composite', $mul411);
        $this->assertSame($mul411->getParent(), $sum41);
        $this->assertEquals($mul411->getOperator(), TEC_Model_Composite::OPERATOR_PRODUCT);
        $this->assertEquals($mul411->getModifier(), TEC_Model_Component::MODIFIER_ADD);
        $this->assertEquals(count($sum411Children), 2);

        $i = $sum411Children[0];
        $this->assertInstanceOf('TEC_Model_Leaf', $i);
        $this->assertSame($i->getParent(), $mul411);
        $this->assertEquals($i->getName(), 'I');
        $this->assertEquals($i->getModifier(), TEC_Model_Component::MODIFIER_ADD);

        $j = $sum411Children[1];
        $this->assertInstanceOf('TEC_Model_Leaf', $j);
        $this->assertSame($j->getParent(), $mul411);
        $this->assertEquals($j->getName(), 'J');
        $this->assertEquals($j->getModifier(), TEC_Model_Component::MODIFIER_ADD);

        return $expression;
    }

    /**
     * Test la méthode
     *
     * @depends testCreateTree
     *
     * @param TEC_Expression_Algo_Numeric $expression
     */
    public function testGetTreeAsString(TEC_Expression_Algo_Numeric $expression)
    {
        $treeAsString = $expression->convertTreeToString();
        $expectedString = 'A + (B * D / C) + (G / (H + (I * J))) - (E + F)';
        $this->assertEquals($expectedString, $treeAsString);
    }

    /**
     * Test la méthode
     *
     * @depends testCreateTree
     *
     * @param TEC_Expression_Algo_Numeric $expression
     */
    public function testGetTreeAsGraph(TEC_Expression_Algo_Numeric $expression)
    {
        $treeAsGraph = $expression->convertTreeToGraph();
        $expectedGraph = '[{v:"0",f:"<b>Somme</b>"},"",""],'
                            .'[{v:"0-0",f:"+ A"},"0",""],'
                            .'[{v:"0-1",f:"+ <b>Produit</b>"},"0",""],'
                            .'[{v:"0-1-0",f:"B"},"0-1",""],'
                            .'[{v:"0-1-1",f:"1/C"},"0-1",""],'
                            .'[{v:"0-1-2",f:"D"},"0-1",""],'
                            .'[{v:"0-2",f:"- E"},"0",""],'
                            .'[{v:"0-3",f:"- F"},"0",""],'
                            .'[{v:"0-4",f:"+ <b>Produit</b>"},"0",""],'
                            .'[{v:"0-4-0",f:"G"},"0-4",""],'
                            .'[{v:"0-4-1",f:"1/<b>Somme</b>"},"0-4",""],'
                            .'[{v:"0-4-1-0",f:"+ H"},"0-4-1",""],'
                            .'[{v:"0-4-1-1",f:"+ <b>Produit</b>"},"0-4-1",""],'
                            .'[{v:"0-4-1-1-0",f:"I"},"0-4-1-1",""],'
                            .'[{v:"0-4-1-1-1",f:"J"},"0-4-1-1",""],';
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