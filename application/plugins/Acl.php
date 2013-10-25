<?php
/**
 * @author valentin.claras
 * @package Inventory
 * @subpackage Plugin
 */

use Doc\Domain\Library;
use User\Application\ForbiddenException;
use User\Application\Plugin\ACLPlugin;
use User\Domain\ACL\Action\DefaultAction;
use User\Domain\ACL\Resource\EntityResource;
use User\Domain\ACL\Resource\NamedResource;
use User\Domain\ACL\SecurityIdentity;

/**
 * Plugin pour la vérification des ACL
 *
 * @package Configuration
 * @subpackage Plugin
 * @uses AbstractACLPlugin
 */
class Inventory_Plugin_Acl extends ACLPlugin
{
    /**
     * @param SecurityIdentity      $identity
     * @param Zend_Controller_Request_Abstract $request
     * @return bool
     */
    public function viewOrganizationsRule(SecurityIdentity $identity, Zend_Controller_Request_Abstract $request)
    {
        $isIdentityAbleToEditOrganizations = $this->editOrganizationsRule($identity, $request);
        if ($isIdentityAbleToEditOrganizations) {
            return true;
        }

        $aclQuery = new Core_Model_Query();
        $aclQuery->aclFilter->enabled = true;
        $aclQuery->aclFilter->user = $identity;
        $aclQuery->aclFilter->action = DefaultAction::VIEW();
        $isIdentityAbleToSeeManyOrganizations = (Orga_Model_Organization::countTotal($aclQuery) > 1);
        if ($isIdentityAbleToSeeManyOrganizations) {
            return true;
        }

        return false;
    }

    /**
     * @param SecurityIdentity      $identity
     * @param Zend_Controller_Request_Abstract $request
     * @return bool
     */
    public function editOrganizationsRule(SecurityIdentity $identity, Zend_Controller_Request_Abstract $request)
    {
        $organizationResource = EntityResource::loadByEntityName(Orga_Model_Organization::class);

        $isIdentityAbleToCreateOrganizations = $this->aclService->isAllowed(
            $identity,
            DefaultAction::CREATE(),
            $organizationResource
        );
        if ($isIdentityAbleToCreateOrganizations) {
            return true;
        }

        $aclQuery = new Core_Model_Query();
        $aclQuery->aclFilter->enabled = true;
        $aclQuery->aclFilter->user = $identity;
        $aclQuery->aclFilter->action = DefaultAction::EDIT();
        $isIdentityAbleToEditOrganizations = (Orga_Model_Organization::countTotal($aclQuery) > 0);
        if ($isIdentityAbleToEditOrganizations) {
            return true;
        }

        return false;
    }

    /**
     * @param SecurityIdentity      $identity
     * @param Zend_Controller_Request_Abstract $request
     * @return bool
     */
    public function createOrganizationRule(SecurityIdentity $identity, Zend_Controller_Request_Abstract $request)
    {
        return $this->aclService->isAllowed(
            $identity,
            DefaultAction::CREATE(),
            EntityResource::loadByEntityName(Orga_Model_Organization::class)
        );
    }

    /**
     * @param SecurityIdentity      $identity
     * @param Zend_Controller_Request_Abstract $request
     * @return bool
     */
    protected function viewOrganizationRule(SecurityIdentity $identity, Zend_Controller_Request_Abstract $request)
    {
        return $this->aclService->isAllowed(
            $identity,
            DefaultAction::VIEW(),
            $this->getOrganization($request)
        );
    }

    /**
     * @param SecurityIdentity      $identity
     * @param Zend_Controller_Request_Abstract $request
     * @return bool
     */
    protected function editOrganizationRule(SecurityIdentity $identity, Zend_Controller_Request_Abstract $request)
    {
        return $this->aclService->isAllowed(
            $identity,
            DefaultAction::EDIT(),
            $this->getOrganization($request)
        );
    }

