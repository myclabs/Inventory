<?php

namespace Tests\AF;

use AF\Domain\AF;
use AF\Domain\AFLibrary;
use AF\Domain\Component\Checkbox;
use AF\Domain\Component\NumericField;
use AF\Domain\Input\CheckboxInput;
use AF\Domain\Input\NumericFieldInput;
use AF\Domain\InputService;
use Core\Test\TestCase;
use Core\Translation\TranslatedString;
use Unit\UnitAPI;

/**
 * @covers \AF\Domain\InputService
 */
class InputServiceNewInputTest extends TestCase
{
    /**
     * @Inject
     * @var InputService
     */
    private $inputService;

    public function testCreateDefaultInputSet()
    {
        $library = $this->getMock(AFLibrary::class, [], [], '', false);

        $af = new AF($library, new TranslatedString());

        $comp1 = new NumericField();
        $comp1->setRef('comp1');
        $comp1->setAf($af);
        $comp1->setUnit(new UnitAPI('m'));
        $comp1->setDefaultValue(new \Calc_Value(10, 20));
        $af->addComponent($comp1);

        $comp2 = new Checkbox();
        $comp1->setRef('comp2');
        $comp2->setAf($af);
        $comp2->setDefaultValue(true);
        $af->addComponent($comp2);

        $inputSet = $this->inputService->createDefaultInputSet($af);

        /** @var NumericFieldInput $input */
        $input = $inputSet->getInputForComponent($comp1);
        $this->assertInstanceOf(NumericFieldInput::class, $input);
        $this->assertEquals(10, $input->getValue()->getDigitalValue());
        $this->assertEquals(20, $input->getValue()->getUncertainty());

        /** @var CheckboxInput $input */
        $input = $inputSet->getInputForComponent($comp2);
        $this->assertInstanceOf(CheckboxInput::class, $input);
        $this->assertTrue($input->getValue());
    }
}
