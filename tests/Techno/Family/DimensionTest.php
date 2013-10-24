<?php
/**
 * @author  matthieu.napoli
 * @package Techno
 */

/**
 * @package Techno
 */
class Techno_Test_Family_DimensionTest
{
    /**
     * Creation of the test suite
     */
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite();
        $suite->addTestSuite('Techno_Test_Family_DimensionSetUp');
        $suite->addTestSuite('Techno_Test_Family_DimensionMetier');
        return $suite;
    }

    /**
     * Génere un objet dérivé prêt à l'emploi pour les tests.
     * @return Techno_Model_Family_Dimension
     */
    public static function generateObject()
    {
        // Fixtures
        $family = Techno_Test_Family_CoeffTest::generateObject();
        $meaning = Techno_Test_MeaningTest::generateObject();
        $o = new Techno_Model_Family_Dimension($family, $meaning,
                                               Techno_Model_Family_Dimension::ORIENTATION_HORIZONTAL);
        $o->save();
        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->flush();
        return $o;
    }

    /**
     * Deletion of an object created with generateObject
     * @param Techno_Model_Family_Dimension $o
     */
    public static function deleteObject($o)
    {
        $o->getFamily()->removeDimension($o);
        $o->delete();
        Techno_Test_Family_CoeffTest::deleteObject($o->getFamily());
        Techno_Test_MeaningTest::deleteObject($o->getMeaning());
        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->flush();
    }

}

/**
 * @package Techno
 */
class Techno_Test_Family_DimensionSetUp extends PHPUnit_Framework_TestCase
{

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;

    /**
     * Fonction appelee une fois, avant tous les tests
     */
    public static function setUpBeforeClass()
    {
        $entityManager = Zend_Registry::get('EntityManagers')['default'];
        // Vérification qu'il ne reste aucun objet en base, sinon suppression
        foreach (Techno_Model_Component::loadList() as $o) {
            $o->delete();
        }
        foreach (Techno_Model_Tag::loadList() as $o) {
            $o->delete();
        }
        foreach (Techno_Model_Meaning::loadList() as $o) {
            $o->delete();
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
        $entityManagers = Zend_Registry::get('EntityManagers');
        $this->entityManager = $entityManagers['default'];
    }

    /**
     * @return Techno_Model_Family_Dimension
     */
    function testConstruct()
    {
        // Fixtures
        $family = Techno_Test_Family_CoeffTest::generateObject();
        $meaning = Techno_Test_MeaningTest::generateObject();

        $o = new Techno_Model_Family_Dimension($family, $meaning,
                                               Techno_Model_Family_Dimension::ORIENTATION_HORIZONTAL);

        $this->assertSame($family, $o->getFamily());
        $this->assertSame($meaning, $o->getMeaning());
        $this->assertEquals(Techno_Model_Family_Dimension::ORIENTATION_HORIZONTAL, $o->getOrientation());

        $o->save();
        $this->entityManager->flush();

        $this->assertInstanceOf('Techno_Model_Family', $o->getFamily());
        $this->assertEquals($family->getRef(), $o->getFamily()->getRef());
        $this->assertInstanceOf('Techno_Model_Meaning', $o->getMeaning());
        $this->assertEquals($meaning->getKey(), $o->getMeaning()->getKey());
        return $o;
    }

    /**
     * @depends testConstruct
     * @param Techno_Model_Family_Dimension $o
     * @return Techno_Model_Family_Dimension
     */
    function testLoad($o)
    {
        $this->entityManager->clear();
        /** @var $oLoaded Techno_Model_Family_Dimension */
        $oLoaded = Techno_Model_Family_Dimension::load($o->getKey());

        $this->assertInstanceOf('Techno_Model_Family_Dimension', $oLoaded);
        $this->assertNotSame($o, $oLoaded);
        $this->assertEquals($o->getKey(), $oLoaded->getKey());
        // getFamily
        $this->assertInstanceOf('Techno_Model_Family', $oLoaded->getFamily());
        $this->assertEquals($o->getFamily()->getRef(), $oLoaded->getFamily()->getRef());
        // getMeaning
        $this->assertInstanceOf('Techno_Model_Meaning', $oLoaded->getMeaning());
        $this->assertEquals($o->getMeaning()->getKey(), $oLoaded->getMeaning()->getKey());
        return $oLoaded;
    }

    /**
     * @depends testLoad
     * @param Techno_Model_Family_Dimension $o
     */
    function testDelete($o)
    {
        $o->delete();
        $this->assertEquals(\Doctrine\ORM\UnitOfWork::STATE_REMOVED,
                            $this->entityManager->getUnitOfWork()->getEntityState($o));
        // Remove from the family to avoid cascade problems
        $o->getFamily()->removeDimension($o);
        // Delete fixtures
        Techno_Test_Family_CoeffTest::deleteObject($o->getFamily());
        Techno_Test_MeaningTest::deleteObject($o->getMeaning());
        $this->entityManager->flush();
        $this->assertEquals(\Doctrine\ORM\UnitOfWork::STATE_NEW,
                            $this->entityManager->getUnitOfWork()->getEntityState($o));
    }

}

/**
 * Test des fonctionnalités de l'objet métier Techno_Model_Family_Dimension
 * @package Techno
 */
class Techno_Test_Family_DimensionMetier extends PHPUnit_Framework_TestCase
{

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;

