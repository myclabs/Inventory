<?php

namespace Orga\Model\Repository;

use Core_Model_Repository;
use Orga_Model_Cell;
use User\Domain\ACL\Action;
use User\Domain\ACL\AuthorizationRepositoryInterface;
use User\Domain\User;

class CellAuthorizationRepository extends Core_Model_Repository implements AuthorizationRepositoryInterface
{
    /**
     * {@inheritdoc}
     * @param Orga_Model_Cell|null $cell
     */
    public function exists(User $user, Action $action, $cell = null)
    {
        $qb = $this->createQueryBuilder('auth');
        $qb->select('count(auth.id)')
            ->where('auth.user = :user')
            ->andWhere('auth.action = :action')
            ->setParameter('user', $user)
            ->setParameter('action', $action, 'user_action');

        if ($cell) {
            $qb->andWhere('auth.resource = :target')
                ->setParameter('target', $cell);
        }

        $count = $qb->getQuery()->getSingleScalarResult();

        return ($count > 0);
    }
}
