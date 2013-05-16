<?php
/**
 * Test de l'objet métier Expression
 * @author valentin.claras
 * @author hugo.charboniere
 * @author yoann.croizer
 * @package TEC
 */

/**
 * ExpressionTest
 * @package TEC
 */
class TEC_Test_ExpressionTest
{
    /**
     * lance les autre classe de tests
     */
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite();
        $suite->addTestSuite('TEC_Test_ExpressionSetUp');
        $suite->addTestSuite('expressionLogiqueMetierTest');
        return $suite;
    }

    /**
     * Permet de générer un objet de base sur lequel on pourra travailler
     * @param string $expression
     * @return TEC_Model_Expression $o
     */
    public static function generateObject($expression)
    {
        $o = new TEC_Model_Expression($expression);
        $o->save();
        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->flush();
        return $o;
    }

    /**
     * Permet de supprimer l'arbre associé à une expression en supprimant
     * tous les noeuds qui lui son associés.
     * @param TEC_Model_Expression $o
     */
    public static function deleteObject(TEC_Model_Expression $o)
    {
        $o->delete();
        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->flush();
    }

}

/**
 * expressionSetUpTest
 * @package TEC
 */
class TEC_Test_ExpressionSetUp extends PHPUnit_Framework_TestCase
{

    /**
     * Méthode appelée avant l'appel à la classe de test
     */
    public static function setUpBeforeClass()
    {
        // Vérification qu'il ne reste aucun TEC_Model_Expression en base, sinon suppression !
        if (TEC_Model_Expression::countTotal() > 0) {
            echo PHP_EOL . 'Des TEC_Expression restantes ont été trouvé avant les tests, suppression en cours !';
            foreach (TEC_Model_Expression::loadList() as $component) {
                $component->delete();
            }
            $entityManagers = Zend_Registry::get('EntityManagers');
            $entityManagers['default']->flush();
        }
    }

    /**
     * Méthode appelée avant chaque test
     */
    protected function setUp()
    {
    }

    /**
     * Test des constructeurs et de la sauvegarde en base de données
     * @return TEC_Model_Expression
     */
    function testConstruct()
    {
        $o = new TEC_Model_Expression('a+b');
        $o->save();
        $this->assertInstanceOf('TEC_Model_Expression', $o);
        $this->assertEquals(array(), $o->getKey());
        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->flush();
        $this->assertNotEquals(array(), $o->getKey());
        $this->assertEquals('a+b', $o->getExpression());
        return $o;
    }

    /**
     * @depends testConstruct
     * @param TEC_Model_Expression $o
     * @return TEC_Model_Expression
     */
    function testLoad(TEC_Model_Expression $o)
    {
        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->clear($o);
        $oLoaded = TEC_Model_Expression::load($o->getKey());
        $this->assertInstanceOf('TEC_Model_Expression', $o);
        $this->assertEquals($oLoaded->getKey(), $o->getKey());
        $this->assertEquals($oLoaded->getExpression(), 'a+b');
        return $oLoaded;
    }

    /**
     * @depends testLoad
     * @param TEC_Model_Expression $o
     */
    function testDelete(TEC_Model_Expression $o)
    {
        $o->delete();
        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->flush();
        $this->assertEquals(array(), $o->getKey());
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
        // Vérification qu'il ne reste aucun TEC_Model_Expression en base, sinon suppression !
        if (TEC_Model_Expression::countTotal() > 0) {
            echo PHP_EOL . 'Des TEC_Expression restantes ont été trouvé après les tests, suppression en cours !';
            foreach (TEC_Model_Expression::loadList() as $component) {
                $component->delete();
            }
            $entityManagers = Zend_Registry::get('EntityManagers');
            $entityManagers['default']->flush();
        }
    }

}

/**
 * expressionLogiqueMetierTest
 * @package TEC
 */
class expressionLogiqueMetierTest extends PHPUnit_Framework_TestCase
{
     /**
      * Méthode appelée avant l'appel à la classe de test
      */
     public static function setUpBeforeClass()
     {
        // Vérification qu'il ne reste aucun TEC_Model_Expression en base, sinon suppression !
        if (TEC_Model_Expression::countTotal() > 0) {
            echo PHP_EOL . 'Des TEC_Expression restantes ont été trouvé avant les tests, suppression en cours !';
            foreach (TEC_Model_Expression::loadList() as $component) {
                $component->delete();
            }
            $entityManagers = Zend_Registry::get('EntityManagers');
            $entityManagers['default']->flush();
        }
        // Vérification qu'il ne reste aucun TEC_Model_Component en base, sinon suppression !
        if (TEC_Model_Component::countTotal() > 0) {
            echo PHP_EOL . 'Des TEC_Component restants ont été trouvé avant les tests, suppression en cours !';
            foreach (TEC_Model_Component::loadList() as $component) {
                $component->delete();
            }
            $entityManagers = Zend_Registry::get('EntityManagers');
            $entityManagers['default']->flush();
        }
     }

