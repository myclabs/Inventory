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
     * Returns the whole list of authorizations that apply to this resource.
     *
     * @return Authorization[]
     */
    public function getACL();

    /**
     * Returns the list of authorizations that apply to this resource, excluding inherited authorizations.
     *
     * Useful for resource inheritance, to cascade authorizations.
     *
     * @return Authorization[]
     */
    public function getRootACL();

    /**
     * Ne pas utiliser directement. Uniquement utilisé par Authorization::create().
     *
     * @param Authorization $authorization
     */
    public function addToACL(Authorization $authorization);

    /**
     * Ne pas utiliser directement. Uniquement utilisé par Authorization et Role.
     *
     * @param Authorization $authorization
     */
    public function removeFromACL(Authorization $authorization);
}
