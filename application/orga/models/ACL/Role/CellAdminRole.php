<?php

namespace Orga\Model\ACL\Role;

use DW\Model\ACL\ReportAuthorization;
use Orga\Model\ACL\CellAuthorization;
use Orga\Model\ACL\OrganizationAuthorization;
use Orga\Action\CellAction;
use Orga_Model_Cell;
use Orga_Model_GranularityReport;
use User\Domain\ACL\Action;
use User\Domain\ACL\Role;
use User\Domain\User;

/**
 * Cell administrator.
 */
class CellAdminRole extends AbstractCellRole
{
    public function __construct(User $user, Orga_Model_Cell $cell)
    {
        $cell->addAdminRole($this);

        parent::__construct($user, $cell);
    }

    public function buildAuthorizations()
    {
        $this->authorizations->clear();

        // Voir l'organisation
        OrganizationAuthorization::create($this, $this->user, Action::VIEW(), $this->cell->getOrganization());

        $view = CellAuthorization::create($this, $this->user, Action::VIEW(), $this->cell);
        $edit = CellAuthorization::create($this, $this->user, Action::EDIT(), $this->cell);
        $allow = CellAuthorization::create($this, $this->user, Action::ALLOW(), $this->cell);
        $comment = CellAuthorization::create($this, $this->user, CellAction::COMMENT(), $this->cell);
        $input = CellAuthorization::create($this, $this->user, CellAction::INPUT(), $this->cell);

        // Voir les copies des rapports préconfigurés
        if ($this->cell->getGranularity()->getCellsGenerateDWCubes()) {
            foreach ($this->cell->getDWCube()->getReports() as $report) {
                if (Orga_Model_GranularityReport::isDWReportCopiedFromGranularityDWReport($report)) {
                    ReportAuthorization::createChildAuthorization($view, $report);
                }
            }
        }

        // Cellules filles
        foreach ($this->cell->getChildCells() as $childCell) {
            CellAuthorization::createChildAuthorization($view, $childCell);
            CellAuthorization::createChildAuthorization($edit, $childCell);
            CellAuthorization::createChildAuthorization($allow, $childCell);
            CellAuthorization::createChildAuthorization($comment, $childCell);
            CellAuthorization::createChildAuthorization($input, $childCell);

            // Voir les copies des rapports préconfigurés
            if ($childCell->getGranularity()->getCellsGenerateDWCubes()) {
                foreach ($childCell->getDWCube()->getReports() as $report) {
                    if (Orga_Model_GranularityReport::isDWReportCopiedFromGranularityDWReport($report)) {
                        ReportAuthorization::createChildAuthorization($view, $report);
                    }
                }
            }
        }
    }

    public static function getLabel()
    {
        return __('Orga', 'role', 'cellAdministrator');
    }
}
