<?php
/**
 * @package Inventory
 * @subpackage ObserverProvider
 */

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\PostFlushEventArgs;

/**
 * Classe permettant de construire les ACL relatives aux éléments d'Intégration.
 * @author valentin.claras
 * @package Inventory
 * @subpackage ObserverProvider
 *
 */
class Inventory_Service_ACLManager extends Core_Service implements User_Service_ACL_ResourceTreeTraverser
{
    /**
     * Ensemble des nouveaux Project.
     *
     * @var array
     */
    protected $newProjects = [];

    /**
     * Ensemble des nouveaux CellDataProvider.
     *
     * @var Inventory_Model_CellDataProvider[]
     */
    protected $newCellDataProviders = [];

    /**
     * Ensemble des nouveaux Report.
     *
     * @var array
     */
    protected $newReports = [];

    /**
     * Ensemble des nouveaux GranularityReport.
     *
     * @var DW_Model_Report[]
     */
    protected $newGranularityCellDataProviderReports = [];

    /**
     * Ensemble des nouvelles Ressources.
     *
     * @var array
     */
    protected $newResources = [
        'project' => [],
        'cellDataProvider' => [],
        'report' => []
    ];

    /**
     * Ensemble des liens entre ressource qui viennent d'être créée.
     *
     * @var array
     */
    protected $associativeCellDtaProviderParentResources = [];


    /**
     * @param PostFlushEventArgs $eventArgs
     */
    public function postFlush(PostFlushEventArgs $eventArgs)
    {
        $flushNeeded = false;
        $entityManagers = Zend_Registry::get('EntityManagers');
        /** @var EntityManager $entityManager */
        $entityManager = $entityManagers['default'];

        if (count($this->newProjects) > 0) {
            foreach ($this->newProjects as $project) {
                $this->processProject($project);
            }

            $this->newProjects = array();
            $flushNeeded = true;
        }

        if (count($this->newCellDataProviders) > 0) {
            foreach ($this->newCellDataProviders as $cellDataProvider) {
                $this->processCellDataProvider($cellDataProvider);

                if ($cellDataProvider->getGranularityDataProvider()->getCellsGenerateDWCubes()) {
                    $idCellDataProvider = $cellDataProvider->getKey()['id'];
                    if (isset($this->newResources['cellDataProvider'][$idCellDataProvider])) {
                        $cellDataProviderResource = $this->newResources['cellDataProvider'][$idCellDataProvider];
                    } else {
                        $cellDataProviderResource = User_Model_Resource_Entity::loadByEntity($cellDataProvider);
                    }
                    foreach ($this->getChildResources($cellDataProviderResource) as $childResource) {
                        /** @var Inventory_Model_CellDataProvider $childCellDataProvider */
                        $childCellDataProvider = $childResource->getEntity();
                        $childGranularityDataProvider = $childCellDataProvider->getGranularityDataProvider();
                        if ($childGranularityDataProvider->getCellsGenerateDWCubes()) {
                            foreach ($childCellDataProvider->getGranularityReports() as $childCellDataProviderReport) {
                                $this->processCellDataProviderReportForResource($childCellDataProviderReport,
                                                                                $childResource);
                            }
                        }
                    }
                }
            }

            $this->newCellDataProviders = array();
            $flushNeeded = true;
        }

        if ((count($this->newReports) > 0) || (count($this->newGranularityCellDataProviderReports) > 0)) {
            foreach ($this->newReports as $report) {
                $this->processReport($report);
            }
            foreach ($this->newGranularityCellDataProviderReports as $cellDataProviderReport) {
                $this->processCellDataProviderReport($cellDataProviderReport);
            }

            $this->newReports = array();
            $this->newGranularityCellDataProviderReports = array();
            $flushNeeded = true;
        }

        if ($flushNeeded) {
            $this->newResources = array('project' => array(), 'cellDataProvider' => array(), 'report' => array());
            $entityManager->flush();
        }
    }

