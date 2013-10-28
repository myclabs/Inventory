<?php

namespace User\Architecture\Repository;

use Core_Model_Repository;
use User\Domain\ACL\Action;
use User\Domain\ACL\AuthorizationRepositoryInterface;
use User\Domain\User;

class UserAuthorizationRepository extends Core_Model_Repository implements AuthorizationRepositoryInterface
{
    /**
     * {@inheritdoc}
     * @param User|null $targetUser
     */
    public function exists(User $user, Action $action, $targetUser = null)
    {
        $qb = $this->createQueryBuilder('auth');
        $qb->select('count(auth.id)')
            ->where('auth.user = :user')
            ->andWhere('auth.action = :action')
            ->setParameter('user', $user)
            ->setParameter('action', $action, 'user_action');

        if ($targetUser) {
            $qb->andWhere('auth.resource = :target')
                ->setParameter('target', $targetUser);
        }

        $count = $qb->getQuery()->getSingleScalarResult();

        return ($count > 0);
    }
}
