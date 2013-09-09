<?php
/**
 * Creation of the Techno Meaning test.
 * @package Techno
 */
use Keyword\Application\Service\KeywordService;
use Keyword\Domain\Keyword;
use Keyword\Domain\KeywordRepository;

/**
 * Test Techno package.
 * @package Techno
 */
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
     * @return Techno_Model_Meaning
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
        $meaning = new Techno_Model_Meaning();
        $meaning->setKeyword($keywordService->get($keywordRef));
        $meaning->save();
        $entityManager->flush();
        return $meaning;
    }

    /**
     * DeleteObject
     * @param Techno_Model_Meaning $o
     */
    public static function deleteObject($o)
    {
        /** @var \Doctrine\ORM\EntityManager $entityManager */
        $entityManager = Zend_Registry::get('EntityManagers')['default'];
        /** @var KeywordRepository $keywordRepository */
        $keywordRepository = $entityManager->getRepository('\Keyword\Domain\Keyword');
        $keywordRepository->remove($keywordRepository->getByRef($o->getKeyword()->getRef()));
        $o->delete();
        $entityManager->flush();
    }
}

/**
 *  @package Techno
 */
class Techno_Test_MeaningSetUp extends PHPUnit_Framework_TestCase
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
     * @return Techno_Model_Meaning $meaning
     */
    function testConstruct()
    {
        $keywordRef = strtolower(Core_Tools::generateString(20));
        /** @var KeywordRepository $keywordRepository */
        $keywordRepository = $this->entityManager->getRepository('\Keyword\Domain\Keyword');
        $keywordRepository->add(new Keyword($keywordRef, 'Label'));
        $this->entityManager->flush();

        $o = new Techno_Model_Meaning();
        $o->setKeyword($this->keywordService->get($keywordRef));
        $o->save();
        $this->entityManager->flush();

        $this->assertEquals($keywordRef, $o->getKeyword()->getRef());
        return $o;
    }

    /**
     * @depends testConstruct
     * @param Techno_Model_Meaning $o
     * @return Techno_Model_Meaning
     */
    function testLoad($o)
    {
        $this->entityManager->clear();
        /** @var $oLoaded Techno_Model_Meaning */
        $oLoaded = Techno_Model_Meaning::load($o->getKey());

        $this->assertInstanceOf('Techno_Model_Meaning', $oLoaded);
        $this->assertNotSame($o, $oLoaded);
        $this->assertEquals($o->getKey(), $oLoaded->getKey());
        $this->assertInstanceOf('Keyword\Application\Service\KeywordDTO', $oLoaded->getKeyword());
        $this->assertEquals($o->getKeyword()->getRef(), $oLoaded->getKeyword()->getRef());
        return $oLoaded;
    }

    /**
     * @depends testLoad
     * @param Techno_Model_Meaning $o
     */
    function testDelete($o)
    {
        /** @var KeywordRepository $keywordRepository */
        $keywordRepository = $this->entityManager->getRepository('\Keyword\Domain\Keyword');
        $keyword = $keywordRepository->getByRef($o->getKeyword()->getRef());
        $keywordRepository->remove($keyword);
        $o->delete();
        $this->assertEquals(\Doctrine\ORM\UnitOfWork::STATE_REMOVED,
            $this->entityManager->getUnitOfWork()->getEntityState($o));
        $this->entityManager->flush();
        $this->assertEquals(\Doctrine\ORM\UnitOfWork::STATE_NEW,
            $this->entityManager->getUnitOfWork()->getEntityState($o));
    }

    /**
     * Test de la position
     */
    function testPosition()
    {
        $keywordRef1 = strtolower(Core_Tools::generateString(10));
        $keywordRef2 = strtolower(Core_Tools::generateString(10));
        /** @var KeywordRepository $keywordRepository */
        $keywordRepository = $this->entityManager->getRepository('\Keyword\Domain\Keyword');
        $keywordRepository->add(new Keyword($keywordRef1, 'Label'));
        $keywordRepository->add(new Keyword($keywordRef2, 'Label'));
        $this->entityManager->flush();

        $o1 = new Techno_Model_Meaning();
        $o1->setKeyword($this->keywordService->get($keywordRef1));
        $o1->setPosition();
        $o1->save();
        $o2 = new Techno_Model_Meaning();
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