    /**
     * Sauvegarde les ressource et roles d'un Project et génère les authorisations.
     *
     * @param Inventory_Model_Project $project
     */
    protected function processProject($project)
    {
        // Création de la ressource projet donné.
        $projectResource = new User_Model_Resource_Entity();
        $projectResource->setEntity($project);
        $projectResource->save();
        $this->newResources['project'][$project->getKey()['id']] = $projectResource;

        // Création du rôle administrateur du projet donné.
        $projectAdministrator = new User_Model_Role();
        $projectAdministrator->setRef('projectAdministrator_'.$project->getKey()['id']);
        $projectAdministrator->setName(__('Inventory', 'role', 'projectAdministrator'));
        $projectAdministrator->save();

        // Ajout des autorisations du rôle administrateur sur la ressource.
        User_Service_ACL::getInstance()->allow(
            $projectAdministrator,
            User_Model_Action_Default::VIEW(),
            $projectResource
        );
        User_Service_ACL::getInstance()->allow(
            $projectAdministrator,
            User_Model_Action_Default::EDIT(),
            $projectResource
        );
        User_Service_ACL::getInstance()->allow(
            $projectAdministrator,
            User_Model_Action_Default::DELETE(),
            $projectResource
        );
    }

    /**
     * Sauvegarde les ressource et roles d'un CellDataProvider et génère les authorisations.
     *
     * @param Inventory_Model_CellDataProvider $cellDataProvider
     */
    protected function processCellDataProvider($cellDataProvider)
    {
        $project = Inventory_Model_Project::loadByOrgaCube(
            $cellDataProvider->getOrgaCell()->getGranularity()->getCube()
        );
        if (isset($this->newResources['project'][$project->getKey()['id']])) {
            $projectResource = $this->newResources['project'][$project->getKey()['id']];
        } else {
            $projectResource = User_Model_Resource_Entity::loadByEntity($project);
        }

        // Création de la ressource cellule donnée.
        $cellDataProviderResource = new User_Model_Resource_Entity();
        $cellDataProviderResource->setEntity($cellDataProvider);
        $cellDataProviderResource->save();
        $this->newResources['cellDataProvider'][$cellDataProvider->getKey()['id']] = $cellDataProviderResource;


        // Création du rôle administrateur de la cellule donnée.
        $cellDataProviderAdministrator = new User_Model_Role();
        $cellDataProviderAdministrator->setRef('cellDataProviderAdministrator_'.$cellDataProvider->getKey()['id']);
        $cellDataProviderAdministrator->setName(__('Inventory', 'role', 'cellAdministrator'));
        $cellDataProviderAdministrator->save();

        // Ajout des autorisations du rôle administrateur sur la ressource.
        User_Service_ACL::getInstance()->allow(
            $cellDataProviderAdministrator,
            User_Model_Action_Default::VIEW(),
            $projectResource
        );
        User_Service_ACL::getInstance()->allow(
            $cellDataProviderAdministrator,
            User_Model_Action_Default::VIEW(),
            $cellDataProviderResource
        );
        User_Service_ACL::getInstance()->allow(
            $cellDataProviderAdministrator,
            User_Model_Action_Default::EDIT(),
            $cellDataProviderResource
        );
        User_Service_ACL::getInstance()->allow(
            $cellDataProviderAdministrator,
            User_Model_Action_Default::ALLOW(),
            $cellDataProviderResource
        );
        User_Service_ACL::getInstance()->allow(
            $cellDataProviderAdministrator,
            Inventory_Action_Cell::COMMENT(),
            $cellDataProviderResource
        );
        User_Service_ACL::getInstance()->allow(
            $cellDataProviderAdministrator,
            Inventory_Action_Cell::INPUT(),
            $cellDataProviderResource
        );


        // Création du rôle contributeur de la cellule donnée.
        $cellDataProviderContributor = new User_Model_Role();
        $cellDataProviderContributor->setRef('cellDataProviderContributor_'.$cellDataProvider->getKey()['id']);
        $cellDataProviderContributor->setName(__('Inventory', 'role', 'cellContributor'));
        $cellDataProviderContributor->save();

        // Ajout des autorisations du rôle administrateur sur la ressource.
        User_Service_ACL::getInstance()->allow(
            $cellDataProviderContributor,
            User_Model_Action_Default::VIEW(),
            $projectResource
        );
        User_Service_ACL::getInstance()->allow(
            $cellDataProviderContributor,
            User_Model_Action_Default::VIEW(),
            $cellDataProviderResource
        );
        User_Service_ACL::getInstance()->allow(
            $cellDataProviderContributor,
            Inventory_Action_Cell::COMMENT(),
            $cellDataProviderResource
        );
        User_Service_ACL::getInstance()->allow(
            $cellDataProviderContributor,
            Inventory_Action_Cell::INPUT(),
            $cellDataProviderResource
        );


        // Création du rôle observateur de la cellule donnée.
        $cellDataProviderObserver = new User_Model_Role();
        $cellDataProviderObserver->setRef('cellDataProviderObserver_'.$cellDataProvider->getKey()['id']);
        $cellDataProviderObserver->setName(__('Inventory', 'role', 'cellObserver'));
        $cellDataProviderObserver->save();

        // Ajout des autorisations du rôle observateur sur la ressource.
        User_Service_ACL::getInstance()->allow(
            $cellDataProviderObserver,
            User_Model_Action_Default::VIEW(),
            $projectResource
        );
        User_Service_ACL::getInstance()->allow(
            $cellDataProviderObserver,
            User_Model_Action_Default::VIEW(),
            $cellDataProviderResource
        );
        User_Service_ACL::getInstance()->allow(
            $cellDataProviderObserver,
            Inventory_Action_Cell::COMMENT(),
            $cellDataProviderResource
        );
    }