    /**
     * Méthode appelée avant les tests
     */
    public static function setUpBeforeClass()
    {
        $entityManager = Zend_Registry::get('EntityManagers')['default'];
        // Vérification qu'il ne reste aucun objet en base, sinon suppression
        foreach (Techno_Model_Family_Dimension::loadList() as $o) {
            $o->delete();
        }
        foreach (Techno_Model_Component::loadList() as $o) {
            $o->delete();
        }
        foreach (Techno_Model_Tag::loadList() as $o) {
            $o->delete();
        }
        foreach (Techno_Model_Meaning::loadList() as $o) {
            $o->delete();
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
        $entityManagers = Zend_Registry::get('EntityManagers');
        $this->entityManager = $entityManagers['default'];
    }

    /**
     * Teste l'association à sa famille
     */
    function testBidirectionalFamilyAssociation()
    {
        // Fixtures
        $family = Techno_Test_Family_CoeffTest::generateObject();
        $meaning = Techno_Test_MeaningTest::generateObject();

        // Charge la collection pour éviter le lazy-loading en dessous
        // (le lazy loading entrainerait le chargement depuis la BDD et donc la prise en compte
        // de l'association BDD même si elle n'était pas faite au niveau PHP)
        $family->getDimensions();

        $o = new Techno_Model_Family_Dimension($family, $meaning,
                                               Techno_Model_Family_Dimension::ORIENTATION_HORIZONTAL);

        // Vérifie que l'association a été affectée bidirectionnellement
        $this->assertTrue($family->hasDimension($o));

        Techno_Test_Family_CoeffTest::deleteObject($family);
        Techno_Test_MeaningTest::deleteObject($meaning);
    }

    /**
     * Teste la persistence en cascade depuis la famille
     */
    function testCascadeFromFamily()
    {
        // Fixtures
        $family = Techno_Test_Family_CoeffTest::generateObject();
        $meaning = Techno_Test_MeaningTest::generateObject();

        $o = new Techno_Model_Family_Dimension($family, $meaning,
                                               Techno_Model_Family_Dimension::ORIENTATION_HORIZONTAL);

        // Vérification de la cascade de la persistence
        $family->save();
        $this->entityManager->flush();
        $this->assertEquals(\Doctrine\ORM\UnitOfWork::STATE_MANAGED,
                            $this->entityManager->getUnitOfWork()->getEntityState($o));

        // Vérification de la cascade de la suppression
        Techno_Test_Family_CoeffTest::deleteObject($family);
        $this->assertEquals(\Doctrine\ORM\UnitOfWork::STATE_NEW,
                            $this->entityManager->getUnitOfWork()->getEntityState($o));

        Techno_Test_MeaningTest::deleteObject($meaning);
    }

    /**
     * Test de la position
     */
    function testPosition()
    {
        // Fixtures
        $family = Techno_Test_Family_CoeffTest::generateObject();
        $meaning1 = Techno_Test_MeaningTest::generateObject();
        $meaning2 = Techno_Test_MeaningTest::generateObject();
        $meaning3 = Techno_Test_MeaningTest::generateObject();
        $meaning4 = Techno_Test_MeaningTest::generateObject();

        $o1 = new Techno_Model_Family_Dimension($family, $meaning1,
                                                Techno_Model_Family_Dimension::ORIENTATION_HORIZONTAL);
        $o1->save();
        $o2 = new Techno_Model_Family_Dimension($family, $meaning2,
                                                Techno_Model_Family_Dimension::ORIENTATION_HORIZONTAL);
        $o2->save();
        $o3 = new Techno_Model_Family_Dimension($family, $meaning3,
                                                Techno_Model_Family_Dimension::ORIENTATION_VERTICAL);
        $o3->save();
        $o4 = new Techno_Model_Family_Dimension($family, $meaning4,
                                                Techno_Model_Family_Dimension::ORIENTATION_VERTICAL);
        $o4->save();
        $this->entityManager->flush();

        $this->assertEquals(1, $o1->getPosition());
        $this->assertEquals(2, $o2->getPosition());
        $this->assertEquals(1, $o3->getPosition());
        $this->assertEquals(2, $o4->getPosition());
        // setPosition
        $o2->setPosition(1);
        $o2->save();
        $this->entityManager->flush();
        $this->assertEquals(2, $o1->getPosition());
        $this->assertEquals(1, $o2->getPosition());
        $this->assertEquals(1, $o3->getPosition());
        $this->assertEquals(2, $o4->getPosition());
        // up
        $o1->goUp();
        $o1->save();
        $this->entityManager->flush();
        $this->assertEquals(1, $o1->getPosition());
        $this->assertEquals(2, $o2->getPosition());
        $this->assertEquals(1, $o3->getPosition());
        $this->assertEquals(2, $o4->getPosition());
        // down
        $o1->goDown();
        $o1->save();
        $this->entityManager->flush();
        $this->assertEquals(2, $o1->getPosition());
        $this->assertEquals(1, $o2->getPosition());
        $this->assertEquals(1, $o3->getPosition());
        $this->assertEquals(2, $o4->getPosition());
        // Delete
        $o2->delete();
        $this->assertEquals(1, $o1->getPosition());
        $this->assertEquals(1, $o3->getPosition());
        $this->assertEquals(2, $o4->getPosition());
        Techno_Test_Family_CoeffTest::deleteObject($family);
        Techno_Test_MeaningTest::deleteObject($meaning1);
        Techno_Test_MeaningTest::deleteObject($meaning2);
        Techno_Test_MeaningTest::deleteObject($meaning3);
        Techno_Test_MeaningTest::deleteObject($meaning4);
    }

}
