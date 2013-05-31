<?php
/**
 * @author valentin.claras
 * @package Inventory
 * @subpackage Plugin
 */

/**
 * Plugin pour la vérification des ACL
 *
 * @package Configuration
 * @subpackage Plugin
 * @uses User_Plugin_Abstract
 */
class Inventory_Plugin_Acl extends User_Plugin_Acl
{
    /**
     * @param User_Model_SecurityIdentity      $identity
     * @param Zend_Controller_Request_Abstract $request
     * @return bool
     */
    public function viewProjectsRule(User_Model_SecurityIdentity $identity, Zend_Controller_Request_Abstract $request)
    {
        $isIdentityAbleToEditProjects = $this->editProjectsRule($identity, $request);
        if ($isIdentityAbleToEditProjects) {
            return true;
        }

        $aclQuery = new Core_Model_Query();
        $aclQuery->aclFilter->enabled = true;
        $aclQuery->aclFilter->user = $identity;
        $aclQuery->aclFilter->action = User_Model_Action_Default::VIEW();
        $isIdentityAbleToSeeManyProjects = (Orga_Model_Project::countTotal($aclQuery) > 1);
        if ($isIdentityAbleToSeeManyProjects) {
            return true;
        }

        return false;
    }
    /**
     * @param User_Model_SecurityIdentity      $identity
     * @param Zend_Controller_Request_Abstract $request
     * @return bool
     */
    public function editProjectsRule(User_Model_SecurityIdentity $identity, Zend_Controller_Request_Abstract $request)
    {
        $projectResource = User_Model_Resource_Entity::loadByEntityName('Orga_Model_Project');

        $isIdentityAbleToCreateProjects = $this->aclService->isAllowed(
            $identity,
            User_Model_Action_Default::CREATE(),
            $projectResource
        );
        if ($isIdentityAbleToCreateProjects) {
            return true;
        }

        $aclQuery = new Core_Model_Query();
        $aclQuery->aclFilter->enabled = true;
        $aclQuery->aclFilter->user = $identity;
        $aclQuery->aclFilter->action = User_Model_Action_Default::EDIT();
        $isIdentityAbleToEditProjects = (Orga_Model_Project::countTotal($aclQuery) > 0);
        if ($isIdentityAbleToEditProjects) {
            return true;
        }

        return false;
    }

    /**
     * @param User_Model_SecurityIdentity      $identity
     * @param Zend_Controller_Request_Abstract $request
     * @return bool
     */
    public function createProjectRule(User_Model_SecurityIdentity $identity, Zend_Controller_Request_Abstract $request)
    {
        return $this->aclService->isAllowed(
            $identity,
            User_Model_Action_Default::CREATE(),
            User_Model_Resource_Entity::loadByEntityName('Orga_Model_Project')
        );
    }

    /**
     * @param User_Model_SecurityIdentity      $identity
     * @param Zend_Controller_Request_Abstract $request
     * @return bool
     */
    protected function viewProjectRule(User_Model_SecurityIdentity $identity, Zend_Controller_Request_Abstract $request)
    {
        return $this->aclService->isAllowed(
            $identity,
            User_Model_Action_Default::VIEW(),
            $this->getProject($request)
        );
    }

    /**
     * @param User_Model_SecurityIdentity      $identity
     * @param Zend_Controller_Request_Abstract $request
     * @return bool
     */
    protected function editProjectRule(User_Model_SecurityIdentity $identity, Zend_Controller_Request_Abstract $request)
    {
        return $this->aclService->isAllowed(
            $identity,
            User_Model_Action_Default::EDIT(),
            $this->getProject($request)
        );
    }

    /**
     * @param User_Model_SecurityIdentity      $identity
     * @param Zend_Controller_Request_Abstract $request
     * @return bool
     */
    public function deleteProjectRule(User_Model_SecurityIdentity $identity, Zend_Controller_Request_Abstract $request)
    {
        return $this->aclService->isAllowed(
            $identity,
            User_Model_Action_Default::DELETE(),
            $this->getProject($request)
        );
    }

