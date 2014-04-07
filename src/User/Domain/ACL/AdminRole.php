<?php

namespace User\Domain\ACL;

use Account\Domain\Account;
use MyCLabs\ACL\ACL;
use MyCLabs\ACL\Model\ClassResource;
use MyCLabs\ACL\Model\Role;
use User\Domain\User;

/**
 * Application administrator.
 */
class AdminRole extends Role
{
    public function createAuthorizations(ACL $acl)
    {
        // Admin all users
        $acl->allow($this, Actions::all(), new ClassResource(User::class));

        // Admin all accounts
        $acl->allow($this, Actions::all(), new ClassResource(Account::class));
    }

    public static function getLabel()
    {
        return __('User', 'role', 'roleAdmin');
    }
}
