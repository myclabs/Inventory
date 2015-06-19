<?php

use Account\Domain\AccountRepository;
use AF\Application\AFViewConfiguration;
use AF\Domain\AF;
use AF\Domain\AFLibrary;
use AF\Domain\Input\Input;
use AF\Domain\InputSet\PrimaryInputSet;
use Classification\Domain\ClassificationLibrary;
use Doc\Domain\Library;
use DW\Domain\Cube;
use DW\Domain\Report;
use Orga\Domain\Cell\CellInputComment;
use Orga\Domain\Cell;
use Orga\Domain\Report\CellReport;
use Orga\Domain\Granularity;
use Orga\Domain\Workspace;
use Parameter\Domain\Family\Family;
use Parameter\Domain\ParameterLibrary;
use User\Application\HttpNotFoundException;
use User\Application\Plugin\AbstractACLPlugin;
use User\Domain\ACL\Actions;
use MyCLabs\ACL\Model\ClassResource;
use User\Application\ForbiddenException;
use User\Domain\User;

/**
 * Plugin pour la vérification des ACL
 *
 * @author valentin.claras
 */
class Inventory_Plugin_ACL extends AbstractACLPlugin
{
    /**
     * @Inject
     * @var AccountRepository
     */
    private $accountRepository;

    public function viewWorkspacesRule(User $identity, Zend_Controller_Request_Abstract $request)
    {
        $isIdentityAbleToEditWorkspaces = $this->editWorkspacesRule($identity, $request);
        if ($isIdentityAbleToEditWorkspaces) {
            return true;
        }

        $aclQuery = new Core_Model_Query();
        $aclQuery->aclFilter->enabled = true;
        $aclQuery->aclFilter->user = $identity;
        $aclQuery->aclFilter->action = Actions::TRAVERSE;
        $isIdentityAbleToSeeManyWorkspaces = (Workspace::countTotal($aclQuery) > 0);
        if ($isIdentityAbleToSeeManyWorkspaces) {
            return true;
        }

        return false;
    }

    public function editWorkspacesRule(User $identity)
    {
        $isIdentityAbleToCreateWorkspaces = $this->acl->isAllowed(
            $identity,
            Actions::CREATE,
            new ClassResource(Workspace::class)
        );
        if ($isIdentityAbleToCreateWorkspaces) {
            return true;
        }

        $aclQuery = new Core_Model_Query();
        $aclQuery->aclFilter->enabled = true;
        $aclQuery->aclFilter->user = $identity;
        $aclQuery->aclFilter->action = Actions::EDIT;
        $isIdentityAbleToEditWorkspaces = (Workspace::countTotal($aclQuery) > 0);
        if ($isIdentityAbleToEditWorkspaces) {
            return true;
        }

        return false;
    }

    public function createWorkspaceRule(User $identity)
    {
        return $this->acl->isAllowed(
            $identity,
            Actions::CREATE,
            new ClassResource(Workspace::class)
        );
    }

    /**
     * @param User      $identity
     * @param Zend_Controller_Request_Abstract $request
     * @return bool
     */
    protected function viewWorkspaceRule(User $identity, Zend_Controller_Request_Abstract $request)
    {
        return $this->acl->isAllowed(
            $identity,
            Actions::TRAVERSE,
            $this->getWorkspace($request)
        );
    }

    /**
     * @param User      $identity
     * @param Zend_Controller_Request_Abstract $request
     * @return bool
     */
    public function editWorkspaceAndCellsRule(User $identity, Zend_Controller_Request_Abstract $request)
    {
        $workspace = $this->getWorkspace($request);

        $isUserAllowedToEditWorkspaceAndCells = $this->acl->isAllowed(
            $identity,
            Actions::EDIT,
            $workspace
        );
        if (!$isUserAllowedToEditWorkspaceAndCells) {
            foreach ($workspace->getOrderedGranularities() as $granularity) {
                $aclCellQuery = new Core_Model_Query();
                $aclCellQuery->aclFilter->enabled = true;
                $aclCellQuery->aclFilter->user = $identity;
                $aclCellQuery->aclFilter->action = Actions::EDIT;
                $aclCellQuery->filter->addCondition(Cell::QUERY_GRANULARITY, $granularity);

                $numberCellsUserCanEdit = Cell::countTotal($aclCellQuery);
                if ($numberCellsUserCanEdit > 0) {
                    return true;
                }
            }
        }
        return $isUserAllowedToEditWorkspaceAndCells;
    }

