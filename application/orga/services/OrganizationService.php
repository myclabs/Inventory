<?php

use Account\Domain\Account;
use Account\Domain\AccountRepository;
use AF\Domain\AF;
use Doctrine\ORM\EntityManager;
use Mnapoli\Translated\Translator;
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
     * @var Translator
     */
    private $translator;

    public function __construct(
        EntityManager $entityManager,
        Orga_Service_ACLManager $aclManager,
        UserService $userService,
        Translator $translator
    ) {
        $this->entityManager = $entityManager;
        $this->aclManager = $aclManager;
        $this->userService = $userService;
        $this->translator = $translator;
    }

    /**
     * @return string[]
     */
    public function getOrganizationTemplates()
    {
        return [
            self::TEMPLATE_EMPTY => __('Orga', 'add', 'templateEmpty'),
            self::TEMPLATE_USER_INVENTORY => __('Orga', 'add', 'templateUserInventory'),
            self::TEMPLATE_USER_REPORTING => __('Orga', 'add', 'templateUserReports'),
        ];
    }

    /**
     * @param Account $account
     * @param string $labelOrganization
     * @throws Exception
     * @return Orga_Model_Organization
     */
    public function createOrganization(Account $account, $labelOrganization = '')
    {
        $this->entityManager->beginTransaction();

        try {
            // Création de l'organization.
            $organization = new Orga_Model_Organization($account);
            $organization->getLabel()->set($labelOrganization, 'fr');

            $organization->save();
            $this->entityManager->flush();

            // Création d'une granularité globale par défaut.
            new Orga_Model_Granularity($organization);

            $organization->save();
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
     * @param User $administrator
     * @param Account $account
     * @param array $formData
     * @throws Exception
     * @return Orga_Model_Organization
     */
    public function createOrganizationFromTemplatesForm(User $administrator, Account $account, array $formData)
    {
        $this->entityManager->beginTransaction();

        try {
            $organizationLabel = $formData['organizationLabel'];
            $organization = $this->createOrganization($account, $organizationLabel);

            $template = $formData['organizationTemplate'];
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

        $idOrganization = $organization->getId();
        try {
            foreach (glob(APPLICATION_PATH . '/../public/workspaceBanners/' . $idOrganization . '.*') as $file) {
                unlink($file);
            }

            $organization = Orga_Model_Organization::load($idOrganization);
            $organization->setGranularityForInventoryStatus();

            $this->entityManager->flush();
            $this->entityManager->clear();

            $organization = Orga_Model_Organization::load($idOrganization);
            $granularities = $organization->getOrderedGranularities()->toArray();
            foreach (array_reverse($granularities) as $granularity) {
                /** @var Orga_Model_Granularity $granularity */
                $granularity = Orga_Model_Granularity::load($granularity->getId());
                $granularity->setInputConfigGranularity();
                $granularity->delete();
            }

            $this->entityManager->flush();
            $this->entityManager->clear();

            $organization = Orga_Model_Organization::load($idOrganization);
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
     * @param array $configuration [$attribute => $value] : relevance(bool), reports(bool), acl(bool)
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
                case 'inventory':
                    $granularity->setCellsMonitorInventory($value);
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
     * @param array $changes [$attribute => $value] : relevance(bool), reports(bool), acl(bool)
     * @throws Exception
     */
    public function editGranularity(Orga_Model_Granularity $granularity, $changes)
    {
        foreach ($changes as $attribute => $value) {
            switch ($attribute) {
                case 'relevance':
                    $granularity->setCellsControlRelevance($value);
                    break;
                case 'inventory':
                    $granularity->setCellsMonitorInventory($value);
                    break;
                case 'afs':
                    try {
                        $inputConfigGranularity = $granularity->getOrganization()->getGranularityByRef(
                            Orga_Model_Granularity::buildRefFromAxes($value)
                        );
                    } catch (Core_Exception_NotFound $e) {
                        $inputConfigGranularity = new Orga_Model_Granularity($granularity->getOrganization(), $value);
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
                    foreach ($granularity->getOrganization()->getGranularities() as $organizationGranularity) {
                        $organizationGranularity->setCellsMonitorInventory(false);
                    }
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
        $this->translator->set($member->getLabel(), $label);

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
        /** @var Orga_Model_Granularity[] $reportsGranularities */
        $reportsGranularities = [];

        $axesData = $formData['axes'];
        /** @var Orga_Model_Axis[] $axes */
        $axes = [];
        // Création de l'axe temps.
        $timeAxisLabel = $axesData['timeAxis'];
        $timeAxisRef = Core_Tools::refactor($timeAxisLabel);
        try {
            $organization->getAxisByRef($timeAxisRef);
            $timeAxisRef = 't_'.$timeAxisRef;
        } catch (Core_Exception_NotFound $e) {
        }
        $timeAxis = new Orga_Model_Axis($organization, $timeAxisRef);
        $this->translator->set($timeAxis->getLabel(), $timeAxisLabel);
        $axes['timeAxis'] = $timeAxis;
        // Création de l'axe de subdivision.
        $subdivisionAxisLabel = $axesData['subdivisionAxis'];
        if (!empty($subdivisionAxisLabel)) {
            $subdivisionAxisRef = Core_Tools::refactor($subdivisionAxisLabel);
            try {
                $organization->getAxisByRef($subdivisionAxisRef);
                $subdivisionAxisRef = 'd_'.$subdivisionAxisRef;
            } catch (Core_Exception_NotFound $e) {
            }
            $subdivisionAxis = new Orga_Model_Axis($organization, $subdivisionAxisRef);
            $this->translator->set($subdivisionAxis->getLabel(), $subdivisionAxisLabel);
            $axes['subdivisionAxis'] = $subdivisionAxis;
        }
        // Création des axes principaux.
        foreach ($axesData as $mainAxisId => $mainAxisLabel) {
            if (($mainAxisId === 'timeAxis') || ($mainAxisId === 'subdivisionAxis')
                || (strpos($mainAxisId, '-parent') === (strlen($mainAxisId) - 7))) {
                continue;
            }
            if (isset($axesData[$mainAxisId.'-parent'])) {
                $mainParentAxis = $axes[$axesData[$mainAxisId.'-parent']];
            } else {
                $mainParentAxis = null;
            }

            $mainAxisRef = Core_Tools::refactor($mainAxisLabel);

            $complement = '';
            $i = 2;
            do {
                try {
                    $organization->getAxisByRef($mainAxisRef.$complement);
                    $complement = '_' . $i;
                    $i++;
                } catch (Core_Exception_NotFound $e) {
                    $mainAxis = new Orga_Model_Axis($organization, $mainAxisRef.$complement, $mainParentAxis);
                    $this->translator->set($mainAxis->getLabel(), $mainAxisLabel);
                    $axes[$mainAxisId] = $mainAxis;
                }
            } while (!isset($axes[$mainAxisId]));
        }

        // Création des membres.
        $completeRef = Orga_Model_Member::COMPLETEREF_JOIN . Orga_Model_Member::buildParentMembersHashKey([]);
        $membersData = $formData['members'];
        $members = [];
        foreach ($membersData as $axisId => $axisMembersData) {
            $membersAxis = $axes[$axisId];
            foreach ($axisMembersData as $memberId => $memberLabel) {
                if (strpos($memberId, '-parent') === (strlen($memberId) - 7)) {
                    continue;
                }
                $memberRef = Core_Tools::refactor($memberLabel);
                if (empty($memberRef)) {
                    continue;
                }
                /** @var Orga_Model_Member[] $parentMembers */
                $parentMembers = [];
                if (isset($axisMembersData[$memberId.'-parent'])) {
                    foreach ($axisMembersData[$memberId.'-parent'] as $parentMemberAxisId => $parentMemberId) {
                        $parentMembers[] = $members[$parentMemberAxisId][$parentMemberId];
                    }
                }

                $complement = '';
                $i = 2;
                do {
                    try {
                        $membersAxis->getMemberByCompleteRef($memberRef.$complement.$completeRef);
                        $complement = '_' . $i;
                        $i++;
                    } catch (Core_Exception_NotFound $e) {
                        $member = new Orga_Model_Member($membersAxis, $memberRef, $parentMembers);
                        $this->translator->set($member->getLabel(), $memberLabel);
                        $members[$axisId][$memberId] = $member;
                    }
                } while (!isset($members[$axisId][$memberId]));
            }
        }

        // Création de la granularité de collecte.
        $inventoryGranularityId = $formData['inventoryGranularity'];
        $inventoryGranularityAxes = [];
        foreach (explode('|', $inventoryGranularityId) as $inventoryGranularityAxisId) {
            $inventoryGranularityAxes[] = $axes[$inventoryGranularityAxisId];
        }
        $inventoryGranularity = new Orga_Model_Granularity($organization, $inventoryGranularityAxes);
        $organization->setGranularityForInventoryStatus($inventoryGranularity);

        // Création de la granularité de saisie.
        $inputsGranularityAxes = [$axes['mainAxis'], $axes['timeAxis']];
        $inputsConfigurationGranularityAxes = [$axes['timeAxis']];
        if (isset($axes['subdivisionAxis'])) {
            $inputsGranularityAxes[] = $axes['subdivisionAxis'];
            $inputsConfigurationGranularityAxes[] = $axes['subdivisionAxis'];
        }
        try {
            $inputsGranularity = $organization->getGranularityByRef(
                Orga_Model_Granularity::buildRefFromAxes($inputsGranularityAxes)
            );
        } catch (Core_Exception_NotFound $e) {
            $inputsGranularity = new Orga_Model_Granularity($organization, $inputsGranularityAxes);
        }
        try {
            $inputsConfigurationGranularity = $organization->getGranularityByRef(
                Orga_Model_Granularity::buildRefFromAxes($inputsConfigurationGranularityAxes)
            );
        } catch (Core_Exception_NotFound $e) {
            $inputsConfigurationGranularity = new Orga_Model_Granularity(
                $organization,
                $inputsConfigurationGranularityAxes
            );
        }
        $inputsGranularity->setInputConfigGranularity($inputsConfigurationGranularity);

        // Création des granularités d'acl.
        $aclGranularitiesData = $formData['aclGranularities'];
        foreach ($aclGranularitiesData as $aclGranularityId) {
            if ($aclGranularityId === 'global') {
                $aclGranularity = $defaultGranularity;
            } else {
                $aclGranularityAxes = [];
                foreach (explode('|', $aclGranularityId) as $aclGranularityAxisId) {
                    $aclGranularityAxes[] = $axes[$aclGranularityAxisId];
                }
                try {
                    $aclGranularity = $organization->getGranularityByRef(
                        Orga_Model_Granularity::buildRefFromAxes($aclGranularityAxes)
                    );
                } catch (Core_Exception_NotFound $e) {
                    $aclGranularity = new Orga_Model_Granularity($organization, $aclGranularityAxes);
                }
            }
            $aclGranularity->setCellsWithACL(true);
        }

        // Création des granularités de reports.
        if ($formData['organizationTemplate'] === self::TEMPLATE_USER_REPORTING) {
            $reportsGranularitiesData = $formData['reportsGranularities'];
            foreach ($reportsGranularitiesData as $reportsGranularityId) {
                if ($reportsGranularityId === 'global') {
                    $reportsGranularity = $defaultGranularity;
                } else {
                    $reportsGranularityAxes = [];
                    foreach (explode('|', $reportsGranularityId) as $reportsGranularityAxisId) {
                        $reportsGranularityAxes[] = $axes[$reportsGranularityAxisId];
                    }
                    try {
                        $reportsGranularity = $organization->getGranularityByRef(
                            Orga_Model_Granularity::buildRefFromAxes($reportsGranularityAxes)
                        );
                    } catch (Core_Exception_NotFound $e) {
                        $reportsGranularity = new Orga_Model_Granularity($organization, $reportsGranularityAxes);
                    }
                }
                $reportsGranularities[] = $reportsGranularity;
            }
        }

        $organization->save();
        $this->entityManager->flush();

        // Définition de la création des DW après pour éviter un bug d'insertion.
        foreach ($reportsGranularities as $granularityWithDW) {
            $granularityWithDW->setCellsGenerateDWCubes(true);
            $granularityWithDW->save();
        }
        $this->entityManager->flush();
    }

    /**
     * @param Orga_Model_Organization $organization
     */
    public function initOrganizationDemo(Orga_Model_Organization $organization)
    {
        // Axe Catégorie
        $categoryAxis = new Orga_Model_Axis($organization, 'categorie');
        $categoryAxis->getLabel()->set('Catégorie', 'fr');
        $categoryAxis->save();
        $categoryEnergy = new Orga_Model_Member($categoryAxis, 'energie');
        $this->translator->set($categoryEnergy->getLabel(), 'Énergie');
        $categoryEnergy->save();
        $categoryTravel = new Orga_Model_Member($categoryAxis, 'deplacement');
        $this->translator->set($categoryTravel->getLabel(), 'Déplacement');
        $categoryTravel->save();

        // Axe Année
        $timeAxis = new Orga_Model_Axis($organization, 'annee');
        $categoryAxis->getLabel()->set('Année', 'fr');
        $timeAxis->save();
        $year2012 = new Orga_Model_Member($timeAxis, '2012');
        $this->translator->set($year2012->getLabel(), '2012');
        $year2012->save();
        $year2013 = new Orga_Model_Member($timeAxis, '2013');
        $this->translator->set($year2013->getLabel(), '2013');
        $year2013->save();
        $year2014 = new Orga_Model_Member($timeAxis, '2014');
        $this->translator->set($year2014->getLabel(), '2014');
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
        $organization->getGranularityByRef('global')->setCellsWithACL(true);
        $organization->setGranularityForInventoryStatus($granularityYear);
        $granularityYearCategory->setInputConfigGranularity($granularityCategory);
        $granularityCategory->getCellByMembers([$categoryEnergy])
            ->getCellsGroupForInputGranularity($granularityYearCategory)
            ->setAF(AF::loadByRef('energie'));
        $granularityCategory->getCellByMembers([$categoryTravel])
            ->getCellsGroupForInputGranularity($granularityYearCategory)
            ->setAF(AF::loadByRef('deplacement'));

        // Lance l'inventaire 2013
        $granularityYear->getCellByMembers([$year2013])
            ->setInventoryStatus(Orga_Model_Cell::INVENTORY_STATUS_ACTIVE);

        $organization->save();
        // Flush pour persistence des cellules avant l'ajout du role et ajout des rapports préconfigurés.
        $this->entityManager->flush();

        // Analyses préconfigurées
        $report = new DW_Model_Report($granularityYear->getDWCube());
        $report->getLabel()->set('GES émis par catégorie', 'fr');
        $report->setChartType(DW_Model_Report::CHART_PIE);
        $report->setWithUncertainty(false);
        $report->setNumerator(DW_Model_Indicator::loadByRefAndCube('ges', $granularityYear->getDWCube()));
        $report->setNumeratorAxis1(DW_Model_Axis::loadByRefAndCube('o_categorie', $granularityYear->getDWCube()));
        $report->setSortType(DW_Model_Report::SORT_CONVENTIONAL);
        $report->save();

        $report = new DW_Model_Report($granularityYear->getDWCube());
        $report->getLabel()->set('GES émis par catégorie et poste article 75', 'fr');
        $report->setChartType(DW_Model_Report::CHART_VERTICAL_STACKED);
        $report->setWithUncertainty(false);
        $report->setNumerator(DW_Model_Indicator::loadByRefAndCube('ges', $granularityYear->getDWCube()));
        $report->setNumeratorAxis1(DW_Model_Axis::loadByRefAndCube('o_categorie', $granularityYear->getDWCube()));
        $report->setNumeratorAxis2(DW_Model_Axis::loadByRefAndCube('c_poste_article_75', $granularityYear->getDWCube()));
        $report->setSortType(DW_Model_Report::SORT_CONVENTIONAL);
        $report->save();

        $report = new DW_Model_Report($granularityYear->getDWCube());
        $report->getLabel()->set('Energie finale consommée par catégorie', 'fr');
        $report->setChartType(DW_Model_Report::CHART_PIE);
        $report->setWithUncertainty(false);
        $report->setNumerator(DW_Model_Indicator::loadByRefAndCube('energie_finale', $granularityYear->getDWCube()));
        $report->setNumeratorAxis1(DW_Model_Axis::loadByRefAndCube('o_categorie', $granularityYear->getDWCube()));
        $report->setSortType(DW_Model_Report::SORT_CONVENTIONAL);
        $report->save();
    }
}
