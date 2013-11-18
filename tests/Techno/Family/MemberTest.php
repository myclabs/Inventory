<?php

use Core\Test\TestCase;
use Doctrine\ORM\UnitOfWork;
use Keyword\Application\Service\KeywordService;
use Keyword\Domain\KeywordRepository;
use Keyword\Domain\Keyword;
use Techno\Domain\Component;
use Techno\Domain\Family\Dimension;
use Techno\Domain\Family\Member;

class Techno_Test_Family_MemberTest
{
    /**
     * Creation of the test suite
     */
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite();
        $suite->addTestSuite('Techno_Test_Family_MemberSetUp');
        $suite->addTestSuite('Techno_Test_Family_MemberMetierTest');
        return $suite;
    }

    /**
     * Génere un objet dérivé prêt à l'emploi pour les tests.
     * @return Member
     */
    public static function generateObject()
    {
        $container = Zend_Registry::get('container');
        /** @var \Doctrine\ORM\EntityManager $entityManager */
        $entityManager = Zend_Registry::get('EntityManagers')['default'];
        /** @var KeywordRepository $keywordRepository */
        $keywordRepository = $entityManager->getRepository('Keyword\Domain\Keyword');
        $keywordRef = strtolower(Core_Tools::generateString(10));
        $keywordRepository->add(new Keyword($keywordRef, 'Label'));
        $entityManager->flush();
        /** @var KeywordService $keywordService */
        $keywordService = $container->get('Keyword\Application\Service\KeywordService');
        $member = new Member(
            Techno_Test_Family_DimensionTest::generateObject(),
            $keywordService->get($keywordRef)
        );
        $member->save();
        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->flush();
        return $member;
    }

    public static function deleteObject(Member $o)
    {
        $o->delete();
        // Remove from the family to avoid cascad problems
        $o->getDimension()->removeMember($o);
        // Delete fixtures
        Techno_Test_Family_DimensionTest::deleteObject($o->getDimension());
        /** @var \Doctrine\ORM\EntityManager $entityManager */
        $entityManager = Zend_Registry::get('EntityManagers')['default'];
        /** @var KeywordRepository $keywordRepository */
        $keywordRepository = $entityManager->getRepository('Keyword\Domain\Keyword');
        $keywordRepository->remove($keywordRepository->getByRef($o->getKeyword()->getRef()));
        $entityManager->flush();
    }
}

class Techno_Test_Family_MemberSetUp extends TestCase
{
    /**
     * @var KeywordService
     */
    private $keywordService;

    public static function setUpBeforeClass()
    {
        /** @var \Doctrine\ORM\EntityManager $entityManager */
        $entityManager = Zend_Registry::get('EntityManagers')['default'];
        // Vérification qu'il ne reste aucun objet en base, sinon suppression
        /** @var KeywordRepository $keywordRepository */
        $keywordRepository = $entityManager->getRepository('Keyword\Domain\Keyword');
        foreach ($keywordRepository->getAll() as $o) {
            $keywordRepository->remove($o);
        }
        foreach (Member::loadList() as $o) {
            $o->delete();
        }
        foreach (Dimension::loadList() as $o) {
            $o->delete();
        }
        foreach (Component::loadList() as $o) {
            $o->delete();
        }
        $entityManager->flush();
    }

    public function setUp()
    {
        parent::setUp();
        $this->keywordService = $this->get('Keyword\Application\Service\KeywordService');
    }

    /**
     * @return Member $Family_Member
     */
    public function testConstruct()
    {
        $keywordRef = 'keywordtest';
        /** @var KeywordRepository $keywordRepository */
        $keywordRepository = $this->entityManager->getRepository('Keyword\Domain\Keyword');
        $keywordRepository->add(new Keyword($keywordRef, 'Label'));
        $this->entityManager->flush();

        $o = new Member(Techno_Test_Family_DimensionTest::generateObject(),
                                            $this->keywordService->get($keywordRef));

        $this->assertEquals($keywordRef, $o->getKeyword()->getRef());

        $o->save();
        $this->entityManager->flush();

        $this->assertInstanceOf('Keyword\Application\Service\KeywordDTO', $o->getKeyword());
        $this->assertEquals($keywordRef, $o->getKeyword()->getRef());
        return $o;
    }

    /**
     * @depends testConstruct
     * @param Member $o
     * @return Member
     */
    public function testLoad($o)
    {
        $this->entityManager->clear();
        /** @var $oLoaded Member */
        $oLoaded = Member::load($o->getKey());

        $this->assertInstanceOf('Techno\Domain\Family\Member', $oLoaded);
        $this->assertNotSame($o, $oLoaded);
        $this->assertEquals($o->getKey(), $oLoaded->getKey());
        // Keyword
        $this->assertInstanceOf('Keyword\Application\Service\KeywordDTO', $oLoaded->getKeyword());
        $this->assertEquals($o->getKeyword()->getRef(), $oLoaded->getKeyword()->getRef());
        // Dimension
        $this->assertEquals($o->getDimension()->getKey(), $oLoaded->getDimension()->getKey());
        $this->assertTrue($oLoaded->getDimension()->hasMember($oLoaded));
        return $oLoaded;
    }

