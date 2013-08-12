<?php
/**
 * @author valentin.claras
 * @author bertrand.ferry
 * @package Keyword
 * @subpackage Test
 */
use Keyword\Domain\Association;
use Keyword\Domain\Keyword;
use Keyword\Domain\Predicate;

/**
 * Creation de la suite de test
 * @package Keyword
 * @subpackage Test
 */
class Keyword_Test_KeywordTest
{
    /**
     * Creation de la suite de test
     */
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite();
        $suite->addTestSuite('Keyword_Test_KeywordSetUp');
        $suite->addTestSuite('Keyword_Test_KeywordOther');
        $suite->addTestSuite('Keyword_Test_KeywordAssociation');
        return $suite;
    }

    /**
     * Generation de l'objet de test.
     *
     * @param string $ref
     * @param string $label
     *
     * @return Keyword
     */
    public static function generateObject($ref=null, $label=null)
    {
        $o = new Keyword();
        $o->setRef(($ref ===null) ? 'ref' : $ref);
        $o->setLabel(($label ===null) ? 'label' : $label);
        $o->save();
        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->flush();
        return $o;
    }

    /**
     * Suppression d'un objet cree avec generateObject
     * @param Keyword $o
     */

    public static function deleteObject($o)
    {
        $o->delete();
        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->flush();
    }
}

/**
 * Test de la creation/modification/suppression de l'entite
 * @package Keyword
 * @subpackage Test
 */
class Keyword_Test_KeywordSetUp extends PHPUnit_Framework_TestCase
{
    /**
     * Fonction appelee une fois, avant tous les tests
     */
    public static function setUpBeforeClass()
    {
        // Vérification qu'il ne reste aucun Keyword en base, sinon suppression !
        if (Keyword::countTotal() > 0) {
            echo PHP_EOL . 'Des Keyword_Keyword restants ont été trouvé avant les tests, suppression en cours !';
            foreach (Keyword::loadList() as $keyword) {
                $keyword->delete();
            }
            $entityManagers = Zend_Registry::get('EntityManagers');
            $entityManagers['default']->flush();
        }
    }

    /**
     * Test du Constructeur.
     * @return Keyword
     */
    function testConstruct()
    {
        $o = new Keyword();
        $this->assertInstanceOf('Keyword\Domain\Keyword', $o);
        $o->setRef('RefKeywordTest');
        $o->setLabel('LabelKeywordTest');
        $this->assertEquals(array(), $o->getKey());
        $o->save();
        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->flush();
        $this->assertNotEquals(array(), $o->getKey());
        return $o;
    }

    /**
     * Test dy chargement.
     * @depends testConstruct
     * @param Keyword $o
     * @return Keyword
     */
    function testLoad(Keyword $o)
    {
        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->clear($o);
        $oLoaded = Keyword::load($o->getKey());
        $this->assertInstanceOf('Keyword\Domain\Keyword', $o);
        $this->assertEquals($oLoaded->getKey(), $o->getKey());
        $this->assertEquals($oLoaded->getRef(), $o->getRef());
        $this->assertEquals($oLoaded->getLabel(), $o->getLabel());
        return $oLoaded;
    }

    /**
     * Test de la suppression.
     * @param Keyword $o
     * @depends testLoad
     */
    function testDelete(Keyword $o)
    {
        $o->delete();
        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->flush();
        $this->assertEquals(array(), $o->getKey());
    }

    /**
     * Fonction appelee une fois, apres tous les tests
     */
    public static function tearDownAfterClass()
    {
        // Vérification qu'il ne reste aucun Keyword en base, sinon suppression !
        if (Keyword::countTotal() > 0) {
            echo PHP_EOL . 'Des Keyword_Keyword restants ont été trouvé avant les tests, suppression en cours !';
            foreach (Keyword::loadList() as $keyword) {
                $keyword->delete();
            }
            $entityManagers = Zend_Registry::get('EntityManagers');
            $entityManagers['default']->flush();
        }
    }
}

/**
 * Tests de la classe Keyword
 * @package Keyword
 * @subpackage Test
 */
class Keyword_Test_KeywordOther extends PHPUnit_Framework_TestCase
{
    // Objet de test
    protected $keyword;

    /**
     * Fonction appelee une fois, avant tous les tests
     */
    public static function setUpBeforeClass()
    {
        // Vérification qu'il ne reste aucun Keyword en base, sinon suppression !
        if (Keyword::countTotal() > 0) {
            echo PHP_EOL . 'Des Keyword_Keyword restants ont été trouvé avant les tests, suppression en cours !';
            foreach (Keyword::loadList() as $keyword) {
                $keyword->delete();
            }
            $entityManagers = Zend_Registry::get('EntityManagers');
            $entityManagers['default']->flush();
        }
    }

