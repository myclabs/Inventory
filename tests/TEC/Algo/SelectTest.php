<?php

namespace Tests\TEC\Algo;

use Core\Test\TestCase;
use TEC\Algo\Select;
use TEC\Component\Composite;

class SelectTest extends TestCase
{
    /**
     * Test de la méthode getErrors()
     */
    public function testCheckSelect()
    {
        $expression = new Select('');
        $errors = $expression->getErrors();
        $this->assertEquals($errors[0], __('TEC', 'syntaxError', 'missingOperator', array('PART' => '')));

        $expression = new Select('a b');
        $erreur = $expression->getErrors();
        $this->assertEquals($erreur[0], __('TEC', 'syntaxError', 'missingOperator', array('PART' => 'a b')));

        $expression = new Select('(a:b');
        $erreur = $expression->getErrors();
        $this->assertEquals($erreur[0], __('TEC', 'syntaxError', 'openingBracketNotClosed'));

        $expression = new Select('a:b)');
        $erreur = $expression->getErrors();
        $this->assertEquals($erreur[0], __('TEC', 'syntaxError', 'closingBracketNotOpened'));


        $expression = new Select('a:b+c;');
        $errors = $expression->getErrors();
        $this->assertEquals($errors[0], __('TEC', 'syntaxError', 'invalidSelection', array('PART' => 'b+c')));

        $expression = new Select('a:;');
        $errors = $expression->getErrors();
        $this->assertEquals($errors[0], __('TEC', 'syntaxError', 'emptySelection', array('PART' => 'a:')));

        $expression = new Select('a:b:c');
        $errors = $expression->getErrors();
        $this->assertEquals($errors[0], __('TEC', 'syntaxError', 'invalidSelection', array('PART' => 'b:c')));

        $expression = new Select('a:b;;');
        $errors = $expression->getErrors();
        $this->assertEquals($errors[0], __('TEC', 'syntaxError', 'missingOperator', array('PART' => '')));
    }

    /**
     * Vérifie le parenthesage de l'expression.
     *
     * @return Select
     */
    public function testCorrectBrackets()
    {
        $expression = new Select('a:(b:(c:o1);d:o2);:o3;:o4;');
        $this->assertEquals($expression->correctBrackets(), 'a:(b:(c:o1);d:o2);:o3;:o4;');
    }

    /**
     * Test la création d'un arbre select.
     *
     * @depends testCorrectBrackets
     *
     * @return Select
     */
    public function testCreateTree()
    {
        $expression = new Select('a:(b:(c:o1);d:o2);:o3;:o4;');
        $rootNode = $expression->createTree();

        $rootChildren = $rootNode->getChildren();
        $this->assertInstanceOf('TEC\Component\Composite', $rootNode);
        $this->assertNull($rootNode->getParent());
        $this->assertEquals($rootNode->getOperator(), Composite::SELECT);
        $this->assertNull($rootNode->getModifier());
        $this->assertEquals(count($rootChildren), 3);

        $a = $rootChildren[0];
        $aChildren = $a->getChildren();
        $this->assertInstanceOf('TEC\Component\Composite', $a);
        $this->assertSame($a->getParent(), $rootNode);
        $this->assertEquals($a->getOperator(), Composite::SELECT);
        $this->assertEquals($a->getModifier(), 'a');
        $this->assertEquals(count($aChildren), 2);

        $b = $aChildren[0];
        $bChildren = $b->getChildren();
        $this->assertInstanceOf('TEC\Component\Composite', $b);
        $this->assertSame($b->getParent(), $a);
        $this->assertEquals($b->getOperator(), Composite::SELECT);
        $this->assertEquals($b->getModifier(), 'b');
        $this->assertEquals(count($bChildren), 1);

        $c = $bChildren[0];
        $cChildren = $c->getChildren();
        $this->assertInstanceOf('TEC\Component\Composite', $c);
        $this->assertSame($c->getParent(), $b);
        $this->assertEquals($c->getOperator(), Composite::SELECT);
        $this->assertEquals($c->getModifier(), 'c');
        $this->assertEquals(count($cChildren), 1);

        $o1 = $cChildren[0];
        $this->assertInstanceOf('TEC\Component\Leaf', $o1);
        $this->assertSame($o1->getParent(), $c);
        $this->assertEquals($o1->getName(), 'o1');

        $d = $aChildren[1];
        $dChildren = $d->getChildren();
        $this->assertInstanceOf('TEC\Component\Composite', $d);
        $this->assertSame($d->getParent(), $a);
        $this->assertEquals($d->getOperator(), Composite::SELECT);
        $this->assertEquals($d->getModifier(), 'd');
        $this->assertEquals(count($dChildren), 1);

        $o2 = $dChildren[0];
        $this->assertInstanceOf('TEC\Component\Leaf', $o2);
        $this->assertSame($o2->getParent(), $d);
        $this->assertEquals($o2->getName(), 'o2');

        $o3 = $rootChildren[1];
        $this->assertInstanceOf('TEC\Component\Leaf', $o3);
        $this->assertSame($o3->getParent(), $rootNode);
        $this->assertEquals($o3->getName(), 'o3');

        $o4 = $rootChildren[2];
        $this->assertInstanceOf('TEC\Component\Leaf', $o4);
        $this->assertSame($o4->getParent(), $rootNode);
        $this->assertEquals($o4->getName(), 'o4');
    }

    /**
     * Test la méthode
     *
     * @depends testCreateTree
     */
    public function testGetTreeAsString()
    {
        $expression = new Select('a:(b:(c:o1);d:o2);:o3;:o4;');
        $treeAsString = $expression->convertTreeToString();
        $expectedString = 'a : (b : (c : o1) ; d : o2) ; : o3 ; : o4';
        $this->assertEquals($expectedString, $treeAsString);
    }

    /**
     * Test la méthode
     *
     * @depends testCreateTree
     */
    public function testGetTreeAsGraph()
    {
        $expression = new Select('a:(b:(c:o1);d:o2);:o3;:o4;');
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
}
