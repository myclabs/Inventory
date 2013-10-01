<?php
/**
 * @author valentin.claras
 * @author bertrand.ferry
 * @package Keyword
 * @subpackage Test
 */
use Keyword\Domain\Predicate;

/**
 * Creation de la suite de test.
 * @package Keyword
 * @subpackage Test
 */
class Keyword_Test_PredicateTest extends Core_Test_TestCase
{
    /**
     * Creation de la suite de test.
     */
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite();
        $suite->addTestSuite('Keyword_Test_PredicateEntity');
        return $suite;
    }
}

/**
 * @package Keyword
 * @subpackage Test
 */
class Keyword_Test_PredicateEntity extends Core_Test_TestCase
{

    /**
     *
     */
    function testConstruct()
    {
        $predicate = new Predicate('ref', 'reverseref');
        $this->assertInstanceOf('Keyword\Domain\Predicate', $predicate);
        $this->assertEquals('ref', $predicate->getRef());
        $this->assertEquals('reverseref', $predicate->getReverseRef());
        $this->assertEquals('', $predicate->getLabel());
        $this->assertEquals('', $predicate->getReverseLabel());

        $predicate = new Predicate('ref', 'reverseref', 'Label', 'ReverseLabel');
        $this->assertInstanceOf('Keyword\Domain\Predicate', $predicate);
        $this->assertEquals('ref', $predicate->getRef());
        $this->assertEquals('reverseref', $predicate->getReverseRef());
        $this->assertEquals('Label', $predicate->getLabel());
        $this->assertEquals('ReverseLabel', $predicate->getReverseLabel());

        $predicate = new Predicate(null, '', 'Label', 'ReverseLabel');
        $this->assertInstanceOf('Keyword\Domain\Predicate', $predicate);
        $this->assertEquals('label', $predicate->getRef());
        $this->assertEquals('reverselabel', $predicate->getReverseRef());
        $this->assertEquals('Label', $predicate->getLabel());
        $this->assertEquals('ReverseLabel', $predicate->getReverseLabel());
    }

    /**
     * @depends testConstruct
     */
    function testDescription()
    {
        $predicate = new Predicate('ref', 'reverseref');
        $this->assertEquals('', $predicate->getDescription());
        $predicate->setDescription('Une description pour le predicate');
        $this->assertEquals('Une description pour le predicate', $predicate->getDescription());
    }

}