    /**
     * @param Zend_Controller_Request_Abstract $request
     * @return Orga_Model_Project
     */
    protected function getProject(Zend_Controller_Request_Abstract $request)
    {
        $idProject = $request->getParam('idProject');
        if ($idProject !== null) {
            return Orga_Model_Project::load(array('id' => $idProject));
        }
        $idCube = $request->getParam('idCube');
        if ($idCube !== null) {
            return Orga_Model_Project::loadByOrgaCube(Orga_Model_Project::load(array('id' => $idCube)));
        }
        $index = $request->getParam('index');
        if ($index !== null) {
            return Orga_Model_Project::load(array('id' => $index));
        }
        $idCell = $request->getParam('idCell');
        if ($idCell !== null) {
            return Orga_Model_Project::loadByOrgaCube(
                Orga_Model_Cell::load(array('id' => $idCell))->getGranularity()->getCube()
            );
        }

        throw new User_Exception_Forbidden();
    }

    /**
     * @param User_Model_SecurityIdentity      $identity
     * @param Zend_Controller_Request_Abstract $request
     * @return bool
     */
    protected function viewCellRule(User_Model_SecurityIdentity $identity, Zend_Controller_Request_Abstract $request)
    {
        return $this->aclService->isAllowed(
            $identity,
            User_Model_Action_Default::VIEW(),
            $this->getCell($request)
        );
    }

    /**
     * @param User_Model_SecurityIdentity      $identity
     * @param Zend_Controller_Request_Abstract $request
     * @return bool
     */
    protected function editCellRule(User_Model_SecurityIdentity $identity, Zend_Controller_Request_Abstract $request)
    {
        return $this->aclService->isAllowed(
            $identity,
            User_Model_Action_Default::EDIT(),
            $this->getCell($request)
        );
    }

    /**
     * @param User_Model_SecurityIdentity      $identity
     * @param Zend_Controller_Request_Abstract $request
     * @return bool
     */
    protected function allowCellRule(User_Model_SecurityIdentity $identity, Zend_Controller_Request_Abstract $request)
    {
        return $this->aclService->isAllowed(
            $identity,
            User_Model_Action_Default::ALLOW(),
            Orga_Model_Cell::loadByOrgaCell(
                Orga_Model_Cell::load(array('id' => $request->getParam('idCell')))
            )
        );
    }

    /**
     * @param User_Model_SecurityIdentity      $identity
     * @param Zend_Controller_Request_Abstract $request
     * @return bool
     */
    protected function inputCellRule(User_Model_SecurityIdentity $identity, Zend_Controller_Request_Abstract $request)
    {
        return $this->aclService->isAllowed(
            $identity,
            Inventory_Action_Cell::INPUT(),
            $this->getCell($request)
        );
    }

    /**
     * @param User_Model_SecurityIdentity      $identity
     * @param Zend_Controller_Request_Abstract $request
     * @return bool
     */
    protected function commentCellRule(User_Model_SecurityIdentity $identity, Zend_Controller_Request_Abstract $request)
    {
        return $this->aclService->isAllowed(
            $identity,
            Inventory_Action_Cell::COMMENT(),
            $this->getCell($request)
        );
    }

    /**
     * @param Zend_Controller_Request_Abstract $request
     * @return Orga_Model_Cell
     */
    protected function getCell(Zend_Controller_Request_Abstract $request)
    {
        $index = $request->getParam('index');
        if ($index !== null) {
            try {
                return Orga_Model_Cell::load(array('id' => $index));
            } catch (Core_Exception_NotFound $e) {
                // Pas une cellule.
            }
        }
        $idCell = $request->getParam('idCell');
        if ($idCell !== null) {
            return Orga_Model_Cell::loadByOrgaCell(Orga_Model_Cell::load(array('id' => $idCell)));
        }
        $idCell = $request->getParam('idCell');
        if ($idCell !== null) {
            return Orga_Model_Cell::load(array('id' => $idCell));
        }

        throw new User_Exception_Forbidden();
    }

    /**
     * @param User_Model_SecurityIdentity      $identity
     * @param Zend_Controller_Request_Abstract $request
     * @return bool
     */
    protected function viewOrgaCubeRule(User_Model_SecurityIdentity $identity, Zend_Controller_Request_Abstract $request)
    {
        try {
            return $this->viewCellRule($identity, $request);
        } catch (User_Exception_Forbidden $e) {
            return $this->viewProjectRule($identity, $request);
        }
    }

