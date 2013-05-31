<?php
/**
 * @package Orga
 * @subpackage ObserverProvider
 */

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\PostFlushEventArgs;

/**
 * Classe permettant de construire les ACL relatives aux éléments d'Intégration.
 * @author valentin.claras
 * @package Orga
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
     * Ensemble des nouveaux Cell.
     *
     * @var Orga_Model_Cell[]
     */
    protected $newCells = [];

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
    protected $newGranularityCellReports = [];

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

        if (count($this->newCells) > 0) {
            foreach ($this->newCells as $cell) {
                $this->processCell($cell);

                if ($cell->getGranularity()->getCellsGenerateDWCubes()) {
                    $idCell = $cell->getKey()['id'];
                    if (isset($this->newResources['cellDataProvider'][$idCell])) {
                        $cellResource = $this->newResources['cellDataProvider'][$idCell];
                    } else {
                        $cellResource = User_Model_Resource_Entity::loadByEntity($cell);
                    }
                    foreach ($this->getChildResources($cellResource) as $childResource) {
                        /** @var Orga_Model_Cell $childCell */
                        $childCell = $childResource->getEntity();
                        $childGranularity = $childCell->getGranularity();
                        if ($childGranularity->getCellsGenerateDWCubes()) {
                            foreach ($childCell->getGranularityReports() as $childCellReport) {
                                $this->processCellReportForResource($childCellReport,
                                                                                $childResource);
                            }
                        }
                    }
                }
            }

            $this->newCells = array();
            $flushNeeded = true;
        }

        if ((count($this->newReports) > 0) || (count($this->newGranularityCellReports) > 0)) {
            foreach ($this->newReports as $report) {
                $this->processReport($report);
            }
            foreach ($this->newGranularityCellReports as $cellReport) {
                $this->processCellReport($cellReport);
            }

            $this->newReports = array();
            $this->newGranularityCellReports = array();
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
     * @param Orga_Model_Project $project
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
        $projectAdministrator->setName(__('Orga', 'role', 'projectAdministrator'));
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
     * Sauvegarde les ressource et roles d'un Cell et génère les authorisations.
     *
     * @param Orga_Model_Cell $cell
     */
    protected function processCell($cell)
    {
        $project = Orga_Model_Project::loadByOrgaCube(
            $cell->getGranularity()->getCube()
        );
        if (isset($this->newResources['project'][$project->getKey()['id']])) {
            $projectResource = $this->newResources['project'][$project->getKey()['id']];
        } else {
            $projectResource = User_Model_Resource_Entity::loadByEntity($project);
        }

        // Création de la ressource cellule donnée.
        $cellResource = new User_Model_Resource_Entity();
        $cellResource->setEntity($cell);
        $cellResource->save();
        $this->newResources['cellDataProvider'][$cell->getKey()['id']] = $cellResource;


        // Création du rôle administrateur de la cellule donnée.
        $cellAdministrator = new User_Model_Role();
        $cellAdministrator->setRef('cellDataProviderAdministrator_'.$cell->getKey()['id']);
        $cellAdministrator->setName(__('Orga', 'role', 'cellAdministrator'));
        $cellAdministrator->save();

        // Ajout des autorisations du rôle administrateur sur la ressource.
        User_Service_ACL::getInstance()->allow(
            $cellAdministrator,
            User_Model_Action_Default::VIEW(),
            $projectResource
        );
        User_Service_ACL::getInstance()->allow(
            $cellAdministrator,
            User_Model_Action_Default::VIEW(),
            $cellResource
        );
        User_Service_ACL::getInstance()->allow(
            $cellAdministrator,
            User_Model_Action_Default::EDIT(),
            $cellResource
        );
        User_Service_ACL::getInstance()->allow(
            $cellAdministrator,
            User_Model_Action_Default::ALLOW(),
            $cellResource
        );
        User_Service_ACL::getInstance()->allow(
            $cellAdministrator,
            Orga_Action_Cell::COMMENT(),
            $cellResource
        );
        User_Service_ACL::getInstance()->allow(
            $cellAdministrator,
            Orga_Action_Cell::INPUT(),
            $cellResource
        );


        // Création du rôle contributeur de la cellule donnée.
        $cellContributor = new User_Model_Role();
        $cellContributor->setRef('cellDataProviderContributor_'.$cell->getKey()['id']);
        $cellContributor->setName(__('Orga', 'role', 'cellContributor'));
        $cellContributor->save();

        // Ajout des autorisations du rôle administrateur sur la ressource.
        User_Service_ACL::getInstance()->allow(
            $cellContributor,
            User_Model_Action_Default::VIEW(),
            $projectResource
        );
        User_Service_ACL::getInstance()->allow(
            $cellContributor,
            User_Model_Action_Default::VIEW(),
            $cellResource
        );
        User_Service_ACL::getInstance()->allow(
            $cellContributor,
            Orga_Action_Cell::COMMENT(),
            $cellResource
        );
        User_Service_ACL::getInstance()->allow(
            $cellContributor,
            Orga_Action_Cell::INPUT(),
            $cellResource
        );


        // Création du rôle observateur de la cellule donnée.
        $cellObserver = new User_Model_Role();
        $cellObserver->setRef('cellDataProviderObserver_'.$cell->getKey()['id']);
        $cellObserver->setName(__('Orga', 'role', 'cellObserver'));
        $cellObserver->save();

        // Ajout des autorisations du rôle observateur sur la ressource.
        User_Service_ACL::getInstance()->allow(
            $cellObserver,
            User_Model_Action_Default::VIEW(),
            $projectResource
        );
        User_Service_ACL::getInstance()->allow(
            $cellObserver,
            User_Model_Action_Default::VIEW(),
            $cellResource
        );
        User_Service_ACL::getInstance()->allow(
            $cellObserver,
            Orga_Action_Cell::COMMENT(),
            $cellResource
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
     * Créer les autorisations relatives au Report d'un Cell.
     *
     * @param DW_Model_Report $cellReport
     */
    protected function processCellReport(DW_Model_Report $cellReport)
    {
        $cell = Orga_Model_Cell::loadByDWCube($cellReport->getCube());
        if (isset($this->newResources['cellDataProvider'][$cell->getKey()['id']])) {
            $cellResource = $this->newResources['cellDataProvider'][$cell->getKey()['id']];
        } else {
            $cellResource = User_Model_Resource_Entity::loadByEntity($cell);
        }

        $cellResources = $this->getParentResources($cellResource);
        $cellResources[] = $cellResource;

        foreach ($cellResources as $cellResource) {
            $this->processCellReportForResource($cellReport, $cellResource);
        }
    }

    /**
     * Créer les autorisations relatives au Report sur un Cell.
     *
     * @param DW_Model_Report $cellReport
     * @param User_Model_Resource_Entity $cellResource
     */
    protected function processCellReportForResource($cellReport, $cellResource)
    {
        if (isset($this->newResources['report'][$cellReport->getKey()['id']])) {
            $cellReportResource = $this->newResources['report'][$cellReport->getKey()['id']];
        } else {
            $cellReportResource = User_Model_Resource_Entity::loadByEntity($cellReport);
            // Problème : null
        }

        // TODO désactivation des droits sur le rapport
        // @see http://dev.myc-sense.com:3000/issues/5721
//        foreach ($cellResource->getLinkedSecurityIdentities() as $linkedIdentity) {
//            if ($linkedIdentity instanceof User_Model_Role) {
//                User_Service_ACL::getInstance()->allow(
//                    $linkedIdentity,
//                    User_Model_Action_Default::VIEW(),
//                    $cellReportResource
//                );
//            }
//        }
    }

    /**
     * Créer la ressource correspondante au Project et les rôles associés.
     *
     * @param Orga_Model_Project $project
     */
    protected function createProjectResourceAndRolesService($project)
    {
        $this->newProjects[] = $project;
    }

    /**
     * Supprime la ressource correspondante au Project et les rôles associés.
     *
     * @param Orga_Model_Project $project
     */
    protected function deleteProjectResourceAndRolesService($project)
    {
        $projectResource = User_Model_Resource_Entity::loadByEntity($project);
        $projectResource->delete();

        $projectAdministrator = User_Model_Role::loadByRef('projectAdministrator_'.$project->getKey()['id']);
        $projectAdministrator->delete();
    }

    /**
     * Créer la ressource correspondante au Cell et les rôles associés.
     *
     * @param Orga_Model_Cell $cell
     */
    protected function createCellResourceAndRolesService($cell)
    {
        $this->newCells[] = $cell;
    }

    /**
     * Supprime la ressource correspondante au Cell et les rôles associés.
     *
     * @param Orga_Model_Cell $cell
     */
    protected function deleteCellResourceAndRolesService($cell)
    {
        $cellResource = User_Model_Resource_Entity::loadByEntity($cell);
        $cellResource->delete();

        $cellAdministrator = User_Model_Role::loadByRef(
            'cellDataProviderAdministrator_'.$cell->getKey()['id']
        );
        $cellAdministrator->delete();

        $cellContributor = User_Model_Role::loadByRef(
            'cellDataProviderContributor_'.$cell->getKey()['id']
        );
        $cellContributor->delete();

        $cellObserver = User_Model_Role::loadByRef(
            'cellDataProviderObserver_'.$cell->getKey()['id']
        );
        $cellObserver->delete();
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

        /** @var Orga_Model_Cell $cell */
        $cell = $resource->getEntity();

        try {
            // Si la cellule a été supprimée, il n'y a plus de parents
            $parentCells = $cell->getParentCells();
        } catch (Core_Exception_NotFound $e) {
            return $parentResources;
        }

        foreach ($parentCells as $parentCell) {
            if (isset($this->newResources['cell'][$parentCell->getKey()['id']])) {
                $parentResource = $this->newResources['cell'][$parentCell->getKey()['id']];
            } else {
                $parentResource = User_Model_Resource_Entity::loadByEntity($parentCell);
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

        /** @var Orga_Model_Cell $cell */
        $cell = $resource->getEntity();

        foreach ($cell->getChildCells() as $childCell) {
            if (isset($this->newResources['cellDataProvider'][$childCell->getKey()['id']])) {
                $childResource = $this->newResources['cellDataProvider'][$childCell->getKey()['id']];
            } else {
                $childResource = User_Model_Resource_Entity::loadByEntity($childCell);
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
     * @param Orga_Model_Project $project
     * @param User_Model_User $user
     */
    protected function addProjectAdministratorService($project, $user)
    {
        $user->addRole(User_Model_Role::loadByRef('projectAdministrator_'.$project->getKey()['id']));

        $globalCell = Orga_Model_Cell::loadByOrgaCell(
            Orga_Model_Granularity::loadByRefAndCube('global', $project->getOrgaCube())->getCells()[0]
        );
        $user->addRole(
            User_Model_Role::loadByRef('cellDataProviderAdministrator_'.$globalCell->getKey()['id'])
        );
    }

    /**
     * Retire au projet donné, l'utilisateur comme administrateur.
     *
     * @param Orga_Model_Project $project
     * @param User_Model_User $user
     */
    protected function removeProjectAdministratorService($project, $user)
    {
        $user->removeRole(User_Model_Role::loadByRef('projectAdministrator_'.$project->getKey()['id']));

        $globalCell = Orga_Model_Cell::loadByOrgaCell(
            Orga_Model_Granularity::loadByRefAndCube('global', $project->getOrgaCube())->getCells()[0]
        );
        $user->removeRole(
            User_Model_Role::loadByRef('cellDataProviderAdministrator_'.$globalCell->getKey()['id'])
        );
    }

    /**
     * Ajoute les authorizations sur les rapports des Granularity.
     *
     * @param Orga_Model_GranularityReport $granularityReport
     */
    protected function addGranularityReportViewAuthorizationService(
        Orga_Model_GranularityReport $granularityReport
    ) {
        foreach ($granularityReport->getCellDWReports() as $cellReport) {
            $this->newGranularityCellReports[] = $cellReport;
        }
    }

    /**
     * Ajoute les authorizations sur les rapports issues d'une Granularity pour un Cell.
     *
     * @param Orga_Model_Cell $cell
     */
    protected function addGranularityReportViewAuthorizationToCellService(Orga_Model_Cell $cell)
    {
        $granularity = $cell->getGranularity();

        if ($granularity->getCellsGenerateDWCubes()) {
            foreach ($cell->getGranularityReports() as $cellReport) {
                $this->newGranularityCellReports[] = $cellReport;
            }
        }
    }

}