<?php

use Doctrine\ORM\EntityManager;
use User\Domain\ACL\Role\AdminRole;
use User\Domain\User;
use User\Domain\UserService;

class Orga_Service_OrganizationService
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var Orga_Service_ACLManager
     */
    private $aclManager;

    /**
     * @var UserService
     */
    private $userService;

    /**
     * @param EntityManager           $entityManager
     * @param Orga_Service_ACLManager $aclManager
     * @param UserService             $userService
     */
    public function __construct(
        EntityManager $entityManager,
        Orga_Service_ACLManager $aclManager,
        UserService $userService
    ) {
        $this->entityManager = $entityManager;
        $this->aclManager = $aclManager;
        $this->userService = $userService;
    }

    /**
     * Crée un projet et assigne un utilisateur comme administrateur
     *
     * @param User $administrator
     * @param array $formData
     * @throws Exception
     * @return Orga_Model_Organization
     */
    public function createOrganization(User $administrator, array $formData)
    {
        $this->entityManager->beginTransaction();

        try {
            /** @var Orga_Model_Granularity[] $dWGranularities */
            $dWGranularities = [];

            // Création de l'organization.
            $organization = new Orga_Model_Organization();
            $organization->setLabel($formData['organization']['elements']['organizationLabel']['value']);

            // Création d'une granularité globale par défaut.
            $defaultGranularity = new Orga_Model_Granularity($organization);
            $defaultGranularity->setCellsWithACL(true);
            $dWGranularities[] = $defaultGranularity;

            if ($formData['organization']['elements']['organizationType']['value'] !== 'empty') {

                $axesData = $formData['axes']['elements'];
                $axes = [];
                // Création des axes principaux.
                foreach ($axesData['mainAxisGroup']['elements'] as $mainAxisData) {
                    $mainAxisId = $mainAxisData['name'];
                    if (isset($mainAxisData['hiddenValues'][$mainAxisId.'-parent'])) {
                        $mainParentAxisId = $mainAxisData['hiddenValues'][$mainAxisId.'-parent'];
                        $mainParentAxis = $axes[$mainParentAxisId];
                    } else {
                        $mainParentAxis = null;
                    }

                    $mainAxisRef = Core_Tools::refactor($mainAxisData['value']);
                    if (isset($axes[$mainAxisRef])) {
                        $i = 2;
                        while (isset($axes[$mainAxisRef.'_'.$i])) {
                            $i++;
                        }
                        $mainAxisRef .= '_'.$i;
                    }

                    $mainAxis = new Orga_Model_Axis($organization, $mainAxisRef, $mainParentAxis);
                    $mainAxis->setLabel($mainAxisData['value']);

                    $axes[$mainAxisId] = $mainAxis;
                }
                // Création de l'axe temps.
                $timeAxisLabel = $axesData['timeAxisGroup']['elements']['timeAxis']['value'];
                $timeAxisRef = Core_Tools::refactor($timeAxisLabel);
                try {
                    $organization->getAxisByRef($timeAxisRef);
                    $timeAxisRef = 't_'.$timeAxisRef;
                } catch (Core_Exception_NotFound $e) {}
                $timeAxis = new Orga_Model_Axis($organization, $timeAxisRef);
                $timeAxis->setLabel($timeAxisLabel);
                $axes['timeAxis'] = $timeAxis;
                // Création de l'axe de subdivision.
                $subdivisionAxisLabel = $axesData['subdivisionAxisGroup']['elements']['subdivisionAxis']['value'];
                if (!empty($subdivisionAxisLabel)) {
                    $subdivisionAxisRef = Core_Tools::refactor($subdivisionAxisLabel);
                    try {
                        $organization->getAxisByRef($subdivisionAxisRef);
                        $subdivisionAxisRef = 'd_'.$subdivisionAxisRef;
                    } catch (Core_Exception_NotFound $e) {}
                    $subdivisionAxis = new Orga_Model_Axis($organization, $subdivisionAxisRef);
                    $subdivisionAxis->setLabel($subdivisionAxisLabel);
                    $axes['subdivisionAxis'] = $subdivisionAxis;
                }

                // Création des membres.
                $membersData = $formData['members'];
                $members = [];
                foreach ($membersData['elements'] as $membersAxisId => $axisMembersData) {
                    $axisId = explode('-members', $membersAxisId)[0];
                    $membersAxis = $axes[$axisId];
                    foreach ($axisMembersData['elements'] as $memberData) {
                        $memberId = $memberData['name'];
                        $memberRef = Core_Tools::refactor($memberData['value']);
                        if (($memberId === 'addMember'.$axisId) || (empty($memberRef))) {
                            continue;
                        }
                        /** @var Orga_Model_Member[] $parentMembers */
                        $parentMembers = [];
                        foreach ($memberData['children'] as $parentMemberAxisId => $parentMemberData) {
                            $parentAxisId = substr($parentMemberAxisId, strlen('parent'.ucfirst($memberId)));
                            $parentMembers[] = $members[$parentAxisId][$parentMemberData['value']];
                        }

                        if (isset($members[$axisId][$memberRef])) {
                            $i = 2;
                            while (isset($members[$axisId][$memberRef.'_'.$i])) {
                                $i++;
                            }
                            $memberRef .= '_'.$i;
                        }
                        $member = new Orga_Model_Member($membersAxis, $memberRef, $parentMembers);
                        $member->setLabel($memberData['value']);
                        $members[$axisId][$memberId] = $member;
                    }
                }

                $granularitiesData = $formData['granularities'];
                // Création de la granularité de collecte.
                $inventoryGranularityId = $granularitiesData['elements']['inventoryGranularityGroup']['elements']['inventoryGranularity']['value'];
                $inventoryGranularityAxes = [];
                $inventoryNavigableGranularityAxes = [];
                foreach (explode('|', $inventoryGranularityId) as $inventoryGranularityAxisId) {
                    $inventoryGranularityAxes[] = $axes[$inventoryGranularityAxisId];
                    if ($inventoryGranularityAxisId !== 'timeAxis') {
                        $inventoryNavigableGranularityAxes[] = $axes[$inventoryGranularityAxisId];
                    }
                }
                $inventoryGranularity = new Orga_Model_Granularity($organization, $inventoryGranularityAxes);
                $organization->setGranularityForInventoryStatus($inventoryGranularity);
                $navigableInventoryGranularity = new Orga_Model_Granularity($organization, $inventoryNavigableGranularityAxes);
                $navigableInventoryGranularity->setCellsWithACL(true);
                // Création des granularités de saisie
                $inputsGranularitiesData = $granularitiesData['elements']['inputsGranularitiesGroup']['elements']['inputsGranularities'];
                foreach ($inputsGranularitiesData['value'] as $inputsGranularityId) {
                    $inputsGranularityAxes = [];
                    $inputNavigableGranularityAxes = [];
                    if ($inputsGranularityId === 'global') {
                        $defaultGranularity->setInputConfigGranularity($defaultGranularity);
                        break;
                    }
                    foreach (explode('|', $inputsGranularityId) as $inputsGranularityAxisId) {
                        $inputsGranularityAxes[] = $axes[$inputsGranularityAxisId];
                        if ($inputsGranularityAxisId !== 'timeAxis' && $inputsGranularityAxisId !== 'subdivisionAxis') {
                            $inputNavigableGranularityAxes[] = $axes[$inputsGranularityAxisId];
                        }
                    }
                    try {
                        $inputsGranularity = $organization->getGranularityByRef(Orga_Model_Granularity::buildRefFromAxes($inputsGranularityAxes));
                    } catch (Core_Exception_NotFound $e) {
                        $inputsGranularity = new Orga_Model_Granularity($organization, $inputsGranularityAxes);
                    }
                    if ($inputsGranularityAxes !== $inputNavigableGranularityAxes) {
                        try {
                            $navigableInputsGranularity = $organization->getGranularityByRef(Orga_Model_Granularity::buildRefFromAxes($inputNavigableGranularityAxes));
                        } catch (Core_Exception_NotFound $e) {
                            $navigableInputsGranularity = new Orga_Model_Granularity($organization, $inputNavigableGranularityAxes);
                        }
                    } else {
                        $navigableInputsGranularity = $inputsGranularity;
                    }
                    $navigableInputsGranularity->setCellsWithACL(true);
                    $inputsGranularity->setInputConfigGranularity($navigableInputsGranularity);
                }

                if ($formData['organization']['elements']['organizationType']['value'] === 'reporting') {
                    $dWGranularitiesData = $formData['dw']['elements']['dwGranularitiesGroup']['elements']['dwGranularities'];
                    foreach ($dWGranularitiesData['value'] as $dWGranularityId) {
                        $dWGranularityAxes = [];
                        if ($dWGranularityId === 'global ') {
                            break;
                        }
                        foreach (explode('|', $dWGranularityId) as $dWGranularityAxisId) {
                            $dWGranularityAxes[] = $axes[$dWGranularityAxisId];
                        }
                        try {
                            $dWGranularity = $organization->getGranularityByRef(Orga_Model_Granularity::buildRefFromAxes($dWGranularityAxes));
                        } catch (Core_Exception_NotFound $e) {
                            $dWGranularity = new Orga_Model_Granularity($organization, $dWGranularityAxes);
                        }
                        $dWGranularity->setCellsWithACL(true);
                        $dWGranularities[] = $dWGranularity;
                    }
                }
            }

            // Sauvegarde.
            $organization->save();
            $this->entityManager->flush();
            // Définition de la création des DW après pour éviter un bug d'insertion.
            foreach ($dWGranularities as $granularityWithDW) {
                $granularityWithDW->setCellsGenerateDWCubes(true);
            }
            $organization->save();

            // Ajout de l'utilisateur courant en tant qu'administrateur.
            $this->aclManager->addOrganizationAdministrator($organization, $administrator->getEmail(), false);
            $this->entityManager->flush();

            // Ajout des superadmins en tant qu'administrateur de l'organisation
            foreach (AdminRole::loadList() as $adminRole) {
                /** @var AdminRole $adminRole */
                $email = $adminRole->getUser()->getEmail();
                $this->aclManager->addOrganizationAdministrator($organization, $email, false);
                $this->entityManager->flush();
            }

            $this->entityManager->commit();

            return $organization;
        } catch (Exception $e) {
            $this->entityManager->rollback();
            $this->entityManager->clear();

            throw $e;
        }
    }

    /**
     * Supprime un projet
     *
     * @param Orga_Model_Organization $organization
     * @throws Exception
     */
    public function deleteOrganization(Orga_Model_Organization $organization)
    {
        $this->entityManager->beginTransaction();

        try {
            $organization = Orga_Model_Organization::load($organization->getId());
            $organization->setGranularityForInventoryStatus();

            $this->entityManager->flush();
            $this->entityManager->clear();

            $organization = Orga_Model_Organization::load($organization->getId());
            $granularities = $organization->getOrderedGranularities()->toArray();
            foreach (array_reverse($granularities) as $granularity) {
                $granularity = Orga_Model_Granularity::load($granularity->getId());
                $granularity->setInputConfigGranularity();
                $granularity->delete();
            }

            $this->entityManager->flush();
            $this->entityManager->clear();

            $organization = Orga_Model_Organization::load($organization->getId());
            $organization->delete();

            $this->entityManager->flush();
            $this->entityManager->commit();
        } catch (Exception $e) {
            $this->entityManager->rollback();
            $this->entityManager->clear();

            throw $e;
        }
    }

    /**
     * @param Orga_Model_Organization $organization
     * @param Orga_Model_Axis[] $axes
     * @param array $configuration [$attribute => $value] : relevance(bool), dWCube(bool)
     *
     * @throws Exception
     *
     * @return \Orga_Model_Granularity
     */
    public function addGranularity(Orga_Model_Organization $organization, array $axes, array $configuration=[])
    {
        try {
            $granularity = $organization->getGranularityByRef(Orga_Model_Granularity::buildRefFromAxes($axes));
        } catch (Core_Exception_NotFound $e) {
            $granularity = new Orga_Model_Granularity($organization, $axes);
        }

        foreach ($configuration as $attribute => $value) {
            switch ($attribute) {
                case 'relevance':
                    $granularity->setCellsControlRelevance($value);
                    break;
                case 'afs':
                    try {
                        $inputConfigGranularity = $organization->getGranularityByRef(
                            Orga_Model_Granularity::buildRefFromAxes($value)
                        );
                    } catch (Core_Exception_NotFound $e) {
                        $inputConfigGranularity = new Orga_Model_Granularity($organization, $value);
                        $inputConfigGranularity->save();
                    }
                    $granularity->setInputConfigGranularity($inputConfigGranularity);
                    break;
                case 'reports':
                    $granularity->setCellsGenerateDWCubes($value);
                    break;
                case 'acl':
                    $granularity->setCellsWithACL($value);
                    break;
            }
        }

        try {
            $this->entityManager->beginTransaction();

            $granularity->save();

            $this->entityManager->flush();
            $this->entityManager->commit();
        } catch (Exception $e) {
            $this->entityManager->rollback();
            $this->entityManager->clear();

            throw $e;
        }

        return $granularity;
    }

    /**
     * @param Orga_Model_Axis $axis
     * @param $ref
     * @param $label
     * @param array $parentMembers
     *
     * @throws Exception
     */
    public function addMember(Orga_Model_Axis $axis, $ref, $label, array $parentMembers)
    {
        $member = new Orga_Model_Member($axis, $ref, $parentMembers);
        $member->setLabel($label);

        try {
            $this->entityManager->beginTransaction();

            $member->save();

            $this->entityManager->flush();
            $this->entityManager->commit();
        } catch (Exception $e) {
            $this->entityManager->rollback();
            $this->entityManager->clear();

            throw $e;
        }
    }

    /**
     * @param Orga_Model_Member $member
     *
     * @throws Exception
     */
    public function deleteMember(Orga_Model_Member $member)
    {
        try {
            $this->entityManager->beginTransaction();

            $member->removeFromAxis();
            $member->delete();

            $this->entityManager->flush();
            $this->entityManager->commit();
        } catch (Exception $e) {
            $this->entityManager->rollback();
            $this->entityManager->clear();

            throw $e;
        }
    }

    /**
     * @param string $email
     * @param string $password
     * @return Orga_Model_Organization
     */
    public function initDemoUserAndWorkspace($email, $password)
    {
        $user = $this->userService->createUser($email, $password);

        // MOCHE
        $formData = [];
        $formData['organization']['elements']['organizationLabel']['value'] =
            __('Orga', 'organization', 'defaultWorkspaceLabel');
        $formData['organization']['elements']['organizationType']['value'] = 'empty';

        return $this->createOrganization($user, $formData);
    }
}
