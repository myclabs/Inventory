<?php

namespace Account\Domain\ACL;

use Account\Domain\Account;
use Doctrine\ORM\EntityManager;
use MyCLabs\ACL\Model\Actions;
use MyCLabs\ACL\Model\Authorization;
use MyCLabs\ACL\Model\Resource;
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

    /**
     * @param EntityManager $entityManager
     * @return Authorization[]
     */
    public function createAuthorizations(EntityManager $entityManager)
    {
        return [
            Authorization::create($this, Actions::all(), Resource::fromEntity($this->account)),
        ];
    }
}
