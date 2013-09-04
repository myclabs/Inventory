<?php
/**
 * @author valentin.claras
 * @package Keyword
 * @subpackage Test
 */
use Keyword\Domain\Association;
use Keyword\Domain\Keyword;
use Keyword\Domain\Predicate;

/**
 * Creation de la suite de test.
 * @package Keyword
 * @subpackage Test
 */
class Keyword_Test_AssociationTest
{
    /**
     * Creation de la suite de test.
     */
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite();
        $suite->addTestSuite('Keyword_Test_AssociationSetUp');
        $suite->addTestSuite('Keyword_Test_AssociationOther');
        return $suite;
    }
}

/**
 * Test de la creation/modification/suppression de l'entite.
 * @package Keyword
 * @subpackage Test
 */
class Keyword_Test_AssociationSetUp extends PHPUnit_Framework_TestCase
{
    /**
     * Fonction appelee une fois, avant tous les tests.
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
     * Test du Constructeur.
     * @return Association
     */
    function testConstruct()
    {
        $keywordSubject = Keyword_Test_KeywordTest::generateObject('subjectRef', 'SubjectLabel');
        $keywordObject = Keyword_Test_KeywordTest::generateObject('objectRef', 'objectLabel');
        $predicate = Keyword_Test_PredicateTest::generateObject();

        $o = new Association();
        $o->setSubject($keywordSubject);
        $o->setObject($keywordObject);
        $o->setPredicate($predicate);
        $this->assertEquals(array(), $o->getKey());
        $o->save();
        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->flush();
        $this->assertNotEquals(array(), $o->getKey());
        return $o;
    }

    /**
     * Test dy chargement.
     * @param Association $o
     * @depends testConstruct
     * @return Association
     */
    function testLoad(Association $o)
    {
        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->clear($o);
        $oLoaded = Association::load($o->getKey());
        $this->assertInstanceOf('Keyword\Domain\Association', $oLoaded);
        $this->assertSame($oLoaded->getSubject(), $o->getSubject());
        $this->assertSame($oLoaded->getObject(), $o->getObject());
        $this->assertSame($oLoaded->getPredicate(), $o->getPredicate());
        return $oLoaded;
    }

    /**
     * Test de la suppression.
     * @param Association $o
     * @depends testLoad
     */
    function testDelete(Association $o)
    {
        $o->delete();
        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->flush();
        $this->assertEquals(array(), $o->getKey());

        Keyword_Test_PredicateTest::deleteObject($o->getPredicate());
        Keyword_Test_KeywordTest::deleteObject($o->getObject());
        Keyword_Test_KeywordTest::deleteObject($o->getSubject());
    }

