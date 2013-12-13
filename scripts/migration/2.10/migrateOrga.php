<?php

echo "\n".'Vérification des ganularité contrôlant la pertinence…'."\n";
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

echo "\n".'-> Flush starting…'."\n";
$em->flush();
$em->clear();
echo "\n".'-> Flush ended !'."\n";

