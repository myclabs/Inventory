<?php
/**
 * @author valentin.claras
 * @author bertrand.ferry
 * @package Keyword
 * @subpackage Test
 */

/**
 * Creation de la suite de test.
 * @package Keyword
 * @subpackage Test
 */
class Keyword_Test_PredicateTest
{
    /**
     * Creation de la suite de test.
     */
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite();
        $suite->addTestSuite('Keyword_Test_PredicateSetUp');
        $suite->addTestSuite('Keyword_Test_PredicateOther');
        return $suite;
    }

    /**
     * Generation de l'objet de test.
     *
     * @param string $ref
     * @param string $label
     * @param string $revRef
     * @param string $revLabel
     * @param string $description
     *
     * @return Keyword_Model_Predicate
     */
    public static function generateObject($ref=null, $label=null, $revRef=null, $revLabel=null, $description=null)
    {
        $o = new Keyword_Model_Predicate();
        $o->setRef(($ref ===null) ? 'ref' : $ref);
        $o->setLabel(($label ===null) ? 'label' : $label);
        $o->setReverseRef(($revRef ===null) ? 'revRef' : $revRef);
        $o->setReverseLabel(($revLabel ===null) ? 'revLabel' : $revLabel);
        $o->setDescription(($description ===null) ? 'description' : $description);
        $o->save();
        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->flush();
        return $o;
    }

    /**
     * Suppression d'un objet cree avec generateObject.
     * @param Keyword_Model_Predicate $o
     */
    public static function deleteObject($o)
    {
        $o->delete();
        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->flush();
    }
}

/**
 * Test de la creation/modification/suppression de l'entite.
 * @package Keyword
 * @subpackage Test
 */
class Keyword_Test_PredicateSetUp extends PHPUnit_Framework_TestCase
{
    /**
     * Fonction appelee une fois, avant tous les tests.
     */
    public static function setUpBeforeClass()
    {
        // Vérification qu'il ne reste aucun Keyword_Model_Predicate en base, sinon suppression !
        if (Keyword_Model_Predicate::countTotal() > 0) {
            echo PHP_EOL . 'Des Keyword_Predicate restants ont été trouvé avant les tests, suppression en cours !';
            foreach (Keyword_Model_Predicate::loadList() as $predicate) {
                $predicate->delete();
            }
            $entityManagers = Zend_Registry::get('EntityManagers');
            $entityManagers['default']->flush();
        }
    }

    /**
     * Test du Constructeur.
     * @return Keyword_Model_Predicate
     */
    function testConstruct()
    {
        $o = new Keyword_Model_Predicate();
        $this->assertInstanceOf('Keyword_Model_Predicate', $o);
        $o->setRef('RefPredicateTest');
        $o->setLabel('LabelPredicateTest');
        $o->setReverseRef('ReverseRefPredicateTest');
        $o->setReverseLabel('ReverseLabelPredicateTest');
        $o->setDescription('DescriptionPredicateTest');
        $this->assertEquals(array(), $o->getKey());
        $o->save();
        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->flush();
        $this->assertNotEquals(array(), $o->getKey());
        return $o;
    }

    /**
     * Test dy chargement.
     * @param Keyword_Model_Predicate $o
     * @depends testConstruct
     */
    function testLoad(Keyword_Model_Predicate $o)
    {
        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->clear($o);
        $oLoaded = Keyword_Model_Predicate::load($o->getKey());
        $this->assertInstanceOf('Keyword_Model_Predicate', $o);
        $this->assertEquals($oLoaded->getKey(), $o->getKey());
        $this->assertEquals($oLoaded->getRef(), $o->getRef());
        $this->assertEquals($oLoaded->getLabel(), $o->getLabel());
        $this->assertEquals($oLoaded->getReverseRef(), $o->getReverseRef());
        $this->assertEquals($oLoaded->getReverseLabel(), $o->getReverseLabel());
        $this->assertEquals($oLoaded->getDescription(), $o->getDescription());
        return $oLoaded;
    }

    /**
     * Test de la suppression.
     * @param Keyword_Model_Predicate $o
     * @depends testLoad
     */
    function testDelete(Keyword_Model_Predicate $o)
    {
        $o->delete();
        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->flush();
        $this->assertEquals(array(), $o->getKey());
    }