    /**
     * @param User_Model_SecurityIdentity      $identity
     * @param Zend_Controller_Request_Abstract $request
     * @return bool
     */
    protected function editOrgaCubeRule(User_Model_SecurityIdentity $identity, Zend_Controller_Request_Abstract $request)
    {
        try {
            return $this->editCellRule($identity, $request);
        } catch (User_Exception_Forbidden $e) {
            return $this->editProjectRule($identity, $request);
        } catch (Core_Exception_NotFound $e) {
            return $this->editProjectRule($identity, $request);
        }
    }

    /**
     * @param User_Model_SecurityIdentity      $identity
     * @param Zend_Controller_Request_Abstract $request
     * @return bool
     */
    protected function viewReportRule(User_Model_SecurityIdentity $identity, Zend_Controller_Request_Abstract $request)
    {
        $idReport = $request->getParam('idReport');
        if ($idReport !== null) {
            return $this->aclService->isAllowed(
                $identity,
                User_Model_Action_Default::VIEW(),
                User_Model_Resource_Entity::loadByEntity(DW_Model_Report::load(array('id' => $idReport)))
            );
        }

        $idCube = $request->getParam('idCube');
        if ($idCube !== null) {
            $dWCube = DW_Model_Cube::load(array('id' => $idCube));
            // Si le DWCube est d'un Granularity, vérification que l'utilisateur peut configurer le projet.
            try {
                $granularity = Inventory_Model_Granularity::loadByDWCube($dWCube);
                $project = Orga_Model_Project::loadByOrgaCube(
                    $granularity->getCube()
                );
                $request->setParam('idProject', $project->getKey()['id']);
                return $this->editProjectRule($identity, $request);
            } catch (Core_Exception_NotFound $e) {
                // Le cube n'appartient pas à un Granularity.
            }
            // Si le DWCube est d'un Cell, vérification que l'utilisateur peut voir la cellule.
            try {
                $cell = Orga_Model_Cell::loadByDWCube($dWCube);
                $request->setParam('idCell', $cell->getKey()['id']);
                return $this->viewCellRule($identity, $request);
            } catch (Core_Exception_NotFound $e) {
                // Le cube n'appartient pas à un Cell.
            }
            // Si le DWCube est d'un SimulationSet, vérification que le Set appartient à l'utilisateur.
            try {
                $simulationSet = Simulation_Model_Set::loadByDWCube($dWCube);
                return ($simulationSet->getUser() === $identity);
            } catch (Core_Exception_NotFound $e) {
                // Le cube n'appartient pas à un SimulationSet.
            }
        }

        throw new User_Exception_Forbidden();
    }

    /**
     * @param User_Model_SecurityIdentity      $identity
     * @param Zend_Controller_Request_Abstract $request
     * @return bool
     */
    protected function editReportRule(User_Model_SecurityIdentity $identity, Zend_Controller_Request_Abstract $request)
    {
        return $this->viewReportRule($identity, $request);
    }

    /**
     * @param User_Model_SecurityIdentity      $identity
     * @param Zend_Controller_Request_Abstract $request
     * @return bool
     */
    protected function deleteReportRule(User_Model_SecurityIdentity $identity, Zend_Controller_Request_Abstract $request)
    {
        $idReport = $request->getParam('index');
        return $this->aclService->isAllowed(
            $identity,
            User_Model_Action_Default::DELETE(),
            User_Model_Resource_Entity::loadByEntity(DW_Model_Report::load(array('id' => $idReport)))
        );
    }

    /**
     * @param Zend_Controller_Request_Abstract $request
     * @return DW_Model_Report
     */
    protected function getReport(Zend_Controller_Request_Abstract $request)
    {
        $idReport = $request->getParam('idReport');
        if ($idReport !== null) {
            return DW_Model_Report::load(array('id' => $idReport));
        }
        $hashReport = $request->getParam('hashReport');
        if ($hashReport !== null) {
            $configuration = Zend_Registry::get('configuration');
            $sessionName = $configuration->sessionStorage->name.'_'.APPLICATION_ENV;
            $zendSessionReport = new Zend_Session_Namespace($sessionName);

            return DW_Model_Report::getFromString($zendSessionReport->$hashReport);
        }

        throw new User_Exception_Forbidden();
    }

    /**
     * @param User_Model_SecurityIdentity      $identity
     * @param Zend_Controller_Request_Abstract $request
     * @return bool
     */
    protected function viewDWCubeRule(User_Model_SecurityIdentity $identity, Zend_Controller_Request_Abstract $request)
    {
        return $this->viewReportRule($identity, $request);
    }

