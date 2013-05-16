<?php
/**
 * @author     matthieu.napoli
 * @package    User
 * @subpackage Model
 */

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Ressource
 * @package    User
 * @subpackage Model
 */
abstract class User_Model_Resource extends Core_Model_Entity
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
        $this->directAuthorizations->removeElement($authorization);
    }

    /**
     * Retourne les utilisateurs et roles auquel la ressource est liée *directement* par des autorisations
     *
     * Ne prend pas en compte l'héritage des resources, renvoie uniquement les liens directs.
     * @return User_Model_SecurityIdentity[]
     */
    public function getLinkedSecurityIdentities()
    {
        $identities = $this->directAuthorizations->map(function(User_Model_Authorization $authorization) {
            return $authorization->getIdentity();
        });
        // Supprime les doublons
        $filteredIdentities = new ArrayCollection();
        foreach ($identities as $identity) {
            if (! $filteredIdentities->contains($identity)) {
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
