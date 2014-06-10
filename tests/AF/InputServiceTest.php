<?php

namespace Tests\AF;

use Account\Domain\Account;
use Account\Domain\AccountRepository;
use AF\Domain\AF;
use AF\Domain\AFLibrary;
use AF\Domain\Component\Checkbox;
use AF\Domain\Component\NumericField;
use AF\Domain\Input\CheckboxInput;
use AF\Domain\Input\NumericFieldInput;
use AF\Domain\InputSet\PrimaryInputSet;
use AF\Domain\InputService;
use Core\Test\TestCase;
use Core\Translation\TranslatedString;
use Unit\UnitAPI;

/**
 * @covers \AF\Domain\InputService
 */
class InputServiceTest extends TestCase
{
    /**
     * @Inject
     * @var InputService
     */
    private $inputService;

    /**
     * @Inject
     * @var AccountRepository
     */
    private $accountRepository;

    /**
     * @var AF
     */
    private $af;

    /**
     * @var NumericField
     */
    private $comp1;

    /**
     * @var Checkbox
     */
    private $comp2;

    /**
     * @var Checkbox
     */
    private $comp3;

    public function testEditInputSet()
    {
        $inputSet1 = new PrimaryInputSet($this->af);
        $input1 = new CheckboxInput($inputSet1, $this->comp2);
        $input3 = new NumericFieldInput($inputSet1, $this->comp3);
        $inputSet1->setInputForComponent($this->comp3, $input3);
        $input3->setValue($input3->getValue()->copyWithNewValue(1));
        $input3->setHidden(false);
        $input3->setDisabled(false);

        $inputSet2 = new PrimaryInputSet($this->af);
        $input2 = new NumericFieldInput($inputSet2, $this->comp1);
        $inputSet2->setInputForComponent($this->comp1, $input2);
        $input2->setValue($input2->getValue()->copyWithNewValue(10));
        $input32 = new NumericFieldInput($inputSet2, $this->comp3);
        $inputSet2->setInputForComponent($this->comp3, $input32);
        $input32->setValue($input32->getValue()->copyWithNewValue(2));
        $input32->setHidden(true);
        $input32->setDisabled(true);

        $this->inputService->editInputSet($inputSet1, $inputSet2);

        // La saisie pour le composant 1 a été ajouté
        /** @var NumericFieldInput $newInputForComp1 */
        $newInputForComp1 = $inputSet1->getInputForComponent($this->comp1);
        $this->assertNotNull($newInputForComp1);
        $this->assertEquals(10, $newInputForComp1->getValue()->getDigitalValue());

        // La saisie pour le composant 2 a été supprimée
        $this->assertNull($inputSet1->getInputForComponent($this->comp2));

        // La saisie pour le composant 3 a été remplacée
        /** @var NumericFieldInput $newInputForComp3 */
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

        $account = new Account('foo');
        $this->accountRepository->add($account);

        $library = new AFLibrary($account, new TranslatedString());
        $library->save();

        $this->af = new AF($library, new TranslatedString());

        $this->comp1 = new NumericField();
        $this->comp1->setAf($this->af);
        $this->comp1->setRef('comp1');
        $this->comp1->setUnit(new UnitAPI('m'));
        $this->af->addComponent($this->comp1);

        $this->comp2 = new Checkbox();
        $this->comp2->setAf($this->af);
        $this->comp2->setRef('comp2');
        $this->af->addComponent($this->comp2);

        $this->comp3 = new NumericField();
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

        if ($this->af) {
            $this->comp1->delete();
            $this->comp2->delete();
            $this->comp3->delete();
            $this->af->delete();
            $this->af->getLibrary()->delete();
            $this->accountRepository->remove($this->af->getLibrary()->getAccount());
            $this->entityManager->flush();
        }
    }

    public static function setUpBeforeClass()
    {
        if (AF::countTotal() > 0) {
            foreach (AF::loadList() as $o) {
                $o->delete();
            }
            self::getEntityManager()->flush();
        }
    }
}
