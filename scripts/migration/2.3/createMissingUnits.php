<?php

use Unit\Domain\PhysicalQuantity;
use Unit\Domain\Unit\DiscreteUnit;
use Unit\Domain\Unit\StandardUnit;
use Unit\Domain\UnitSystem;

$entityManagers = Zend_Registry::get('EntityManagers');
/** @var \Doctrine\ORM\EntityManager $entityManager */
$entityManager = $entityManagers['default'];

// Unité discrète : devise
try {
    DiscreteUnit::loadByRef('devise');
    echo "Unit 'devise' already exists, skipping" . PHP_EOL;
} catch (Core_Exception_NotFound $e) {
    // L'unité n'existe pas, il faut la créer
    $devise = new DiscreteUnit();
    $devise->setRef('devise');

    $devise->setTranslationLocale(Core_Locale::load('fr'));
    $devise->setName('devise ci-dessus');
    $devise->setSymbol('devise ci-dessus');
    $devise->save();
    $entityManager->flush();

    $devise->setTranslationLocale(Core_Locale::load('en'));
    $devise->setName('currency above');
    $devise->setSymbol('currency above');
    $devise->save();
    $entityManager->flush();

    echo "Created unit 'devise'" . PHP_EOL;
}

// Unité standard : mile
try {
    StandardUnit::loadByRef('mile');
    echo "Unit 'mile' already exists, skipping" . PHP_EOL;
} catch (Core_Exception_NotFound $e) {
    // L'unité n'existe pas, il faut la créer
    $mile = new StandardUnit();
    $mile->setRef('mile');
    $mile->setMultiplier(1609.344);
    $mile->setUnitSystem(UnitSystem::loadByRef('anglo_saxon'));
    $mile->setPhysicalQuantity(PhysicalQuantity::loadByRef('l'));

    $mile->setTranslationLocale(Core_Locale::load('fr'));
    $mile->setName('mile');
    $mile->setSymbol('mi');
    $mile->save();
    $entityManager->flush();

    $mile->setTranslationLocale(Core_Locale::load('en'));
    $mile->setName('mile');
    $mile->setSymbol('mi');
    $mile->save();
    $entityManager->flush();

    echo "Created unit 'mile'" . PHP_EOL;
}
