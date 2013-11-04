<?php
/**
 * @author  matthieu.napoli
 * @package Orga
 * @subpackage Service
 */

use Doctrine\ORM\EntityManager;

/**
 * @package Orga
 * @subpackage Service
 */
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
     * @param EntityManager           $entityManager
     * @param Orga_Service_ACLManager $aclManager
     */
    public function __construct(EntityManager $entityManager, Orga_Service_ACLManager $aclManager)
    {
        $this->entityManager = $entityManager;
        $this->aclManager = $aclManager;
    }

    /**
     * Crée un projet et assigne un utilisateur comme administrateur
     *
     * @param User_Model_User $administrator
     * @param array $formData
     * @throws Exception
     * @return Orga_Model_Organization
     */
    public function createOrganization(User_Model_User $administrator, array $formData)
    {
        $this->entityManager->beginTransaction();

        try {
            $dWGranularities = [];

            // Création de l'organization.
            $organization = new Orga_Model_Organization();
            $organization->setLabel($formData['organization']['elements']['organizationLabel']['value']);
            
            // Création d'une granularité globale par défaut.
            $defaultGranularity = new Orga_Model_Granularity($organization);
            $defaultGranularity->setNavigability(true);
            $defaultGranularity->setCellsWithOrgaTab(true);
            $defaultGranularity->setCellsWithACL(true);
            $defaultGranularity->setCellsWithAFConfigTab(true);
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

                    $mainAxis = new Orga_Model_Axis($organization);
                    $mainAxis->setRef($mainAxisRef);
                    $mainAxis->setLabel($mainAxisData['value']);
                    if ($mainParentAxis) {
                        $mainAxis->setDirectNarrower($mainParentAxis);
                    }

                    $axes[$mainAxisId] = $mainAxis;
                }
                // Création de l'axe temps.
                $timeAxisLabel = $axesData['timeAxisGroup']['elements']['timeAxis']['value'];
                $timeAxisRef = Core_Tools::refactor($timeAxisLabel);
                try {
                    $organization->getAxisByRef($timeAxisRef);
                    $timeAxisRef = 't_'.$timeAxisRef;
                } catch (Core_Exception_NotFound $e) {}
                $timeAxis = new Orga_Model_Axis($organization);
                $timeAxis->setRef($timeAxisRef);
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
                    $subdivisionAxis = new Orga_Model_Axis($organization);
                    $subdivisionAxis->setRef($subdivisionAxisRef);
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

                        if (isset($members[$axisId][$memberRef])) {
                            $i = 2;
                            while (isset($members[$axisId][$memberRef.'_'.$i])) {
                                $i++;
                            }
                            $memberRef .= '_'.$i;
                        }
                        $member = new Orga_Model_Member($membersAxis);
                        $member->setRef($memberRef);
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
                $inventoryGranularity->setNavigability(false);
                $organization->setGranularityForInventoryStatus($inventoryGranularity);
                $navigableInventoryGranularity = new Orga_Model_Granularity($organization, $inventoryNavigableGranularityAxes);
                $navigableInventoryGranularity->setNavigability(true);
                $navigableInventoryGranularity->setCellsWithACL(true);
                // Création des granularités de saisie
                $inputGranularitiesData = $granularitiesData['elements']['inputGranularitiesGroup']['elements']['inputGranularities'];
                foreach ($inputGranularitiesData['value'] as $inputGranularityId) {
                    $inputGranularityAxes = [];
                    $inputNavigableGranularityAxes = [];
                    if ($inputGranularityId === 'global') {
                        $defaultGranularity->setInputConfigGranularity($defaultGranularity);
                        break;
                    }
                    foreach (explode('|', $inputGranularityId) as $inputGranularityAxisId) {
                        $inputGranularityAxes[] = $axes[$inputGranularityAxisId];
                        if ($inputGranularityAxisId !== 'timeAxis' && $inputGranularityAxisId !== 'subdivisionAxis') {
                            $inputNavigableGranularityAxes[] = $axes[$inputGranularityAxisId];
                        }
                    }
                    try {
                        $inputGranularity = $organization->getGranularityByRef(Orga_Model_Granularity::buildRefFromAxes($inputGranularityAxes));
                    } catch (Core_Exception_NotFound $e) {
                        $inputGranularity = new Orga_Model_Granularity($organization, $inputGranularityAxes);
                        $inputGranularity->setNavigability(false);
                    }
                    if ($inputGranularityAxes !== $inputNavigableGranularityAxes) {
                        try {
                            $navigableInputGranularity = $organization->getGranularityByRef(Orga_Model_Granularity::buildRefFromAxes($inputNavigableGranularityAxes));
                        } catch (Core_Exception_NotFound $e) {
                            $navigableInputGranularity = new Orga_Model_Granularity($organization, $inputNavigableGranularityAxes);
                        }
                    } else {
                        $navigableInputGranularity = $inputGranularity;
                    }
                    $navigableInputGranularity->setNavigability(true);
                    $navigableInputGranularity->setCellsWithACL(true);
                    $inputGranularity->setInputConfigGranularity($navigableInputGranularity);
                }

                Core_Tools::dump($formData['dw']['elements']);
                if ($formData['organization']['elements']['organizationType']['value'] === 'reporting') {
                    $dWGranularitiesData = $formData['dw']['elements']['dwGranularitiesGroup']['elements']['dwGranularities'];
                    foreach ($dWGranularitiesData['value'] as $dWGranularityId) {
                        $dWGranularityAxes = [];
                        if ($dWGranularityId === 'global') {
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
                        $dWGranularity->setNavigability(true);
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
            $this->aclManager->addOrganizationAdministrator($organization, $administrator, false);
            $this->entityManager->flush();

            $this->entityManager->commit();

            return $organization;
            throw new Core_Exception_InvalidArgument();
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
            // Suppression des autorisations de tous les utilisateurs.
            $this->clearAttachedUsers(User_Model_Resource_Entity::loadByEntity($organization));
            foreach ($organization->getGranularities() as $granularity) {
                foreach ($granularity->getCells() as $cell) {
                    $this->clearAttachedUsers(User_Model_Resource_Entity::loadByEntity($cell));
                }
            }

            $this->entityManager->flush();
            $this->entityManager->clear();

            $organization = Orga_Model_Organization::load($organization->getId());
            $organization->setGranularityForInventoryStatus();

            $this->entityManager->flush();
            $this->entityManager->clear();

            $organization = Orga_Model_Organization::load($organization->getId());
            $granularities = $organization->getGranularities();
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
     * @param User_Model_Resource_Entity $resource
     */
    protected function clearAttachedUsers(User_Model_Resource_Entity $resource)
    {
        foreach ($resource->getLinkedSecurityIdentities() as $securityIdentity) {
            if ($securityIdentity instanceof User_Model_Role) {
                foreach ($securityIdentity->getUsers() as $attachedUser) {
                    $attachedUser->removeRole($securityIdentity);
                }
            }
        }
    }

}