    /**
     * @depends testLoad
     * @param Member $o
     */
    public function testDelete($o)
    {
        /** @var KeywordRepository $keywordRepository */
        $keywordRepository = $this->entityManager->getRepository('Keyword\Domain\Keyword');
        $keyword = $keywordRepository->getByRef($o->getKeyword()->getRef());
        $keywordRepository->remove($keyword);
        $o->delete();
        $this->assertEquals(UnitOfWork::STATE_REMOVED, $this->entityManager->getUnitOfWork()->getEntityState($o));
        // Remove from the family to avoid cascad problems
        $this->assertCount(1, $o->getDimension()->getMembers());
        $o->getDimension()->removeMember($o);
        $this->assertCount(0, $o->getDimension()->getMembers());
        // Delete fixtures
        Techno_Test_Family_DimensionTest::deleteObject($o->getDimension());
        $this->entityManager->flush();
        $this->assertEquals(UnitOfWork::STATE_NEW, $this->entityManager->getUnitOfWork()->getEntityState($o));
    }
}

class Techno_Test_Family_MemberMetierTest extends TestCase
{
    /**
     * @var KeywordService
     */
    private $keywordService;

    public static function setUpBeforeClass()
    {
        /** @var \Doctrine\ORM\EntityManager $entityManager */
        $entityManager = Zend_Registry::get('EntityManagers')['default'];
        // Vérification qu'il ne reste aucun objet en base, sinon suppression
        /** @var KeywordRepository $keywordRepository */
        $keywordRepository = $entityManager->getRepository('Keyword\Domain\Keyword');
        foreach ($keywordRepository->getAll() as $o) {
            $keywordRepository->remove($o);
        }
        foreach (Member::loadList() as $o) {
            $o->delete();
        }
        foreach (Dimension::loadList() as $o) {
            $o->delete();
        }
        foreach (Component::loadList() as $o) {
            $o->delete();
        }
        $entityManager->flush();
    }

    public function setUp()
    {
        parent::setUp();
        $this->keywordService = $this->get('Keyword\Application\Service\KeywordService');
    }

    /**
     * Test de la position
     */
    public function testPosition()
    {
        $dimension = Techno_Test_Family_DimensionTest::generateObject();

        $keywordRef1 = strtolower(Core_Tools::generateString(10));
        $keywordRef2 = strtolower(Core_Tools::generateString(10));
        /** @var KeywordRepository $keywordRepository */
        $keywordRepository = $this->entityManager->getRepository('Keyword\Domain\Keyword');
        $keywordRepository->add(new Keyword($keywordRef1, 'Label'));
        $keywordRepository->add(new Keyword($keywordRef2, 'Label'));
        $this->entityManager->flush();

        $o1 = new Member($dimension, $this->keywordService->get($keywordRef1));
        $o1->save();
        $o2 = new Member($dimension, $this->keywordService->get($keywordRef2));
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
        $keywordRepository->remove($keywordRepository->getByRef($keywordRef1));
        $keywordRepository->remove($keywordRepository->getByRef($keywordRef2));
        $this->entityManager->flush();
    }

    /**
     * Teste l'association à sa dimension
     */
    public function testBidirectionalDimensionAssociation()
    {
        // Fixtures
        $dimension = Techno_Test_Family_DimensionTest::generateObject();

        $keywordRef = strtolower(Core_Tools::generateString(10));
        /** @var KeywordRepository $keywordRepository */
        $keywordRepository = $this->entityManager->getRepository('Keyword\Domain\Keyword');
        $keywordRepository->add(new Keyword($keywordRef, 'Label'));
        $this->entityManager->flush();

        // Charge la collection pour éviter le lazy-loading en dessous
        // (le lazy loading entrainerait le chargement depuis la BDD et donc la prise en compte
        // de l'association BDD même si elle n'était pas faite au niveau PHP)
        $members = $dimension->getMembers();
        $this->assertCount(0, $members);

        $o = new Member($dimension, $this->keywordService->get($keywordRef));

        // Vérifie que l'association a été affectée bidirectionnellement
        $this->assertTrue($dimension->hasMember($o));

        Techno_Test_Family_CoeffTest::deleteObject($dimension->getFamily());
        $keywordRepository->remove($keywordRepository->getByRef($keywordRef));
        $this->entityManager->flush();
    }

    /**
     * Teste la persistence en cascade depuis la dimension
     */
    public function testCascadeFromFamily()
    {
        // Fixtures
        $dimension = Techno_Test_Family_DimensionTest::generateObject();

        $keywordRef = strtolower(Core_Tools::generateString(10));
        /** @var KeywordRepository $keywordRepository */
        $keywordRepository = $this->entityManager->getRepository('Keyword\Domain\Keyword');
        $keywordRepository->add(new Keyword($keywordRef, 'Label'));
        $this->entityManager->flush();

        $o = new Member($dimension, $this->keywordService->get($keywordRef));

        // Vérification de la cascade de la persistence
        $dimension->save();
        $this->entityManager->flush();
        $this->assertEquals(UnitOfWork::STATE_MANAGED, $this->entityManager->getUnitOfWork()->getEntityState($o));

        // Vérification de la cascade de la suppression
        Techno_Test_Family_DimensionTest::deleteObject($dimension);
        $this->assertEquals(UnitOfWork::STATE_NEW, $this->entityManager->getUnitOfWork()->getEntityState($o));
        $keywordRepository->remove($keywordRepository->getByRef($keywordRef));
        $this->entityManager->flush();
    }
}