    /**
     * Sauvegarde la ressource d'un Report.
     *
     * @param DW_Model_Report $report
     */
    protected function processReport($report)
    {
        // Création de la ressource Report donné.
        $reportResource = new User_Model_Resource_Entity();
        $reportResource->setEntity($report);
        $reportResource->save();
        $this->newResources['report'][$report->getKey()['id']] = $reportResource;

        User_Service_ACL::getInstance()->allow(
            User_Model_User::load(Zend_Auth::getInstance()->getIdentity()),
            User_Model_Action_Default::VIEW(),
            $reportResource
        );
        User_Service_ACL::getInstance()->allow(
            User_Model_User::load(Zend_Auth::getInstance()->getIdentity()),
            User_Model_Action_Default::EDIT(),
            $reportResource
        );
        User_Service_ACL::getInstance()->allow(
            User_Model_User::load(Zend_Auth::getInstance()->getIdentity()),
            User_Model_Action_Default::DELETE(),
            $reportResource
        );
    }

    /**
     * Créer les autorisations relatives au Report d'un CellDataProvider.
     *
     * @param DW_Model_Report $cellDataProviderReport
     */
    protected function processCellDataProviderReport(DW_Model_Report $cellDataProviderReport)
    {
        $cellDataProvider = Inventory_Model_CellDataProvider::loadByDWCube($cellDataProviderReport->getCube());
        if (isset($this->newResources['cellDataProvider'][$cellDataProvider->getKey()['id']])) {
            $cellDataProviderResource = $this->newResources['cellDataProvider'][$cellDataProvider->getKey()['id']];
        } else {
            $cellDataProviderResource = User_Model_Resource_Entity::loadByEntity($cellDataProvider);
        }

        $cellDataProviderResources = $this->getParentResources($cellDataProviderResource);
        $cellDataProviderResources[] = $cellDataProviderResource;

        foreach ($cellDataProviderResources as $cellDataProviderResource) {
            $this->processCellDataProviderReportForResource($cellDataProviderReport, $cellDataProviderResource);
        }
    }