    /**
     * @param User      $identity
     * @param Zend_Controller_Request_Abstract $request
     * @return bool
     */
    public function allowWorkspaceAndCellsRule(User $identity, Zend_Controller_Request_Abstract $request)
    {
        $workspace = $this->getWorkspace($request);

        $isUserAllowedToEditWorkspaceAndCells = $this->acl->isAllowed(
            $identity,
            Actions::ALLOW,
            $workspace
        );
        if (!$isUserAllowedToEditWorkspaceAndCells) {
            foreach ($workspace->getOrderedGranularities() as $granularity) {
                $aclCellQuery = new Core_Model_Query();
                $aclCellQuery->aclFilter->enabled = true;
                $aclCellQuery->aclFilter->user = $identity;
                $aclCellQuery->aclFilter->action = Actions::ALLOW;
                $aclCellQuery->filter->addCondition(Cell::QUERY_GRANULARITY, $granularity);

                $numberCellsUserCanAllow = Cell::countTotal($aclCellQuery);
                if ($numberCellsUserCanAllow > 0) {
                    return true;
                }
            }
        }
        return $isUserAllowedToEditWorkspaceAndCells;
    }

    /**
     * @param User      $identity
     * @param Zend_Controller_Request_Abstract $request
     * @return bool
     */
    protected function allowWorkspaceRule(User $identity, Zend_Controller_Request_Abstract $request)
    {
        return $this->acl->isAllowed(
            $identity,
            Actions::EDIT,
            $this->getWorkspace($request)
        );
    }

    /**
     * @param User      $identity
     * @param Zend_Controller_Request_Abstract $request
     * @return bool
     */
    protected function editWorkspaceRule(User $identity, Zend_Controller_Request_Abstract $request)
    {
        return $this->acl->isAllowed(
            $identity,
            Actions::EDIT,
            $this->getWorkspace($request)
        );
    }

    /**
     * @param User      $identity
     * @param Zend_Controller_Request_Abstract $request
     * @return bool
     */
    public function deleteWorkspaceRule(User $identity, Zend_Controller_Request_Abstract $request)
    {
        return $this->acl->isAllowed(
            $identity,
            Actions::DELETE,
            $this->getWorkspace($request)
        );
    }