    /**
     * @param User_Model_SecurityIdentity      $identity
     * @param Zend_Controller_Request_Abstract $request
     * @return bool
     */
    protected function viewInputAFRule(User_Model_SecurityIdentity $identity, Zend_Controller_Request_Abstract $request)
    {
        $idCell = $request->getParam('idCell');
        if ($idCell !== null) {
            return $this->viewCellRule($identity, $request);
        }

        return $this->genericInputAF($identity, $request);
    }

    /**
     * @param User_Model_SecurityIdentity      $identity
     * @param Zend_Controller_Request_Abstract $request
     * @return bool
     */
    protected function editInputAFRule(User_Model_SecurityIdentity $identity, Zend_Controller_Request_Abstract $request)
    {
        $idCell = $request->getParam('idCell');
        if ($idCell !== null) {
            return $this->inputCellRule($identity, $request);
        }

        return $this->genericInputAF($identity, $request);
    }

    /**
     * @param User_Model_SecurityIdentity      $identity
     * @param Zend_Controller_Request_Abstract $request
     * @return bool
     */
    protected function modeInputAFRule(User_Model_SecurityIdentity $identity, Zend_Controller_Request_Abstract $request)
    {
        $idCell = $request->getParam('idCell');
        if ($idCell !== null) {
            $mode = $request->getParam('mode');
            if ($mode == AF_ViewConfiguration::MODE_READ) {
                return $this->viewCellRule($identity, $request);
            } else if ($mode == AF_ViewConfiguration::MODE_WRITE) {
                return $this->inputCellRule($identity, $request);
            }

        }

        return $this->genericInputAF($identity, $request);
    }

    /**
     * @param User_Model_SecurityIdentity      $identity
     * @param Zend_Controller_Request_Abstract $request
     * @return bool
     */
    protected function genericInputAF(User_Model_SecurityIdentity $identity, Zend_Controller_Request_Abstract $request)
    {
        $idScenario = $request->getParam('idScenario');
        if ($idScenario !== null) {
            /* @var Simulation_Model_Scenario $scenario */
            $scenario = Simulation_Model_Scenario::load(array('id' => $idScenario));
            return ($scenario->getSet()->getUser() === $identity);
        }

        return $this->editAFRule($identity, $request);
    }

    /**
     * @param User_Model_SecurityIdentity      $identity
     * @param Zend_Controller_Request_Abstract $request
     * @return bool
     */
    protected function editAFRule(User_Model_SecurityIdentity $identity, Zend_Controller_Request_Abstract $request)
    {
        return $this->editReferential($identity);
    }

    /**
     * @param User_Model_SecurityIdentity      $identity
     * @param Zend_Controller_Request_Abstract $request
     * @return bool
     */
    protected function viewTECRule(User_Model_SecurityIdentity $identity, Zend_Controller_Request_Abstract $request)
    {
        return $this->editReferential($identity);
    }

    /**
     * @param User_Model_SecurityIdentity      $identity
     * @param Zend_Controller_Request_Abstract $request
     * @return bool
     */
    protected function viewClassifRule(User_Model_SecurityIdentity $identity, Zend_Controller_Request_Abstract $request)
    {
        return $this->viewReferential($identity);
    }

    /**
     * @param User_Model_SecurityIdentity      $identity
     * @param Zend_Controller_Request_Abstract $request
     * @return bool
     */
    protected function editClassifRule(User_Model_SecurityIdentity $identity, Zend_Controller_Request_Abstract $request)
    {
        return $this->editReferential($identity);
    }

    /**
     * @param User_Model_SecurityIdentity      $identity
     * @param Zend_Controller_Request_Abstract $request
     * @return bool
     */
    protected function viewTechnoRule(User_Model_SecurityIdentity $identity, Zend_Controller_Request_Abstract $request)
    {
        return $this->loggedInRule($identity, $request);
    }

    /**
     * @param User_Model_SecurityIdentity      $identity
     * @param Zend_Controller_Request_Abstract $request
     * @return bool
     */
    protected function editTechnoRule(User_Model_SecurityIdentity $identity, Zend_Controller_Request_Abstract $request)
    {
        return $this->editReferential($identity);
    }

    /**
     * @param User_Model_SecurityIdentity      $identity
     * @param Zend_Controller_Request_Abstract $request
     * @return bool
     */
    protected function viewKeywordRule(User_Model_SecurityIdentity $identity, Zend_Controller_Request_Abstract $request)
    {
        return $this->viewReferential($identity);
    }