    /**
     * @expectedException TEC_Model_InvalidExpressionException
     */
    public function testCheckInvalidExpression()
    {
        $expression = new TEC_Model_Expression();
        $expression->setExpression('a');
        $expression->check();
    }

    /**
     * Vérifie que les types d'expression sont correctement détectés.
     */
    public function testTypeDetection()
    {
        try {
            $expression = new TEC_Model_Expression();
            $expression->setExpression('');
            $this->fail('Empty expression not invalid');
        } catch (TEC_Model_InvalidExpressionException $e) {
            // Expression vide : invalide.
        }

        try {
            $expression = new TEC_Model_Expression();
            $expression->setExpression('a');
            $this->fail('No symbol expression not invalid');
        } catch (TEC_Model_InvalidExpressionException $e) {
            // Expression vide : invalide.
        }

        try {
            $expression = new TEC_Model_Expression();
            $expression->setExpression('a b');
            $this->fail('No symbol expression not invalid');
        } catch (TEC_Model_InvalidExpressionException $e) {
            // Expression vide : invalide.
        }

        $expression = new TEC_Model_Expression();
        $expression->setExpression('a+b');
        $this->assertEquals($expression->getType(), TEC_Model_Expression::TYPE_NUMERIC);

        $expression = new TEC_Model_Expression();
        $expression->setExpression('a-b');
        $this->assertEquals($expression->getType(), TEC_Model_Expression::TYPE_NUMERIC);

        $expression = new TEC_Model_Expression();
        $expression->setExpression('a*b');
        $this->assertEquals($expression->getType(), TEC_Model_Expression::TYPE_NUMERIC);

        $expression = new TEC_Model_Expression();
        $expression->setExpression('a/b');
        $this->assertEquals($expression->getType(), TEC_Model_Expression::TYPE_NUMERIC);

        $expression = new TEC_Model_Expression();
        $expression->setExpression('a&b');
        $this->assertEquals($expression->getType(), TEC_Model_Expression::TYPE_LOGICAL);

        $expression = new TEC_Model_Expression();
        $expression->setExpression('a|b');
        $this->assertEquals($expression->getType(), TEC_Model_Expression::TYPE_LOGICAL);

        $expression = new TEC_Model_Expression();
        $expression->setExpression('!a');
        $this->assertEquals($expression->getType(), TEC_Model_Expression::TYPE_LOGICAL);

        $expression = new TEC_Model_Expression();
        $expression->setExpression('a:b');
        $this->assertEquals($expression->getType(), TEC_Model_Expression::TYPE_SELECT);

        try {
            $expression = new TEC_Model_Expression();
            $expression->setExpression('a + b & c');
            $this->fail('No symbol expression not invalid');
        } catch (TEC_Model_InvalidExpressionException $e) {
            // Expression vide : invalide.
        }

        try {
            $expression = new TEC_Model_Expression();
            $expression->setExpression('a * b : c');
            $this->fail('No symbol expression not invalid');
        } catch (TEC_Model_InvalidExpressionException $e) {
            // Expression multiple : invalide.
        }

        try {
            $expression = new TEC_Model_Expression();
            $expression->setExpression('a | b : c');
            $this->fail('No symbol expression not invalid');
        } catch (TEC_Model_InvalidExpressionException $e) {
            // Expression multiple : invalide.
        }

        try {
            $expression = new TEC_Model_Expression();
            $expression->setExpression('!a - b / c;');
            $this->fail('No symbol expression not invalid');
        } catch (TEC_Model_InvalidExpressionException $e) {
            // Expression multiple : invalide.
        }
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
        // Vérification qu'il ne reste aucun TEC_Model_Expression en base, sinon suppression !
        if (TEC_Model_Expression::countTotal() > 0) {
            echo PHP_EOL . 'Des TEC_Expression restantes ont été trouvé après les tests, suppression en cours !';
            foreach (TEC_Model_Expression::loadList() as $component) {
                $component->delete();
            }
            $entityManagers = Zend_Registry::get('EntityManagers');
            $entityManagers['default']->flush();
        }
        // Vérification qu'il ne reste aucun TEC_Model_Component en base, sinon suppression !
        if (TEC_Model_Component::countTotal() > 0) {
            echo PHP_EOL . 'Des TEC_Component restants ont été trouvé après les tests, suppression en cours !';
            foreach (TEC_Model_Component::loadList() as $component) {
                $component->delete();
            }
            $entityManagers = Zend_Registry::get('EntityManagers');
            $entityManagers['default']->flush();
        }
    }

}
