<?php

namespace Account\Domain\ACL;

use Account\Domain\Account;
use MyCLabs\ACL\ACL;
use User\Domain\ACL\Actions;
use MyCLabs\ACL\Model\Role;
use User\Domain\User;

/**
 * Administrateur de compte.
 *
 * @author matthieu.napoli
 */
class AccountAdminRole extends Role
{
    /**
     * @var Account
     */
    protected $account;

    public function __construct(User $identity, Account $account)
    {
        $this->account = $account;

        $account->addAdminRole($this);

        parent::__construct($identity);
    }

    /**
     * @return Account
     */
    public function getAccount()
    {
        return $this->account;
    }

    public function createAuthorizations(ACL $acl)
    {
        $acl->allow(
            $this,
            Actions::all(),
            $this->account
        );
    }
}
