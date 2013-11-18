<?php

use Core\Test\TestCase;
use Doctrine\ORM\UnitOfWork;
use Keyword\Domain\KeywordRepository;
use Keyword\Application\Service\KeywordService;
use Keyword\Domain\Keyword;
use Techno\Domain\Meaning;
use Techno\Domain\Tag;

class Techno_Test_TagTest
{
    /**
     * Creation of the test suite
     */
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite();
        $suite->addTestSuite('Techno_Test_TagSetUp');
        return $suite;
    }

    /**
     * Génere un objet dérivé prêt à l'emploi pour les tests.
     * @return Tag
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
        $meaningTest = new Techno_Test_MeaningTest();
        $meaning = $meaningTest->generateObject();
        $tag = new Tag();
        $tag->setMeaning($meaning);
        $tag->setValue($keywordService->get($keywordRef));
        $tag->save();
        $entityManager->flush();
        return $tag;
    }

    /**
     * Deletion of an object created with generateObject
     * @param Tag $o
     */
    public static function deleteObject($o)
    {
        $o->delete();
        Techno_Test_MeaningTest::deleteObject($o->getMeaning());
        /** @var \Doctrine\ORM\EntityManager $entityManager */
        $entityManager = Zend_Registry::get('EntityManagers')['default'];
        /** @var KeywordRepository $keywordRepository */
        $keywordRepository = $entityManager->getRepository('Keyword\Domain\Keyword');
        $keywordRepository->remove($keywordRepository->getByRef($o->getValue()->getRef()));
        $entityManager->flush();
    }
}

class Techno_Test_TagSetUp extends TestCase
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
        foreach (Tag::loadList() as $o) {
            $o->delete();
        }
        foreach (Meaning::loadList() as $o) {
            $o->delete();
        }
        /** @var KeywordRepository $keywordRepository */
        $keywordRepository = $entityManager->getRepository('Keyword\Domain\Keyword');
        foreach ($keywordRepository->getAll() as $o) {
            $keywordRepository->remove($o);
        }
        $entityManager->flush();
    }

    public function setUp()
    {
        parent::setUp();
        $this->keywordService = $this->get('Keyword\Application\Service\KeywordService');
    }

    /**
     * @return Tag
     */
    public function testConstruct()
    {
        $keywordRef = 'keywordtest2';
        /** @var KeywordRepository $keywordRepository */
        $keywordRepository = $this->entityManager->getRepository('Keyword\Domain\Keyword');
        $keywordRepository->add(new Keyword($keywordRef, 'Label'));
        $this->entityManager->flush();

        $meaningTest = new Techno_Test_MeaningTest();
        $meaning = $meaningTest->generateObject();

        $o = new Tag();
        $o->setMeaning($meaning);
        $o->setValue($this->keywordService->get($keywordRef));

        $this->assertSame($meaning, $o->getMeaning());
        $this->assertEquals($keywordRef, $o->getValue()->getRef());

        $o->save();
        $this->entityManager->flush();

        $this->assertInstanceOf('Techno\Domain\Meaning', $o->getMeaning());
        $this->assertEquals($meaning->getKey(), $o->getMeaning()->getKey());
        $this->assertInstanceOf('Keyword\Application\Service\KeywordDTO', $o->getValue());
        $this->assertEquals($keywordRef, $o->getValue()->getRef());
        return $o;
    }

    /**
     * @depends testConstruct
     * @param Tag $o
     * @return Tag
     */
    public function testLoad($o)
    {
        $this->entityManager->clear('Techno\Domain\Tag');
        /** @var $oLoaded Tag */
        $oLoaded = Tag::load($o->getKey());

        $this->assertInstanceOf('Techno\Domain\Tag', $oLoaded);
        $this->assertNotSame($o, $oLoaded);
        $this->assertEquals($o->getKey(), $oLoaded->getKey());
        // getMeaning
        $this->assertInstanceOf('Techno\Domain\Meaning', $oLoaded->getMeaning());
        $this->assertEquals($o->getMeaning()->getKey(), $oLoaded->getMeaning()->getKey());
        // getValue
        $this->assertInstanceOf('Keyword\Application\Service\KeywordDTO', $oLoaded->getValue());
        $this->assertEquals($o->getValue()->getRef(), $oLoaded->getValue()->getRef());
        return $oLoaded;
    }

    /**
     * @depends testLoad
     * @param Tag $o
     */
    public function testDelete($o)
    {
        /** @var KeywordRepository $keywordRepository */
        $keywordRepository = $this->entityManager->getRepository('Keyword\Domain\Keyword');
        $keyword = $keywordRepository->getByRef($o->getValue()->getRef());
        $keywordRepository->remove($keyword);
        $o->delete();
        $this->assertEquals(UnitOfWork::STATE_REMOVED, $this->entityManager->getUnitOfWork()->getEntityState($o));
        // Delete fixtures
        $meaningTest = new Techno_Test_MeaningTest();
        $meaningTest->deleteObject($o->getMeaning());
        $this->entityManager->flush();
        $this->assertEquals(UnitOfWork::STATE_NEW, $this->entityManager->getUnitOfWork()->getEntityState($o));
    }
}
