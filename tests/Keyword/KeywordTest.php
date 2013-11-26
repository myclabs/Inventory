<?php
/**
 * @author valentin.claras
 * @author bertrand.ferry
 * @package Keyword
 * @subpackage Test
 */
use Core\Test\TestCase;
use Keyword\Domain\Keyword;
use Keyword\Domain\Predicate;
use Keyword\Domain\Association;

/**
 * Creation de la suite de test.
 * @package Keyword
 * @subpackage Test
 */
class Keyword_Test_KeywordTest extends TestCase
{
    /**
     * Creation de la suite de test.
     */
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite();
        $suite->addTestSuite('Keyword_Test_KeywordEntity');
        $suite->addTestSuite('Keyword_Test_AssociationEntity');
        return $suite;
    }
}

/**
 * @package Keyword
 * @subpackage Test
 */
class Keyword_Test_KeywordEntity extends TestCase
{

    /**
     *
     */
    function testConstruct()
    {
        $Keyword = new Keyword('ref');
        $this->assertInstanceOf('Keyword\Domain\Keyword', $Keyword);
        $this->assertEquals('ref', $Keyword->getRef());
        $this->assertEquals('', $Keyword->getLabel());

        $Keyword = new Keyword('ref', 'Label');
        $this->assertInstanceOf('Keyword\Domain\Keyword', $Keyword);
        $this->assertEquals('ref', $Keyword->getRef());
        $this->assertEquals('Label', $Keyword->getLabel());
    }

}

/**
 * @package Keyword
 * @subpackage Test
 */
class Keyword_Test_AssociationEntity extends TestCase
{

    /**
     *
     */
    function testConstruct()
    {
        $Keyword1 = new Keyword('ref1');
        $this->assertFalse($Keyword1->hasAssociationsAsSubject());
        $this->assertFalse($Keyword1->hasAssociationsAsObject());
        $this->assertEquals(0, $Keyword1->countAssociationsAsSubject());
        $this->assertEquals(0, $Keyword1->countAssociationsAsObject());
        $this->assertEquals(0, $Keyword1->countAssociations());

        $Keyword2 = new Keyword('ref2', 'Label');
        $this->assertFalse($Keyword2->hasAssociationsAsSubject());
        $this->assertFalse($Keyword2->hasAssociationsAsObject());
        $this->assertEquals(0, $Keyword2->countAssociationsAsSubject());
        $this->assertEquals(0, $Keyword2->countAssociationsAsObject());
        $this->assertEquals(0, $Keyword2->countAssociations());
        
        $predicate1 = new Predicate('ref1', 'reverseref1');
        $predicate2 = new Predicate('ref2', 'reverseref2');

        $association = $Keyword1->addAssociationWith($predicate1, $Keyword2);
        $this->assertSame($Keyword1, $association->getSubject());
        $this->assertSame($predicate1, $association->getPredicate());
        $this->assertSame($Keyword2, $association->getObject());
        $association->setPredicate($predicate2);
        $this->assertSame($predicate2, $association->getPredicate());

        $this->assertTrue($Keyword1->hasAssociationsAsSubject());
        $this->assertFalse($Keyword1->hasAssociationsAsObject());
        $this->assertEquals(1, $Keyword1->countAssociationsAsSubject());
        $this->assertEquals(0, $Keyword1->countAssociationsAsObject());
        $this->assertEquals(1, $Keyword1->countAssociations());

        $this->assertFalse($Keyword2->hasAssociationsAsSubject());
        $this->assertTrue($Keyword2->hasAssociationsAsObject());
        $this->assertEquals(0, $Keyword2->countAssociationsAsSubject());
        $this->assertEquals(1, $Keyword2->countAssociationsAsObject());
        $this->assertEquals(1, $Keyword2->countAssociations());
    }

}