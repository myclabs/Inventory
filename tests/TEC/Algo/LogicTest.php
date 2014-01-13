<?php

namespace Tests\TEC\Algo;

use Core\Test\TestCase;
use TEC\Algo\Logic;
use TEC\Component\Component;
use TEC\Component\Composite;

class LogicTest extends TestCase
{
    /**
     * Test de la méthode getErrors()
     */
    public function testCheckLogic()
    {
        $expression = new Logic('');
        $erreur = $expression->getErrors();
        $this->assertEquals($erreur[0], __('TEC', 'syntaxError', 'missingOperator', array('PART' => '')));

        $expression = new Logic('a b');
        $erreur = $expression->getErrors();
        $this->assertEquals($erreur[0], __('TEC', 'syntaxError', 'missingOperator', array('PART' => 'a b')));

        $expression = new Logic('(a&b');
        $erreur = $expression->getErrors();
        $this->assertEquals($erreur[0], __('TEC', 'syntaxError', 'openingBracketNotClosed'));

        $expression = new Logic('a&b)');
        $erreur = $expression->getErrors();
        $this->assertEquals($erreur[0], __('TEC', 'syntaxError', 'closingBracketNotOpened'));


        $expression = new Logic('a+b');
        $erreur = $expression->getErrors();
        $this->assertEquals($erreur[0], __('TEC', 'syntaxError', 'missingOperator', array('PART' => 'a+b')));

        $expression = new Logic('a+b&c');
        $erreur = $expression->getErrors();
        $this->assertEquals($erreur[0], __('TEC', 'syntaxError', 'invalidOperand', array('PART' => 'a+b')));

        $expression = new Logic('a&&b');
        $erreur = $expression->getErrors();
        $this->assertEquals($erreur[0], __('TEC', 'syntaxError', 'emptyOperand', array('PART' => '')));

        $expression = new Logic('ab');
        $erreur = $expression->getErrors();
        $this->assertEquals($erreur[0], __('TEC', 'syntaxError', 'missingOperator', array('PART' => 'ab')));

        $expression = new Logic('a&b&(&)');
        $erreur = $expression->getErrors();
        $this->assertEquals($erreur[0], __('TEC', 'syntaxError', 'emptyOperand', array('PART' => '')));
        $this->assertEquals($erreur[1], __('TEC', 'syntaxError', 'emptyOperand', array('PART' => '')));

        $expression = new Logic('a&b(a&b)');
        $erreur = $expression->getErrors();
        $this->assertEquals($erreur[0], __('TEC', 'syntaxError', 'missingOperator', array('PART' => 'b(a&b)')));

        $expression = new Logic('a&b!');
        $erreur = $expression->getErrors();
        $this->assertEquals($erreur[0], __('TEC', 'syntaxError', 'invalidOperand', array('PART' => 'b!')));

        $expression = new Logic('a&(b&c!)');
        $erreur = $expression->getErrors();
        $this->assertEquals($erreur[0], __('TEC', 'syntaxError', 'invalidOperand', array('PART' => 'c!')));
    }

    /**
     * Vérifie le parenthesage de l'expression.
     *
     * @return Logic
     */
    public function testCorrectBrackets()
    {
        $expression = new Logic('A&(B|C)&D');
        $this->assertEquals($expression->correctBrackets(), 'A&(B|C)&D');

        $expression = new Logic('A|B&!C&D|!E|!F|G&!(H|I&J)');
        $this->assertEquals($expression->correctBrackets(), 'A|(B&!C&D)|!E|!F|(G&!(H|(I&J)))');
    }

