<?php

namespace Core\Criteria;

use Doctrine\Common\Collections\Expr\Expression;
use Doctrine\Common\Collections\ExpressionBuilder;
use User_Model_Action;
use User_Model_SecurityIdentity;

/**
 * Filter using ACL
 *
 * @author matthieu.napoli
 */
trait ACLFilter
{
    private $withACLFilter = false;

    public function withACLFilter(User_Model_SecurityIdentity $user, User_Model_Action $action)
    {
        $builder = new ExpressionBuilder();

//        $queryBuilder->innerJoin('User_Model_ACLFilterEntry', 'acl_cache');
//
//        $queryBuilder->andWhere('acl_cache.idUser = :aclUserId');
//        $queryBuilder->andWhere('acl_cache.action = :aclAction');
//        $queryBuilder->andWhere('acl_cache.entityName = :aclEntityName');
//        $queryBuilder->andWhere('acl_cache.entityIdentifier = ' . $this->rootAlias . '.id');
//
//        $queryBuilder->setParameter('aclEntityName', $this->entityName);
//        $queryBuilder->setParameter('aclAction', $this->aclFilter->action, ActionType::TYPE_NAME);
//        $queryBuilder->setParameter('aclUserId', $this->aclFilter->user->getId());

        $this->andWhere($builder->eq('acl_filter.idUser', $user->getId()));
        $this->andWhere($builder->eq('acl_filter.action', $action));
    }

    /**
     * @return boolean
     */
    public function getWithACLFilter()
    {
        return $this->withACLFilter;
    }

    public abstract function andWhere(Expression $expression);
}