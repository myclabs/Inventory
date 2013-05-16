<?php
/**
 * @author matthieu.napoli
 * @package User
 */

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Class représentant une identité ayant des droits d'accès
 *
 * @package User
 */
abstract class User_Model_SecurityIdentity extends Core_Model_Entity
{

    /**
     * @var int
     */
    protected $id;

    /**
     * Authorizations related to this resource
     * @var User_Model_Authorization[]|Collection
     */
    protected $directAuthorizations;


    /**
     * Constructeur
     */
    public function __construct()
    {
        $this->directAuthorizations = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Retourne les autorisations directement associées à cette ressource
     *
     * Ne retourne pas les autorisations héritées via l'héritage des ressources.
     *
     * @return User_Model_Authorization[]
     */
    public function getDirectAuthorizations()
    {
        return $this->directAuthorizations->toArray();
    }

    /**
     * @param User_Model_Authorization $authorization
     */
    public function addAuthorization(User_Model_Authorization $authorization)
    {
        if (! $this->directAuthorizations->contains($authorization)) {
            $this->directAuthorizations->add($authorization);
        }
    }

    /**
     * @param User_Model_Authorization $authorization
     */
    public function removeAuthorization(User_Model_Authorization $authorization)
    {
        if ($this->directAuthorizations->contains($authorization)) {
            $this->directAuthorizations->removeElement($authorization);
        }
    }

    /**
     * Retourne les ressources auquel le role ou l'utilisateur est *directement* lié par une autorisation
     *
     * Ne prend pas en compte l'héritage des resources ou le fait qu'un utilisateur ait des rôles.
     *
     * @return User_Model_Resource[]
     */
    public function getLinkedResources()
    {
        $resources = $this->directAuthorizations->map(function(User_Model_Authorization $authorization) {
            return $authorization->getResource();
        });
        // Supprime les doublons
        $filteredResources = new ArrayCollection();
        foreach ($resources as $resource) {
            if (! $filteredResources->contains($resource)) {
                $filteredResources->add($resource);
            }
        }
        return $filteredResources;
    }

}