    /**
     * Fonction appelee une fois, apres tous les tests.
     */
    public static function tearDownAfterClass()
    {
        // Vérification qu'il ne reste aucun Association en base, sinon suppression !
        if (Predicate::countTotal() > 0) {
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

/**
 * Tests de la classe Predicate.
 * @package Keyword
 * @subpackage Test
 */
class Keyword_Test_AssociationOther extends PHPUnit_Framework_TestCase
{
    /**
     * Fonction appelee une fois, avant tous les tests.
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
     * Fonction appelee avant chaque test.
     */
    protected function setUp()
    {
    }

    /**
     * Test l'exception obtenue lors d'un getSubject sans sujet défini.
     * @expectedException Core_Exception_UndefinedAttribute
     */
    public function testGetNotDefinedSubject()
    {
        $association = new Association();

        try {
            $ref = $association->getSubject();
        } catch (Core_Exception_UndefinedAttribute $e) {
            if ($e->getMessage() === 'The subject keyword has not been defined yet.') {
                throw $e;
            }
        }
        $this->fail();
    }

    /**
     * Test l'exception obtenue lors d'un getObject sans objet défini.
     * @expectedException Core_Exception_UndefinedAttribute
     */
    public function testGetNotDefinedObject()
    {
        $association = new Association();

        try {
            $ref = $association->getObject();
        } catch (Core_Exception_UndefinedAttribute $e) {
            if ($e->getMessage() === 'The object keyword has not been defined yet.') {
                throw $e;
            }
        }
        $this->fail();
    }

    /**
     * Test l'exception obtenue lors d'un getPredicate sans predicat défini.
     * @expectedException Core_Exception_UndefinedAttribute
     */
    public function testGetNotDefinedPredicate()
    {
        $association = new Association();

        try {
            $ref = $association->getPredicate();
        } catch (Core_Exception_UndefinedAttribute $e) {
            if ($e->getMessage() === 'The predicate has not been defined yet.') {
                throw $e;
            }
        }
        $this->fail();
    }

    /**
     * Test l'exception obtenue lors d'un setSubject avec un sujet défini.
     * @expectedException Core_Exception_TooMany
     */
    public function testSetAlreadyDefinedSubject()
    {
        $association = new Association();
        $association->setSubject(new Keyword());

        $subject = new Keyword();
        try {
            $ref = $association->setSubject($subject);
        } catch (Core_Exception_TooMany $e) {
            if ($e->getMessage() === 'The subject has already been defined.') {
                throw $e;
            }
        }
        $this->fail();
    }

    /**
     * Test l'exception obtenue lors d'un setObject avec un objet défini.
     * @expectedException Core_Exception_TooMany
     */
    public function testSetAlreadyDefinedObject()
    {
        $association = new Association();
        $association->setObject(new Keyword());

        $object = new Keyword();
        try {
            $ref = $association->setObject($object);
        } catch (Core_Exception_TooMany $e) {
            if ($e->getMessage() === 'The object has already been defined.') {
                throw $e;
            }
        }
        $this->fail();
    }

    /**
     * Test le loadByRef.
     */
    public function testLoadByRef()
    {
        $subject = Keyword_Test_KeywordTest::generateObject('subject');
        $object = Keyword_Test_KeywordTest::generateObject('object');
        $predicate = Keyword_Test_PredicateTest::generateObject('predicate');

        $association = new Association();
        $association->setSubject($subject);
        $association->setObject($object);
        $association->setPredicate($predicate);
        $association->save();
        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->flush();

        $this->assertSame($association, Association::loadByRefs('subject', 'object', 'predicate'));

        Keyword_Test_KeywordTest::deleteObject($subject);
        Keyword_Test_KeywordTest::deleteObject($object);
        Keyword_Test_PredicateTest::deleteObject($predicate);
    }

    /**
     * Test la suppression en cascade.
     */
    public function testCascadeDeleteSubject()
    {
        $keywordSubject = Keyword_Test_KeywordTest::generateObject('subjectRef', 'SubjectLabel');
        $keywordObject = Keyword_Test_KeywordTest::generateObject('objectRef', 'objectLabel');
        $predicate = Keyword_Test_PredicateTest::generateObject();

        $association = new Association();
        $association->setSubject($keywordSubject);
        $association->setObject($keywordObject);
        $association->setPredicate($predicate);
        $association->save();

        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->flush();

        $this->assertNotEquals(array(), $association->getKey());

        Keyword_Test_KeywordTest::deleteObject($association->getSubject());

        $this->assertEquals(array(), $association->getKey());

        Keyword_Test_PredicateTest::deleteObject($association->getPredicate());
        Keyword_Test_KeywordTest::deleteObject($association->getObject());
    }

    /**
     * Test la suppression en cascade.
     */
    public function testCascadeDeleteObject()
    {
        $keywordSubject = Keyword_Test_KeywordTest::generateObject('subjectRef', 'SubjectLabel');
        $keywordObject = Keyword_Test_KeywordTest::generateObject('objectRef', 'objectLabel');
        $predicate = Keyword_Test_PredicateTest::generateObject();

        $association = new Association();
        $association->setSubject($keywordSubject);
        $association->setObject($keywordObject);
        $association->setPredicate($predicate);
        $association->save();

        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->flush();

        $this->assertNotEquals(array(), $association->getKey());

        Keyword_Test_KeywordTest::deleteObject($association->getObject());

        $this->assertEquals(array(), $association->getKey());

        Keyword_Test_PredicateTest::deleteObject($association->getPredicate());
        Keyword_Test_KeywordTest::deleteObject($association->getSubject());
    }

    /**
     * Fonction appelee apres chaque test.
     */
    protected function tearDown()
    {
    }

    /**
     * Fonction appelee une fois, apres tous les tests.
     */
    public static function tearDownAfterClass()
    {
        // Vérification qu'il ne reste aucun Association en base, sinon suppression !
        if (Predicate::countTotal() > 0) {
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