    /**
     * @param User_Model_SecurityIdentity      $identity
     * @param Zend_Controller_Request_Abstract $request
     * @return bool
     */
    protected function editKeywordRule(User_Model_SecurityIdentity $identity, Zend_Controller_Request_Abstract $request)
    {
        return $this->editReferential($identity);
    }

    /**
     * @param User_Model_SecurityIdentity      $identity
     * @param Zend_Controller_Request_Abstract $request
     * @return bool
     */
    protected function viewUnitRule(User_Model_SecurityIdentity $identity, Zend_Controller_Request_Abstract $request)
    {
        return $this->loggedInRule($identity, $request);
    }

    /**
     * @param User_Model_SecurityIdentity      $identity
     * @param Zend_Controller_Request_Abstract $request
     * @return bool
     */
    protected function viewReferential(User_Model_SecurityIdentity $identity)
    {
        return $this->aclService->isAllowed(
            $identity,
            User_Model_Action_Default::VIEW(),
            User_Model_Resource_Named::loadByName('referential')
        );
    }

    /**
     * @param User_Model_SecurityIdentity      $identity
     * @param Zend_Controller_Request_Abstract $request
     * @return bool
     */
    protected function editReferential(User_Model_SecurityIdentity $identity)
    {
        return $this->aclService->isAllowed(
            $identity,
            User_Model_Action_Default::EDIT(),
            User_Model_Resource_Named::loadByName('referential')
        );
    }

    /**
     * @param User_Model_SecurityIdentity      $identity
     * @param Zend_Controller_Request_Abstract $request
     * @return bool
     */
    protected function viewLibraryRule(User_Model_SecurityIdentity $identity, Zend_Controller_Request_Abstract $request)
    {
        return true;
    }

    /**
     * @param User_Model_SecurityIdentity      $identity
     * @param Zend_Controller_Request_Abstract $request
     * @return bool
     */
    protected function editLibraryRule(User_Model_SecurityIdentity $identity, Zend_Controller_Request_Abstract $request)
    {
        return true;
    }

    /**
     * @param Zend_Controller_Request_Abstract $request
     * @return Orga_Model_Cell
     */
    protected function getCellFromLibrary(Zend_Controller_Request_Abstract $request)
    {
        $idLibrary = $request->getParam('id');
        $library = Doc_Model_Library::load(array('id' => $idLibrary));

        try {
            return Orga_Model_Cell::loadByDocLibraryForAFInputSetsPrimary($library);
        } catch (Core_Exception_NotFound $e) {
            // Pas de Cell
        }
        try {
            return Orga_Model_Cell::loadByDocLibraryForSocialGenericAction($library);
        } catch (Core_Exception_NotFound $e) {
            // Pas de Cell
        }
        try {
            return Orga_Model_Cell::loadByDocLibraryForSocialContextAction($library);
        } catch (Core_Exception_NotFound $e) {
            // Pas de Cell
        }

        throw new User_Exception_Forbidden();
    }

    /**
     * @param User_Model_SecurityIdentity      $identity
     * @param Zend_Controller_Request_Abstract $request
     * @return bool
     */
    protected function problemToSolveRule(User_Model_SecurityIdentity $identity, Zend_Controller_Request_Abstract $request)
    {
        return $this->loggedInRule($identity, $request);
    }

    /**
     * @param User_Model_SecurityIdentity      $identity
     * @param Zend_Controller_Request_Abstract $request
     * @return bool
     */
    protected function viewBibliographyRule(User_Model_SecurityIdentity $identity, Zend_Controller_Request_Abstract $request)
    {
        return $this->problemToSolveRule($identity, $request);
    }

    /**
     * @param User_Model_SecurityIdentity      $identity
     * @param Zend_Controller_Request_Abstract $request
     * @return bool
     */
    protected function editBibliographyRule(User_Model_SecurityIdentity $identity, Zend_Controller_Request_Abstract $request)
    {
        return $this->problemToSolveRule($identity, $request);
    }

    /**
     * @param User_Model_SecurityIdentity      $identity
     * @param Zend_Controller_Request_Abstract $request
     * @return bool
     */
    protected function viewDocumentRule(User_Model_SecurityIdentity $identity, Zend_Controller_Request_Abstract $request)
    {
        return $this->problemToSolveRule($identity, $request);
    }

