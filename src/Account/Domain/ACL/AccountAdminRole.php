<?php

namespace Account\Domain\ACL;

use Account\Domain\Account;
use MyCLabs\ACL\ACLManager;
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

        parent::__construct($identity);
    }

    /**
     * @return Account
     */
    public function getAccount()
    {
        return $this->account;
    }

    public function createAuthorizations(ACLManager $aclManager)
    {
        $aclManager->allow(
            $this,
            Actions::all(),
            $this->account
        );

        // TODO cascade manuelle sur les cellules globales
    }
}
