<?php

use Doc\Domain\Library;
use User\Application\ForbiddenException;
use User\Application\Plugin\ACLPlugin;
use User\Domain\ACL\Action;
use User\Domain\ACL\Resource\NamedResource;
use User\Domain\User;

/**
 * Plugin pour la vérification des ACL
 *
 * @author valentin.claras
 * @uses AbstractACLPlugin
 */
class Inventory_Plugin_Acl extends ACLPlugin
{
    public function viewOrganizationsRule(User $identity, Zend_Controller_Request_Abstract $request)
    {
        $isIdentityAbleToEditOrganizations = $this->editOrganizationsRule($identity, $request);
        if ($isIdentityAbleToEditOrganizations) {
            return true;
        }

        $aclQuery = new Core_Model_Query();
        $aclQuery->aclFilter->enabled = true;
        $aclQuery->aclFilter->user = $identity;
        $aclQuery->aclFilter->action = Action::VIEW();
        $isIdentityAbleToSeeManyOrganizations = (Orga_Model_Organization::countTotal($aclQuery) > 1);
        if ($isIdentityAbleToSeeManyOrganizations) {
            return true;
        }

        return false;
    }

    public function editOrganizationsRule(User $identity)
    {
        $organizationResource = NamedResource::loadByName(Orga_Model_Organization::class);

        $isIdentityAbleToCreateOrganizations = $this->aclService->isAllowed(
            $identity,
            Action::CREATE(),
            $organizationResource
        );
        if ($isIdentityAbleToCreateOrganizations) {
            return true;
        }

        $aclQuery = new Core_Model_Query();
        $aclQuery->aclFilter->enabled = true;
        $aclQuery->aclFilter->user = $identity;
        $aclQuery->aclFilter->action = Action::EDIT();
        $isIdentityAbleToEditOrganizations = (Orga_Model_Organization::countTotal($aclQuery) > 0);
        if ($isIdentityAbleToEditOrganizations) {
            return true;
        }

        return false;
    }

    public function createOrganizationRule(User $identity)
    {
        return $this->aclService->isAllowed(
            $identity,
            Action::CREATE(),
            NamedResource::loadByName(Orga_Model_Organization::class)
        );
    }

    /**
     * @param User      $identity
     * @param Zend_Controller_Request_Abstract $request
     * @return bool
     */
    protected function viewOrganizationRule(User $identity, Zend_Controller_Request_Abstract $request)
    {
        return $this->aclService->isAllowed(
            $identity,
            Action::VIEW(),
            $this->getOrganization($request)
        );
    }

    /**
     * @param User      $identity
     * @param Zend_Controller_Request_Abstract $request
     * @return bool
     */
    protected function editOrganizationRule(User $identity, Zend_Controller_Request_Abstract $request)
    {
        return $this->aclService->isAllowed(
            $identity,
            Action::EDIT(),
            $this->getOrganization($request)
        );
    }

