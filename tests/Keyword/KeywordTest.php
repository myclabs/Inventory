<?php
/**
 * @author valentin.claras
 * @author bertrand.ferry
 * @package Keyword
 * @subpackage Test
 */
use Keyword\Domain\Keyword;
use Keyword\Domain\Predicate;
use Keyword\Domain\Association;

/**
 * Creation de la suite de test.
 * @package Keyword
 * @subpackage Test
 */
class Keyword_Test_KeywordTest extends Core_Test_TestCase
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
class Keyword_Test_KeywordEntity extends Core_Test_TestCase
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

        $Keyword = new Keyword('', 'Label');
        $this->assertInstanceOf('Keyword\Domain\Keyword', $Keyword);
        $this->assertEquals('label', $Keyword->getRef());
        $this->assertEquals('Label', $Keyword->getLabel());

        $Keyword = new Keyword(null, 'Label');
        $this->assertInstanceOf('Keyword\Domain\Keyword', $Keyword);
        $this->assertEquals('label', $Keyword->getRef());
        $this->assertEquals('Label', $Keyword->getLabel());

    }

}

/**
 * @package Keyword
 * @subpackage Test
 */
class Keyword_Test_AssociationEntity extends Core_Test_TestCase
{

    /**
     *
     */
    function testConstruct()
    {
        $Keyword1 = new Keyword('ref1');

        $Keyword2 = new Keyword('ref2', 'Label');
        
        $predicate1 = new Predicate('ref1', 'reverseref1');
        $predicate2 = new Predicate('ref2', 'reverseref2');

        $association = new Association($Keyword1, $predicate1, $Keyword2);
        $this->assertSame($Keyword1, $association->getSubject());
        $this->assertSame($predicate1, $association->getPredicate());
        $this->assertSame($Keyword2, $association->getObject());
        $this->assertTrue($Keyword1->hasAssociationAsSubject($association));
        $this->assertTrue($Keyword2->hasAssociationAsObject($association));
        $association->setPredicate($predicate2);
        $this->assertSame($predicate2, $association->getPredicate());
    }

}