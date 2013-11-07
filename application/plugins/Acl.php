<?php
/**
 * @author valentin.claras
 * @package Inventory
 * @subpackage Plugin
 */

use Doc\Domain\Library;
use User\ForbiddenException;

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
    public function viewOrganizationsRule(User_Model_SecurityIdentity $identity, Zend_Controller_Request_Abstract $request)
    {
        $isIdentityAbleToEditOrganizations = $this->editOrganizationsRule($identity, $request);
        if ($isIdentityAbleToEditOrganizations) {
            return true;
        }

        $aclQuery = new Core_Model_Query();
        $aclQuery->aclFilter->enabled = true;
        $aclQuery->aclFilter->user = $identity;
        $aclQuery->aclFilter->action = User_Model_Action_Default::VIEW();
        $isIdentityAbleToSeeManyOrganizations = (Orga_Model_Organization::countTotal($aclQuery) > 1);
        if ($isIdentityAbleToSeeManyOrganizations) {
            return true;
        }

        return false;
    }

    /**
     * @param User_Model_SecurityIdentity      $identity
     * @param Zend_Controller_Request_Abstract $request
     * @return bool
     */
    public function editOrganizationsRule(User_Model_SecurityIdentity $identity, Zend_Controller_Request_Abstract $request)
    {
        $organizationResource = User_Model_Resource_Entity::loadByEntityName(Orga_Model_Organization::class);

        $isIdentityAbleToCreateOrganizations = $this->aclService->isAllowed(
            $identity,
            User_Model_Action_Default::CREATE(),
            $organizationResource
        );
        if ($isIdentityAbleToCreateOrganizations) {
            return true;
        }

        $aclQuery = new Core_Model_Query();
        $aclQuery->aclFilter->enabled = true;
        $aclQuery->aclFilter->user = $identity;
        $aclQuery->aclFilter->action = User_Model_Action_Default::EDIT();
        $isIdentityAbleToEditOrganizations = (Orga_Model_Organization::countTotal($aclQuery) > 0);
        if ($isIdentityAbleToEditOrganizations) {
            return true;
        }

        return false;
    }

    /**
     * @param User_Model_SecurityIdentity      $identity
     * @param Zend_Controller_Request_Abstract $request
     * @return bool
     */
    public function createOrganizationRule(User_Model_SecurityIdentity $identity, Zend_Controller_Request_Abstract $request)
    {
        return $this->aclService->isAllowed(
            $identity,
            User_Model_Action_Default::CREATE(),
            User_Model_Resource_Entity::loadByEntityName(Orga_Model_Organization::class)
        );
    }

    /**
     * @param User_Model_SecurityIdentity      $identity
     * @param Zend_Controller_Request_Abstract $request
     * @return bool
     */
    protected function viewOrganizationRule(User_Model_SecurityIdentity $identity, Zend_Controller_Request_Abstract $request)
    {
        return $this->aclService->isAllowed(
            $identity,
            User_Model_Action_Default::VIEW(),
            $this->getOrganization($request)
        );
    }

    /**
     * @param User_Model_SecurityIdentity      $identity
     * @param Zend_Controller_Request_Abstract $request
     * @return bool
     */
    public function editOrganizationAndCellsRule(User_Model_SecurityIdentity $identity, Zend_Controller_Request_Abstract $request)
    {
        $organization = $this->getOrganization($request);

        $isUserAllowedToEditOrganizationAndCells = $this->aclService->isAllowed(
            $identity,
            User_Model_Action_Default::EDIT(),
            $organization
        );
        if (!$isUserAllowedToEditOrganizationAndCells) {
            foreach ($organization->getGranularities() as $granularity) {
                $aclCellQuery = new Core_Model_Query();
                $aclCellQuery->aclFilter->enabled = true;
                $aclCellQuery->aclFilter->user = $identity;
                $aclCellQuery->aclFilter->action = User_Model_Action_Default::EDIT();
                $aclCellQuery->filter->addCondition(Orga_Model_Cell::QUERY_GRANULARITY, $granularity);

                $numberCellsUserCanEdit = Orga_Model_Cell::countTotal($aclCellQuery);
                if ($numberCellsUserCanEdit > 0) {
                    return true;
                }
            }
            foreach ($organization->getGranularities() as $granularity) {
                $aclCellQuery = new Core_Model_Query();
                $aclCellQuery->aclFilter->enabled = true;
                $aclCellQuery->aclFilter->user = $identity;
                $aclCellQuery->aclFilter->action = User_Model_Action_Default::ALLOW();
                $aclCellQuery->filter->addCondition(Orga_Model_Cell::QUERY_GRANULARITY, $granularity);

                $numberCellsUserCanAllow = Orga_Model_Cell::countTotal($aclCellQuery);
                if ($numberCellsUserCanAllow > 0) {
                    return true;
                }
            }
        }
        return $isUserAllowedToEditOrganizationAndCells;
    }

    /**
     * @param User_Model_SecurityIdentity      $identity
     * @param Zend_Controller_Request_Abstract $request
     * @return bool
     */
    protected function editOrganizationRule(User_Model_SecurityIdentity $identity, Zend_Controller_Request_Abstract $request)
    {
        return $this->aclService->isAllowed(
            $identity,
            User_Model_Action_Default::EDIT(),
            $this->getOrganization($request)
        );
    }

    /**
     * @param User_Model_SecurityIdentity      $identity
     * @param Zend_Controller_Request_Abstract $request
     * @return bool
     */
    public function deleteOrganizationRule(User_Model_SecurityIdentity $identity, Zend_Controller_Request_Abstract $request)
    {
        return $this->aclService->isAllowed(
            $identity,
            User_Model_Action_Default::DELETE(),
            $this->getOrganization($request)
        );
    }

    /**
     * @param Zend_Controller_Request_Abstract $request
     * @throws ForbiddenException
     * @return Orga_Model_Organization
     */
    protected function getOrganization(Zend_Controller_Request_Abstract $request)
    {
        $idOrganization = $request->getParam('idOrganization');
        if ($idOrganization !== null) {
            return Orga_Model_Organization::load($idOrganization);
        }
        $index = $request->getParam('index');
        if ($index !== null) {
            return Orga_Model_Organization::load($index);
        }
        $idCell = $request->getParam('idCell');
        if ($idCell !== null) {
            return Orga_Model_Cell::load($idCell)->getGranularity()->getOrganization();
        }

        throw new ForbiddenException();
    }

    /**
     * @param User_Model_SecurityIdentity      $identity
     * @param Zend_Controller_Request_Abstract $request
     * @return bool
     */
    public function viewMembersRule(User_Model_SecurityIdentity $identity, Zend_Controller_Request_Abstract $request)
    {
        return ($this->viewOrganizationRule($identity, $request) || $this->editCellRule($identity, $request));
    }

    /**
     * @param User_Model_SecurityIdentity      $identity
     * @param Zend_Controller_Request_Abstract $request
     * @return bool
     */
    public function editMembersRule(User_Model_SecurityIdentity $identity, Zend_Controller_Request_Abstract $request)
    {
        return (
            $this->editOrganizationRule($identity, $request)
            || $this->aclService->isAllowed(
                $identity,
                User_Model_Action_Default::EDIT(),
                Orga_Model_Cell::load($request->getParam('idCell'))
            )
        );
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
            Orga_Model_Cell::load($request->getParam('idCell'))
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
            Orga_Action_Cell::INPUT(),
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
            Orga_Action_Cell::COMMENT(),
            $this->getCell($request)
        );
    }

    /**
     * @param User_Model_SecurityIdentity      $identity
     * @param Zend_Controller_Request_Abstract $request
     * @return bool
     */
    protected function editCommentRule(User_Model_SecurityIdentity $identity, Zend_Controller_Request_Abstract $request)
    {
        $comment = Social_Model_Comment::load($request->getParam('id'));

        return $this->aclService->isAllowed(
            $identity,
            User_Model_Action_Default::EDIT(),
            $comment
        );
    }

    /**
     * @param User_Model_SecurityIdentity      $identity
     * @param Zend_Controller_Request_Abstract $request
     * @return bool
     */
    protected function deleteCommentRule(User_Model_SecurityIdentity $identity, Zend_Controller_Request_Abstract $request)
    {
        $comment = Social_Model_Comment::load($request->getParam('id'));

        return $this->aclService->isAllowed(
            $identity,
            User_Model_Action_Default::DELETE(),
            $comment
        );
    }

    /**
     * @param Zend_Controller_Request_Abstract $request
     * @throws ForbiddenException
     * @return Orga_Model_Cell
     */
    protected function getCell(Zend_Controller_Request_Abstract $request)
    {
        $index = $request->getParam('index');
        if ($index !== null) {
            try {
                return Orga_Model_Cell::load($index);
            } catch (Core_Exception_NotFound $e) {
                // Pas une cellule.
            }
        }
        $idCell = $request->getParam('idCell');
        if ($idCell !== null) {
            return Orga_Model_Cell::load($idCell);
        }

        throw new ForbiddenException();
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
        } catch (ForbiddenException $e) {
            return $this->viewOrganizationRule($identity, $request);
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
        } catch (ForbiddenException $e) {
            return $this->editOrganizationRule($identity, $request);
        } catch (Core_Exception_NotFound $e) {
            return $this->editOrganizationRule($identity, $request);
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
            $isAllowed = $this->aclService->isAllowed(
                $identity,
                User_Model_Action_Default::VIEW(),
                User_Model_Resource_Entity::loadByEntity(DW_Model_Report::load($idReport))
            );
            if ($isAllowed) {
                return $isAllowed;
            } else {
                return $this->viewCellRule($identity, $request);
            }
        }

        $idCube = $request->getParam('idCube');
        if ($idCube !== null) {
            $dWCube = DW_Model_Cube::load($idCube);
            // Si le DWCube est d'un Granularity, vérification que l'utilisateur peut configurer le projet.
            try {
                $granularity = Orga_Model_Granularity::loadByDWCube($dWCube);
                $organization = $granularity->getOrganization();
                $request->setParam('idOrganization', $organization->getKey()['id']);
                return $this->editOrganizationRule($identity, $request);
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

        $idCell = $request->getParam('idCube');
        if ($idCell !== null) {

        }

        throw new ForbiddenException();
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
            User_Model_Resource_Entity::loadByEntity(DW_Model_Report::load($idReport))
        );
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
            $scenario = Simulation_Model_Scenario::load($idScenario);
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
    protected function deleteKeywordRule(User_Model_SecurityIdentity $identity, Zend_Controller_Request_Abstract $request)
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
        $library = Library::load($idLibrary);

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

        throw new ForbiddenException();
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
