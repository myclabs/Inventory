<?php

use Doctrine\ORM\EntityManager;
use Orga\Model\ACL\Action\CellAction;
use Orga\Model\ACL\CellAuthorization;
use Orga\Model\ACL\Role\CellManagerRole;
use User\Domain\ACL\ACLService;
use User\Domain\ACL\Action;
use User\Domain\ACL\Role\AdminRole;
use User\Domain\User;
use User\Domain\UserService;

class Orga_Service_OrganizationService
{
    const TEMPLATE_EMPTY = 'empty';
    const TEMPLATE_DEMO = 'demo';
    const TEMPLATE_USER_INVENTORY = 'userInventory';
    const TEMPLATE_USER_REPORTING = 'userReports';

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
     * @var ACLService
     */
    private $aclService;

    /**
     * @param EntityManager           $entityManager
     * @param Orga_Service_ACLManager $aclManager
     * @param UserService             $userService
     * @param ACLService              $aclService
     */
    public function __construct(
        EntityManager $entityManager,
        Orga_Service_ACLManager $aclManager,
        UserService $userService,
        ACLService $aclService
    ) {
        $this->entityManager = $entityManager;
        $this->aclManager = $aclManager;
        $this->userService = $userService;
        $this->aclService = $aclService;
    }

    /**
     * @return array
     */
    public function getOrganizationTemplates()
    {
        return [
            self::TEMPLATE_EMPTY => __('Orga', 'add', 'templateEmpty'),
            self::TEMPLATE_USER_INVENTORY => __('Orga', 'add', 'templateUserInventory'),
            self::TEMPLATE_USER_REPORTING => __('Orga', 'add', 'templateUserReports'),
            self::TEMPLATE_DEMO => __('Orga', 'add', 'templateDemo'),
        ];
    }

    /**
     * @param string $labelOrganization
     * @throws Exception
     * @return Orga_Model_Organization
     */
    public function createOrganization($labelOrganization = '')
    {
        $this->entityManager->beginTransaction();

        try {
            // Création de l'organization.
            $organization = new Orga_Model_Organization();
            $organization->setLabel($labelOrganization);

            // Création d'une granularité globale par défaut.
            $defaultGranularity = new Orga_Model_Granularity($organization);
            $defaultGranularity->setCellsWithACL(true);

            $organization->save();
            $this->entityManager->flush();

            // Héritage des ACL sur toutes les organizations, sur la cellule globale de la nouvelle.
            $globalCell = $organization->getGranularityByRef('global')->getCellByMembers([]);
            /** @var AdminRole $admin */
            foreach (AdminRole::loadList() as $admin) {
                // Cellule global
                $cellAuthorizations = CellAuthorization::createMany($admin, $globalCell, [
                    Action::VIEW(),
                    Action::EDIT(),
                    Action::ALLOW(),
                    CellAction::COMMENT(),
                    CellAction::INPUT(),
                    CellAction::VIEW_REPORTS(),
                ]);

                // Cellules filles
                foreach ($globalCell->getChildCells() as $childCell) {
                    foreach ($cellAuthorizations as $authorization) {
                        CellAuthorization::createChildAuthorization($authorization, $childCell);
                    }
                }
            }

            $this->entityManager->flush();
            $this->entityManager->commit();

            // Recharge l'organisation pour que les ACL soient rechargées depuis la BDD
            $this->entityManager->refresh($organization);
            $this->entityManager->refresh($organization->getGranularityByRef('global')->getCellByMembers([]));

            return $organization;
        } catch (Exception $e) {
            $this->entityManager->rollback();
            $this->entityManager->clear();

            throw $e;
        }
    }

    /**
     * @param User $administrator
     * @param array $formData
     * @throws Exception
     * @return Orga_Model_Organization
     */
    public function createOrganizationFromTemplatesForm(User $administrator, array $formData)
    {
        $this->entityManager->beginTransaction();

        try {
            $organizationLabel = $formData['organization']['elements']['organizationLabel']['value'];
            $organization = $this->createOrganization($organizationLabel);

            $template = $formData['organization']['elements']['organizationTemplate']['value'];
            if ($template !== self::TEMPLATE_EMPTY) {
                if (($template === self::TEMPLATE_USER_INVENTORY) || ($template == self::TEMPLATE_USER_REPORTING)) {
                    $this->initOrganizationUserForm($organization, $formData);
                } else {
                    $functionName = 'initOrganization' . ucfirst($template);
                    $this->$functionName($organization);
                }

                $organization->save();
                $this->entityManager->flush();
            }

            // Ajout de l'utilisateur courant en tant qu'administrateur.
            $this->aclManager->addOrganizationAdministrator($organization, $administrator->getEmail(), false);
            $this->entityManager->flush();

            $this->entityManager->commit();
            return $organization;
        } catch (Exception $e) {
            $this->entityManager->rollback();
            $this->entityManager->clear();

            throw $e;
        }
    }

