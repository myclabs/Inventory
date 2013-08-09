<?php
// Entity Manager
$entityManagers = Zend_Registry::get('EntityManagers');
/** @var $entityManager \Doctrine\ORM\EntityManager */
$entityManager = $entityManagers['default'];

$entityManager->beginTransaction();

/** @var Techno_Model_Family_Process[] $families */
$families = Techno_Model_Family_Process::loadList();

foreach ($families as $processFamily) {
    echo "Creating " . $processFamily->getRef() . PHP_EOL;

    $coeffFamily = new Techno_Model_Family_Coeff();
    $coeffFamily->setRef($processFamily->getRef());

    // Renomme l'ancienne famille
    $processFamily->setRef($processFamily->getRef() . '_backup');
    $entityManager->flush();

    $coeffFamily->setLabel($processFamily->getLabel());
    $coeffFamily->setCategory($processFamily->getCategory());

    // Unit
    $unit = $processFamily->getValueUnit();
    $coeffFamily->setBaseUnit($unit->getNormalizedUnit());
    $coeffFamily->setUnit($unit);

    $coeffFamily->save();
    $entityManager->flush();

    // Tags
    foreach ($processFamily->getTags() as $tag) {
        $newTag = new Techno_Model_Tag();
        $newTag->setValue($tag->getValue());
        $newTag->setMeaning($tag->getMeaning());
        $coeffFamily->addTag($newTag);
    }

    // Dimensions
    foreach ($processFamily->getDimensions() as $dimension) {
        $newDimension = new Techno_Model_Family_Dimension(
            $coeffFamily,
            $dimension->getMeaning(),
            $dimension->getOrientation(),
            $dimension->getQuery()
        );
        // Members
        foreach ($dimension->getMembers() as $member) {
            $newMember = new Techno_Model_Family_Member($newDimension, $member->getKeyword());
            $newDimension->addMember($newMember);
            $newMember->save();
        }
        $coeffFamily->addDimension($newDimension);
    }
    echo "\tDimensions: " . count($processFamily->getDimensions()) . " => " . count($coeffFamily->getDimensions()) . PHP_EOL;

    // Elements
    $countElements = 0;
    foreach ($processFamily->getCells() as $cell) {
        /** @var Techno_Model_Element_Process $chosenElement */
        $chosenElement = $cell->getChosenElement();
        if ($chosenElement) {
            $countElements++;
            $element = new Techno_Model_Element_Coeff();
            $element->setValue(clone $chosenElement->getValue());
            $element->setBaseUnit(clone $coeffFamily->getBaseUnit());
            $element->setUnit(clone $coeffFamily->getUnit());
            $element->save();

            $coeffFamily->getCell($cell->getMembers())->setChosenElement($element);

            $chosenElement->delete();
        }
    }
    echo "\tCells: " . count($processFamily->getCells()) . " => " . count($coeffFamily->getCells()) . PHP_EOL;
    echo "\tElements: " . $countElements . PHP_EOL;

    $processFamily->delete();

    echo "Created " . $processFamily->getRef() . PHP_EOL . PHP_EOL;
}

$entityManager->flush();
$entityManager->commit();