    /**
     * @param Zend_Controller_Request_Abstract $request
     * @throws ForbiddenException
     * @return Workspace
     */
    protected function getWorkspace(Zend_Controller_Request_Abstract $request)
    {
        $workspaceId = $request->getParam('workspace');
        if ($workspaceId !== null) {
            return Workspace::load($workspaceId);
        }
        $idGranularity = $request->getParam('granularity');
        if ($idGranularity !== null) {
            return Granularity::load($idGranularity)->getWorkspace();
        }
        $index = $request->getParam('index');
        if ($index !== null) {
            return Workspace::load($index);
        }
        $cellId = $request->getParam('cell');
        if ($cellId !== null) {
            return Cell::load($cellId)->getWorkspace();
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
        return ($this->viewWorkspaceRule($identity, $request) || $this->editCellRule($identity, $request));
    }

    /**
     * @param User      $identity
     * @param Zend_Controller_Request_Abstract $request
     * @return bool
     */
    public function editMembersRule(User $identity, Zend_Controller_Request_Abstract $request)
    {
        return (
            $this->editWorkspaceRule($identity, $request)
            || $this->acl->isAllowed(
                $identity,
                Actions::EDIT,
                Cell::load($request->getParam('cell'))
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
        return $this->acl->isAllowed(
            $identity,
            Actions::VIEW,
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
        return $this->acl->isAllowed(
            $identity,
            Actions::EDIT,
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
        return $this->acl->isAllowed(
            $identity,
            Actions::ALLOW,
            Cell::load($request->getParam('cell'))
        );
    }

    /**
     * @param User                             $identity
     * @param Zend_Controller_Request_Abstract $request
     * @return bool
     */
    protected function editInventoryStatusRule(User $identity, Zend_Controller_Request_Abstract $request)
    {
        $canInput = $this->acl->isAllowed(
            $identity,
            Actions::INPUT,
            $this->getCell($request)
        );
        $canViewReports = $this->acl->isAllowed(
            $identity,
            Actions::ANALYZE,
            $this->getCell($request)
        );

        return $canInput && $canViewReports;
    }

    /**
     * @param User      $identity
     * @param Zend_Controller_Request_Abstract $request
     * @return bool
     */
    protected function inputCellRule(User $identity, Zend_Controller_Request_Abstract $request)
    {
        return $this->acl->isAllowed(
            $identity,
            Actions::INPUT,
            $this->getCell($request)
        );
    }

    /**
     * @param User                             $identity
     * @param Zend_Controller_Request_Abstract $request
     * @return bool
     */
    protected function viewInputHistoryRule(User $identity, Zend_Controller_Request_Abstract $request)
    {
        // TODO
        return true;
    }

    /**
     * @param User      $identity
     * @param Zend_Controller_Request_Abstract $request
     * @return bool
     */
    protected function commentCellRule(User $identity, Zend_Controller_Request_Abstract $request)
    {
        return $this->acl->isAllowed(
            $identity,
            Actions::VIEW,
            $this->getCell($request)
        );
    }

    /**
     * @param User      $identity
     * @param Zend_Controller_Request_Abstract $request
     * @return bool
     */
    protected function analyseCellRule(User $identity, Zend_Controller_Request_Abstract $request)
    {
        return $this->acl->isAllowed(
            $identity,
            Actions::ANALYZE,
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
        $comment = CellInputComment::load($request->getParam('id'));

        return $identity === $comment->getAuthor();
    }

    /**
     * @param User      $identity
     * @param Zend_Controller_Request_Abstract $request
     * @return bool
     */
    protected function deleteCommentRule(User $identity, Zend_Controller_Request_Abstract $request)
    {
        $comment = CellInputComment::load($request->getParam('id'));

        return $identity === $comment->getAuthor();
    }

    /**
     * @param Zend_Controller_Request_Abstract $request
     * @throws \User\Application\ForbiddenException
     * @return Cell
     */
    protected function getCell(Zend_Controller_Request_Abstract $request)
    {
        $index = $request->getParam('index');
        if ($index !== null) {
            try {
                return Cell::load($index);
            } catch (Core_Exception_NotFound $e) {
                // Pas une cellule.
            }
        }
        $cellId = $request->getParam('cell');
        if ($cellId !== null) {
            return Cell::load($cellId);
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
            return $this->viewWorkspaceRule($identity, $request);
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
            return $this->editWorkspaceRule($identity, $request);
        } catch (Core_Exception_NotFound $e) {
            return $this->editWorkspaceRule($identity, $request);
        }
    }

    protected function viewReportRule(User $identity, Zend_Controller_Request_Abstract $request)
    {
        $idReport = $request->getParam('report');
        if ($idReport !== null) {
            $dWCubeId = Report::load($idReport)->getCube()->getId();
        } else {
            $dWCubeId = $request->getParam('cube');
        }

        if ($dWCubeId !== null) {
            $dWCube = Cube::load($dWCubeId);
            // Si le DWCube est d'un Granularity, vérification que l'utilisateur peut configurer le projet.
            try {
                $granularity = Granularity::loadByDWCube($dWCube);
                $workspace = $granularity->getWorkspace();
                $request->setParam('workspace', $workspace->getKey()['id']);
                return $this->editWorkspaceAndCellsRule($identity, $request);
            } catch (Core_Exception_NotFound $e) {
                // Le cube n'appartient pas à un Granularity.
            }
            // Si le DWCube est d'un Cell, vérification que l'utilisateur peut voir la cellule.
            try {
                $cell = Cell::loadByDWCube($dWCube);
                $request->setParam('cell', $cell->getKey()['id']);
                return $this->viewCellRule($identity, $request);
            } catch (Core_Exception_NotFound $e) {
                // Le cube n'appartient pas à un Cell.
            }
        }

        return false;
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
        $idReport = $request->getParam('report');
        $cellReport = CellReport::loadByCellDWReport(Report::load($idReport));
        return ($cellReport->getOwner() === $identity);
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
        $cellId = $request->getParam('cell');
        if ($cellId !== null) {
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
        $cellId = $request->getParam('cell');
        if ($cellId !== null) {
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
        $cellId = $request->getParam('cell');
        if ($cellId !== null) {
            $mode = $request->getParam('mode');
            if ($mode == AFViewConfiguration::MODE_READ) {
                return $this->viewCellRule($identity, $request);
            } elseif ($mode == AFViewConfiguration::MODE_WRITE) {
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
        return $this->editAFRule($identity, $request);
    }

    protected function viewUnitRule(User $identity, Zend_Controller_Request_Abstract $request)
    {
        return $this->loggedInRule($identity, $request);
    }

    protected function editAccountRule(User $identity, Zend_Controller_Request_Abstract $request)
    {
        try {
            $account = $this->accountRepository->get($request->getParam('account'));
        } catch (Core_Exception_NotFound $e) {
            throw new HttpNotFoundException;
        }
        return $this->acl->isAllowed($identity, Actions::EDIT, $account);
    }

    protected function allowAccountRule(User $identity, Zend_Controller_Request_Abstract $request)
    {
        try {
            $account = $this->accountRepository->get($request->getParam('account'));
        } catch (Core_Exception_NotFound $e) {
            throw new HttpNotFoundException;
        }
        return $this->acl->isAllowed($identity, Actions::ALLOW, $account);
    }

    protected function editAFLibraryRule(User $identity, Zend_Controller_Request_Abstract $request)
    {
        $libraryId = $request->getParam('id') ?: $request->getParam('library');
        return $this->acl->isAllowed($identity, Actions::EDIT, AFLibrary::load($libraryId));
    }

    protected function deleteAFLibraryRule(User $identity, Zend_Controller_Request_Abstract $request)
    {
        $libraryId = $request->getParam('id') ?: $request->getParam('library');
        return $this->acl->isAllowed($identity, Actions::DELETE, AFLibrary::load($libraryId));
    }

    protected function editAFRule(User $identity, Zend_Controller_Request_Abstract $request)
    {
        $afId = $request->getParam('idAF') ?: $request->getParam('id');
        try {
            $af = AF::load($afId);
        } catch (Core_Exception_NotFound $e) {
            throw new HttpNotFoundException;
        }
        return $this->acl->isAllowed($identity, Actions::EDIT, $af->getLibrary());
    }

    protected function viewParameterLibraryRule(User $identity, Zend_Controller_Request_Abstract $request)
    {
        $libraryId = $request->getParam('id') ?: $request->getParam('library');
        return $this->acl->isAllowed($identity, Actions::VIEW, ParameterLibrary::load($libraryId));
    }

    protected function editParameterLibraryRule(User $identity, Zend_Controller_Request_Abstract $request)
    {
        $libraryId = $request->getParam('id') ?: $request->getParam('library');
        return $this->acl->isAllowed($identity, Actions::EDIT, ParameterLibrary::load($libraryId));
    }

    protected function deleteParameterLibraryRule(User $identity, Zend_Controller_Request_Abstract $request)
    {
        $libraryId = $request->getParam('id') ?: $request->getParam('library');
        return $this->acl->isAllowed($identity, Actions::DELETE, ParameterLibrary::load($libraryId));
    }

    protected function viewParameterFamilyRule(User $identity, Zend_Controller_Request_Abstract $request)
    {
        $id = $request->getParam('id') ?: $request->getParam('idFamily');
        $parameterFamily = Family::load($id);
        return $this->acl->isAllowed($identity, Actions::VIEW, $parameterFamily->getLibrary());
    }

    protected function editParameterFamilyRule(User $identity, Zend_Controller_Request_Abstract $request)
    {
        $id = $request->getParam('id') ?: $request->getParam('idFamily');
        $parameterFamily = Family::load($id);
        return $this->acl->isAllowed($identity, Actions::EDIT, $parameterFamily->getLibrary());
    }

    protected function deleteParameterFamilyRule(User $identity, Zend_Controller_Request_Abstract $request)
    {
        $parameterFamily = Family::load($request->getParam('id'));
        return $this->acl->isAllowed($identity, Actions::DELETE, $parameterFamily->getLibrary());
    }

    protected function viewClassificationLibraryRule(User $identity, Zend_Controller_Request_Abstract $request)
    {
        $libraryId = $request->getParam('library') ?: $request->getParam('id');
        return $this->acl->isAllowed($identity, Actions::VIEW, ClassificationLibrary::load($libraryId));
    }

    protected function editClassificationLibraryRule(User $identity, Zend_Controller_Request_Abstract $request)
    {
        $libraryId = $request->getParam('library') ?: $request->getParam('id');
        return $this->acl->isAllowed($identity, Actions::EDIT, ClassificationLibrary::load($libraryId));
    }

    protected function deleteClassificationLibraryRule(User $identity, Zend_Controller_Request_Abstract $request)
    {
        $libraryId = $request->getParam('library') ?: $request->getParam('id');
        return $this->acl->isAllowed($identity, Actions::DELETE, ClassificationLibrary::load($libraryId));
    }

    /**
     * @param Zend_Controller_Request_Abstract $request
     * @throws ForbiddenException
     * @return Cell
     */
    protected function getCellFromLibrary(Zend_Controller_Request_Abstract $request)
    {
        $idLibrary = $request->getParam('id');
        $library = Library::load($idLibrary);

        try {
            return Cell::loadByDocLibraryForAFInputSetsPrimary($library);
        } catch (Core_Exception_NotFound $e) {
            // Pas de Cell
            $this->logger->warning('No cell found for document library #' . $idLibrary);
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
    /**
     * @param User                 $identity
     * @param Zend_Controller_Request_Abstract $request
     * @return bool
     */
    public function createUserRule(User $identity, Zend_Controller_Request_Abstract $request)
    {
        return $this->acl->isAllowed(
            $identity,
            Actions::CREATE,
            new ClassResource(User::class)
        );
    }

    /**
     * @param User                 $identity
     * @param Zend_Controller_Request_Abstract $request
     * @return bool
     */
    public function editUserRule(User $identity, Zend_Controller_Request_Abstract $request)
    {
        if ($request->getParam('id') === null) {
            // Éditer son propre compte
            return true;
        }

        $user = User::load($request->getParam('id'));
        if ($user === $identity) {
            return true;
        }

        // Si on peut modifier tous les utilisateurs
        // Pas d'ACL directe entre utilisateurs, c'est overkill
        return $this->acl->isAllowed(
            $identity,
            Actions::EDIT,
            new ClassResource(User::class)
        );
    }

    /**
     * @param User                 $identity
     * @param Zend_Controller_Request_Abstract $request
     * @return bool
     */
    public function disableUserRule(User $identity, Zend_Controller_Request_Abstract $request)
    {
        // Si on peut supprimer tous les utilisateurs
        // Pas d'ACL directe entre utilisateurs, c'est overkill
        return $this->acl->isAllowed(
            $identity,
            Actions::DELETE,
            new ClassResource(User::class)
        );
    }

    /**
     * @param User                 $identity
     * @param Zend_Controller_Request_Abstract $request
     * @return bool
     */
    public function enableUserRule(User $identity, Zend_Controller_Request_Abstract $request)
    {
        // Si on peut réactiver tous les utilisateurs
        // Pas d'ACL directe entre utilisateurs, c'est overkill
        return $this->acl->isAllowed(
            $identity,
            Actions::UNDELETE,
            new ClassResource(User::class)
        );
    }

    /**
     * @param User                 $identity
     * @param Zend_Controller_Request_Abstract $request
     * @return bool
     */
    public function viewAllUsersRule(User $identity, Zend_Controller_Request_Abstract $request)
    {
        return $this->acl->isAllowed($identity, Actions::VIEW, new ClassResource(User::class));
    }

    protected function viewLibraryRule(User $identity, Zend_Controller_Request_Abstract $request)
    {
        $cell = $this->getCellFromLibrary($request);

        return $this->acl->isAllowed($identity, Actions::VIEW, $cell);
    }

    protected function editLibraryRule(User $identity, Zend_Controller_Request_Abstract $request)
    {
        $cell = $this->getCellFromLibrary($request);

        return $this->acl->isAllowed($identity, Actions::VIEW, $cell);
    }
}
