<?php
/**
 * @author     valentin.claras
 * @package    TEC
 * @subpackage Test
 */

use TEC\Expression;
use TEC\Exception\InvalidExpressionException;

/**
 * @package    TEC
 * @subpackage Test
 */
class TEC_Test_ExpressionTest
{
    /**
     * lance les autre classe de tests
     */
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite();
        $suite->addTestSuite('TEC_Test_Expression');
        return $suite;
    }

}

/**
 * @package    TEC
 * @subpackage Test
 */
class TEC_Test_Expression extends Core_Test_TestCase
{
    /**
     * @expectedException TEC\Exception\InvalidExpressionException
     */
    public function testCheckInvalidExpression()
    {
        $expression = new Expression('a');
        $expression->check();
    }

    /**
     * Vérifie que les types d'expression sont correctement détectés.
     */
    public function testTypeDetection()
    {
        try {
            $expression = new Expression('');
            $this->fail('Empty expression not invalid');
        } catch (InvalidExpressionException $e) {
            // Expression vide : invalide.
        }

        try {
            $expression = new Expression('a');
            $this->fail('No symbol expression not invalid');
        } catch (InvalidExpressionException $e) {
            // Expression vide : invalide.
        }

        try {
            $expression = new Expression('a b');
            $this->fail('No symbol expression not invalid');
        } catch (InvalidExpressionException $e) {
            // Expression vide : invalide.
        }

        $expression = new Expression('a+b');
        $this->assertEquals($expression->getType(), Expression::TYPE_NUMERIC);

        $expression = new Expression('a-b');
        $this->assertEquals($expression->getType(), Expression::TYPE_NUMERIC);

        $expression = new Expression('a*b');
        $this->assertEquals($expression->getType(), Expression::TYPE_NUMERIC);

        $expression = new Expression('a/b');
        $this->assertEquals($expression->getType(), Expression::TYPE_NUMERIC);

        $expression = new Expression('a&b');
        $this->assertEquals($expression->getType(), Expression::TYPE_LOGICAL);

        $expression = new Expression('a|b');
        $this->assertEquals($expression->getType(), Expression::TYPE_LOGICAL);

        $expression = new Expression('!a');
        $this->assertEquals($expression->getType(), Expression::TYPE_LOGICAL);

        $expression = new Expression('a:b');
        $this->assertEquals($expression->getType(), Expression::TYPE_SELECT);

        try {
            $expression = new Expression('a + b & c');
            $this->fail('No symbol expression not invalid');
        } catch (InvalidExpressionException $e) {
            // Expression vide : invalide.
        }

        try {
            $expression = new Expression('a * b : c');
            $this->fail('No symbol expression not invalid');
        } catch (InvalidExpressionException $e) {
            // Expression multiple : invalide.
        }

        try {
            $expression = new Expression('a | b : c');
            $this->fail('No symbol expression not invalid');
        } catch (InvalidExpressionException $e) {
            // Expression multiple : invalide.
        }

        try {
            $expression = new Expression('!a - b / c');
            $this->fail('No symbol expression not invalid');
        } catch (InvalidExpressionException $e) {
            // Expression multiple : invalide.
        }
    }

}
