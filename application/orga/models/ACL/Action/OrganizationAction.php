<?php

namespace Orga\Model\ACL\Action;

use User\Domain\ACL\Action;

/**
 * Actions pouvant être réalisées sur les organisations.
 *
 * @author valentin.claras
 */
class OrganizationAction extends Action
{
    /**
     * Éditer les rapports des granularités.
     */
    const EDIT_GRANULARITY_REPORTS = 5;


    /**
     * @return self
     */
    public static function EDIT_GRANULARITY_REPORTS()
    {
        return new static(self::EDIT_GRANULARITY_REPORTS);
    }
}
