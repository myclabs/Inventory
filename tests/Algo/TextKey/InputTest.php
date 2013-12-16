<?php
/**
 * @author matthieu.napoli
 * @author yoann.croizer
 * @author hugo.charbonnier
 * @package Algo
 */

/**
 * Creation of the Test Suite.
 */
class TextKey_InputTest
{
    /**
     * Creation of the test suite
     */
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite();
        $suite->addTestSuite('TextKey_InputSetUpTest');
        $suite->addTestSuite('TextKey_InputLogiqueMetierTest');
        return $suite;
    }

    /**
     * Génere un objet dérivé prêt à l'emploi pour les tests.
     * @return Algo_Model_Selection_TextKey_Input
     */
    public static function generateObject()
    {
        $o = new Algo_Model_Selection_TextKey_Input();
        $o->setRef(strtolower(Core_Tools::generateString(20)));
        $o->setInputRef('algoOption');

        $set = new Algo_Model_Set();
        $set->save();

        $o->setSet($set);
        $o->save();
        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->flush();
        return $o;
    }

    /**
     * Supprime un objet utilisé dans les tests
     * @param Algo_Model_Selection_TextKey_Input $o
     */
    public static function deleteObject(Algo_Model_Selection_TextKey_Input $o)
    {
        $o->delete();
        $o->getSet()->delete();
        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->flush();
    }
}

/**
 * TextKey_InputSetUpTest
 * @package Algo
 */
class TextKey_InputSetUpTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;

    /**
     * Méthode appelée avant l'appel à la classe de test
     */
    public static function setUpBeforeClass()
    {
        /** @var \Doctrine\ORM\EntityManager $entityManager */
        $entityManager = Zend_Registry::get('EntityManagers')['default'];
        // Vérification qu'il ne reste aucun objet en base, sinon suppression
        foreach (Algo_Model_Set::loadList() as $o) {
            $o->delete();
        }
        foreach (Algo_Model_Algo::loadList() as $o) {
            $o->delete();
        }
        foreach (Classif_Model_Context::loadList() as $o) {
            $o->delete();
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
     * @return Algo_Model_Selection_TextKey_Input $o
     */
    public function testConstruct()
    {
        $set = new Algo_Model_Set();
        $set->save();
        $this->entityManager->flush();

        $o = new Algo_Model_Selection_TextKey_Input();
        $o->setRef(strtolower(Core_Tools::generateString(20)));
        $o->setInputRef('ref1');
        $o->setSet($set);
        $o->save();
        $this->entityManager->flush();

        $this->assertSame($set, $o->getSet());
        return $o;
    }

    /**
     * @depends testConstruct
     * @param Algo_Model_Selection_TextKey_Input $o
     * @return Algo_Model_Selection_TextKey_Input
     */
    public function testLoad(Algo_Model_Selection_TextKey_Input $o)
    {
        $this->entityManager->clear();
        /** @var $oLoaded Algo_Model_Selection_TextKey_Input */
        $oLoaded = Algo_Model_Selection_TextKey_Input::load($o->getKey());

        $this->assertInstanceOf('Algo_Model_Selection_TextKey_Input', $oLoaded);
        $this->assertNotSame($o, $oLoaded);
        $this->assertEquals($o->getKey(), $oLoaded->getKey());
        $this->assertEquals($o->getSet()->getKey(), $oLoaded->getSet()->getKey());
        return $oLoaded;
    }

    /**
     * @depends testLoad
     * @param Algo_Model_Selection_TextKey_Input $o
     */
    public function testDelete(Algo_Model_Selection_TextKey_Input $o)
    {
        $o->delete();
        $o->getSet()->delete();
        $this->assertEquals(
            \Doctrine\ORM\UnitOfWork::STATE_REMOVED,
            $this->entityManager->getUnitOfWork()->getEntityState($o)
        );
        $this->entityManager->flush();
        $this->assertEquals(
            \Doctrine\ORM\UnitOfWork::STATE_NEW,
            $this->entityManager->getUnitOfWork()->getEntityState($o)
        );
    }
}


/**
 * TextKey_InputLogiqueMetierTest
 */
class TextKey_InputLogiqueMetierTest extends PHPUnit_Framework_TestCase
{
    /**
     * Test de la méthode execute()
     */
    public function testExecute1()
    {
        $algoTextKeyInput = new Algo_Model_Selection_TextKey_Input();
        $algoTextKeyInput->setInputRef('myInput');

        $input = $this->getMockForAbstractClass('Algo_Model_Input_String');
        $input->expects($this->once())
            ->method('getValue')
            ->will($this->returnValue('Valeur'));

        $inputSet = $this->getMockForAbstractClass('Algo_Model_InputSet');
        $inputSet->expects($this->once())
            ->method('getInputByRef')
            ->with('myInput')
            ->will($this->returnValue($input));

        /** @var $inputSet Algo_Model_InputSet */
        $result = $algoTextKeyInput->execute($inputSet);

        $this->assertEquals('Valeur', $result);
    }

    /**
     * Input non trouvé
     * @expectedException Core_Exception_NotFound
     */
    public function testExecute2()
    {
        $algoTextKeyInput = new Algo_Model_Selection_TextKey_Input();
        $algoTextKeyInput->setInputRef('myInput');

        $inputSet = $this->getMockForAbstractClass('Algo_Model_InputSet');
        $inputSet->expects($this->once())
            ->method('getInputByRef')
            ->with('myInput')
            ->will($this->returnValue(null));

        /** @var $inputSet Algo_Model_InputSet */
        $algoTextKeyInput->execute($inputSet);
    }
}
