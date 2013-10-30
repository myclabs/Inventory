<?php

namespace User\Domain\ACL\Resource;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Selectable;
use User\Domain\ACL\Action;
use User\Domain\ACL\Authorization\Authorization;
use User\Domain\User;

/**
 * Resource trait helper.
 *
 * This trait needs a $acl attribute.
 *
 * @property Authorization[]|Collection|Selectable $acl
 */
trait ResourceTrait
{
    public function isAllowed(User $user, Action $action)
    {
        $criteria = new Criteria();
        $criteria->where($criteria->expr()->eq('user', $user));
        $criteria->andWhere($criteria->expr()->eq('actionId', $action->exportToString()));

        /** @var Collection $entries */
        $entries = $this->acl->matching($criteria);

        return ($entries->count() > 0);
    }

    public function getACL()
    {
        return $this->acl;
    }

    public function addToACL(Authorization $authorization)
    {
        $this->acl->add($authorization);
    }

    public function removeFromACL(Authorization $authorization)
    {
        $this->acl->removeElement($authorization);
    }
}
