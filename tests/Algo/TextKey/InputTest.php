<?php

namespace Tests\Algo\TextKey;

use AF\Domain\Algorithm\Algo;
use AF\Domain\Algorithm\Input\StringInput;
use AF\Domain\Algorithm\InputSet;
use AF\Domain\Algorithm\Selection\TextKey\InputSelectionAlgo;
use AF\Domain\Algorithm\AlgoSet;
use Classif_Model_Context;
use Core\Test\TestCase;
use Core_Tools;
use Doctrine\ORM\UnitOfWork;

class InputTest extends TestCase
{
    /**
     * @return InputSelectionAlgo
     */
    public static function generateObject()
    {
        $o = new InputSelectionAlgo();
        $o->setRef(strtolower(Core_Tools::generateString(20)));
        $o->setInputRef('algoOption');

        $set = new AlgoSet();
        $set->save();

        $o->setSet($set);
        $o->save();
        self::getEntityManager()->flush();
        return $o;
    }

    /**
     * @param InputSelectionAlgo $o
     */
    public static function deleteObject(InputSelectionAlgo $o)
    {
        $o->delete();
        $o->getSet()->delete();
        self::getEntityManager()->flush();
    }

    public static function setUpBeforeClass()
    {
        foreach (AlgoSet::loadList() as $o) {
            $o->delete();
        }
        foreach (Algo::loadList() as $o) {
            $o->delete();
        }
        foreach (Classif_Model_Context::loadList() as $o) {
            $o->delete();
        }
        self::getEntityManager()->flush();
    }

    public function testConstruct()
    {
        $set = new AlgoSet();
        $set->save();
        $this->entityManager->flush();

        $o = new InputSelectionAlgo();
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
     * @param \AF\Domain\Algorithm\Selection\TextKey\InputSelectionAlgo $o
     * @return \AF\Domain\Algorithm\Selection\TextKey\InputSelectionAlgo
     */
    public function testLoad(InputSelectionAlgo $o)
    {
        $this->entityManager->clear();
        /** @var $oLoaded \AF\Domain\Algorithm\Selection\TextKey\InputSelectionAlgo */
        $oLoaded = InputSelectionAlgo::load($o->getKey());

        $this->assertInstanceOf(InputSelectionAlgo::class, $oLoaded);
        $this->assertNotSame($o, $oLoaded);
        $this->assertEquals($o->getKey(), $oLoaded->getKey());
        $this->assertEquals($o->getSet()->getKey(), $oLoaded->getSet()->getKey());
        return $oLoaded;
    }

    /**
     * @depends testLoad
     * @param \AF\Domain\Algorithm\Selection\TextKey\InputSelectionAlgo $o
     */
    public function testDelete(InputSelectionAlgo $o)
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
        $algoTextKeyInput = new InputSelectionAlgo();
        $algoTextKeyInput->setInputRef('myInput');

        $input = $this->getMockForAbstractClass(StringInput::class);
        $input->expects($this->once())
            ->method('getValue')
            ->will($this->returnValue('Valeur'));

        $inputSet = $this->getMockForAbstractClass(InputSet::class);
        $inputSet->expects($this->once())
            ->method('getInputByRef')
            ->with('myInput')
            ->will($this->returnValue($input));

        /** @var $inputSet \AF\Domain\Algorithm\InputSet */
        $result = $algoTextKeyInput->execute($inputSet);

        $this->assertEquals('Valeur', $result);
    }

    /**
     * Input non trouvé
     * @expectedException \Core_Exception_NotFound
     */
    public function testExecute2()
    {
        $algoTextKeyInput = new InputSelectionAlgo();
        $algoTextKeyInput->setInputRef('myInput');

        $inputSet = $this->getMockForAbstractClass(InputSet::class);
        $inputSet->expects($this->once())
            ->method('getInputByRef')
            ->with('myInput')
            ->will($this->returnValue(null));

        /** @var $inputSet \AF\Domain\Algorithm\InputSet */
        $algoTextKeyInput->execute($inputSet);
    }
}