    /**
     * Test la création d'un arbre select.
     *
     * @depends testCorrectBrackets
     *
     * @return Logic
     */
    public function testCreateTree()
    {
        $expression = new Logic('A|B&!C&D|!E|!F|G&!(H|I&J)');
        $rootOrNode = $expression->createTree();

        $rootOrChildren = $rootOrNode->getChildren();
        $this->assertInstanceOf('TEC\Component\Composite', $rootOrNode);
        $this->assertNull($rootOrNode->getParent());
        $this->assertEquals($rootOrNode->getOperator(), Composite::LOGICAL_OR);
        $this->assertNull($rootOrNode->getModifier());
        $this->assertEquals(count($rootOrChildren), 5);

        $a = $rootOrChildren[0];
        $this->assertInstanceOf('TEC\Component\Leaf', $a);
        $this->assertSame($a->getParent(), $rootOrNode);
        $this->assertEquals($a->getName(), 'A');
        $this->assertNull($a->getModifier());

        $and1 = $rootOrChildren[1];
        $and1Children = $and1->getChildren();
        $this->assertInstanceOf('TEC\Component\Composite', $and1);
        $this->assertSame($and1->getParent(), $rootOrNode);
        $this->assertEquals($and1->getOperator(), Composite::LOGICAL_AND);
        $this->assertNull($and1->getModifier());
        $this->assertEquals(count($and1Children), 3);

        $b = $and1Children[0];
        $this->assertInstanceOf('TEC\Component\Leaf', $b);
        $this->assertSame($b->getParent(), $and1);
        $this->assertEquals($b->getName(), 'B');
        $this->assertNull($b->getModifier());

        $c = $and1Children[1];
        $this->assertInstanceOf('TEC\Component\Leaf', $c);
        $this->assertSame($c->getParent(), $and1);
        $this->assertEquals($c->getName(), 'C');
        $this->assertEquals($c->getModifier(), Component::MODIFIER_NOT);

        $d = $and1Children[2];
        $this->assertInstanceOf('TEC\Component\Leaf', $d);
        $this->assertSame($d->getParent(), $and1);
        $this->assertEquals($d->getName(), 'D');
        $this->assertNull($d->getModifier());

        $e = $rootOrChildren[2];
        $this->assertInstanceOf('TEC\Component\Leaf', $e);
        $this->assertSame($e->getParent(), $rootOrNode);
        $this->assertEquals($e->getName(), 'E');
        $this->assertEquals($e->getModifier(), Component::MODIFIER_NOT);

        $f = $rootOrChildren[3];
        $this->assertInstanceOf('TEC\Component\Leaf', $f);
        $this->assertSame($f->getParent(), $rootOrNode);
        $this->assertEquals($f->getName(), 'F');
        $this->assertEquals($f->getModifier(), Component::MODIFIER_NOT);

        $and4 = $rootOrChildren[4];
        $and4Children = $and4->getChildren();
        $this->assertInstanceOf('TEC\Component\Composite', $and4);
        $this->assertSame($and4->getParent(), $rootOrNode);
        $this->assertEquals($and4->getOperator(), Composite::LOGICAL_AND);
        $this->assertNull($and4->getModifier());
        $this->assertEquals(count($and4Children), 2);

        $g = $and4Children[0];
        $this->assertInstanceOf('TEC\Component\Leaf', $g);
        $this->assertSame($g->getParent(), $and4);
        $this->assertEquals($g->getName(), 'G');
        $this->assertNull($g->getModifier());

        $or41 = $and4Children[1];
        $or41Children = $or41->getChildren();
        $this->assertInstanceOf('TEC\Component\Composite', $or41);
        $this->assertSame($or41->getParent(), $and4);
        $this->assertEquals($or41->getOperator(), Composite::LOGICAL_OR);
        $this->assertEquals($or41->getModifier(), Component::MODIFIER_NOT);
        $this->assertEquals(count($or41Children), 2);

        $h = $or41Children[0];
        $this->assertInstanceOf('TEC\Component\Leaf', $h);
        $this->assertSame($h->getParent(), $or41);
        $this->assertEquals($h->getName(), 'H');
        $this->assertNull($h->getModifier());

        $and411 = $or41Children[1];
        $an411Children = $and411->getChildren();
        $this->assertInstanceOf('TEC\Component\Composite', $and411);
        $this->assertSame($and411->getParent(), $or41);
        $this->assertEquals($and411->getOperator(), Composite::LOGICAL_AND);
        $this->assertNull($and411->getModifier());
        $this->assertEquals(count($an411Children), 2);

        $i = $an411Children[0];
        $this->assertInstanceOf('TEC\Component\Leaf', $i);
        $this->assertSame($i->getParent(), $and411);
        $this->assertEquals($i->getName(), 'I');
        $this->assertNull($i->getModifier());

        $j = $an411Children[1];
        $this->assertInstanceOf('TEC\Component\Leaf', $j);
        $this->assertSame($j->getParent(), $and411);
        $this->assertEquals($j->getName(), 'J');
        $this->assertNull($j->getModifier());
    }

    /**
     * Test la méthode
     *
     * @depends testCreateTree
     */
    public function testGetTreeAsString()
    {
        $expression = new Logic('A|B&!C&D|!E|!F|G&!(H|I&J)');
        $treeAsString = $expression->convertTreeToString();
        $expectedString = 'A | (B & !C & D) | !E | !F | (G & !(H | (I & J)))';
        $this->assertEquals($expectedString, $treeAsString);
    }

    /**
     * Test la méthode
     *
     * @depends testCreateTree
     */
    public function testGetTreeAsGraph()
    {
        $expression = new Logic('A|B&!C&D|!E|!F|G&!(H|I&J)');
        $treeAsGraph = $expression->convertTreeToGraph();
        $expectedGraph = '[{v:"0",f:"<b>OU</b>"},"",""],'
                            .'[{v:"0-0",f:"A"},"0",""],'
                            .'[{v:"0-1",f:"<b>ET</b>"},"0",""],'
                            .'[{v:"0-1-0",f:"B"},"0-1",""],'
                            .'[{v:"0-1-1",f:"NON C"},"0-1",""],'
                            .'[{v:"0-1-2",f:"D"},"0-1",""],'
                            .'[{v:"0-2",f:"NON E"},"0",""],'
                            .'[{v:"0-3",f:"NON F"},"0",""],'
                            .'[{v:"0-4",f:"<b>ET</b>"},"0",""],'
                            .'[{v:"0-4-0",f:"G"},"0-4",""],'
                            .'[{v:"0-4-1",f:"NON <b>OU</b>"},"0-4",""],'
                            .'[{v:"0-4-1-0",f:"H"},"0-4-1",""],'
                            .'[{v:"0-4-1-1",f:"<b>ET</b>"},"0-4-1",""],'
                            .'[{v:"0-4-1-1-0",f:"I"},"0-4-1-1",""],'
                            .'[{v:"0-4-1-1-1",f:"J"},"0-4-1-1",""],';
        $this->assertEquals($expectedGraph, $treeAsGraph);
    }
}
