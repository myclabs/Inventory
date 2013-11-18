<?php
use Core\Test\TestCase;
use Unit\UnitAPI;

/**
 * Test de la copie d'un AF
 */
class AFCopyTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        foreach (AF_Model_Component::loadList() as $o) {
            $o->delete();
        }
        foreach (AF_Model_AF::loadList() as $o) {
            $o->delete();
        }
        $this->entityManager->flush();
    }

    public function testCopyAF()
    {
        $oldAF = new AF_Model_AF('old_ref');
        $oldAF->setLabel('label');
        $oldAF->setDocumentation('documentation');
        $oldAF->save();

        $component = new AF_Model_Component_Numeric();
        $component->setRef('component1');
        $component->setUnit(new UnitAPI('m'));
        $component->setAf($oldAF);
        $component->save();
        $oldAF->addComponent($component);

        $condition = new AF_Model_Condition_Elementary_Numeric();
        $condition->setRef('condition1');
        $condition->setAf($oldAF);
        $condition->setField($component);
        $condition->setRelation(AF_Model_Condition_Elementary_Numeric::RELATION_EQUAL);
        $condition->setValue(0);
        $oldAF->addCondition($condition);

        $this->entityManager->flush();

        $afCopyService = new AF_Service_AFCopyService();
        /** @var AF_Model_AF $newAF */
        $newAF = $afCopyService->copyAF($oldAF, 'new_ref');

        $this->assertInstanceOf(get_class($oldAF), $newAF);

        $this->assertNull($newAF->getId());
        $this->assertNull($newAF->getPosition());
        $this->assertEquals('new_ref', $newAF->getRef());
        $this->assertEquals($oldAF->getLabel(), $newAF->getLabel());
        $this->assertEquals($oldAF->getDocumentation(), $newAF->getDocumentation());
        $this->assertNull($newAF->getCategory());

        // Root group
        $this->assertNotSame($oldAF->getRootGroup(), $newAF->getRootGroup());
        $this->assertInstanceOf(get_class($oldAF->getRootGroup()), $newAF->getRootGroup());
        $this->assertNull($newAF->getRootGroup()->getId());
        $this->assertSame($newAF, $newAF->getRootGroup()->getAf());
        $this->assertSameSize($oldAF->getRootGroup()->getSubComponentsRecursive(),
            $newAF->getRootGroup()->getSubComponentsRecursive());
        foreach ($newAF->getRootGroup()->getSubComponentsRecursive() as $newSubComponent) {
            $this->assertSame($newAF, $newSubComponent->getAf());
            $this->assertNull($newSubComponent->getId());
            $this->assertNull($newSubComponent->getPosition());
        }

        // Component
        /** @var AF_Model_Component_Numeric $component2 */
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
        /** @var AF_Model_Condition_Elementary_Numeric $condition2 */
        $condition2 = $newAF->getConditions()[0];
        $this->assertNotSame($condition, $condition2);
        $this->assertNull($condition2->getId());
        $this->assertSame($newAF, $condition2->getAf());
        $this->assertSame($component2, $condition2->getField());
        $this->assertSame($condition->getRef(), $condition2->getRef());
        $this->assertSame($condition->getValue(), $condition2->getValue());
        $this->assertSame($condition->getRelation(), $condition2->getRelation());

        $condition->delete();
        $component->delete();
        $this->entityManager->flush();

        $oldAF->delete();
        $this->entityManager->flush();
    }
}
