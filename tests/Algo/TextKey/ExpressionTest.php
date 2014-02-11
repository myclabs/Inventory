<?php

namespace Tests\Algo\TextKey;

use AF\Domain\Algorithm\Algo;
use AF\Domain\Algorithm\Selection\TextKey\ExpressionSelectionAlgo;
use AF\Domain\Algorithm\AlgoSet;
use Classif\Domain\Context;
use Classif\Domain\ContextIndicator;
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
        foreach (ContextIndicator::loadList() as $o) {
            $o->delete();
        }
        foreach (Context::loadList() as $o) {
            $o->delete();
        }
        self::getEntityManager()->flush();
    }

    /**
     * @return ExpressionSelectionAlgo
     */
    public function testConstruct()
    {
        $set = new AlgoSet();
        $set->save();
        $expression = self::generateExpression();
        $this->entityManager->flush();

        $o = new ExpressionSelectionAlgo();
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
     * @param \AF\Domain\Algorithm\Selection\TextKey\ExpressionSelectionAlgo $o
     * @return ExpressionSelectionAlgo
     */
    public function testLoad(ExpressionSelectionAlgo $o)
    {
        $this->entityManager->clear();
        /** @var $oLoaded ExpressionSelectionAlgo */
        $oLoaded = ExpressionSelectionAlgo::load($o->getId());

        $this->assertInstanceOf(ExpressionSelectionAlgo::class, $oLoaded);
        $this->assertNotSame($o, $oLoaded);
        $this->assertEquals($o->getId(), $oLoaded->getId());
        $this->assertEquals($o->getRef(), $oLoaded->getRef());
        $this->assertNotNull($oLoaded->getExpression());
        $this->assertEquals($o->getExpression(), $oLoaded->getExpression());
        return $oLoaded;
    }

    /**
     * @depends testLoad
     * @param \AF\Domain\Algorithm\Selection\TextKey\ExpressionSelectionAlgo $o
     */
    public function testDelete(ExpressionSelectionAlgo $o)
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
