<?php

/** @var Orga_Model_Organization $organization */
foreach (Orga_Model_Organization::loadList() as $organization) {
    echo "Root axes" . PHP_EOL;
    /** @var Orga_Model_Axis $rootAxis */
    foreach ($organization->getRootAxes() as $rootAxis) {
        $rootAxis->updateNarrowerTag();
    }
    echo "Axes & members" . PHP_EOL;
    $axes = $organization->getAxes()->toArray();
    $axes = array_reverse($axes);
    /** @var Orga_Model_Axis $axis */
    foreach ($axes as $axis) {
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
    foreach ($axes as $axis) {
        $axis->updateBroaderTag();
        foreach ($axis->getMembers() as $member) {
            foreach ($member->getCells() as $cell) {
                $cell->updateMembersHashKey();
                $cell->updateTags();
            }
        }
    }
}

echo "\n".'-> Flush starting…'."\n";
$em->flush();
$em->clear();
echo "\n".'-> Flush ended !'."\n";