    /**
     * Fonction appelee avant chaque test
     */
    protected function setUp()
    {
        $this->keyword = Keyword_Test_KeywordTest::generateObject();
    }

    /**
     * Test l'exception obtenue lors d'un getRef sans ref définie.
     * @expectedException Core_Exception_UndefinedAttribute
     */
    public function testGetNotDefinedRef()
    {
        $keyword = new Keyword();

        try {
            $ref = $keyword->getRef();
        } catch (Core_Exception_UndefinedAttribute $e) {
            if ($e->getMessage() === 'The keyword reference has not been defined yet.') {
                throw $e;
            }
        }
        $this->fail();
    }

    /**
     * Test l'exception obtenue lors d'un getLabel sans label définie.
     * @expectedException Core_Exception_UndefinedAttribute
     */
    public function testGetNotDefinedLabel()
    {
        $keyword = new Keyword();

        try {
            $ref = $keyword->getLabel();
        } catch (Core_Exception_UndefinedAttribute $e) {
            if ($e->getMessage() === 'The keyword label has not been defined yet.') {
                throw $e;
            }
        }
        $this->fail();
    }

    /**
     * Test de loadByRef
     */
    public function testLoadByRef()
    {
        $this->assertSame(Keyword::loadByRef($this->keyword->getRef()), $this->keyword);
    }

    /**
     * Fonction appelee apres chaque test
     */
    protected function tearDown()
    {
        // Supprime l'objet de test
        Keyword_Test_KeywordTest::deleteObject($this->keyword);
    }

    /**
     * Fonction appelee une fois, apres tous les tests
     */
    public static function tearDownAfterClass()
    {
        // Vérification qu'il ne reste aucun Keyword en base, sinon suppression !
        if (Keyword::countTotal() > 0) {
            echo PHP_EOL . 'Des Keyword_Keyword restants ont été trouvé après les tests, suppression en cours !';
            foreach (Keyword::loadList() as $keyword) {
                $keyword->delete();
            }
            $entityManagers = Zend_Registry::get('EntityManagers');
            $entityManagers['default']->flush();
        }
    }
}

/**
 * Tests de la classe Keyword
 * @package Keyword
 * @subpackage Test
 */
class Keyword_Test_KeywordAssociation extends PHPUnit_Framework_TestCase
{
    // Objet de test
    protected $keyword1;
    protected $keyword2;
    protected $predicate12;
    protected $predicate21;

    /**
     * Fonction appelee une fois, avant tous les tests
     */
    public static function setUpBeforeClass()
    {
        // Vérification qu'il ne reste aucun Association en base, sinon suppression !
        if (Association::countTotal() > 0) {
            echo PHP_EOL . 'Des Keyword_Association restants ont été trouvé avant les tests, suppression en cours !';
            foreach (Association::loadList() as $association) {
                $association->delete();
            }
            $entityManagers = Zend_Registry::get('EntityManagers');
            $entityManagers['default']->flush();
        }
        // Vérification qu'il ne reste aucun Keyword en base, sinon suppression !
        if (Keyword::countTotal() > 0) {
            echo PHP_EOL . 'Des Keyword_Keyword restants ont été trouvé avant les tests, suppression en cours !';
            foreach (Keyword::loadList() as $keyword) {
                $keyword->delete();
            }
            $entityManagers = Zend_Registry::get('EntityManagers');
            $entityManagers['default']->flush();
        }
        // Vérification qu'il ne reste aucun Predicate en base, sinon suppression !
        if (Predicate::countTotal() > 0) {
            echo PHP_EOL . 'Des Keyword_Predicate restants ont été trouvé avant les tests, suppression en cours !';
            foreach (Predicate::loadList() as $predicate) {
                $predicate->delete();
            }
            $entityManagers = Zend_Registry::get('EntityManagers');
            $entityManagers['default']->flush();
        }
    }

    /**
     * Fonction appelee avant chaque test
     */
    protected function setUp()
    {
        $this->keyword1 = Keyword_Test_KeywordTest::generateObject('subject');
        $this->keyword2 = Keyword_Test_KeywordTest::generateObject('object');
        $this->predicate12 = Keyword_Test_PredicateTest::generateObject('1', '1', 'rev1', 'rev1');
        $this->predicate21 = Keyword_Test_PredicateTest::generateObject('2', '2', 'rev2', 'rev2');
    }

