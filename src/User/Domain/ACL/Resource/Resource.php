<?php

namespace User\Domain\ACL\Resource;

use User\Domain\ACL\Authorization\Authorization;

/**
 * Resource interface.
 */
interface Resource
{
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
     * @param Authorization[] $authorizations
     */
    public function addToACL(array $authorizations);

    /**
     * Ne pas utiliser directement. Uniquement utilisé par Authorization et Role.
     *
     * @param Authorization $authorization
     */
    public function removeFromACL(Authorization $authorization);

    /**
     * Une ressource doit avoir un ID simple.
     *
     * @return mixed
     */
    public function getId();
}
