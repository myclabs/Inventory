<?php
/**
 * Creation of the Techno Meaning test.
 * @package Techno
 */

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
        $suite->addTestSuite('Techno_Test_MeaningSetUp');
        return $suite;
    }

    /**
     * Génere un objet dérivé prêt à l'emploi pour les tests.
     * @return Techno_Model_Meaning
     */
    public static function generateObject()
    {
        $keyword = new Keyword_Model_Keyword();
        $keyword->setLabel('Label');
        $keyword->setRef(Core_Tools::generateString(10));
        $keyword->save();
        $meaning = new Techno_Model_Meaning();
        $meaning->setKeyword($keyword);
        $meaning->save();
        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->flush();
        return $meaning;
    }

    /**
     * DeleteObject
     * @param Techno_Model_Meaning $o
     */
    public static function deleteObject($o)
    {
        $o->getKeyword()->delete();
        $o->delete();
        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->flush();
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
     * Fonction appelee une fois, avant tous les tests
     */
    public static function setUpBeforeClass()
    {
        // Vérification qu'il ne reste aucun objet en base, sinon suppression
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
     * @return Techno_Model_Meaning $meaning
     */
    function testConstruct()
    {
        $keyword = new Keyword_Model_Keyword();
        $keyword->setLabel('Label');
        $keyword->setRef(Core_Tools::generateString(20));
        $keyword->save();

        $o = new Techno_Model_Meaning();
        $o->setKeyword($keyword);
        $o->save();
        $this->entityManager->flush();

        $this->assertSame($keyword, $o->getKeyword());
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
        $this->assertInstanceOf('Keyword_Model_Keyword', $oLoaded->getKeyword());
        $this->assertEquals($o->getKeyword()->getRef(), $oLoaded->getKeyword()->getRef());
        return $oLoaded;
    }

    /**
     * @depends testLoad
     * @param Techno_Model_Meaning $o
     */
    function testDelete($o)
    {
        $o->delete();
        $o->getKeyword()->delete();
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
        $keyword1 = new Keyword_Model_Keyword();
        $keyword1->setLabel('Label');
        $keyword1->setRef(Core_Tools::generateString(10));
        $keyword1->save();
        $keyword2 = new Keyword_Model_Keyword();
        $keyword2->setLabel('Label');
        $keyword2->setRef(Core_Tools::generateString(10));
        $keyword2->save();

        $o1 = new Techno_Model_Meaning();
        $o1->setKeyword($keyword1);
        $o1->setPosition();
        $o1->save();
        $o2 = new Techno_Model_Meaning();
        $o2->setKeyword($keyword2);
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
        $keyword1->delete();
        $keyword2->delete();
        $this->entityManager->flush();
    }

}