    /**
     * @param User      $identity
     * @param Zend_Controller_Request_Abstract $request
     * @return bool
     */
    public function deleteOrganizationRule(User $identity, Zend_Controller_Request_Abstract $request)
    {
        return $this->aclService->isAllowed(
            $identity,
            Action::DELETE(),
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
     * @param User      $identity
     * @param Zend_Controller_Request_Abstract $request
     * @return bool
     */
    public function viewMembersRule(User $identity, Zend_Controller_Request_Abstract $request)
    {
        return ($this->viewOrganizationRule($identity, $request) || $this->editCellRule($identity, $request));
    }

    /**
     * @param User      $identity
     * @param Zend_Controller_Request_Abstract $request
     * @return bool
     */
    public function editMembersRule(User $identity, Zend_Controller_Request_Abstract $request)
    {
        return (
            $this->editOrganizationRule($identity, $request)
            || $this->aclService->isAllowed(
                $identity,
                Action::EDIT(),
                Orga_Model_Cell::load($request->getParam('idCell'))
            )
        );
    }

    /**
     * @param User      $identity
     * @param Zend_Controller_Request_Abstract $request
     * @return bool
     */
    protected function viewCellRule(User $identity, Zend_Controller_Request_Abstract $request)
    {
        return $this->aclService->isAllowed(
            $identity,
            Action::VIEW(),
            $this->getCell($request)
        );
    }

    /**
     * @param User      $identity
     * @param Zend_Controller_Request_Abstract $request
     * @return bool
     */
    protected function editCellRule(User $identity, Zend_Controller_Request_Abstract $request)
    {
        return $this->aclService->isAllowed(
            $identity,
            Action::EDIT(),
            $this->getCell($request)
        );
    }

    /**
     * @param User      $identity
     * @param Zend_Controller_Request_Abstract $request
     * @return bool
     */
    protected function allowCellRule(User $identity, Zend_Controller_Request_Abstract $request)
    {
        return $this->aclService->isAllowed(
            $identity,
            Action::ALLOW(),
            Orga_Model_Cell::load($request->getParam('idCell'))
        );
    }

    /**
     * @param User      $identity
     * @param Zend_Controller_Request_Abstract $request
     * @return bool
     */
    protected function inputCellRule(User $identity, Zend_Controller_Request_Abstract $request)
    {
        return $this->aclService->isAllowed(
            $identity,
            Orga_Action_Cell::INPUT(),
            $this->getCell($request)
        );
    }

    /**
     * @param User      $identity
     * @param Zend_Controller_Request_Abstract $request
     * @return bool
     */
    protected function commentCellRule(User $identity, Zend_Controller_Request_Abstract $request)
    {
        return $this->aclService->isAllowed(
            $identity,
            Orga_Action_Cell::COMMENT(),
            $this->getCell($request)
        );
    }

    /**
     * @param User      $identity
     * @param Zend_Controller_Request_Abstract $request
     * @return bool
     */
    protected function editCommentRule(User $identity, Zend_Controller_Request_Abstract $request)
    {
        $comment = Social_Model_Comment::load($request->getParam('id'));

        return $identity === $comment->getAuthor();
    }

    /**
     * @param User      $identity
     * @param Zend_Controller_Request_Abstract $request
     * @return bool
     */
    protected function deleteCommentRule(User $identity, Zend_Controller_Request_Abstract $request)
    {
        $comment = Social_Model_Comment::load($request->getParam('id'));

        return $identity === $comment->getAuthor();
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
     * @param User      $identity
     * @param Zend_Controller_Request_Abstract $request
     * @return bool
     */
    protected function viewOrgaCubeRule(User $identity, Zend_Controller_Request_Abstract $request)
    {
        try {
            return $this->viewCellRule($identity, $request);
        } catch (ForbiddenException $e) {
            return $this->viewOrganizationRule($identity, $request);
        }
    }

    /**
     * @param User      $identity
     * @param Zend_Controller_Request_Abstract $request
     * @return bool
     */
    protected function editOrgaCubeRule(User $identity, Zend_Controller_Request_Abstract $request)
    {
        try {
            return $this->editCellRule($identity, $request);
        } catch (ForbiddenException $e) {
            return $this->editOrganizationRule($identity, $request);
        } catch (Core_Exception_NotFound $e) {
            return $this->editOrganizationRule($identity, $request);
        }
    }

    protected function viewReportRule(User $identity, Zend_Controller_Request_Abstract $request)
    {
        $idReport = $request->getParam('idReport');
        if ($idReport !== null) {
            $isAllowed = $this->aclService->isAllowed($identity, Action::VIEW(), DW_Model_Report::load($idReport));
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
     * @param User      $identity
     * @param Zend_Controller_Request_Abstract $request
     * @return bool
     */
    protected function editReportRule(User $identity, Zend_Controller_Request_Abstract $request)
    {
        return $this->viewReportRule($identity, $request);
    }

    /**
     * @param User      $identity
     * @param Zend_Controller_Request_Abstract $request
     * @return bool
     */
    protected function deleteReportRule(User $identity, Zend_Controller_Request_Abstract $request)
    {
        $idReport = $request->getParam('index');
        return $this->aclService->isAllowed($identity, Action::DELETE(), DW_Model_Report::load($idReport));
    }

    /**
     * @param User      $identity
     * @param Zend_Controller_Request_Abstract $request
     * @return bool
     */
    protected function viewDWCubeRule(User $identity, Zend_Controller_Request_Abstract $request)
    {
        return $this->viewReportRule($identity, $request);
    }

    /**
     * @param User      $identity
     * @param Zend_Controller_Request_Abstract $request
     * @return bool
     */
    protected function viewInputAFRule(User $identity, Zend_Controller_Request_Abstract $request)
    {
        $idCell = $request->getParam('idCell');
        if ($idCell !== null) {
            return $this->viewCellRule($identity, $request);
        }

        return $this->genericInputAF($identity, $request);
    }

    /**
     * @param User      $identity
     * @param Zend_Controller_Request_Abstract $request
     * @return bool
     */
    protected function editInputAFRule(User $identity, Zend_Controller_Request_Abstract $request)
    {
        $idCell = $request->getParam('idCell');
        if ($idCell !== null) {
            return $this->inputCellRule($identity, $request);
        }

        return $this->genericInputAF($identity, $request);
    }

    /**
     * @param User      $identity
     * @param Zend_Controller_Request_Abstract $request
     * @return bool
     */
    protected function modeInputAFRule(User $identity, Zend_Controller_Request_Abstract $request)
    {
        $idCell = $request->getParam('idCell');
        if ($idCell !== null) {
            $mode = $request->getParam('mode');
            if ($mode == AF_ViewConfiguration::MODE_READ) {
                return $this->viewCellRule($identity, $request);
            } elseif ($mode == AF_ViewConfiguration::MODE_WRITE) {
                return $this->inputCellRule($identity, $request);
            }

        }

        return $this->genericInputAF($identity, $request);
    }

    /**
     * @param User      $identity
     * @param Zend_Controller_Request_Abstract $request
     * @return bool
     */
    protected function genericInputAF(User $identity, Zend_Controller_Request_Abstract $request)
    {
        $idScenario = $request->getParam('idScenario');
        if ($idScenario !== null) {
            /* @var Simulation_Model_Scenario $scenario */
            $scenario = Simulation_Model_Scenario::load($idScenario);
            return ($scenario->getSet()->getUser() === $identity);
        }

        return $this->editAFRule($identity, $request);
    }

    protected function editAFRule(User $identity)
    {
        return $this->editRepository($identity);
    }

    protected function viewTECRule(User $identity)
    {
        return $this->editRepository($identity);
    }

    protected function viewClassifRule(User $identity)
    {
        return $this->viewReferential($identity);
    }

    protected function editClassifRule(User $identity)
    {
        return $this->editRepository($identity);
    }

    protected function viewTechnoRule(User $identity, Zend_Controller_Request_Abstract $request)
    {
        return $this->loggedInRule($identity, $request);
    }

    protected function editTechnoRule(User $identity)
    {
        return $this->editRepository($identity);
    }

    protected function viewKeywordRule(User $identity)
    {
        return $this->viewReferential($identity);
    }

    protected function editKeywordRule(User $identity)
    {
        return $this->editRepository($identity);
    }

    protected function deleteKeywordRule(User $identity)
    {
        return $this->editRepository($identity);
    }

    protected function viewUnitRule(User $identity, Zend_Controller_Request_Abstract $request)
    {
        return $this->loggedInRule($identity, $request);
    }

    protected function viewReferential(User $identity)
    {
        return $this->aclService->isAllowed(
            $identity,
            Action::VIEW(),
            NamedResource::loadByName('repository')
        );
    }

    protected function editRepository(User $identity)
    {
        return $this->aclService->isAllowed(
            $identity,
            Action::EDIT(),
            NamedResource::loadByName('repository')
        );
    }

    protected function viewLibraryRule()
    {
        return true;
    }

    protected function editLibraryRule()
    {
        return true;
    }

    /**
     * @param Zend_Controller_Request_Abstract $request
     * @throws ForbiddenException
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
     * @param User      $identity
     * @param Zend_Controller_Request_Abstract $request
     * @return bool
     */
    protected function problemToSolveRule(User $identity, Zend_Controller_Request_Abstract $request)
    {
        return $this->loggedInRule($identity, $request);
    }

    /**
     * @param User      $identity
     * @param Zend_Controller_Request_Abstract $request
     * @return bool
     */
    protected function viewBibliographyRule(User $identity, Zend_Controller_Request_Abstract $request)
    {
        return $this->problemToSolveRule($identity, $request);
    }

    /**
     * @param User      $identity
     * @param Zend_Controller_Request_Abstract $request
     * @return bool
     */
    protected function editBibliographyRule(User $identity, Zend_Controller_Request_Abstract $request)
    {
        return $this->problemToSolveRule($identity, $request);
    }

    /**
     * @param User      $identity
     * @param Zend_Controller_Request_Abstract $request
     * @return bool
     */
    protected function viewDocumentRule(User $identity, Zend_Controller_Request_Abstract $request)
    {
        return $this->problemToSolveRule($identity, $request);
    }

    /**
     * @param User      $identity
     * @param Zend_Controller_Request_Abstract $request
     * @return bool
     */
    protected function adminThemesRule(User $identity, Zend_Controller_Request_Abstract $request)
    {
        return $this->problemToSolveRule($identity, $request);
    }

    /**
     * @param User      $identity
     * @param Zend_Controller_Request_Abstract $request
     * @return bool
     */
    protected function viewGenericActionsRule(User $identity, Zend_Controller_Request_Abstract $request)
    {
        return $this->problemToSolveRule($identity, $request);
    }

    /**
     * @param User      $identity
     * @param Zend_Controller_Request_Abstract $request
     * @return bool
     */
    protected function viewGenericActionRule(User $identity, Zend_Controller_Request_Abstract $request)
    {
        return $this->problemToSolveRule($identity, $request);
    }

    /**
     * @param User      $identity
     * @param Zend_Controller_Request_Abstract $request
     * @return bool
     */
    protected function addGenericActionRule(User $identity, Zend_Controller_Request_Abstract $request)
    {
        return $this->problemToSolveRule($identity, $request);
    }

    /**
     * @param User      $identity
     * @param Zend_Controller_Request_Abstract $request
     * @return bool
     */
    protected function editGenericActionRule(User $identity, Zend_Controller_Request_Abstract $request)
    {
        return $this->problemToSolveRule($identity, $request);
    }

    /**
     * @param User      $identity
     * @param Zend_Controller_Request_Abstract $request
     * @return bool
     */
    protected function commentGenericActionRule(User $identity, Zend_Controller_Request_Abstract $request)
    {
        return $this->problemToSolveRule($identity, $request);
    }

    /**
     * @param User      $identity
     * @param Zend_Controller_Request_Abstract $request
     * @return bool
     */
    protected function deleteGenericActionRule(User $identity, Zend_Controller_Request_Abstract $request)
    {
        return $this->problemToSolveRule($identity, $request);
    }

    /**
     * @param User      $identity
     * @param Zend_Controller_Request_Abstract $request
     * @return bool
     */
    protected function viewContextActionsRule(User $identity, Zend_Controller_Request_Abstract $request)
    {
        return $this->problemToSolveRule($identity, $request);
    }

    /**
     * @param User      $identity
     * @param Zend_Controller_Request_Abstract $request
     * @return bool
     */
    protected function viewContextActionRule(User $identity, Zend_Controller_Request_Abstract $request)
    {
        return $this->problemToSolveRule($identity, $request);
    }

    /**
     * @param User      $identity
     * @param Zend_Controller_Request_Abstract $request
     * @return bool
     */
    protected function addContextActionRule(User $identity, Zend_Controller_Request_Abstract $request)
    {
        return $this->problemToSolveRule($identity, $request);
    }

    /**
     * @param User      $identity
     * @param Zend_Controller_Request_Abstract $request
     * @return bool
     */
    protected function editContextActionRule(User $identity, Zend_Controller_Request_Abstract $request)
    {
        return $this->problemToSolveRule($identity, $request);
    }

    /**
     * @param User      $identity
     * @param Zend_Controller_Request_Abstract $request
     * @return bool
     */
    protected function commentContextActionRule(User $identity, Zend_Controller_Request_Abstract $request)
    {
        return $this->problemToSolveRule($identity, $request);
    }

    /**
     * @param User      $identity
     * @param Zend_Controller_Request_Abstract $request
     * @return bool
     */
    protected function deleteContextActionRule(User $identity, Zend_Controller_Request_Abstract $request)
    {
        return $this->problemToSolveRule($identity, $request);
    }
}
