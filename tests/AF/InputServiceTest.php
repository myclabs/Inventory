<?php

namespace Tests\AF;

use AF_Model_AF;
use AF_Model_Component_Checkbox;
use AF_Model_Component_Numeric;
use AF_Model_Input_Checkbox;
use AF_Model_Input_Numeric;
use AF_Model_InputSet_Primary;
use AF_Service_InputService;
use Core\Test\TestCase;
use Unit\UnitAPI;

class InputServiceTest extends TestCase
{
    /**
     * @var AF_Service_InputService
     */
    private $inputService;
    /**
     * @var AF_Model_AF
     */
    private $af;
    /**
     * @var AF_Model_Component_Numeric
     */
    private $comp1;
    /**
     * @var AF_Model_Component_Checkbox
     */
    private $comp2;
    /**
     * @var AF_Model_Component_Checkbox
     */
    private $comp3;

    public function testEditInputSet()
    {
        $inputSet1 = new AF_Model_InputSet_Primary($this->af);
        $input1 = new AF_Model_Input_Checkbox($inputSet1, $this->comp2);
        $input3 = new AF_Model_Input_Numeric($inputSet1, $this->comp3);
        $input3->setValue($input3->getValue()->copyWithNewValue(1));
        $input3->setHidden(false);
        $input3->setDisabled(false);

        $inputSet2 = new AF_Model_InputSet_Primary($this->af);
        $input2 = new AF_Model_Input_Numeric($inputSet2, $this->comp1);
        $input2->setValue($input2->getValue()->copyWithNewValue(10));
        $input32 = new AF_Model_Input_Numeric($inputSet2, $this->comp3);
        $input32->setValue($input32->getValue()->copyWithNewValue(2));
        $input32->setHidden(true);
        $input32->setDisabled(true);

        $this->inputService->editInputSet($inputSet1, $inputSet2);

        // La saisie pour le composant 1 a été ajouté
        /** @var AF_Model_Input_Numeric $newInputForComp1 */
        $newInputForComp1 = $inputSet1->getInputForComponent($this->comp1);
        $this->assertNotNull($newInputForComp1);
        $this->assertEquals(10, $newInputForComp1->getValue()->getDigitalValue());

        // La saisie pour le composant 2 a été supprimée
        $this->assertNull($inputSet1->getInputForComponent($this->comp2));

        // La saisie pour le composant 3 a été remplacée
        /** @var AF_Model_Input_Numeric $newInputForComp3 */
        $newInputForComp3 = $inputSet1->getInputForComponent($this->comp3);
        $this->assertNotNull($newInputForComp3);
        $this->assertEquals(2, $newInputForComp3->getValue()->getDigitalValue());
        $this->assertTrue($newInputForComp3->isHidden());
        $this->assertTrue($newInputForComp3->isDisabled());

        $this->assertCount(2, $inputSet1->getInputs());
    }

    public function setUp()
    {
        parent::setUp();

        /** @var AF_Service_InputService $inputService */
        $this->inputService = $this->get(AF_Service_InputService::class);

        $this->af = new AF_Model_AF('test');

        $this->comp1 = new AF_Model_Component_Numeric();
        $this->comp1->setAf($this->af);
        $this->comp1->setRef('comp1');
        $this->comp1->setUnit(new UnitAPI('m'));
        $this->af->addComponent($this->comp1);

        $this->comp2 = new AF_Model_Component_Checkbox();
        $this->comp2->setAf($this->af);
        $this->comp2->setRef('comp2');
        $this->af->addComponent($this->comp2);

        $this->comp3 = new AF_Model_Component_Numeric();
        $this->comp3->setAf($this->af);
        $this->comp3->setRef('comp3');
        $this->comp3->setUnit(new UnitAPI('m'));
        $this->af->addComponent($this->comp3);

        $this->af->save();
        $this->entityManager->flush();
    }

    public function tearDown()
    {
        parent::tearDown();

        $this->af->delete();
        $this->entityManager->flush();
    }

    public static function setUpBeforeClass()
    {
        if (AF_Model_AF::countTotal() > 0) {
            foreach (AF_Model_AF::loadList() as $o) {
                $o->delete();
            }
            self::getEntityManager()->flush();
        }
    }
}