    /**
     * @param User_Model_SecurityIdentity      $identity
     * @param Zend_Controller_Request_Abstract $request
     * @return bool
     */
    protected function adminThemesRule(User_Model_SecurityIdentity $identity, Zend_Controller_Request_Abstract $request)
    {
        return $this->problemToSolveRule($identity, $request);
    }

    /**
     * @param User_Model_SecurityIdentity      $identity
     * @param Zend_Controller_Request_Abstract $request
     * @return bool
     */
    protected function viewGenericActionsRule(User_Model_SecurityIdentity $identity, Zend_Controller_Request_Abstract $request)
    {
        return $this->problemToSolveRule($identity, $request);
    }

    /**
     * @param User_Model_SecurityIdentity      $identity
     * @param Zend_Controller_Request_Abstract $request
     * @return bool
     */
    protected function viewGenericActionRule(User_Model_SecurityIdentity $identity, Zend_Controller_Request_Abstract $request)
    {
        return $this->problemToSolveRule($identity, $request);
    }

    /**
     * @param User_Model_SecurityIdentity      $identity
     * @param Zend_Controller_Request_Abstract $request
     * @return bool
     */
    protected function addGenericActionRule(User_Model_SecurityIdentity $identity, Zend_Controller_Request_Abstract $request)
    {
        return $this->problemToSolveRule($identity, $request);
    }

    /**
     * @param User_Model_SecurityIdentity      $identity
     * @param Zend_Controller_Request_Abstract $request
     * @return bool
     */
    protected function editGenericActionRule(User_Model_SecurityIdentity $identity, Zend_Controller_Request_Abstract $request)
    {
        return $this->problemToSolveRule($identity, $request);
    }

    /**
     * @param User_Model_SecurityIdentity      $identity
     * @param Zend_Controller_Request_Abstract $request
     * @return bool
     */
    protected function commentGenericActionRule(User_Model_SecurityIdentity $identity, Zend_Controller_Request_Abstract $request)
    {
        return $this->problemToSolveRule($identity, $request);
    }

    /**
     * @param User_Model_SecurityIdentity      $identity
     * @param Zend_Controller_Request_Abstract $request
     * @return bool
     */
    protected function deleteGenericActionRule(User_Model_SecurityIdentity $identity, Zend_Controller_Request_Abstract $request)
    {
        return $this->problemToSolveRule($identity, $request);
    }

    /**
     * @param User_Model_SecurityIdentity      $identity
     * @param Zend_Controller_Request_Abstract $request
     * @return bool
     */
    protected function viewContextActionsRule(User_Model_SecurityIdentity $identity, Zend_Controller_Request_Abstract $request)
    {
        return $this->problemToSolveRule($identity, $request);
    }

    /**
     * @param User_Model_SecurityIdentity      $identity
     * @param Zend_Controller_Request_Abstract $request
     * @return bool
     */
    protected function viewContextActionRule(User_Model_SecurityIdentity $identity, Zend_Controller_Request_Abstract $request)
    {
        return $this->problemToSolveRule($identity, $request);
    }

    /**
     * @param User_Model_SecurityIdentity      $identity
     * @param Zend_Controller_Request_Abstract $request
     * @return bool
     */
    protected function addContextActionRule(User_Model_SecurityIdentity $identity, Zend_Controller_Request_Abstract $request)
    {
        return $this->problemToSolveRule($identity, $request);
    }

    /**
     * @param User_Model_SecurityIdentity      $identity
     * @param Zend_Controller_Request_Abstract $request
     * @return bool
     */
    protected function editContextActionRule(User_Model_SecurityIdentity $identity, Zend_Controller_Request_Abstract $request)
    {
        return $this->problemToSolveRule($identity, $request);
    }

    /**
     * @param User_Model_SecurityIdentity      $identity
     * @param Zend_Controller_Request_Abstract $request
     * @return bool
     */
    protected function commentContextActionRule(User_Model_SecurityIdentity $identity, Zend_Controller_Request_Abstract $request)
    {
        return $this->problemToSolveRule($identity, $request);
    }

    /**
     * @param User_Model_SecurityIdentity      $identity
     * @param Zend_Controller_Request_Abstract $request
     * @return bool
     */
    protected function deleteContextActionRule(User_Model_SecurityIdentity $identity, Zend_Controller_Request_Abstract $request)
    {
        return $this->problemToSolveRule($identity, $request);
    }

}