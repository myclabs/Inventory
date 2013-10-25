<?php

namespace User\Domain\ACL;

use Core_Model_Entity;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use User\Domain\ACL\SecurityIdentity;

/**
 * Ressource des ACL.
 *
 * @author matthieu.napoli
 */
abstract class Resource extends Core_Model_Entity
{
    /**
     * @var int
     */
    protected $id;

    /**
     * Authorizations related to this resource
     * @var Authorization[]|Collection
     */
    protected $directAuthorizations;

    public function __construct()
    {
        $this->directAuthorizations = new ArrayCollection();
    }

    /**
     * Retourne les autorisations directement associées à cette ressource
     *
     * Ne retourne pas les autorisations héritées via l'héritage des ressources.
     *
     * @return Authorization[]
     */
    public function getDirectAuthorizations()
    {
        return $this->directAuthorizations->toArray();
    }

    public function addAuthorization(Authorization $authorization)
    {
        $this->directAuthorizations->add($authorization);
    }

    public function removeAuthorization(Authorization $authorization)
    {
        $this->directAuthorizations->removeElement($authorization);
    }

    /**
     * Retourne les utilisateurs et roles auquel la ressource est liée *directement* par des autorisations
     *
     * Ne prend pas en compte l'héritage des resources, renvoie uniquement les liens directs.
     * @return SecurityIdentity[]
     */
    public function getLinkedSecurityIdentities()
    {
        $identities = $this->directAuthorizations->map(
            function (Authorization $authorization) {
                return $authorization->getIdentity();
            }
        );
        // Supprime les doublons
        $filteredIdentities = new ArrayCollection();
        foreach ($identities as $identity) {
            if (!$filteredIdentities->contains($identity)) {
                $filteredIdentities->add($identity);
            }
        }
        return $filteredIdentities;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }
}
