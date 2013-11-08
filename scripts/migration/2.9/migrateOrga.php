<?php
// Entity Manager
$entityManagers = Zend_Registry::get('EntityManagers');
/** @var $entityManager \Doctrine\ORM\EntityManager */
$entityManager = $entityManagers['default'];

/** @var Orga_Model_Organization $organization */
foreach (Orga_Model_Organization::loadList() as $organization) {
    foreach ($organization->getAxes() as $axis) {
        $axis->updateNarrowerTag();
        $axis->updateBroaderTag();
        foreach ($axis->getMembers() as $member) {
            $member->updateParentMembersHashKeys();
            $member->updateTag();
        }
    }
    foreach ($organization->getGranularities() as $granularity) {
        $granularity->updateRef();
        $granularity->updateTag();
    }
    foreach ($organization->getGranularities() as $granularity) {
        foreach ($granularity->getCells() as $cell) {
            $cell->updateMembersHashKey();
            $cell->updateTag();
        }
    }
    foreach ($organization->getRootAxes() as $rootAxis) {
        $rootAxis->updateTags();
    }
}

echo "\n".'-> Flush starting…'."\n";
$entityManager->flush();
echo "\n".'-> Flush ended !'."\n";