    /**
     * Supprime une organization
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
    public function addGranularity(Orga_Model_Organization $organization, array $axes, array $configuration = [])
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
     * @param Orga_Model_Granularity $granularity
     * @throws Exception
     */
    public function removeGranularity(Orga_Model_Granularity $granularity)
    {
        try {
            $this->entityManager->beginTransaction();

            try {
                $granularityForInventoryStatus =  $granularity->getOrganization()->getGranularityForInventoryStatus();
                if ($granularityForInventoryStatus === $granularity) {
                    $granularity->getOrganization()->setGranularityForInventoryStatus();
                }
            } catch (Core_Exception_UndefinedAttribute $e) {
                // Pas de granularité des inventares.
            }

            if ($granularity->getCellsWithACL() || $granularity->isInput() || $granularity->hasInputGranularities()) {
                $granularity->setCellsWithACL(false);
                $granularity->setInputConfigGranularity();
                $this->entityManager->flush();
            }

            $granularity->delete();

            $this->entityManager->flush();
            $this->entityManager->commit();
        } catch (Exception $e) {
            $this->entityManager->rollback();
            $this->entityManager->clear();

            throw $e;
        }
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
     * @param Orga_Model_Organization $organization
     * @param array $formData
     */
    protected function initOrganizationUserForm(Orga_Model_Organization $organization, array $formData)
    {
        $defaultGranularity = $organization->getGranularityByRef('global');
        /** @var Orga_Model_Granularity[] $dWGranularities */
        $dWGranularities = [$defaultGranularity];

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
        } catch (Core_Exception_NotFound $e) {
        }
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
            } catch (Core_Exception_NotFound $e) {
            }
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
                $inputsGranularity = $organization->getGranularityByRef(
                    Orga_Model_Granularity::buildRefFromAxes($inputsGranularityAxes)
                );
            } catch (Core_Exception_NotFound $e) {
                $inputsGranularity = new Orga_Model_Granularity($organization, $inputsGranularityAxes);
            }
            if ($inputsGranularityAxes !== $inputNavigableGranularityAxes) {
                try {
                    $navigableInputsGranularity = $organization->getGranularityByRef(
                        Orga_Model_Granularity::buildRefFromAxes($inputNavigableGranularityAxes)
                    );
                } catch (Core_Exception_NotFound $e) {
                    $navigableInputsGranularity = new Orga_Model_Granularity($organization, $inputNavigableGranularityAxes);
                }
            } else {
                $navigableInputsGranularity = $inputsGranularity;
            }
            $navigableInputsGranularity->setCellsWithACL(true);
            $inputsGranularity->setInputConfigGranularity($navigableInputsGranularity);
        }

        if ($formData['organization']['elements']['organizationTemplate']['value'] === self::TEMPLATE_USER_REPORTING) {
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
                    $dWGranularity = $organization->getGranularityByRef(
                        Orga_Model_Granularity::buildRefFromAxes($dWGranularityAxes)
                    );
                } catch (Core_Exception_NotFound $e) {
                    $dWGranularity = new Orga_Model_Granularity($organization, $dWGranularityAxes);
                }
                $dWGranularity->setCellsWithACL(true);
                $dWGranularities[] = $dWGranularity;
            }
        }

        $organization->save();
        $this->entityManager->flush();

        // Définition de la création des DW après pour éviter un bug d'insertion.
        foreach ($dWGranularities as $granularityWithDW) {
            $granularityWithDW->setCellsGenerateDWCubes(true);
        }
        $organization->save();
        $this->entityManager->flush();
    }

    /**
     * @param Orga_Model_Organization $organization
     */
    public function initOrganizationDemo(Orga_Model_Organization $organization)
    {
        // Axe Catégorie
        $categoryAxis = new Orga_Model_Axis($organization, 'categorie');
        $categoryAxis->setLabel('Catégorie');
        $categoryAxis->save();
        $categoryEnergy = new Orga_Model_Member($categoryAxis, 'energie');
        $categoryEnergy->setLabel('Énergie');
        $categoryEnergy->save();
        $categoryTravel = new Orga_Model_Member($categoryAxis, 'deplacement');
        $categoryTravel->setLabel('Déplacement');
        $categoryTravel->save();

        // Axe Année
        $timeAxis = new Orga_Model_Axis($organization, 'annee');
        $timeAxis->setLabel('Année');
        $timeAxis->save();
        $year2013 = new Orga_Model_Member($timeAxis, '2013');
        $year2013->setLabel('2013');
        $year2013->save();
        $year2014 = new Orga_Model_Member($timeAxis, '2014');
        $year2014->setLabel('2014');
        $year2014->save();

        // Granularités
        $granularityCategory = new Orga_Model_Granularity($organization, [$categoryAxis]);
        $granularityCategory->save();
        $granularityYear = new Orga_Model_Granularity($organization, [$timeAxis]);
        $granularityYear->setCellsGenerateDWCubes(true);
        $granularityYear->save();
        $granularityYearCategory = new Orga_Model_Granularity($organization, [$timeAxis, $categoryAxis]);
        $granularityYearCategory->save();

        // Configuration
        $organization->setGranularityForInventoryStatus($granularityYear);
        $granularityYearCategory->setInputConfigGranularity($granularityCategory);
        $granularityCategory->getCellByMembers([$categoryEnergy])
            ->getCellsGroupForInputGranularity($granularityYearCategory)
            ->setAF(AF_Model_AF::loadByRef('energie'));
        $granularityCategory->getCellByMembers([$categoryTravel])
            ->getCellsGroupForInputGranularity($granularityYearCategory)
            ->setAF(AF_Model_AF::loadByRef('deplacement'));

        // Lance l'inventaire 2013
        $granularityYear->getCellByMembers([$year2013])
            ->setInventoryStatus(Orga_Model_Cell::STATUS_ACTIVE);

        $organization->save();
        // Flush pour persistence des cellules avant l'ajout du role et ajout des rapports préconfigurés.
        $this->entityManager->flush();

        // Analyses préconfigurées
        $report = new DW_Model_Report($granularityYear->getDWCube());
        $report->setLabel('GES émis par catégorie');
        $report->setChartType(DW_Model_Report::CHART_PIE);
        $report->setWithUncertainty(false);
        $report->setNumerator(DW_Model_Indicator::loadByRefAndCube('ges', $granularityYear->getDWCube()));
        $report->setNumeratorAxis1(DW_Model_Axis::loadByRefAndCube('o_categorie', $granularityYear->getDWCube()));
        $report->setSortType(DW_Model_Report::SORT_CONVENTIONAL);
        $report->save();

        $report = new DW_Model_Report($granularityYear->getDWCube());
        $report->setLabel('GES émis par catégorie et poste article 75');
        $report->setChartType(DW_Model_Report::CHART_VERTICAL_STACKED);
        $report->setWithUncertainty(false);
        $report->setNumerator(DW_Model_Indicator::loadByRefAndCube('ges', $granularityYear->getDWCube()));
        $report->setNumeratorAxis1(DW_Model_Axis::loadByRefAndCube('o_categorie', $granularityYear->getDWCube()));
        $report->setNumeratorAxis2(DW_Model_Axis::loadByRefAndCube('c_poste_article_75', $granularityYear->getDWCube()));
        $report->setSortType(DW_Model_Report::SORT_CONVENTIONAL);
        $report->save();

        $report = new DW_Model_Report($granularityYear->getDWCube());
        $report->setLabel('Energie finale consommée par catégorie');
        $report->setChartType(DW_Model_Report::CHART_PIE);
        $report->setWithUncertainty(false);
        $report->setNumerator(DW_Model_Indicator::loadByRefAndCube('energie_finale', $granularityYear->getDWCube()));
        $report->setNumeratorAxis1(DW_Model_Axis::loadByRefAndCube('o_categorie', $granularityYear->getDWCube()));
        $report->setSortType(DW_Model_Report::SORT_CONVENTIONAL);
        $report->save();
    }

    /**
     * @param string $email
     * @param string $password
     * @return Orga_Model_Organization
     */
    public function createDemoOrganizationAndUser($email, $password)
    {
        $user = $this->userService->createUser($email, $password);
        $user->initTutorials();

        $organization = $this->createOrganization();
        $organization->setLabel(__('Orga', 'navigation', 'demoOrganizationLabel', ['LABEL' => rand(1000, 9999)]));

        $this->initOrganizationDemo($organization);

        // Ajoute en tant que manager de la cellule globale
        $globalCell = $organization->getGranularityByRef('global')->getCellByMembers([]);
        $this->aclService->addRole($user, new CellManagerRole($user, $globalCell));

        $this->entityManager->flush();

        return $organization;
    }
}
