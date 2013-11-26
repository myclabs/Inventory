<?php

namespace User\Domain\ACL\Role;

use User\Domain\ACL\Authorization\Authorization;

/**
 * Role
 */
interface OptimizedRole
{
    /**
     * @return Authorization[]
     */
    public function optimizedBuildAuthorizations();
}