    /**
     * Créer les autorisations relatives au Report sur un CellDataProvider.
     *
     * @param DW_Model_Report $cellDataProviderReport
     * @param User_Model_Resource_Entity $cellDataProviderResource
     */
    protected function processCellDataProviderReportForResource($cellDataProviderReport, $cellDataProviderResource)
    {
        if (isset($this->newResources['report'][$cellDataProviderReport->getKey()['id']])) {
            $cellDataProviderReportResource = $this->newResources['report'][$cellDataProviderReport->getKey()['id']];
        } else {
            $cellDataProviderReportResource = User_Model_Resource_Entity::loadByEntity($cellDataProviderReport);
            // Problème : null
        }

        // TODO désactivation des droits sur le rapport
        // @see http://dev.myc-sense.com:3000/issues/5721
//        foreach ($cellDataProviderResource->getLinkedSecurityIdentities() as $linkedIdentity) {
//            if ($linkedIdentity instanceof User_Model_Role) {
//                User_Service_ACL::getInstance()->allow(
//                    $linkedIdentity,
//                    User_Model_Action_Default::VIEW(),
//                    $cellDataProviderReportResource
//                );
//            }
//        }
    }

    /**
     * Créer la ressource correspondante au Project et les rôles associés.
     *
     * @param Inventory_Model_Project $project
     */
    protected function createProjectResourceAndRolesService($project)
    {
        $this->newProjects[] = $project;
    }

    /**
     * Supprime la ressource correspondante au Project et les rôles associés.
     *
     * @param Inventory_Model_Project $project
     */
    protected function deleteProjectResourceAndRolesService($project)
    {
        $projectResource = User_Model_Resource_Entity::loadByEntity($project);
        $projectResource->delete();

        $projectAdministrator = User_Model_Role::loadByRef('projectAdministrator_'.$project->getKey()['id']);
        $projectAdministrator->delete();
    }

    /**
     * Créer la ressource correspondante au CellDataProvider et les rôles associés.
     *
     * @param Inventory_Model_CellDataProvider $cellDataProvider
     */
    protected function createCellDataProviderResourceAndRolesService($cellDataProvider)
    {
        $this->newCellDataProviders[] = $cellDataProvider;
    }

    /**
     * Supprime la ressource correspondante au CellDataProvider et les rôles associés.
     *
     * @param Inventory_Model_CellDataProvider $cellDataProvider
     */
    protected function deleteCellDataProviderResourceAndRolesService($cellDataProvider)
    {
        $cellDataProviderResource = User_Model_Resource_Entity::loadByEntity($cellDataProvider);
        $cellDataProviderResource->delete();

        $cellDataProviderAdministrator = User_Model_Role::loadByRef(
            'cellDataProviderAdministrator_'.$cellDataProvider->getKey()['id']
        );
        $cellDataProviderAdministrator->delete();

        $cellDataProviderContributor = User_Model_Role::loadByRef(
            'cellDataProviderContributor_'.$cellDataProvider->getKey()['id']
        );
        $cellDataProviderContributor->delete();

        $cellDataProviderObserver = User_Model_Role::loadByRef(
            'cellDataProviderObserver_'.$cellDataProvider->getKey()['id']
        );
        $cellDataProviderObserver->delete();
    }

    /**
     * Créer la ressource correspondante à un Report.
     *
     * @param DW_Model_Report $report
     */
    protected function createReportResourceService($report)
    {
        $this->newReports[] = $report;
    }

    /**
     * Supprime la ressource correspondante a un Report.
     *
     * @param DW_Model_Report $report
     */
    protected function deleteReportResourceService($report)
    {
        $reportResource = User_Model_Resource_Entity::loadByEntity($report);
        $reportResource->delete();
    }

    /**
     * Trouve les ressources parent d'une ressource
     *
     * @param User_Model_Resource_Entity $resource
     *
     * @return User_Model_Resource_Entity[] Tableau indexé par l'ID de chaque ressource pour éviter les doublons
     */
    public function getParentResources(User_Model_Resource_Entity $resource)
    {
        $parentResources = [];

        /** @var Inventory_Model_CellDataProvider $cellDataProvider */
        $cellDataProvider = $resource->getEntity();

        try {
            // Si la cellule a été supprimée, il n'y a plus de parents
            $parentCells = $cellDataProvider->getOrgaCell()->getParentCells();
        } catch (Core_Exception_NotFound $e) {
            return $parentResources;
        }

        foreach ($parentCells as $parentOrgaCell) {
            $parentCellDataProvider = Inventory_Model_CellDataProvider::loadByOrgaCell($parentOrgaCell);
            if (isset($this->newResources['cellDataProvider'][$parentCellDataProvider->getKey()['id']])) {
                $parentResource = $this->newResources['cellDataProvider'][$parentCellDataProvider->getKey()['id']];
            } else {
                $parentResource = User_Model_Resource_Entity::loadByEntity($parentCellDataProvider);
            }
            if ($parentResource !== null) {
                $parentResources[] = $parentResource;
            }
        }

        return $parentResources;
    }