    /**
     * Test les associations du côté des keywords.
     */
    public function testAssociation()
    {
        $this->assertFalse($this->keyword1->hasAssociationsAsSubject());
        $this->assertFalse($this->keyword1->hasAssociationsAsObject());
        $this->assertFalse($this->keyword2->hasAssociationsAsSubject());
        $this->assertFalse($this->keyword2->hasAssociationsAsObject());

        $association1 = new Association();
        $association1->setSubject($this->keyword1);
        $association1->setObject($this->keyword2);
        $association1->setPredicate($this->predicate12);
        $association1->save();
        $association2 = new Association();
        $association2->setSubject($this->keyword2);
        $association2->setObject($this->keyword1);
        $association2->setPredicate($this->predicate21);
        $association2->save();
        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->flush();

        $this->assertTrue($this->keyword1->hasAssociationAsSubject($association1));
        $this->assertFalse($this->keyword1->hasAssociationAsSubject($association2));
        $this->assertFalse($this->keyword1->hasAssociationAsObject($association1));
        $this->assertTrue($this->keyword1->hasAssociationAsObject($association2));
        $this->assertFalse($this->keyword2->hasAssociationAsSubject($association1));
        $this->assertTrue($this->keyword2->hasAssociationAsSubject($association2));
        $this->assertTrue($this->keyword2->hasAssociationAsObject($association1));
        $this->assertFalse($this->keyword2->hasAssociationAsObject($association2));

        $this->assertEquals($this->keyword1->getAssociationsAsSubject(), array($association1));
        $this->assertEquals($this->keyword1->getAssociationsAsObject(), array($association2));
        $this->assertEquals($this->keyword2->getAssociationsAsSubject(), array($association2));
        $this->assertEquals($this->keyword2->getAssociationsAsObject(), array($association1));

        $this->assertEquals($this->keyword1->countAssociationsAsSubject(), 1);
        $this->assertEquals($this->keyword1->countAssociationsAsObject(), 1);
        $this->assertEquals($this->keyword1->countAssociations(), 2);
        $this->assertEquals($this->keyword2->countAssociationsAsSubject(), 1);
        $this->assertEquals($this->keyword2->countAssociationsAsObject(), 1);
        $this->assertEquals($this->keyword2->countAssociations(), 2);

        $association1->delete();
        $association2->delete();
        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->flush();

        $this->assertFalse($this->keyword1->hasAssociationsAsSubject());
        $this->assertFalse($this->keyword1->hasAssociationsAsObject());
        $this->assertFalse($this->keyword2->hasAssociationsAsSubject());
        $this->assertFalse($this->keyword2->hasAssociationsAsObject());
        $this->assertEmpty($this->keyword1->getAssociationsAsSubject());
        $this->assertEmpty($this->keyword1->getAssociationsAsObject());
        $this->assertEmpty($this->keyword2->getAssociationsAsSubject());
        $this->assertEmpty($this->keyword2->getAssociationsAsObject());
    }

    /**
     * Test le chargement des keywords racines.
     */
    public function testLoadListRoots()
    {
        $association1 = new Association();
        $association1->setSubject($this->keyword1);
        $association1->setObject($this->keyword2);
        $association1->setPredicate($this->predicate12);
        $association1->save();
        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->flush();

        $this->assertEquals(Keyword::loadListRoots(), array($this->keyword1));

    }

    /**
     * Fonction appelee apres chaque test
     */
    protected function tearDown()
    {
        // Supprime l'objet de test
        Keyword_Test_KeywordTest::deleteObject($this->keyword1);
        Keyword_Test_KeywordTest::deleteObject($this->keyword2);
        Keyword_Test_PredicateTest::deleteObject($this->predicate12);
        Keyword_Test_PredicateTest::deleteObject($this->predicate21);
    }

    /**
     * Fonction appelee une fois, apres tous les tests
     */
    public static function tearDownAfterClass()
    {
        // Vérification qu'il ne reste aucun Association en base, sinon suppression !
        if (Association::countTotal() > 0) {
            echo PHP_EOL . 'Des Keyword_Association restants ont été trouvé après les tests, suppression en cours !';
            foreach (Association::loadList() as $association) {
                $association->delete();
            }
            $entityManagers = Zend_Registry::get('EntityManagers');
            $entityManagers['default']->flush();
        }
        // Vérification qu'il ne reste aucun Keyword en base, sinon suppression !
        if (Keyword::countTotal() > 0) {
            echo PHP_EOL . 'Des Keyword_Keyword restants ont été trouvé après les tests, suppression en cours !';
            foreach (Keyword::loadList() as $keyword) {
                $keyword->delete();
            }
            $entityManagers = Zend_Registry::get('EntityManagers');
            $entityManagers['default']->flush();
        }
        // Vérification qu'il ne reste aucun Predicate en base, sinon suppression !
        if (Predicate::countTotal() > 0) {
            echo PHP_EOL . 'Des Keyword_Predicate restants ont été trouvé après les tests, suppression en cours !';
            foreach (Predicate::loadList() as $predicate) {
                $predicate->delete();
            }
            $entityManagers = Zend_Registry::get('EntityManagers');
            $entityManagers['default']->flush();
        }
    }
}
