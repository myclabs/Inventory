<?php

use Core\Test\TestCase;
use Doctrine\ORM\UnitOfWork;
use Techno\Domain\Family\Dimension;
use Techno\Domain\Family\Family;
use Techno\Domain\Family\Member;

class Techno_Test_Family_MemberTest
{
    /**
     * Creation of the test suite
     */
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite();
        $suite->addTestSuite('Techno_Test_Family_MemberMetierTest');
        return $suite;
    }

    /**
     * Génere un objet dérivé prêt à l'emploi pour les tests.
     * @return Member
     */
    public static function generateObject()
    {
        /** @var \Doctrine\ORM\EntityManager $entityManager */
        $entityManager = Zend_Registry::get('EntityManagers')['default'];

        $member = new Member(
            Techno_Test_Family_DimensionTest::generateObject(),
            strtolower(Core_Tools::generateRef()),
            Core_Tools::generateString(10)
        );
        $member->save();
        $entityManager->flush();

        return $member;
    }

    public static function deleteObject(Member $o)
    {
        $o->delete();
        // Remove from the family to avoid cascade problems
        $o->getDimension()->removeMember($o);
        // Delete fixtures
        Techno_Test_Family_DimensionTest::deleteObject($o->getDimension());

        /** @var \Doctrine\ORM\EntityManager $entityManager */
        $entityManager = Zend_Registry::get('EntityManagers')['default'];
        $entityManager->flush();
    }
}

class Techno_Test_Family_MemberMetierTest extends TestCase
{
    public static function setUpBeforeClass()
    {
        /** @var \Doctrine\ORM\EntityManager $entityManager */
        $entityManager = Zend_Registry::get('EntityManagers')['default'];
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
        $entityManager->flush();
    }

    /**
     * Test de la position
     */
    public function testPosition()
    {
        $dimension = Techno_Test_Family_DimensionTest::generateObject();

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

        Techno_Test_Family_DimensionTest::deleteObject($dimension);
        $this->entityManager->flush();
    }

    /**
     * Teste la persistence en cascade depuis la dimension
     */
    public function testCascadeFromFamily()
    {
        // Fixtures
        $dimension = Techno_Test_Family_DimensionTest::generateObject();

        $o = new Member($dimension, Core_Tools::generateRef(), 'Test');

        // Vérification de la cascade de la persistence
        $dimension->save();
        $this->entityManager->flush();
        $this->assertEquals(UnitOfWork::STATE_MANAGED, $this->entityManager->getUnitOfWork()->getEntityState($o));

        // Vérification de la cascade de la suppression
        Techno_Test_Family_DimensionTest::deleteObject($dimension);
        $this->assertEquals(UnitOfWork::STATE_NEW, $this->entityManager->getUnitOfWork()->getEntityState($o));
        $this->entityManager->flush();
    }
}
