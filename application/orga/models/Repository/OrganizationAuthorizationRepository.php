<?php

namespace Orga\Model\Repository;

use Core_Model_Repository;
use Orga_Model_Organization;
use User\Domain\ACL\Action;
use User\Domain\ACL\AuthorizationRepositoryInterface;
use User\Domain\User;

class OrganizationAuthorizationRepository extends Core_Model_Repository implements AuthorizationRepositoryInterface
{
    /**
     * {@inheritdoc}
     * @param Orga_Model_Organization|null $organization
     */
    public function exists(User $user, Action $action, $organization = null)
    {
        $qb = $this->createQueryBuilder('auth');
        $qb->select('count(auth.id)')
            ->where('auth.user = :user')
            ->andWhere('auth.action = :action')
            ->setParameter('user', $user)
            ->setParameter('action', $action, 'user_action');

        if ($organization) {
            $qb->andWhere('auth.resource = :target')
                ->setParameter('target', $organization);
        }

        $count = $qb->getQuery()->getSingleScalarResult();

        return ($count > 0);
    }
}
