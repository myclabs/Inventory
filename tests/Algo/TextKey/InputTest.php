<?php

namespace Tests\Algo\TextKey;

use Algo_Model_Algo;
use Algo_Model_Input_String;
use Algo_Model_InputSet;
use Algo_Model_Selection_TextKey_Input;
use Algo_Model_Set;
use Classif_Model_Context;
use Core\Test\TestCase;
use Core_Tools;
use Doctrine\ORM\UnitOfWork;

class InputTest extends TestCase
{
    /**
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
        self::getEntityManager()->flush();
        return $o;
    }

    /**
     * @param Algo_Model_Selection_TextKey_Input $o
     */
    public static function deleteObject(Algo_Model_Selection_TextKey_Input $o)
    {
        $o->delete();
        $o->getSet()->delete();
        self::getEntityManager()->flush();
    }

    public static function setUpBeforeClass()
    {
        foreach (Algo_Model_Set::loadList() as $o) {
            $o->delete();
        }
        foreach (Algo_Model_Algo::loadList() as $o) {
            $o->delete();
        }
        foreach (Classif_Model_Context::loadList() as $o) {
            $o->delete();
        }
        self::getEntityManager()->flush();
    }

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

        $this->assertInstanceOf(Algo_Model_Selection_TextKey_Input::class, $oLoaded);
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
            UnitOfWork::STATE_REMOVED,
            $this->entityManager->getUnitOfWork()->getEntityState($o)
        );
        $this->entityManager->flush();
        $this->assertEquals(
            UnitOfWork::STATE_NEW,
            $this->entityManager->getUnitOfWork()->getEntityState($o)
        );
    }

    public function testExecute1()
    {
        $algoTextKeyInput = new Algo_Model_Selection_TextKey_Input();
        $algoTextKeyInput->setInputRef('myInput');

        $input = $this->getMockForAbstractClass(Algo_Model_Input_String::class);
        $input->expects($this->once())
            ->method('getValue')
            ->will($this->returnValue('Valeur'));

        $inputSet = $this->getMockForAbstractClass(Algo_Model_InputSet::class);
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
     * @expectedException \Core_Exception_NotFound
     */
    public function testExecute2()
    {
        $algoTextKeyInput = new Algo_Model_Selection_TextKey_Input();
        $algoTextKeyInput->setInputRef('myInput');

        $inputSet = $this->getMockForAbstractClass(Algo_Model_InputSet::class);
        $inputSet->expects($this->once())
            ->method('getInputByRef')
            ->with('myInput')
            ->will($this->returnValue(null));

        /** @var $inputSet Algo_Model_InputSet */
        $algoTextKeyInput->execute($inputSet);
    }
}
