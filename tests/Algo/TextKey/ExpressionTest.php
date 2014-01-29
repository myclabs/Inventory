<?php

namespace Tests\Algo\TextKey;

use AF\Domain\Algorithm\Algo;
use Algo_Model_Selection_TextKey_Expression;
use AF\Domain\Algorithm\AlgoSet;
use Classif_Model_Context;
use Classif_Model_ContextIndicator;
use Core\Test\TestCase;
use Doctrine\ORM\UnitOfWork;
use TEC\Expression;

class ExpressionTest extends TestCase
{
    /**
     * Permet de générer un objet de base sur lequel on pourra travailler
     * @return Expression $o
     */
    public static function generateExpression()
    {
        return 'ConditionCheckbox:(:KeywordFixed;ConditionMulti:KeywordOption)';
    }

    public static function setUpBeforeClass()
    {
        foreach (AlgoSet::loadList() as $o) {
            $o->delete();
        }
        foreach (Algo::loadList() as $o) {
            $o->delete();
        }
        foreach (Classif_Model_ContextIndicator::loadList() as $o) {
            $o->delete();
        }
        foreach (Classif_Model_Context::loadList() as $o) {
            $o->delete();
        }
        self::getEntityManager()->flush();
    }

    /**
     * @return Algo_Model_Selection_TextKey_Expression
     */
    public function testConstruct()
    {
        $set = new AlgoSet();
        $set->save();
        $expression = self::generateExpression();
        $this->entityManager->flush();

        $o = new Algo_Model_Selection_TextKey_Expression();
        $o->setSet($set);
        $o->setRef('test');
        $o->setExpression($expression);
        $o->save();
        $this->entityManager->flush();

        $this->assertEquals('test', $o->getRef());
        $this->assertEquals($expression, str_replace(' ', '', $o->getExpression()));

        return $o;
    }

    /**
     * @depends testConstruct
     * @param Algo_Model_Selection_TextKey_Expression $o
     * @return Algo_Model_Selection_TextKey_Expression
     */
    public function testLoad(Algo_Model_Selection_TextKey_Expression $o)
    {
        $this->entityManager->clear();
        /** @var $oLoaded Algo_Model_Selection_TextKey_Expression */
        $oLoaded = Algo_Model_Selection_TextKey_Expression::load($o->getId());

        $this->assertInstanceOf(Algo_Model_Selection_TextKey_Expression::class, $oLoaded);
        $this->assertNotSame($o, $oLoaded);
        $this->assertEquals($o->getId(), $oLoaded->getId());
        $this->assertEquals($o->getRef(), $oLoaded->getRef());
        $this->assertNotNull($oLoaded->getExpression());
        $this->assertEquals($o->getExpression(), $oLoaded->getExpression());
        return $oLoaded;
    }

    /**
     * @depends testLoad
     * @param Algo_Model_Selection_TextKey_Expression $o
     */
    public function testDelete(Algo_Model_Selection_TextKey_Expression $o)
    {
        $o->delete();
        $o->getSet()->delete();
        $this->assertEquals(
            UnitOfWork::STATE_REMOVED,
            $this->entityManager->getUnitOfWork()->getEntityState($o)
        );
        $this->entityManager->flush();
        $this->assertEquals(
            UnitOfWork::STATE_NEW,
            $this->entityManager->getUnitOfWork()->getEntityState($o)
        );
    }
}
