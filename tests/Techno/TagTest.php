<?php
/**
 * @author matthieu.napoli
 * @package Techno
 */
use Keyword\Domain\KeywordRepository;
use Keyword\Application\Service\KeywordService;
use Keyword\Domain\Keyword;

/**
 * @package Techno
 */
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
     * @return Techno_Model_Tag
     */
    public static function generateObject()
    {
        $container = Zend_Registry::get('container');
        /** @var \Doctrine\ORM\EntityManager $entityManager */
        $entityManager = Zend_Registry::get('EntityManagers')['default'];
        /** @var KeywordRepository $keywordRepository */
        $keywordRepository = $entityManager->getRepository('\Keyword\Domain\Keyword');
        $keywordRef = strtolower(Core_Tools::generateString(10));
        $keywordRepository->add(new Keyword($keywordRef, 'Label'));
        $entityManager->flush();
        /** @var KeywordService $keywordService */
        $keywordService = $container->get('\Keyword\Application\Service\KeywordService');
        $meaningTest = new Techno_Test_MeaningTest();
        $meaning = $meaningTest->generateObject();
        $tag = new Techno_Model_Tag();
        $tag->setMeaning($meaning);
        $tag->setValue($keywordService->get($keywordRef));
        $tag->save();
        $entityManager->flush();
        return $tag;
    }

    /**
     * Deletion of an object created with generateObject
     * @param Techno_Model_Tag $o
     */
    public static function deleteObject($o)
    {
        $o->delete();
        Techno_Test_MeaningTest::deleteObject($o->getMeaning());
        /** @var \Doctrine\ORM\EntityManager $entityManager */
        $entityManager = Zend_Registry::get('EntityManagers')['default'];
        /** @var KeywordRepository $keywordRepository */
        $keywordRepository = $entityManager->getRepository('\Keyword\Domain\Keyword');
        $keywordRepository->remove($keywordRepository->getOneByRef($o->getValue()->getRef()));
        $entityManager->flush();
    }

}

/**
 *  @package Techno
 */
class Techno_Test_TagSetUp extends PHPUnit_Framework_TestCase
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;

    /**
     * @var KeywordService
     */
    private $keywordService;

    /**
     * Fonction appelee une fois, avant tous les tests
     */
    public static function setUpBeforeClass()
    {
        /** @var \Doctrine\ORM\EntityManager $entityManager */
        $entityManager = Zend_Registry::get('EntityManagers')['default'];
        // Vérification qu'il ne reste aucun objet en base, sinon suppression
        if (Techno_Model_Tag::countTotal() > 0) {
            foreach (Techno_Model_Tag::loadList() as $o) {
                $o->delete();
            }
        }
        if (Techno_Model_Meaning::countTotal() > 0) {
            foreach (Techno_Model_Meaning::loadList() as $o) {
                $o->delete();
            }
        }
        /** @var KeywordRepository $keywordRepository */
        $keywordRepository = $entityManager->getRepository('\Keyword\Domain\Keyword');
        if ($keywordRepository->count() > 0) {
            foreach ($keywordRepository->getAll() as $o) {
                $keywordRepository->remove($o);
            }
        }
        $entityManager->flush();
    }

    /**
     * Set up
     */
    public function setUp()
    {
        $this->entityManager = Zend_Registry::get('EntityManagers')['default'];
        $container = Zend_Registry::get('container');
        $this->keywordService = $container->get('\Keyword\Application\Service\KeywordService');
    }

    /**
     * @return Techno_Model_Tag
     */
    function testConstruct()
    {
        $keywordRef = 'keywordtest2';
        /** @var KeywordRepository $keywordRepository */
        $keywordRepository = $this->entityManager->getRepository('\Keyword\Domain\Keyword');
        $keywordRepository->add(new Keyword($keywordRef, 'Label'));
        $this->entityManager->flush();

        $meaningTest = new Techno_Test_MeaningTest();
        $meaning = $meaningTest->generateObject();

        $o = new Techno_Model_Tag();
        $o->setMeaning($meaning);
        $o->setValue($this->keywordService->get($keywordRef));

        $this->assertSame($meaning, $o->getMeaning());
        $this->assertEquals($keywordRef, $o->getValue()->getRef());

        $o->save();
        $this->entityManager->flush();

        $this->assertInstanceOf('Techno_Model_Meaning', $o->getMeaning());
        $this->assertEquals($meaning->getKey(), $o->getMeaning()->getKey());
        $this->assertInstanceOf('Keyword\Application\Service\KeywordDTO', $o->getValue());
        $this->assertEquals($keywordRef, $o->getValue()->getRef());
        return $o;
    }

    /**
     * @depends testConstruct
     * @param Techno_Model_Tag $o
     * @return Techno_Model_Tag
     */
    function testLoad($o)
    {
        $this->entityManager->clear('Techno_Model_Tag');
        /** @var $oLoaded Techno_Model_Tag */
        $oLoaded = Techno_Model_Tag::load($o->getKey());

        $this->assertInstanceOf('Techno_Model_Tag', $oLoaded);
        $this->assertNotSame($o, $oLoaded);
        $this->assertEquals($o->getKey(), $oLoaded->getKey());
        // getMeaning
        $this->assertInstanceOf('Techno_Model_Meaning', $oLoaded->getMeaning());
        $this->assertEquals($o->getMeaning()->getKey(), $oLoaded->getMeaning()->getKey());
        // getValue
        $this->assertInstanceOf('Keyword\Application\Service\KeywordDTO', $oLoaded->getValue());
        $this->assertEquals($o->getValue()->getRef(), $oLoaded->getValue()->getRef());
        return $oLoaded;
    }

    /**
     * @depends testLoad
     * @param Techno_Model_Tag $o
     */
    function testDelete($o)
    {
        /** @var KeywordRepository $keywordRepository */
        $keywordRepository = $this->entityManager->getRepository('\Keyword\Domain\Keyword');
        $keyword = $keywordRepository->getOneByRef($o->getValue()->getRef());
        $keywordRepository->remove($keyword);
        $o->delete();
        $this->assertEquals(\Doctrine\ORM\UnitOfWork::STATE_REMOVED,
            $this->entityManager->getUnitOfWork()->getEntityState($o));
        // Delete fixtures
        $meaningTest = new Techno_Test_MeaningTest();
        $meaningTest->deleteObject($o->getMeaning());
        $this->entityManager->flush();
        $this->assertEquals(\Doctrine\ORM\UnitOfWork::STATE_NEW,
            $this->entityManager->getUnitOfWork()->getEntityState($o));
    }

}
