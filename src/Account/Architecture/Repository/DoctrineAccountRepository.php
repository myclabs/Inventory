<?php

namespace Account\Architecture\Repository;

use Account\Domain\Account;
use Account\Domain\AccountRepository;
use Core\Domain\DoctrineEntityRepository;
use MyCLabs\ACL\QueryBuilderHelper;
use User\Domain\ACL\Actions;
use User\Domain\User;

/**
 * Account repository.
 *
 * @author matthieu.napoli
 */
class DoctrineAccountRepository extends DoctrineEntityRepository implements AccountRepository
{
    /**
     * {@inheritdoc}
     */
    public function getTraversableAccounts(User $user)
    {
        $qb = $this->createQueryBuilder('account');
        QueryBuilderHelper::joinACL($qb, $user, Actions::TRAVERSE);

        return $qb->getQuery()->getResult();
    }
}
