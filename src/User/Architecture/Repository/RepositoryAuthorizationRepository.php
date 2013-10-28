<?php

namespace User\Architecture\Repository;

use Core_Model_Repository;
use User\Domain\ACL\Action;
use User\Domain\ACL\AuthorizationRepositoryInterface;
use User\Domain\User;

class RepositoryAuthorizationRepository extends Core_Model_Repository implements AuthorizationRepositoryInterface
{
    /**
     * {@inheritdoc}
     * @param null $targetRepository
     */
    public function exists(User $user, Action $action, $targetRepository = null)
    {
        $qb = $this->createQueryBuilder('auth');
        $qb->select('count(auth.id)')
            ->where('auth.user = :user')
            ->andWhere('auth.action = :action')
            ->setParameter('user', $user)
            ->setParameter('action', $action, 'user_action');

        $count = $qb->getQuery()->getSingleScalarResult();

        return ($count > 0);
    }
}
