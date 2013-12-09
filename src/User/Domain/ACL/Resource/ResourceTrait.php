<?php

namespace User\Domain\ACL\Resource;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Selectable;
use User\Domain\ACL\Authorization\Authorization;

/**
 * Resource trait helper.
 *
 * This trait needs a $acl attribute.
 *
 * @property Authorization[]|Collection|Selectable $acl
 */
trait ResourceTrait
{
    public function getACL()
    {
        return $this->acl;
    }

    /**
     * @return Authorization[]
     */
    public function getRootACL()
    {
        $criteria = new Criteria();
        $criteria->where($criteria->expr()->isNull('parentAuthorization'));

        return $this->acl->matching($criteria);
    }

    public function addToACL(array $authorizations)
    {
        foreach ($authorizations as $authorization) {
            $this->acl->add($authorization);
        }
    }

    public function removeFromACL(Authorization $authorization)
    {
        $this->acl->removeElement($authorization);
    }
}