    /**
     * @param SecurityIdentity      $identity
     * @param Zend_Controller_Request_Abstract $request
     * @return bool
     */
    public function deleteOrganizationRule(SecurityIdentity $identity, Zend_Controller_Request_Abstract $request)
    {
        return $this->aclService->isAllowed(
            $identity,
            DefaultAction::DELETE(),
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
     * @param SecurityIdentity      $identity
     * @param Zend_Controller_Request_Abstract $request
     * @return bool
     */
    public function viewMembersRule(SecurityIdentity $identity, Zend_Controller_Request_Abstract $request)
    {
        return ($this->viewOrganizationRule($identity, $request) || $this->editCellRule($identity, $request));
    }

    /**
     * @param SecurityIdentity      $identity
     * @param Zend_Controller_Request_Abstract $request
     * @return bool
     */
    public function editMembersRule(SecurityIdentity $identity, Zend_Controller_Request_Abstract $request)
    {
        return (
            $this->editOrganizationRule($identity, $request)
            || $this->aclService->isAllowed(
                $identity,
                DefaultAction::EDIT(),
                Orga_Model_Cell::load($request->getParam('idCell'))
            )
        );
    }

    /**
     * @param SecurityIdentity      $identity
     * @param Zend_Controller_Request_Abstract $request
     * @return bool
     */
    protected function viewCellRule(SecurityIdentity $identity, Zend_Controller_Request_Abstract $request)
    {
        return $this->aclService->isAllowed(
            $identity,
            DefaultAction::VIEW(),
            $this->getCell($request)
        );
    }

    /**
     * @param SecurityIdentity      $identity
     * @param Zend_Controller_Request_Abstract $request
     * @return bool
     */
    protected function editCellRule(SecurityIdentity $identity, Zend_Controller_Request_Abstract $request)
    {
        return $this->aclService->isAllowed(
            $identity,
            DefaultAction::EDIT(),
            $this->getCell($request)
        );
    }

    /**
     * @param SecurityIdentity      $identity
     * @param Zend_Controller_Request_Abstract $request
     * @return bool
     */
    protected function allowCellRule(SecurityIdentity $identity, Zend_Controller_Request_Abstract $request)
    {
        return $this->aclService->isAllowed(
            $identity,
            DefaultAction::ALLOW(),
            Orga_Model_Cell::load($request->getParam('idCell'))
        );
    }

    /**
     * @param SecurityIdentity      $identity
     * @param Zend_Controller_Request_Abstract $request
     * @return bool
     */
    protected function inputCellRule(SecurityIdentity $identity, Zend_Controller_Request_Abstract $request)
    {
        return $this->aclService->isAllowed(
            $identity,
            Orga_Action_Cell::INPUT(),
            $this->getCell($request)
        );
    }

    /**
     * @param SecurityIdentity      $identity
     * @param Zend_Controller_Request_Abstract $request
     * @return bool
     */
    protected function commentCellRule(SecurityIdentity $identity, Zend_Controller_Request_Abstract $request)
    {
        return $this->aclService->isAllowed(
            $identity,
            Orga_Action_Cell::COMMENT(),
            $this->getCell($request)
        );
    }

    /**
     * @param SecurityIdentity      $identity
     * @param Zend_Controller_Request_Abstract $request
     * @return bool
     */
    protected function editCommentRule(SecurityIdentity $identity, Zend_Controller_Request_Abstract $request)
    {
        $comment = Social_Model_Comment::load($request->getParam('id'));

        return $this->aclService->isAllowed(
            $identity,
            DefaultAction::EDIT(),
            $comment
        );
    }

    /**
     * @param SecurityIdentity      $identity
     * @param Zend_Controller_Request_Abstract $request
     * @return bool
     */
    protected function deleteCommentRule(SecurityIdentity $identity, Zend_Controller_Request_Abstract $request)
    {
        $comment = Social_Model_Comment::load($request->getParam('id'));

        return $this->aclService->isAllowed(
            $identity,
            DefaultAction::DELETE(),
            $comment
        );
    }

    /**
     * @param Zend_Controller_Request_Abstract $request
     * @throws \User\Application\ForbiddenException
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
     * @param SecurityIdentity      $identity
     * @param Zend_Controller_Request_Abstract $request
     * @return bool
     */
    protected function viewOrgaCubeRule(SecurityIdentity $identity, Zend_Controller_Request_Abstract $request)
    {
        try {
            return $this->viewCellRule($identity, $request);
        } catch (ForbiddenException $e) {
            return $this->viewOrganizationRule($identity, $request);
        }
    }

    /**
     * @param SecurityIdentity      $identity
     * @param Zend_Controller_Request_Abstract $request
     * @return bool
     */
    protected function editOrgaCubeRule(SecurityIdentity $identity, Zend_Controller_Request_Abstract $request)
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
     * @param SecurityIdentity      $identity
     * @param Zend_Controller_Request_Abstract $request
     * @return bool
     */
    protected function viewReportRule(SecurityIdentity $identity, Zend_Controller_Request_Abstract $request)
    {
        $idReport = $request->getParam('idReport');
        if ($idReport !== null) {
            $isAllowed = $this->aclService->isAllowed(
                $identity,
                DefaultAction::VIEW(),
                EntityResource::loadByEntity(DW_Model_Report::load($idReport))
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
     * @param SecurityIdentity      $identity
     * @param Zend_Controller_Request_Abstract $request
     * @return bool
     */
    protected function editReportRule(SecurityIdentity $identity, Zend_Controller_Request_Abstract $request)
    {
        return $this->viewReportRule($identity, $request);
    }

    /**
     * @param SecurityIdentity      $identity
     * @param Zend_Controller_Request_Abstract $request
     * @return bool
     */
    protected function deleteReportRule(SecurityIdentity $identity, Zend_Controller_Request_Abstract $request)
    {
        $idReport = $request->getParam('index');
        return $this->aclService->isAllowed(
            $identity,
            DefaultAction::DELETE(),
            EntityResource::loadByEntity(DW_Model_Report::load($idReport))
        );
    }

    /**
     * @param SecurityIdentity      $identity
     * @param Zend_Controller_Request_Abstract $request
     * @return bool
     */
    protected function viewDWCubeRule(SecurityIdentity $identity, Zend_Controller_Request_Abstract $request)
    {
        return $this->viewReportRule($identity, $request);
    }

    /**
     * @param SecurityIdentity      $identity
     * @param Zend_Controller_Request_Abstract $request
     * @return bool
     */
    protected function viewInputAFRule(SecurityIdentity $identity, Zend_Controller_Request_Abstract $request)
    {
        $idCell = $request->getParam('idCell');
        if ($idCell !== null) {
            return $this->viewCellRule($identity, $request);
        }

        return $this->genericInputAF($identity, $request);
    }

    /**
     * @param SecurityIdentity      $identity
     * @param Zend_Controller_Request_Abstract $request
     * @return bool
     */
    protected function editInputAFRule(SecurityIdentity $identity, Zend_Controller_Request_Abstract $request)
    {
        $idCell = $request->getParam('idCell');
        if ($idCell !== null) {
            return $this->inputCellRule($identity, $request);
        }

        return $this->genericInputAF($identity, $request);
    }

    /**
     * @param SecurityIdentity      $identity
     * @param Zend_Controller_Request_Abstract $request
     * @return bool
     */
    protected function modeInputAFRule(SecurityIdentity $identity, Zend_Controller_Request_Abstract $request)
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
     * @param SecurityIdentity      $identity
     * @param Zend_Controller_Request_Abstract $request
     * @return bool
     */
    protected function genericInputAF(SecurityIdentity $identity, Zend_Controller_Request_Abstract $request)
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
     * @param SecurityIdentity      $identity
     * @param Zend_Controller_Request_Abstract $request
     * @return bool
     */
    protected function editAFRule(SecurityIdentity $identity, Zend_Controller_Request_Abstract $request)
    {
        return $this->editReferential($identity);
    }

    /**
     * @param SecurityIdentity      $identity
     * @param Zend_Controller_Request_Abstract $request
     * @return bool
     */
    protected function viewTECRule(SecurityIdentity $identity, Zend_Controller_Request_Abstract $request)
    {
        return $this->editReferential($identity);
    }

    /**
     * @param SecurityIdentity      $identity
     * @param Zend_Controller_Request_Abstract $request
     * @return bool
     */
    protected function viewClassifRule(SecurityIdentity $identity, Zend_Controller_Request_Abstract $request)
    {
        return $this->viewReferential($identity);
    }

    /**
     * @param SecurityIdentity      $identity
     * @param Zend_Controller_Request_Abstract $request
     * @return bool
     */
    protected function editClassifRule(SecurityIdentity $identity, Zend_Controller_Request_Abstract $request)
    {
        return $this->editReferential($identity);
    }

    /**
     * @param SecurityIdentity      $identity
     * @param Zend_Controller_Request_Abstract $request
     * @return bool
     */
    protected function viewTechnoRule(SecurityIdentity $identity, Zend_Controller_Request_Abstract $request)
    {
        return $this->loggedInRule($identity, $request);
    }

    /**
     * @param SecurityIdentity      $identity
     * @param Zend_Controller_Request_Abstract $request
     * @return bool
     */
    protected function editTechnoRule(SecurityIdentity $identity, Zend_Controller_Request_Abstract $request)
    {
        return $this->editReferential($identity);
    }

    /**
     * @param SecurityIdentity      $identity
     * @param Zend_Controller_Request_Abstract $request
     * @return bool
     */
    protected function viewKeywordRule(SecurityIdentity $identity, Zend_Controller_Request_Abstract $request)
    {
        return $this->viewReferential($identity);
    }

    /**
     * @param SecurityIdentity      $identity
     * @param Zend_Controller_Request_Abstract $request
     * @return bool
     */
    protected function editKeywordRule(SecurityIdentity $identity, Zend_Controller_Request_Abstract $request)
    {
        return $this->editReferential($identity);
    }

    /**
     * @param SecurityIdentity      $identity
     * @param Zend_Controller_Request_Abstract $request
     * @return bool
     */
    protected function deleteKeywordRule(SecurityIdentity $identity, Zend_Controller_Request_Abstract $request)
    {
        return $this->editReferential($identity);
    }

    /**
     * @param SecurityIdentity      $identity
     * @param Zend_Controller_Request_Abstract $request
     * @return bool
     */
    protected function viewUnitRule(SecurityIdentity $identity, Zend_Controller_Request_Abstract $request)
    {
        return $this->loggedInRule($identity, $request);
    }

    /**
     * @param SecurityIdentity      $identity
     * @param Zend_Controller_Request_Abstract $request
     * @return bool
     */
    protected function viewReferential(SecurityIdentity $identity)
    {
        return $this->aclService->isAllowed(
            $identity,
            DefaultAction::VIEW(),
            NamedResource::loadByName('referential')
        );
    }

    /**
     * @param SecurityIdentity      $identity
     * @param Zend_Controller_Request_Abstract $request
     * @return bool
     */
    protected function editReferential(SecurityIdentity $identity)
    {
        return $this->aclService->isAllowed(
            $identity,
            DefaultAction::EDIT(),
            NamedResource::loadByName('referential')
        );
    }

    /**
     * @param SecurityIdentity      $identity
     * @param Zend_Controller_Request_Abstract $request
     * @return bool
     */
    protected function viewLibraryRule(SecurityIdentity $identity, Zend_Controller_Request_Abstract $request)
    {
        return true;
    }

    /**
     * @param SecurityIdentity      $identity
     * @param Zend_Controller_Request_Abstract $request
     * @return bool
     */
    protected function editLibraryRule(SecurityIdentity $identity, Zend_Controller_Request_Abstract $request)
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
     * @param SecurityIdentity      $identity
     * @param Zend_Controller_Request_Abstract $request
     * @return bool
     */
    protected function problemToSolveRule(SecurityIdentity $identity, Zend_Controller_Request_Abstract $request)
    {
        return $this->loggedInRule($identity, $request);
    }

    /**
     * @param SecurityIdentity      $identity
     * @param Zend_Controller_Request_Abstract $request
     * @return bool
     */
    protected function viewBibliographyRule(SecurityIdentity $identity, Zend_Controller_Request_Abstract $request)
    {
        return $this->problemToSolveRule($identity, $request);
    }

    /**
     * @param SecurityIdentity      $identity
     * @param Zend_Controller_Request_Abstract $request
     * @return bool
     */
    protected function editBibliographyRule(SecurityIdentity $identity, Zend_Controller_Request_Abstract $request)
    {
        return $this->problemToSolveRule($identity, $request);
    }

    /**
     * @param SecurityIdentity      $identity
     * @param Zend_Controller_Request_Abstract $request
     * @return bool
     */
    protected function viewDocumentRule(SecurityIdentity $identity, Zend_Controller_Request_Abstract $request)
    {
        return $this->problemToSolveRule($identity, $request);
    }

    /**
     * @param SecurityIdentity      $identity
     * @param Zend_Controller_Request_Abstract $request
     * @return bool
     */
    protected function adminThemesRule(SecurityIdentity $identity, Zend_Controller_Request_Abstract $request)
    {
        return $this->problemToSolveRule($identity, $request);
    }

    /**
     * @param SecurityIdentity      $identity
     * @param Zend_Controller_Request_Abstract $request
     * @return bool
     */
    protected function viewGenericActionsRule(SecurityIdentity $identity, Zend_Controller_Request_Abstract $request)
    {
        return $this->problemToSolveRule($identity, $request);
    }

    /**
     * @param SecurityIdentity      $identity
     * @param Zend_Controller_Request_Abstract $request
     * @return bool
     */
    protected function viewGenericActionRule(SecurityIdentity $identity, Zend_Controller_Request_Abstract $request)
    {
        return $this->problemToSolveRule($identity, $request);
    }

    /**
     * @param SecurityIdentity      $identity
     * @param Zend_Controller_Request_Abstract $request
     * @return bool
     */
    protected function addGenericActionRule(SecurityIdentity $identity, Zend_Controller_Request_Abstract $request)
    {
        return $this->problemToSolveRule($identity, $request);
    }

    /**
     * @param SecurityIdentity      $identity
     * @param Zend_Controller_Request_Abstract $request
     * @return bool
     */
    protected function editGenericActionRule(SecurityIdentity $identity, Zend_Controller_Request_Abstract $request)
    {
        return $this->problemToSolveRule($identity, $request);
    }

    /**
     * @param SecurityIdentity      $identity
     * @param Zend_Controller_Request_Abstract $request
     * @return bool
     */
    protected function commentGenericActionRule(SecurityIdentity $identity, Zend_Controller_Request_Abstract $request)
    {
        return $this->problemToSolveRule($identity, $request);
    }

    /**
     * @param SecurityIdentity      $identity
     * @param Zend_Controller_Request_Abstract $request
     * @return bool
     */
    protected function deleteGenericActionRule(SecurityIdentity $identity, Zend_Controller_Request_Abstract $request)
    {
        return $this->problemToSolveRule($identity, $request);
    }

    /**
     * @param SecurityIdentity      $identity
     * @param Zend_Controller_Request_Abstract $request
     * @return bool
     */
    protected function viewContextActionsRule(SecurityIdentity $identity, Zend_Controller_Request_Abstract $request)
    {
        return $this->problemToSolveRule($identity, $request);
    }

    /**
     * @param SecurityIdentity      $identity
     * @param Zend_Controller_Request_Abstract $request
     * @return bool
     */
    protected function viewContextActionRule(SecurityIdentity $identity, Zend_Controller_Request_Abstract $request)
    {
        return $this->problemToSolveRule($identity, $request);
    }

    /**
     * @param SecurityIdentity      $identity
     * @param Zend_Controller_Request_Abstract $request
     * @return bool
     */
    protected function addContextActionRule(SecurityIdentity $identity, Zend_Controller_Request_Abstract $request)
    {
        return $this->problemToSolveRule($identity, $request);
    }

    /**
     * @param SecurityIdentity      $identity
     * @param Zend_Controller_Request_Abstract $request
     * @return bool
     */
    protected function editContextActionRule(SecurityIdentity $identity, Zend_Controller_Request_Abstract $request)
    {
        return $this->problemToSolveRule($identity, $request);
    }

    /**
     * @param SecurityIdentity      $identity
     * @param Zend_Controller_Request_Abstract $request
     * @return bool
     */
    protected function commentContextActionRule(SecurityIdentity $identity, Zend_Controller_Request_Abstract $request)
    {
        return $this->problemToSolveRule($identity, $request);
    }

    /**
     * @param SecurityIdentity      $identity
     * @param Zend_Controller_Request_Abstract $request
     * @return bool
     */
    protected function deleteContextActionRule(SecurityIdentity $identity, Zend_Controller_Request_Abstract $request)
    {
        return $this->problemToSolveRule($identity, $request);
    }

}
