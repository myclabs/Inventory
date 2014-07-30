<?php

namespace Orga\Application\Service\Workspace;

use Account\Domain\Account;
use AF\Domain\AF;
use Core_Exception_NotFound;
use Core_Exception_User;
use Core_Locale;
use Core_Tools;
use Doctrine\ORM\EntityManager;
use DW\Domain\Report;
use Exception;
use Mnapoli\Translated\Translator;
use MyCLabs\Work\Dispatcher\SynchronousWorkDispatcher;
use Orga\Domain\Axis;
use Orga\Domain\Cell;
use Orga\Domain\Granularity;
use Orga\Domain\Member;
use Orga\Domain\Workspace;
use Orga\Domain\Service\OrgaACLManager;
use User\Domain\User;
use User\Domain\UserService;

class WorkspaceService
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
     * @var OrgaACLManager
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
    /**
     * @var SynchronousWorkDispatcher
     */
    private $workDispatcher;

    public function __construct(
        EntityManager $entityManager,
        OrgaACLManager $aclManager,
        UserService $userService,
        Translator $translator,
        SynchronousWorkDispatcher $workDispatcher
    ) {
        $this->entityManager = $entityManager;
        $this->aclManager = $aclManager;
        $this->userService = $userService;
        $this->translator = $translator;
        $this->workDispatcher = $workDispatcher;
    }

    /**
     * @return string[]
     */
    public function getWorkspaceTemplates()
    {
        return [
            self::TEMPLATE_EMPTY => __('Orga', 'add', 'templateEmpty'),
            self::TEMPLATE_USER_INVENTORY => __('Orga', 'add', 'templateUserInventory'),
            self::TEMPLATE_USER_REPORTING => __('Orga', 'add', 'templateUserReports'),
        ];
    }

    /**
     * @param Account $account
     * @param string $workspaceLabel
     * @throws Exception
     * @return Workspace
     */
    public function create(Account $account, $workspaceLabel = '')
    {
        try {
            $this->entityManager->beginTransaction();

            // Création du workspace.
            $workspace = new Workspace($account);
            $workspace->getLabel()->set($workspaceLabel, Core_Locale::loadDefault()->getLanguage());

            $workspace->save();
            $this->entityManager->flush();

            // Création d'une granularité globale par défaut.
            new Granularity($workspace);

            $workspace->save();

            $this->entityManager->flush();
            $this->entityManager->commit();
            return $workspace;
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
     * @return Workspace
     */
    public function createFromTemplatesForm(User $administrator, Account $account, array $formData)
    {
        try {
            $this->entityManager->beginTransaction();

            $workspaceLabel = $formData['workspaceLabel'];
            $workspace = $this->create($account, $workspaceLabel);

            $template = $formData['workspaceTemplate'];
            if ($template !== self::TEMPLATE_EMPTY) {
                if (($template === self::TEMPLATE_USER_INVENTORY) || ($template == self::TEMPLATE_USER_REPORTING)) {
                    $this->initWorkspaceUserForm($workspace, $formData);
                } else {
                    $functionName = 'initWorkspace' . ucfirst($template);
                    $this->$functionName($workspace);
                }

                $workspace->save();
                $this->entityManager->flush();
            }

            // Ajout de l'utilisateur courant en tant qu'administrateur.
            $this->aclManager->addWorkspaceAdministrator($workspace, $administrator->getEmail(), false);

            $this->entityManager->flush();
            $this->entityManager->commit();
            return $workspace;
        } catch (Exception $e) {
            $this->entityManager->rollback();
            $this->entityManager->clear();

            throw $e;
        }
    }

    /**
     * @param Workspace $workspace
     * @param array $changes [$attr => $val] : timeAxis(Axis|null)
     * @throws \Exception
     */
    public function edit(Workspace $workspace, $changes)
    {
        try {
            $this->entityManager->beginTransaction();

            foreach ($changes as $attribute => $value) {
                switch ($attribute) {
                    case 'timeAxis':
                        $workspace->setTimeAxis($value);
                        break;
                }
            }

            $workspace->save();

            $this->entityManager->flush();
            $this->entityManager->commit();
        } catch (Exception $e) {
            $this->entityManager->rollback();
            $this->entityManager->clear();

            throw $e;
        }
    }

    /**
     * @param Workspace $workspace
     * @throws Exception
     */
    public function delete(Workspace $workspace)
    {
        try {
            $this->entityManager->beginTransaction();

            foreach (glob(APPLICATION_PATH . '/../public/workspaceBanners/' . $workspace->getId() . '.*') as $file) {
                unlink($file);
            }

            $workspace->delete();

            $this->entityManager->flush();
            $this->entityManager->commit();
        } catch (Exception $e) {
            $this->entityManager->rollback();
            $this->entityManager->clear();

            throw $e;
        }
    }

    /**
     * @param Axis $axis
     * @throws Exception
     */
    public function deleteAxis(Axis $axis)
    {
        try {
            $this->entityManager->beginTransaction();

            $axis->removeFromWorkspace();

            $this->entityManager->flush();
            $this->entityManager->commit();
        } catch (Exception $e) {
            $this->entityManager->rollback();
            $this->entityManager->clear();

            throw $e;
        }
    }

    /**
     * @param Axis $axis
     * @param $ref
     * @param $label
     * @param array $parentMembers
     *
     * @throws Exception
     */
    public function addMember(Axis $axis, $ref, $label, array $parentMembers)
    {
        try {
            $this->entityManager->beginTransaction();

            $member = new Member($axis, $ref, $parentMembers);
            $this->translator->set($member->getLabel(), $label);
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
     * @param Member $member
     * @param array $changes [$attr => $val] : label(string), ref(string), position(int|string), parents([broaderAxisRef => parentMemberCompleteRef])
     * @throws Core_Exception_User
     * @throws Exception
     */
    public function editMember(Member $member, $changes)
    {
        try {
            $this->entityManager->beginTransaction();

            foreach ($changes as $attribute => $value) {
                switch ($attribute) {
                    case 'label':
                        $this->translator->set($member->getLabel(), $value);
                        break;
                    case 'ref':
                        Core_Tools::checkRef($value);
                        try {
                            $completeRef = Member::buildParentMembersHashKey($member->getContextualizingParents());
                            $completeRef = $value . '#' . $completeRef;
                            if ($member->getAxis()->getMemberByCompleteRef($completeRef) !== $member) {
                                throw new Core_Exception_User('UI', 'formValidation', 'alreadyUsedIdentifier');
                            }
                        } catch (Core_Exception_NotFound $e) {
                            $member->setRef($value);
                        }
                        break;
                    case 'position':
                        switch ($value) {
                            case 'goFirst';
                                $member->setPosition(1);
                                break;
                            case 'goUp';
                                $member->setPosition($member->getPosition() - 1);
                                break;
                            case 'goDown';
                                $member->setPosition($member->getPosition() + 1);
                                break;
                            case 'goLast';
                                $member->setPosition($member->getLastEligiblePosition());
                                break;
                            default:
                                $newPosition = (int) $value;
                                if ($newPosition < 1) {
                                    $newPosition = 1;
                                }
                                if ($newPosition > $member->getLastEligiblePosition()) {
                                    $newPosition = $member->getLastEligiblePosition();
                                }
                                $member->setPosition((int) $newPosition);
                        }
                        break;
                    case 'parents':
                        foreach ($value as $broaderAxisRef => $parentMemberCompleteRef) {
                            $broaderAxis = $member->getAxis()->getWorkspace()->getAxisByRef($broaderAxisRef);
                            if (!empty($parentMemberCompleteRef)) {
                                $parentMember = $broaderAxis->getMemberByCompleteRef($parentMemberCompleteRef);
                                $member->setDirectParentForAxis($parentMember);
                            } else {
                                $member->removeDirectParentForAxis($member->getDirectParentForAxis($broaderAxis));
                            }
                        }
                        break;
                }
            }
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
     * @param Member $member
     *
     * @throws Exception
     */
    public function deleteMember(Member $member)
    {
        try {
            $this->entityManager->beginTransaction();

            $member->removeFromAxis();

            $this->entityManager->flush();
            $this->entityManager->commit();
        } catch (Exception $e) {
            $this->entityManager->rollback();
            $this->entityManager->clear();

            throw $e;
        }
    }

    /**
     * @param Workspace $workspace
     * @param Axis[] $axes
     * @param array $conf [$attr => $val] : relevance(bool), inventory(bool), afs(Axis[]), reports(bool), acl(bool)
     * @throws Exception
     * @return Granularity
     */
    public function addGranularity(Workspace $workspace, array $axes, array $conf = [])
    {
        try {
            $granularity = $workspace->getGranularityByRef(Granularity::buildRefFromAxes($axes));
        } catch (Core_Exception_NotFound $e) {
            $granularity = new Granularity($workspace, $axes);
        }

        $this->editGranularity($granularity, $conf);

        return $granularity;
    }

    /**
     * @param Granularity $granularity
     * @param array $changes [$attr => $val] : relevance(bool), inventory(bool), afs(Axis[]), reports(bool), acl(bool)
     * @throws Exception
     */
    public function editGranularity(Granularity $granularity, $changes)
    {
        try {
            $this->entityManager->beginTransaction();

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
                            $inputConfigGranularity = $granularity->getWorkspace()->getGranularityByRef(
                                Granularity::buildRefFromAxes($value)
                            );
                        } catch (Core_Exception_NotFound $e) {
                            $inputConfigGranularity = new Granularity($granularity->getWorkspace(), $value);
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
     * @param Granularity $granularity
     * @throws Exception
     */
    public function removeGranularity(Granularity $granularity)
    {
        try {
            $this->entityManager->beginTransaction();

            $granularity->removeFromWorkspace();

            $this->entityManager->flush();
            $this->entityManager->commit();
        } catch (Exception $e) {
            $this->entityManager->rollback();
            $this->entityManager->clear();

            throw $e;
        }
    }

    /**
     * @param Workspace $workspace
     * @param array $formData
     */
    protected function initWorkspaceUserForm(Workspace $workspace, array $formData)
    {
        $defaultGranularity = $workspace->getGranularityByRef('global');
        /** @var Granularity[] $reportsGranularities */
        $reportsGranularities = [];

        $axesData = $formData['axes'];
        /** @var Axis[] $axes */
        $axes = [];
        // Création de l'axe temps.
        $timeAxisLabel = $axesData['timeAxis'];
        $timeAxisRef = Core_Tools::refactor($timeAxisLabel);
        try {
            $workspace->getAxisByRef($timeAxisRef);
            $timeAxisRef = 't_' . $timeAxisRef;
        } catch (Core_Exception_NotFound $e) {
        }
        $timeAxis = new Axis($workspace, $timeAxisRef);
        $timeAxis->setMemberPositioning(true);
        $this->translator->set($timeAxis->getLabel(), $timeAxisLabel);
        $axes['timeAxis'] = $timeAxis;
        // Création de l'axe de subdivision.
        if (isset($axesData['subdivisionAxis'])) {
            $subdivisionAxisLabel = $axesData['subdivisionAxis'];
            if (!empty($subdivisionAxisLabel)) {
                $subdivisionAxisRef = Core_Tools::refactor($subdivisionAxisLabel);
                try {
                    $workspace->getAxisByRef($subdivisionAxisRef);
                    $subdivisionAxisRef = 'd_' . $subdivisionAxisRef;
                } catch (Core_Exception_NotFound $e) {
                }
                $subdivisionAxis = new Axis($workspace, $subdivisionAxisRef);
                $this->translator->set($subdivisionAxis->getLabel(), $subdivisionAxisLabel);
                $axes['subdivisionAxis'] = $subdivisionAxis;
            }
        }
        // Création des axes principaux.
        foreach ($axesData as $mainAxisId => $mainAxisLabel) {
            if (($mainAxisId === 'timeAxis') || ($mainAxisId === 'subdivisionAxis')
                || (strpos($mainAxisId, '-parent') === (strlen($mainAxisId) - 7))
            ) {
                continue;
            }
            if (isset($axesData[$mainAxisId . '-parent'])) {
                $mainParentAxis = $axes[$axesData[$mainAxisId . '-parent']];
            } else {
                $mainParentAxis = null;
            }

            $mainAxisRef = Core_Tools::refactor($mainAxisLabel);

            $complement = '';
            $i = 2;
            do {
                try {
                    $workspace->getAxisByRef($mainAxisRef . $complement);
                    $complement = '_' . $i;
                    $i++;
                } catch (Core_Exception_NotFound $e) {
                    $mainAxis = new Axis($workspace, $mainAxisRef . $complement, $mainParentAxis);
                    $this->translator->set($mainAxis->getLabel(), $mainAxisLabel);
                    $axes[$mainAxisId] = $mainAxis;
                }
            } while (!isset($axes[$mainAxisId]));
        }

        // Création des membres.
        $completeRef = Member::COMPLETEREF_JOIN . Member::buildParentMembersHashKey([]);
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
                /** @var Member[] $parentMembers */
                $parentMembers = [];
                if (isset($axisMembersData[$memberId . '-parent'])) {
                    foreach ($axisMembersData[$memberId . '-parent'] as $parentMemberAxisId => $parentMemberId) {
                        $parentMembers[] = $members[$parentMemberAxisId][$parentMemberId];
                    }
                }

                $complement = '';
                $i = 2;
                do {
                    try {
                        $membersAxis->getMemberByCompleteRef($memberRef . $complement . $completeRef);
                        $complement = '_' . $i;
                        $i++;
                    } catch (Core_Exception_NotFound $e) {
                        $member = new Member($membersAxis, $memberRef, $parentMembers);
                        $this->translator->set($member->getLabel(), $memberLabel);
                        $members[$axisId][$memberId] = $member;
                    }
                } while (!isset($members[$axisId][$memberId]));
            }
        }

        // Configuration de l'axe temps.
        $workspace->setTimeAxis($timeAxis);

        // Création de la granularité de collecte.
        $inventoryGranularityId = $formData['inventoryGranularity'];
        $inventoryGranularityAxes = [];
        foreach (explode('|', $inventoryGranularityId) as $inventoryGranularityAxisId) {
            $inventoryGranularityAxes[] = $axes[$inventoryGranularityAxisId];
        }
        $inventoryGranularity = new Granularity($workspace, $inventoryGranularityAxes);
        $workspace->setGranularityForInventoryStatus($inventoryGranularity);

        // Création de la granularité de saisie.
        $inputsGranularityAxes = [$axes['mainAxis'], $axes['timeAxis']];
        $inputsConfigurationGranularityAxes = [$axes['timeAxis']];
        if (isset($axes['subdivisionAxis'])) {
            $inputsGranularityAxes[] = $axes['subdivisionAxis'];
            $inputsConfigurationGranularityAxes[] = $axes['subdivisionAxis'];
        }
        try {
            $inputsGranularity = $workspace->getGranularityByRef(
                Granularity::buildRefFromAxes($inputsGranularityAxes)
            );
        } catch (Core_Exception_NotFound $e) {
            $inputsGranularity = new Granularity($workspace, $inputsGranularityAxes);
        }
        try {
            $inputsConfigurationGranularity = $workspace->getGranularityByRef(
                Granularity::buildRefFromAxes($inputsConfigurationGranularityAxes)
            );
        } catch (Core_Exception_NotFound $e) {
            $inputsConfigurationGranularity = new Granularity(
                $workspace,
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
                    $aclGranularity = $workspace->getGranularityByRef(
                        Granularity::buildRefFromAxes($aclGranularityAxes)
                    );
                } catch (Core_Exception_NotFound $e) {
                    $aclGranularity = new Granularity($workspace, $aclGranularityAxes);
                }
            }
            $aclGranularity->setCellsWithACL(true);
        }

        // Création des granularités de reports.
        if ($formData['workspaceTemplate'] === self::TEMPLATE_USER_REPORTING) {
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
                        $reportsGranularity = $workspace->getGranularityByRef(
                            Granularity::buildRefFromAxes($reportsGranularityAxes)
                        );
                    } catch (Core_Exception_NotFound $e) {
                        $reportsGranularity = new Granularity($workspace, $reportsGranularityAxes);
                    }
                }
                $reportsGranularities[] = $reportsGranularity;
            }
        }

        $workspace->save();
        $this->entityManager->flush();

        // Définition de la création des DW après pour éviter un bug d'insertion.
        foreach ($reportsGranularities as $granularityWithDW) {
            $granularityWithDW->setCellsGenerateDWCubes(true);
            $granularityWithDW->save();
        }
        $this->entityManager->flush();
    }

    /**
     * @param Workspace $workspace
     */
    public function initWorkspaceDemo(Workspace $workspace)
    {
        // Axe Catégorie
        $categoryAxis = new Axis($workspace, 'categorie');
        $categoryAxis->getLabel()->set('Catégorie', 'fr');
        $categoryAxis->save();
        $categoryEnergy = new Member($categoryAxis, 'energie');
        $this->translator->set($categoryEnergy->getLabel(), 'Énergie');
        $categoryEnergy->save();
        $categoryTravel = new Member($categoryAxis, 'deplacement');
        $this->translator->set($categoryTravel->getLabel(), 'Déplacement');
        $categoryTravel->save();

        // Axe Année
        $timeAxis = new Axis($workspace, 'annee');
        $categoryAxis->getLabel()->set('Année', 'fr');
        $timeAxis->save();
        $year2012 = new Member($timeAxis, '2012');
        $this->translator->set($year2012->getLabel(), '2012');
        $year2012->save();
        $year2013 = new Member($timeAxis, '2013');
        $this->translator->set($year2013->getLabel(), '2013');
        $year2013->save();
        $year2014 = new Member($timeAxis, '2014');
        $this->translator->set($year2014->getLabel(), '2014');
        $year2014->save();

        // Granularités
        $granularityCategory = new Granularity($workspace, [$categoryAxis]);
        $granularityCategory->save();
        $granularityYear = new Granularity($workspace, [$timeAxis]);
        $granularityYear->setCellsGenerateDWCubes(true);
        $granularityYear->save();
        $granularityYearCategory = new Granularity($workspace, [$timeAxis, $categoryAxis]);
        $granularityYearCategory->save();

        // Configuration
        $workspace->getGranularityByRef('global')->setCellsWithACL(true);
        $workspace->setGranularityForInventoryStatus($granularityYear);
        $granularityYearCategory->setInputConfigGranularity($granularityCategory);
        $granularityCategory->getCellByMembers([$categoryEnergy])
            ->getSubCellsGroupForInputGranularity($granularityYearCategory)
            ->setAF(AF::loadByRef('energie'));
        $granularityCategory->getCellByMembers([$categoryTravel])
            ->getSubCellsGroupForInputGranularity($granularityYearCategory)
            ->setAF(AF::loadByRef('deplacement'));

        // Lance l'inventaire 2013
        $granularityYear->getCellByMembers([$year2013])
            ->setInventoryStatus(Cell::INVENTORY_STATUS_ACTIVE);

        $workspace->save();
        // Flush pour persistence des cellules avant l'ajout du role et ajout des rapports préconfigurés.
        $this->entityManager->flush();

        // Analyses préconfigurées
        $report = new Report($granularityYear->getDWCube());
        $report->getLabel()->set('GES émis par catégorie', 'fr');
        $report->setChartType(Report::CHART_PIE);
        $report->setWithUncertainty(false);
        $report->setNumeratorIndicator($granularityYear->getDWCube()->getIndicatorByRef('ges'));
        $report->setNumeratorAxis1($granularityYear->getDWCube()->getAxisByRef('o_categorie'));
        $report->setSortType(Report::SORT_CONVENTIONAL);
        $report->save();

        $report = new Report($granularityYear->getDWCube());
        $report->getLabel()->set('GES émis par catégorie et poste article 75', 'fr');
        $report->setChartType(Report::CHART_VERTICAL_STACKED);
        $report->setWithUncertainty(false);
        $report->setNumeratorIndicator($granularityYear->getDWCube()->getIndicatorByRef('ges'));
        $report->setNumeratorAxis1($granularityYear->getDWCube()->getAxisByRef('o_categorie'));
        $report->setNumeratorAxis2($granularityYear->getDWCube()->getAxisByRef('c_poste_article_75'));
        $report->setSortType(Report::SORT_CONVENTIONAL);
        $report->save();

        $report = new Report($granularityYear->getDWCube());
        $report->getLabel()->set('Energie finale consommée par catégorie', 'fr');
        $report->setChartType(Report::CHART_PIE);
        $report->setWithUncertainty(false);
        $report->setNumeratorIndicator($granularityYear->getDWCube()->getIndicatorByRef('energie_finale'));
        $report->setNumeratorAxis1($granularityYear->getDWCube()->getAxisByRef('o_categorie'));
        $report->setSortType(Report::SORT_CONVENTIONAL);
        $report->save();
    }
}
