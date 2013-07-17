<?php
// Entity Manager
$entityManagers = Zend_Registry::get('EntityManagers');
/** @var $entityManager \Doctrine\ORM\EntityManager */
$entityManager = $entityManagers['default'];

$temporaryLabel = 'Temporary Label';
$translatableClasses = [
    'Keyword_Model_Predicate' => [],
    'Keyword_Model_Keyword' => [],
    'Techno_Model_Category' => [],
    'Techno_Model_Family' => [],
    'Classif_Model_Axis' => [],
    'Classif_Model_Member' => [],
    'Classif_Model_Indicator' => [],
    'Classif_Model_Context' => [],
    'AF_Model_Category' => [],
    'AF_Model_AF' => [],
    'AF_Model_Component' => [],
    'AF_Model_Component_Select_Option' => [],
    'Algo_Model_Numeric' => [],
    'Orga_Model_Organization' => [],
    'Orga_Model_Axis' => [],
    'Orga_Model_Member' => []
];

// Save old labels and set a temporary one.
foreach ($translatableClasses as $className => $labels) {
    echo "\n".'Saving old labels for '.$className."\n";
    $listEntities = $className::loadList();
    foreach ($listEntities as $entity) {
        $translatableClasses[$className][spl_object_hash($entity)] = $entity->getLabel();
        $entity->setLabel($temporaryLabel);
        echo "\t".'from '.$translatableClasses[$className][spl_object_hash($entity)].' to '.$entity->getLabel()."\n";
    }
}

echo "\n".'-> Flush starting…'."\n";
$entityManager->flush();
echo "\n".'-> Flush ended !'."\n";

// Restore old labels.
foreach ($translatableClasses as $className => $labels) {
    echo "\n".'Restoring old labels for '.$className."\n";
    $listEntities = $className::loadList();
    foreach ($listEntities as $entity) {
        echo "\t".'from '.$entity->getLabel().' to '.$translatableClasses[$className][spl_object_hash($entity)]."\n";
        $entity->setLabel($translatableClasses[$className][spl_object_hash($entity)]);
    }
}

$entityManager->flush();