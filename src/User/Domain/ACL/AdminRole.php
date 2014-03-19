<?php

namespace User\Domain\ACL;

use Account\Domain\Account;
use User\Domain\ACL\Actions;
use MyCLabs\ACL\ACLManager;
use MyCLabs\ACL\Model\ClassResource;
use MyCLabs\ACL\Model\Role;
use User\Domain\User;

/**
 * Application administrator.
 */
class AdminRole extends Role
{
    public function createAuthorizations(ACLManager $aclManager)
    {
        // Admin all users
        $aclManager->allow($this, Actions::all(), new ClassResource(User::class));

        // Admin all accounts
        $aclManager->allow($this, Actions::all(), new ClassResource(Account::class));
    }

    public static function getLabel()
    {
        return __('User', 'role', 'roleAdmin');
    }
}
