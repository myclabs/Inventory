<?php

/** @var Orga_Model_Organization $organization */
foreach (Orga_Model_Organization::loadList() as $organization) {
    echo "Axes & members" . PHP_EOL;
    foreach ($organization->getAxes() as $axis) {
        $axis->updateNarrowerTag();
        $axis->updateBroaderTag();
        foreach ($axis->getMembers() as $member) {
            $member->updateParentMembersHashKeys();
            $member->updateTag();
        }
    }
    echo "Granularities" . PHP_EOL;
    foreach ($organization->getGranularities() as $granularity) {
        $granularity->updateRef();
        $granularity->updateTag();
    }
    echo "Cells" . PHP_EOL;
    foreach ($organization->getGranularities() as $granularity) {
        foreach ($granularity->getCells() as $cell) {
            $cell->updateMembersHashKey();
            $cell->updateTag();
        }
    }
    echo "Root axes" . PHP_EOL;
    foreach ($organization->getRootAxes() as $rootAxis) {
        $rootAxis->updateTags();
    }
}

echo "\n".'-> Flush starting…'."\n";
$em->flush();
$em->clear();
echo "\n".'-> Flush ended !'."\n";