    /**
     * Fonction appelee une fois, apres tous les tests.
     */
    public static function tearDownAfterClass()
    {
        // Vérification qu'il ne reste aucun Keyword_Model_Predicate en base, sinon suppression !
        if (Keyword_Model_Predicate::countTotal() > 0) {
            echo PHP_EOL . 'Des Keyword_Predicate restants ont été trouvé après les tests, suppression en cours !';
            foreach (Keyword_Model_Predicate::loadList() as $predicate) {
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
class Keyword_Test_PredicateOther extends PHPUnit_Framework_TestCase
{
    // Objet de test
    protected $predicate;

    /**
     * Fonction appelee une fois, avant tous les tests.
     */
    public static function setUpBeforeClass()
    {
        // Vérification qu'il ne reste aucun Keyword_Model_Predicate en base, sinon suppression !
        if (Keyword_Model_Predicate::countTotal() > 0) {
            echo PHP_EOL . 'Des Keyword_Predicate restants ont été trouvé avant les tests, suppression en cours !';
            foreach (Keyword_Model_Predicate::loadList() as $predicate) {
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
        $this->predicate = Keyword_Test_PredicateTest::generateObject();
    }

    /**
     * Test l'exception obtenue lors d'un getRef sans ref définie.
     * @expectedException Core_Exception_UndefinedAttribute
     */
    public function testGetNotDefinedRef()
    {
        $predicate = new Keyword_Model_Predicate();

        try {
            $ref = $predicate->getRef();
        } catch (Core_Exception_UndefinedAttribute $e) {
            if ($e->getMessage() === 'The predicate reference has not been defined yet.') {
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
        $predicate = new Keyword_Model_Predicate();

        try {
            $ref = $predicate->getLabel();
        } catch (Core_Exception_UndefinedAttribute $e) {
            if ($e->getMessage() === 'The predicate label has not been defined yet.') {
                throw $e;
            }
        }
        $this->fail();
    }

    /**
     * Test l'exception obtenue lors d'un getReverseRef sans reverse ref définie.
     * @expectedException Core_Exception_UndefinedAttribute
     */
    public function testGetNotDefinedReverseRef()
    {
        $predicate = new Keyword_Model_Predicate();

        try {
            $ref = $predicate->getReverseRef();
        } catch (Core_Exception_UndefinedAttribute $e) {
            if ($e->getMessage() === 'The predicate reverse reference has not been defined yet.') {
                throw $e;
            }
        }
        $this->fail();
    }

    /**
     * Test l'exception obtenue lors d'un getLabel sans reverse label définie.
     * @expectedException Core_Exception_UndefinedAttribute
     */
    public function testGetNotDefinedReverseLabel()
    {
        $predicate = new Keyword_Model_Predicate();

        try {
            $ref = $predicate->getReverseLabel();
        } catch (Core_Exception_UndefinedAttribute $e) {
            if ($e->getMessage() === 'The predicate reverse label has not been defined yet.') {
                throw $e;
            }
        }
        $this->fail();
    }

    /**
     * Test de loadByRef.
     */
    public function testLoadbyRef()
    {
        $this->assertSame(Keyword_Model_Predicate::loadByRef('ref'), $this->predicate);
    }

    /**
     * Test de loadByReverseRef.
     */
    public function testLoadbyReverseRef()
    {
        $this->assertSame(Keyword_Model_Predicate::loadByReverseRef('revRef'), $this->predicate);
    }

    /**
     * Fonction appelee apres chaque test.
     */
    protected function tearDown()
    {
        Keyword_Test_PredicateTest::deleteObject($this->predicate);
    }

    /**
     * Fonction appelee une fois, apres tous les tests.
     */
    public static function tearDownAfterClass()
    {
        // Vérification qu'il ne reste aucun Keyword_Model_Predicate en base, sinon suppression !
        if (Keyword_Model_Predicate::countTotal() > 0) {
            echo PHP_EOL . 'Des Keyword_Predicate restants ont été trouvé après les tests, suppression en cours !';
            foreach (Keyword_Model_Predicate::loadList() as $predicate) {
                $predicate->delete();
            }
            $entityManagers = Zend_Registry::get('EntityManagers');
            $entityManagers['default']->flush();
        }
    }
}
