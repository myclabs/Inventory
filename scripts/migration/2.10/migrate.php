<?php

use Doctrine\ORM\EntityManager;

define('RUN', false);

require_once __DIR__ . '/../../../application/init.php';

$container = \Core\ContainerSingleton::getContainer();
/** @var EntityManager $em */
$em = $container->get(EntityManager::class);

// Migration d'orga
echo "Vérification des ganularité contrôlant la pertinence\n";
/** @var Orga_Model_Organization $organization */
foreach (Orga_Model_Organization::loadList() as $organization) {
    $organization->orderGranularities();
    foreach ($organization->getOrderedGranularities() as $granularity) {
        foreach ($granularity->getCells() as $cell) {
            if ($cell->getRelevant() === false) {
                $granularity->setCellsControlRelevance(true);
                echo "\t".$granularity->getLabel().' now controls relevance !'."\n";
                continue 2;
            }
        }
        $granularity->setCellsControlRelevance(false);
    }
}

$em->flush();
