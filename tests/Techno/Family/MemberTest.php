<?php
/**
 * @author matthieu.napoli
 * @package Techno
 */

/**
 * @package Techno
 */
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
     * @return Techno_Model_Family_Member
     */
    public static function generateObject()
    {
        $keyword = new Keyword_Model_Keyword();
        $keyword->setLabel('Label');
        $keyword->setRef(Core_Tools::generateString(10));
        $keyword->save();
        $member = new Techno_Model_Family_Member(Techno_Test_Family_DimensionTest::generateObject(),
                                                 $keyword);
        $member->save();
        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->flush();
        return $member;
    }

    /**
     * DeleteObject
     * @param Techno_Model_Family_Member $o
     */
    public static function deleteObject($o)
    {
        $o->delete();
        // Remove from the family to avoid cascad problems
        $o->getDimension()->removeMember($o);
        // Delete fixtures
        Techno_Test_Family_DimensionTest::deleteObject($o->getDimension());
        $o->getKeyword()->delete();
        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->flush();
    }
}

/**
 *  @package Techno
 */
class Techno_Test_Family_MemberSetUp extends PHPUnit_Framework_TestCase
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
        // Vérification qu'il ne reste aucun objet en base, sinon suppression
        if (Keyword_Model_Keyword::countTotal() > 0) {
            foreach (Keyword_Model_Keyword::loadList() as $o) {
                $o->delete();
            }
        }
        if (Techno_Model_Family_Member::countTotal() > 0) {
            foreach (Techno_Model_Family_Member::loadList() as $o) {
                $o->delete();
            }
        }
        if (Techno_Model_Family_Dimension::countTotal() > 0) {
            foreach (Techno_Model_Family_Dimension::loadList() as $o) {
                $o->delete();
            }
        }
        if (Techno_Model_Component::countTotal() > 0) {
            foreach (Techno_Model_Component::loadList() as $o) {
                $o->delete();
            }
        }
        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->flush();
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
     * @return Techno_Model_Family_Member $Family_Member
     */
    function testConstruct()
    {
        $keyword = new Keyword_Model_Keyword();
        $keyword->setLabel('Label');
        $keyword->setRef('keywordTest');
        $keyword->save();
        $this->entityManager->flush();

        $o = new Techno_Model_Family_Member(Techno_Test_Family_DimensionTest::generateObject(),
                                            $keyword);

        $this->assertSame($keyword, $o->getKeyword());

        $o->save();
        $this->entityManager->flush();

        $this->assertInstanceOf('Keyword_Model_Keyword', $o->getKeyword());
        $this->assertEquals($keyword->getRef(), $o->getKeyword()->getRef());
        return $o;
    }

    /**
     * @depends testConstruct
     * @param Techno_Model_Family_Member $o
     * @return Techno_Model_Family_Member
     */
    function testLoad($o)
    {
        $this->entityManager->clear();
        /** @var $oLoaded Techno_Model_Family_Member */
        $oLoaded = Techno_Model_Family_Member::load($o->getKey());

        $this->assertInstanceOf('Techno_Model_Family_Member', $oLoaded);
        $this->assertNotSame($o, $oLoaded);
        $this->assertEquals($o->getKey(), $oLoaded->getKey());
        // Keyword
        $this->assertInstanceOf('Keyword_Model_Keyword', $oLoaded->getKeyword());
        $this->assertEquals($o->getKeyword()->getRef(), $oLoaded->getKeyword()->getRef());
        // Dimension
        $this->assertEquals($o->getDimension()->getKey(), $oLoaded->getDimension()->getKey());
        $this->assertTrue($oLoaded->getDimension()->hasMember($oLoaded));
        return $oLoaded;
    }

    /**
     * @depends testLoad
     * @param Techno_Model_Family_Member $o
     */
    function testDelete($o)
    {
        $o->delete();
        $this->assertEquals(\Doctrine\ORM\UnitOfWork::STATE_REMOVED,
            $this->entityManager->getUnitOfWork()->getEntityState($o));
        // Remove from the family to avoid cascad problems
        $this->assertCount(1, $o->getDimension()->getMembers());
        $o->getDimension()->removeMember($o);
        $this->assertCount(0, $o->getDimension()->getMembers());
        // Delete fixtures
        Techno_Test_Family_DimensionTest::deleteObject($o->getDimension());
        $o->getKeyword()->delete();
        $this->entityManager->flush();
        $this->assertEquals(\Doctrine\ORM\UnitOfWork::STATE_NEW,
            $this->entityManager->getUnitOfWork()->getEntityState($o));
    }

}

