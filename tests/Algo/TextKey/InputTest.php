<?php

namespace Tests\Algo\TextKey;

use AF\Domain\Algorithm\Input\StringInput;
use AF\Domain\Algorithm\InputSet;
use AF\Domain\Algorithm\Selection\TextKey\InputSelectionAlgo;
use Core\Test\TestCase;

class InputTest extends TestCase
{
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

        /** @var $inputSet InputSet */
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

        /** @var $inputSet InputSet */
        $algoTextKeyInput->execute($inputSet);
    }
}
