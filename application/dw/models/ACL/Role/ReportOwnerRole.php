<?php

namespace DW\Model\ACL\Role;

use DW\Model\ACL\ReportAuthorization;
use DW_Model_Report;
use User\Domain\ACL\Action;
use User\Domain\ACL\Role\Role;
use User\Domain\User;

/**
 * Report owner.
 */
class ReportOwnerRole extends Role
{
    protected $report;

    public function __construct(User $user, DW_Model_Report $report)
    {
        $this->report = $report;
        $report->setOwner($this);

        parent::__construct($user);
    }

    public function buildAuthorizations()
    {
        $this->authorizations->clear();

        ReportAuthorization::createMany($this, $this->report, [
            Action::VIEW(),
            Action::EDIT(),
            Action::DELETE(),
        ]);
    }

    /**
     * @return DW_Model_Report
     */
    public function getReport()
    {
        return $this->report;
    }
}