/**
 * Test des fonctionnalités de l'objet métier Techno_Model_Family_Member
 * @package Techno
 */
class Techno_Test_Family_MemberMetierTest extends PHPUnit_Framework_TestCase
{

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;

    /**
     * Méthode appelée avant les tests
     */
    public static  function setUpBeforeClass()
    {
        // Vérification qu'il ne reste aucun objet en base, sinon suppression
        if (Keyword_Model_Keyword::countTotal() > 0) {
            foreach (Keyword_Model_Keyword::loadList() as $o) {
                $o->delete();
            }
        }
        if (Techno_Model_Family_Member::countTotal() > 0) {
            foreach (Techno_Model_Family_Member::loadList() as $o) {
                $o->delete();
            }
        }
        if (Techno_Model_Family_Dimension::countTotal() > 0) {
            foreach (Techno_Model_Family_Dimension::loadList() as $o) {
                $o->delete();
            }
        }
        if (Techno_Model_Component::countTotal() > 0) {
            foreach (Techno_Model_Component::loadList() as $o) {
                $o->delete();
            }
        }
        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->flush();
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
     * Test de la position
     */
    function testPosition()
    {
        $dimension = Techno_Test_Family_DimensionTest::generateObject();

        $keyword1 = new Keyword_Model_Keyword();
        $keyword1->setLabel('Label');
        $keyword1->setRef(Core_Tools::generateString(10));
        $keyword1->save();
        $o1 = new Techno_Model_Family_Member($dimension, $keyword1);
        $o1->save();
        $keyword2 = new Keyword_Model_Keyword();
        $keyword2->setLabel('Label');
        $keyword2->setRef(Core_Tools::generateString(10));
        $keyword2->save();
        $o2 = new Techno_Model_Family_Member($dimension, $keyword2);
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
        $keyword1->delete();
        $keyword2->delete();
    }

    /**
     * Teste l'association à sa dimension
     */
    function testBidirectionalDimensionAssociation()
    {
        // Fixtures
        $dimension = Techno_Test_Family_DimensionTest::generateObject();
        $keyword = new Keyword_Model_Keyword();
        $keyword->setLabel('Label');
        $keyword->setRef('keywordTest');
        $keyword->save();

        // Charge la collection pour éviter le lazy-loading en dessous
        // (le lazy loading entrainerait le chargement depuis la BDD et donc la prise en compte
        // de l'association BDD même si elle n'était pas faite au niveau PHP)
        $members = $dimension->getMembers();
        $this->assertCount(0, $members);

        $o = new Techno_Model_Family_Member($dimension, $keyword);

        // Vérifie que l'association a été affectée bidirectionnellement
        $this->assertTrue($dimension->hasMember($o));

        Techno_Test_Family_CoeffTest::deleteObject($dimension->getFamily());
        $keyword->delete();
        $this->entityManager->flush();
    }

    /**
     * Teste la persistence en cascade depuis la dimension
     */
    function testCascadeFromFamily()
    {
        // Fixtures
        $dimension = Techno_Test_Family_DimensionTest::generateObject();
        $keyword = new Keyword_Model_Keyword();
        $keyword->setLabel('Label');
        $keyword->setRef('keywordTest');
        $keyword->save();

        $o = new Techno_Model_Family_Member($dimension, $keyword);

        // Vérification de la cascade de la persistence
        $dimension->save();
        $this->entityManager->flush();
        $this->assertEquals(\Doctrine\ORM\UnitOfWork::STATE_MANAGED,
                            $this->entityManager->getUnitOfWork()->getEntityState($o));

        // Vérification de la cascade de la suppression
        Techno_Test_Family_DimensionTest::deleteObject($dimension);
        $this->assertEquals(\Doctrine\ORM\UnitOfWork::STATE_NEW,
                            $this->entityManager->getUnitOfWork()->getEntityState($o));

        $keyword->delete();
        $this->entityManager->flush();
    }

}
