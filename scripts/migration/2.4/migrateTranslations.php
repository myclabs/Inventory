<?php
// Entity Manager
$entityManager = \Core\ContainerSingleton::getEntityManager();

$temporaryLabel = 'Temporary Label';
$translatableClasses = [
    'Keyword\Domain\Predicate' => ['attributes' => ['label', 'reverseLabel'], 'translations' => []],
    'Keyword\Domain\Keyword' => ['attributes' => ['label'], 'translations' => []],
    'Techno\Domain\Category' => ['attributes' => ['label'], 'translations' => []],
    'Techno\Domain\Family\Family' => ['attributes' => ['label', 'documentation'], 'translations' => []],
    'Techno\Domain\Element\Element' => ['attributes' => ['documentation'], 'translations' => []],
    'Classif_Model_Axis' => ['attributes' => ['label'], 'translations' => []],
    'Classif_Model_Member' => ['attributes' => ['label'], 'translations' => []],
    'Classif_Model_Indicator' => ['attributes' => ['label'], 'translations' => []],
    'Classif_Model_Context' => ['attributes' => ['label'], 'translations' => []],
    'AF_Model_Category' => ['attributes' => ['label'], 'translations' => []],
    'AF_Model_AF' => ['attributes' => ['label'], 'translations' => []],
    'AF_Model_Component' => ['attributes' => ['label', 'help'], 'translations' => []],
    'AF_Model_Component_Select_Option' => ['attributes' => ['label'], 'translations' => []],
    'Algo_Model_Numeric' => ['attributes' => ['label'], 'translations' => []],
    'Orga_Model_Organization' => ['attributes' => ['label'], 'translations' => []],
    'Orga_Model_Axis' => ['attributes' => ['label'], 'translations' => []],
    'Orga_Model_Member' => ['attributes' => ['label'], 'translations' => []]
];

// Save old labels and set a temporary one.
foreach ($translatableClasses as $className => $config) {
    foreach ($config['attributes'] as $attribute) {
        $setter = 'set'.ucfirst($attribute);
        $getter = 'get'.ucfirst($attribute);
        echo "\n".'Saving old "'.$attribute.'" for '.$className."\n";
        foreach ($className::loadList() as $entity) {
            $translatableClasses[$className]['translations'][$attribute][spl_object_hash($entity)] = $entity->$getter();
            $entity->$setter($temporaryLabel);
            echo "\t".'from '.$translatableClasses[$className]['translations'][$attribute][spl_object_hash($entity)].' to '.$entity->$getter()."\n";
        }
    }
}

echo "\n".'-> Flush starting…'."\n";
$entityManager->flush();
echo "\n".'-> Flush ended !'."\n";

// Restore old labels.
foreach ($translatableClasses as $className => $config) {
    foreach ($config['attributes'] as $attribute) {
        $setter = 'set'.ucfirst($attribute);
        $getter = 'get'.ucfirst($attribute);
        echo "\n".'Restoring old "'.$attribute.'" for '.$className."\n";
        foreach ($className::loadList() as $entity) {
            echo "\t".'from '.$entity->$getter().' to '.$translatableClasses[$className]['translations'][$attribute][spl_object_hash($entity)]."\n";
            $entity->$setter($translatableClasses[$className]['translations'][$attribute][spl_object_hash($entity)]);
        }
    }
}

$entityManager->flush();
