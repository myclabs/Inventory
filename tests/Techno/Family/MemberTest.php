<?php

namespace Tests\Techno\Family;

use Core\Test\TestCase;
use Core_Tools;
use Doctrine\ORM\UnitOfWork;
use Techno\Domain\Family\Dimension;
use Techno\Domain\Family\Family;
use Techno\Domain\Family\Member;

/**
 * @covers \Techno\Domain\Family\Member
 */
class MemberTest extends TestCase
{
    /**
     * @return Member
     */
    public static function generateObject()
    {
        $member = new Member(
            DimensionTest::generateObject(),
            strtolower(Core_Tools::generateRef()),
            Core_Tools::generateString(10)
        );
        $member->save();
        self::getEntityManager()->flush();

        return $member;
    }

    public static function deleteObject(Member $o)
    {
        $o->delete();
        // Remove from the family to avoid cascade problems
        $o->getDimension()->removeMember($o);
        // Delete fixtures
        DimensionTest::deleteObject($o->getDimension());

        self::getEntityManager()->flush();
    }

    public static function setUpBeforeClass()
    {
        // Vérification qu'il ne reste aucun objet en base, sinon suppression
        foreach (Member::loadList() as $o) {
            $o->delete();
        }
        foreach (Dimension::loadList() as $o) {
            $o->delete();
        }
        foreach (Family::loadList() as $o) {
            $o->delete();
        }
        self::getEntityManager()->flush();
    }

    /**
     * Test de la position
     */
    public function testPosition()
    {
        $dimension = DimensionTest::generateObject();

        $ref1 = strtolower(Core_Tools::generateRef());
        $ref2 = strtolower(Core_Tools::generateRef());

        $o1 = new Member($dimension, $ref1, 'A');
        $o1->save();
        $o2 = new Member($dimension, $ref2, 'B');
        $o2->save();

        $this->assertEquals(1, $o1->getPosition());
        $this->assertEquals(2, $o2->getPosition());
        // setPosition
        $o2->setPosition(1);
        $o2->save();
        $this->entityManager->flush();
        $this->assertEquals(2, $o1->getPosition());
        $this->assertEquals(1, $o2->getPosition());
        // up
        $o1->goUp();
        $o1->save();
        $this->entityManager->flush();
        $this->assertEquals(1, $o1->getPosition());
        $this->assertEquals(2, $o2->getPosition());
        // down
        $o1->goDown();
        $o1->save();
        $this->entityManager->flush();
        $this->assertEquals(2, $o1->getPosition());
        $this->assertEquals(1, $o2->getPosition());
        // Delete
        $o2->delete();
        $this->assertEquals(1, $o1->getPosition());

        DimensionTest::deleteObject($dimension);
        $this->entityManager->flush();
    }

    /**
     * Teste la persistence en cascade depuis la dimension
     */
    public function testCascadeFromFamily()
    {
        // Fixtures
        $dimension = DimensionTest::generateObject();

        $o = new Member($dimension, Core_Tools::generateRef(), 'Test');

        // Vérification de la cascade de la persistence
        $dimension->save();
        $this->entityManager->flush();
        $this->assertEquals(UnitOfWork::STATE_MANAGED, $this->entityManager->getUnitOfWork()->getEntityState($o));

        // Vérification de la cascade de la suppression
        DimensionTest::deleteObject($dimension);
        $this->assertEquals(UnitOfWork::STATE_NEW, $this->entityManager->getUnitOfWork()->getEntityState($o));
        $this->entityManager->flush();
    }
}
