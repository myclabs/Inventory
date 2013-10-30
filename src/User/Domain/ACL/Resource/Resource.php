<?php

namespace User\Domain\ACL\Resource;

use User\Domain\ACL\Action;
use User\Domain\ACL\Authorization\Authorization;
use User\Domain\User;

/**
 * Resource interface.
 */
interface Resource
{
    /**
     * Tests if a user is allowed for the given action on the resource.
     *
     * @param User   $user
     * @param Action $action
     * @return bool
     */
    public function isAllowed(User $user, Action $action);

    /**
     * Returns the list of authorizations that apply to this resource.
     *
     * @return Authorization[]
     */
    public function getACL();

    /**
     * @param Authorization $authorization
     */
    public function addToACL(Authorization $authorization);

    /**
     * @param Authorization $authorization
     */
    public function removeFromACL(Authorization $authorization);

    /**
     * @param Authorization[] $authorizations
     */
    public function replaceACL(array $authorizations);
}
