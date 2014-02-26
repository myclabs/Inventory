<?php

namespace Tests\AF;

use AF\Domain\AF;
use AF\Domain\AFLibrary;
use AF\Domain\Component\NumericField;
use AF\Domain\Condition\NumericFieldCondition;
use AF\Domain\AFCopyService;
use Core\Test\TestCase;
use Unit\UnitAPI;

/**
 * Test de la copie d'un AF
 *
 * @covers \AF\Domain\AFCopyService
 */
class AFCopyTest extends TestCase
{
    public function testCopyAF()
    {
        $library = new AFLibrary('foo');

        $oldAF = new AF($library, 'old_ref');
        $oldAF->setLabel('label');
        $oldAF->setDocumentation('documentation');

        $component = new NumericField();
        $component->setRef('component1');
        $component->setUnit(new UnitAPI('m'));
        $component->setAf($oldAF);
        $oldAF->addComponent($component);

        $condition = new NumericFieldCondition();
        $condition->setRef('condition1');
        $condition->setAf($oldAF);
        $condition->setField($component);
        $condition->setRelation(NumericFieldCondition::RELATION_EQUAL);
        $condition->setValue(0);
        $oldAF->addCondition($condition);

        $afCopyService = new AFCopyService();
        /** @var AF $newAF */
        $newAF = $afCopyService->copyAF($oldAF, 'new_ref', 'new label');

        $this->assertInstanceOf(get_class($oldAF), $newAF);

        $this->assertNull($newAF->getId());
        $this->assertNull($newAF->getPosition());
        $this->assertEquals('new_ref', $newAF->getRef());
        $this->assertEquals('new label', $newAF->getLabel());
        $this->assertEquals($oldAF->getDocumentation(), $newAF->getDocumentation());
        $this->assertNull($newAF->getCategory());
        $this->assertSame($library, $newAF->getLibrary());

        // Root group
        $this->assertNotSame($oldAF->getRootGroup(), $newAF->getRootGroup());
        $this->assertInstanceOf(get_class($oldAF->getRootGroup()), $newAF->getRootGroup());
        $this->assertNull($newAF->getRootGroup()->getId());
        $this->assertSame($newAF, $newAF->getRootGroup()->getAf());
        $this->assertSameSize(
            $oldAF->getRootGroup()->getSubComponentsRecursive(),
            $newAF->getRootGroup()->getSubComponentsRecursive()
        );
        foreach ($newAF->getRootGroup()->getSubComponentsRecursive() as $newSubComponent) {
            $this->assertSame($newAF, $newSubComponent->getAf());
            $this->assertNull($newSubComponent->getId());
            $this->assertNull($newSubComponent->getPosition());
        }

        // Component
        /** @var NumericField $component2 */
        $component2 = $newAF->getRootGroup()->getSubComponentsRecursive()[0];
        $this->assertNotSame($component, $component2);
        $this->assertSame($newAF, $component2->getAf());
        $this->assertEquals($component->getRef(), $component2->getRef());
        $this->assertEquals($component->getUnit(), $component2->getUnit());

        // Algos
        $this->assertNotSame($oldAF->getMainAlgo(), $newAF->getMainAlgo());
        $this->assertInstanceOf(get_class($oldAF->getMainAlgo()), $newAF->getMainAlgo());
        $this->assertNull($newAF->getMainAlgo()->getId());
        foreach ($oldAF->getAlgos() as $oldAlgo) {
            $newAlgo = $newAF->getAlgoByRef($oldAlgo->getRef());
            $this->assertNotSame($oldAlgo, $newAlgo);
            $this->assertInstanceOf(get_class($oldAlgo), $newAlgo);
            $this->assertNull($newAlgo->getId());
        }

        // Condition
        $this->assertSameSize($oldAF->getConditions(), $newAF->getConditions());
        /** @var NumericFieldCondition $condition2 */
        $condition2 = $newAF->getConditions()[0];
        $this->assertNotSame($condition, $condition2);
        $this->assertNull($condition2->getId());
        $this->assertSame($newAF, $condition2->getAf());
        $this->assertSame($component2, $condition2->getField());
        $this->assertSame($condition->getRef(), $condition2->getRef());
        $this->assertSame($condition->getValue(), $condition2->getValue());
        $this->assertSame($condition->getRelation(), $condition2->getRelation());
    }
}
