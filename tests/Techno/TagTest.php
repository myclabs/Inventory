<?php
/**
 * @author matthieu.napoli
 * @package Techno
 */

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
        $keyword = new Keyword_Model_Keyword();
        $keyword->setLabel('Label test');
        $keyword->setRef(Core_Tools::generateString(10));
        $keyword->save();
        $meaningTest = new Techno_Test_MeaningTest();
        $meaning = $meaningTest->generateObject();
        $tag = new Techno_Model_Tag();
        $tag->setMeaning($meaning);
        $tag->setValue($keyword);
        $tag->save();
        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->flush();
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
        $o->getValue()->delete();
        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->flush();
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
     * Fonction appelee une fois, avant tous les tests
     */
    public static function setUpBeforeClass()
    {
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
        if (Keyword_Model_Keyword::countTotal() > 0) {
            foreach (Keyword_Model_Keyword::loadList() as $o) {
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
     * @return Techno_Model_Tag
     */
    function testConstruct()
    {
        // Fixtures
        $keyword = new Keyword_Model_Keyword();
        $keyword->setLabel('Label');
        $keyword->setRef('keywordTest2');
        $keyword->save();
        $meaningTest = new Techno_Test_MeaningTest();
        $meaning = $meaningTest->generateObject();

        $o = new Techno_Model_Tag();
        $o->setMeaning($meaning);
        $o->setValue($keyword);

        $this->assertSame($meaning, $o->getMeaning());
        $this->assertSame($keyword, $o->getValue());

        $o->save();
        $this->entityManager->flush();

        $this->assertInstanceOf('Techno_Model_Meaning', $o->getMeaning());
        $this->assertEquals($meaning->getKey(), $o->getMeaning()->getKey());
        $this->assertInstanceOf('Keyword_Model_Keyword', $o->getValue());
        $this->assertEquals($keyword->getRef(), $o->getValue()->getRef());
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
        $this->assertInstanceOf('Keyword_Model_Keyword', $oLoaded->getValue());
        $this->assertEquals($o->getValue()->getRef(), $oLoaded->getValue()->getRef());
        return $oLoaded;
    }

    /**
     * @depends testLoad
     * @param Techno_Model_Tag $o
     */
    function testDelete($o)
    {
        $o->delete();
        $this->assertEquals(\Doctrine\ORM\UnitOfWork::STATE_REMOVED,
            $this->entityManager->getUnitOfWork()->getEntityState($o));
        // Delete fixtures
        $o->getValue()->delete();
        $meaningTest = new Techno_Test_MeaningTest();
        $meaningTest->deleteObject($o->getMeaning());
        $this->entityManager->flush();
        $this->assertEquals(\Doctrine\ORM\UnitOfWork::STATE_NEW,
            $this->entityManager->getUnitOfWork()->getEntityState($o));
    }

}