    /**
     * Trouve les ressources filles d'une ressource
     *
     * @param User_Model_Resource_Entity $resource
     *
     * @return User_Model_Resource_Entity[] Tableau indexé par l'ID de chaque ressource pour éviter les doublons
     */
    public function getChildResources(User_Model_Resource_Entity $resource)
    {
        $childResources = [];

        /** @var Inventory_Model_CellDataProvider $cellDataProvider */
        $cellDataProvider = $resource->getEntity();

        foreach ($cellDataProvider->getOrgaCell()->getChildCells() as $childOrgaCell) {
            $childCellDataProvider = Inventory_Model_CellDataProvider::loadByOrgaCell($childOrgaCell);
            if (isset($this->newResources['cellDataProvider'][$childCellDataProvider->getKey()['id']])) {
                $childResource = $this->newResources['cellDataProvider'][$childCellDataProvider->getKey()['id']];
            } else {
                $childResource = User_Model_Resource_Entity::loadByEntity($childCellDataProvider);
            }
            if ($childResource !== null) {
                $childResources[] = $childResource;
            }
        }

        return $childResources;
    }

    /**
     * Ajoute au projet donné, l'utilisateur comme administrateur.
     *
     * @param Inventory_Model_Project $project
     * @param User_Model_User $user
     */
    protected function addProjectAdministratorService($project, $user)
    {
        $user->addRole(User_Model_Role::loadByRef('projectAdministrator_'.$project->getKey()['id']));

        $globalCellDataProvider = Inventory_Model_CellDataProvider::loadByOrgaCell(
            Orga_Model_Granularity::loadByRefAndCube('global', $project->getOrgaCube())->getCells()[0]
        );
        $user->addRole(
            User_Model_Role::loadByRef('cellDataProviderAdministrator_'.$globalCellDataProvider->getKey()['id'])
        );
    }

    /**
     * Retire au projet donné, l'utilisateur comme administrateur.
     *
     * @param Inventory_Model_Project $project
     * @param User_Model_User $user
     */
    protected function removeProjectAdministratorService($project, $user)
    {
        $user->removeRole(User_Model_Role::loadByRef('projectAdministrator_'.$project->getKey()['id']));

        $globalCellDataProvider = Inventory_Model_CellDataProvider::loadByOrgaCell(
            Orga_Model_Granularity::loadByRefAndCube('global', $project->getOrgaCube())->getCells()[0]
        );
        $user->removeRole(
            User_Model_Role::loadByRef('cellDataProviderAdministrator_'.$globalCellDataProvider->getKey()['id'])
        );
    }

    /**
     * Ajoute les authorizations sur les rapports des Granularity.
     *
     * @param Inventory_Model_GranularityReport $granularityReport
     */
    protected function addGranularityReportViewAuthorizationService(
        Inventory_Model_GranularityReport $granularityReport
    ) {
        foreach ($granularityReport->getCellDataProviderDWReports() as $cellDataProviderReport) {
            $this->newGranularityCellDataProviderReports[] = $cellDataProviderReport;
        }
    }

    /**
     * Ajoute les authorizations sur les rapports issues d'une Granularity pour un CellDataProvider.
     *
     * @param Inventory_Model_CellDataProvider $cellDataProvider
     */
    protected function addGranularityReportViewAuthorizationToCellDataProviderService(
        Inventory_Model_CellDataProvider $cellDataProvider
    ) {
        $granularityDataProvider = $cellDataProvider->getGranularityDataProvider();

        if ($granularityDataProvider->getCellsGenerateDWCubes()) {
            foreach ($cellDataProvider->getGranularityReports() as $cellDataProviderReport) {
                $this->newGranularityCellDataProviderReports[] = $cellDataProviderReport;
            }
        }
    }

}