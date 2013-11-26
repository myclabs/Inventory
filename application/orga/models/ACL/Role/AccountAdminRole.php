<?php

namespace Orga\Model\ACL\Role;

use Orga\Model\Account;
use Orga\Model\ACL\AccountAuthorization;
use User\Domain\ACL\Action;
use User\Domain\ACL\Role\Role;
use User\Domain\User;

/**
 * Account administrator.
 */
class AccountAdminRole extends Role
{
    protected $account;

    public function __construct(User $user, Account $account)
    {
        $this->account = $account;
        $account->addAdminRole($this);

        parent::__construct($user);
    }

    public function buildAuthorizations()
    {
        $this->authorizations->clear();

        AccountAuthorization::createMany($this, $this->account, [
            Action::VIEW(),
            Action::EDIT(),
            Action::DELETE(),
            Action::ALLOW(),
        ]);
    }

    /**
     * @return Account
     */
    public function getAccount()
    {
        return $this->account;
    }

    public static function getLabel()
    {
        return __('Orga', 'role', 'accountAdministrator');
    }
}
