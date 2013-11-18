<?php

use Core\Test\TestCase;
use Doctrine\ORM\UnitOfWork;
use Keyword\Application\Service\KeywordService;
use Keyword\Domain\Keyword;
use Keyword\Domain\KeywordRepository;
use Techno\Domain\Meaning;

class Techno_Test_MeaningTest
{
    /**
     * Creation of the test suite
     */
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite();
        return $suite;
    }

    /**
     * Génere un objet dérivé prêt à l'emploi pour les tests.
     * @return Meaning
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
        $meaning = new Meaning();
        $meaning->setKeyword($keywordService->get($keywordRef));
        $meaning->save();
        $entityManager->flush();
        return $meaning;
    }

    /**
     * DeleteObject
     * @param Meaning $o
     */
    public static function deleteObject($o)
    {
        /** @var \Doctrine\ORM\EntityManager $entityManager */
        $entityManager = Zend_Registry::get('EntityManagers')['default'];
        /** @var KeywordRepository $keywordRepository */
        $keywordRepository = $entityManager->getRepository('Keyword\Domain\Keyword');
        $keywordRepository->remove($keywordRepository->getByRef($o->getKeyword()->getRef()));
        $o->delete();
        $entityManager->flush();
    }
}

class Techno_Test_MeaningSetUp extends TestCase
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
        if (Meaning::countTotal() > 0) {
            foreach (Meaning::loadList() as $o) {
                $o->delete();
            }
        }
        /** @var KeywordRepository $keywordRepository */
        $keywordRepository = $entityManager->getRepository('Keyword\Domain\Keyword');
        if ($keywordRepository->count() > 0) {
            foreach ($keywordRepository->getAll() as $o) {
                $keywordRepository->remove($o);
            }
        }
        $entityManager->flush();
    }

    public function setUp()
    {
        parent::setUp();
        $this->keywordService = $this->get('Keyword\Application\Service\KeywordService');
    }

    /**
     * @return Meaning $meaning
     */
    function testConstruct()
    {
        $keywordRef = strtolower(Core_Tools::generateString(20));
        /** @var KeywordRepository $keywordRepository */
        $keywordRepository = $this->entityManager->getRepository('Keyword\Domain\Keyword');
        $keywordRepository->add(new Keyword($keywordRef, 'Label'));
        $this->entityManager->flush();

        $o = new Meaning();
        $o->setKeyword($this->keywordService->get($keywordRef));
        $o->save();
        $this->entityManager->flush();

        $this->assertEquals($keywordRef, $o->getKeyword()->getRef());
        return $o;
    }

    /**
     * @depends testConstruct
     * @param Meaning $o
     * @return Meaning
     */
    function testLoad($o)
    {
        $this->entityManager->clear();
        /** @var $oLoaded Meaning */
        $oLoaded = Meaning::load($o->getKey());

        $this->assertInstanceOf('Techno\Domain\Meaning', $oLoaded);
        $this->assertNotSame($o, $oLoaded);
        $this->assertEquals($o->getKey(), $oLoaded->getKey());
        $this->assertInstanceOf('Keyword\Application\Service\KeywordDTO', $oLoaded->getKeyword());
        $this->assertEquals($o->getKeyword()->getRef(), $oLoaded->getKeyword()->getRef());
        return $oLoaded;
    }

    /**
     * @depends testLoad
     * @param Meaning $o
     */
    function testDelete($o)
    {
        /** @var KeywordRepository $keywordRepository */
        $keywordRepository = $this->entityManager->getRepository('Keyword\Domain\Keyword');
        $keyword = $keywordRepository->getByRef($o->getKeyword()->getRef());
        $keywordRepository->remove($keyword);
        $o->delete();
        $this->assertEquals(UnitOfWork::STATE_REMOVED, $this->entityManager->getUnitOfWork()->getEntityState($o));
        $this->entityManager->flush();
        $this->assertEquals(UnitOfWork::STATE_NEW, $this->entityManager->getUnitOfWork()->getEntityState($o));
    }

    /**
     * Test de la position
     */
    function testPosition()
    {
        $keywordRef1 = strtolower(Core_Tools::generateString(10));
        $keywordRef2 = strtolower(Core_Tools::generateString(10));
        /** @var KeywordRepository $keywordRepository */
        $keywordRepository = $this->entityManager->getRepository('Keyword\Domain\Keyword');
        $keywordRepository->add(new Keyword($keywordRef1, 'Label'));
        $keywordRepository->add(new Keyword($keywordRef2, 'Label'));
        $this->entityManager->flush();

        $o1 = new Meaning();
        $o1->setKeyword($this->keywordService->get($keywordRef1));
        $o1->setPosition();
        $o1->save();
        $o2 = new Meaning();
        $o2->setKeyword($this->keywordService->get($keywordRef2));
        $o2->setPosition();
        $o2->save();
        $this->entityManager->flush();

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

        $o1->delete();
        $o2->delete();
        $keywordRepository->remove($keywordRepository->getByRef($keywordRef1));
        $keywordRepository->remove($keywordRepository->getByRef($keywordRef2));
        $this->entityManager->flush();
    }
}
