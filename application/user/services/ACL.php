<?php
/**
 * @author     matthieu.napoli
 * @package    User
 * @subpackage Service
 */

use Doctrine\ORM\EntityManager;

/**
 * Droits d'accès
 * @package    User
 * @subpackage Service
 */
class User_Service_ACL
{

    /**
     * @var User_Service_ACL_ResourceTreeTraverser[]
     */
    protected $resourceTreeTraversers = [];


    /**
     * Vérifie une autorisation d'accès à une ressource
     *
     * Retourne un résultat sous forme de booléen (accès autorisé ou non).
     * Si aucune règle n'a été trouvée, retourne false.
     *
     * @param User_Model_SecurityIdentity           $identity Demandeur de l'accès
     * @param User_Model_Action                     $action   Action demandée
     * @param User_Model_Resource|Core_Model_Entity $target   Ressource ou entité
     *
     * @throws Core_Exception
     * @return boolean
     */
    public function isAllowed(User_Model_SecurityIdentity $identity, User_Model_Action $action, $target)
    {
        if ($identity instanceof User_Model_User) {
            $identities = [$identity];
            $identities = array_merge($identities, $identity->getRoles());
        } elseif ($identity instanceof User_Model_Role) {
            $identities = array($identity);
        } else {
            throw new Core_Exception("Unsupported case");
        }

        if ($target instanceof User_Model_Resource) {
            $resources = array($target);
        } else {
            $resources = array($this->getResourceForEntity($target));
        }

        while (count($resources) > 0) {
            foreach ($resources as $resource) {
                foreach ($identities as $identity) {
                    // Cherche l'autorisation
                    $authorization = User_Model_Authorization::search($identity, $action, $resource);
                    if ($authorization) {
                        return true;
                    }
                }
            }
            // Héritage des ressources
            $resources = $this->getParentResources($resources);
        }

        // Aucune règle trouvée
        return false;
    }

    /**
     * Crée une autorisation
     *
     * @param User_Model_SecurityIdentity           $identity
     * @param User_Model_Action                     $action
     * @param User_Model_Resource|Core_Model_Entity $target
     * @return User_Model_Authorization
     * @throws Core_Exception_InvalidArgument
     */
    public function allow(User_Model_SecurityIdentity $identity, User_Model_Action $action,
                          Core_Model_Entity $target
    ) {
        if ($target instanceof User_Model_Resource) {
            $resource = $target;
        } else {
            $resource = $this->getResourceForEntity($target);
            if ($resource === null) {
                throw new Core_Exception_InvalidArgument("No resource found for $target");
            }
        }
        $authorization = new User_Model_Authorization($identity, $action, $resource);
        $authorization->save();
        return $authorization;
    }

    /**
     * Révoque une autorisation
     *
     * @param User_Model_SecurityIdentity           $identity
     * @param User_Model_Action                     $action
     * @param User_Model_Resource|Core_Model_Entity $target
     * @throws Core_Exception_InvalidArgument
     */
    public function disallow(User_Model_SecurityIdentity $identity, User_Model_Action $action,
                             Core_Model_Entity $target
    ) {
        if ($target instanceof User_Model_Resource) {
            $resource = $target;
        } else {
            $resource = $this->getResourceForEntity($target);
            if ($resource === null) {
                throw new Core_Exception_InvalidArgument("No resource found for $target");
            }
        }
        $authorization = User_Model_Authorization::search($identity, $action, $resource);
        if ($authorization) {
            // Retire l'autorisation de l'identité et de la ressource
            $identity->removeAuthorization($authorization);
            $resource->removeAuthorization($authorization);
            $authorization->delete();
        }
    }

    /**
     * @param Core_Model_Entity $entity
     * @return User_Model_Resource_Entity|null
     */
    public function getResourceForEntity(Core_Model_Entity $entity)
    {
        return User_Model_Resource_Entity::loadByEntity($entity);
    }

    /**
     * Retourne la liste de toutes les autorisations d'une ressource,
     * y compris celles héritées des ressources parent
     *
     * @param User_Model_Resource $resource
     *
     * @return User_Model_Authorization[]
     */
    public function getAllAuthorizationsForResource(User_Model_Resource $resource)
    {
        $authorizations = [];

        // Autorisations liées directement à la ressource
        foreach ($resource->getDirectAuthorizations() as $authorization) {
            // Évite les doublons
            if (!in_array($authorization, $authorizations, true)) {
                $authorizations[] = $authorization;
            }
        }

        // Autorisations héritées des ressources parent
        foreach ($this->getParentResources([$resource]) as $parentResource) {
            $authorizations += $this->getAllAuthorizationsForResource($parentResource);
        }

        return $authorizations;
    }

    /**
     * Retourne la liste de toutes les autorisations d'un utilisateur,
     * prenant en compte ses rôles et l'héritage des ressources.
     *
     * @param User_Model_User $user
     *
     * @return array array('action' => $action, 'resource' => $resource)[]
     */
    public function getAllUserAuthorizations(User_Model_User $user)
    {
        // Tableau indexé pour éviter les doublons
        $authorizations = [];

        // Récupère toutes les autorisations directes qu'il a lui-même ou via ses rôles
        foreach ($user->getDirectAuthorizations() as $authorization) {
            $key = $authorization->getAction()->getValue() . '-' . $authorization->getResource()->getId();
            $authorizations[$key] = [
                'action'   => $authorization->getAction(),
                'resource' => $authorization->getResource(),
            ];
        }
        foreach ($user->getRoles() as $role) {
            foreach ($role->getDirectAuthorizations() as $authorization) {
                $key = $authorization->getAction()->getValue() . '-' . $authorization->getResource()->getId();
                $authorizations[$key] = [
                    'action'   => $authorization->getAction(),
                    'resource' => $authorization->getResource(),
                ];
            }
        }

        // Tableau indexé pour éviter les doublons
        $allAuthorizations = $authorizations;

        // Résoud toutes ses autorisations avec l'héritage des resources
        foreach ($authorizations as $authorization) {
            $resource = $authorization['resource'];
            /** @var $action User_Model_Action */
            $action = $authorization['action'];
            $children = $this->getChildResources([$resource]);
            foreach ($children as $childResource) {
                $key = $action->getValue() . '-' . $childResource->getId();
                $allAuthorizations[$key] = [
                    'action'   => $action,
                    'resource' => $childResource,
                ];
            }
        }

        return $allAuthorizations;
    }

    /**
     * Ajoute un service qui permet de traverser la hiérarchie des ressources d'entités (parents/enfants)
     * @param string                                 $entityName Entité pour laquelle appliquer le TreeTraverser
     * @param User_Service_ACL_ResourceTreeTraverser $resourceTreeTraverser
     */
    public function setResourceTreeTraverser($entityName,
                                             User_Service_ACL_ResourceTreeTraverser $resourceTreeTraverser
    ) {
        $this->resourceTreeTraversers[$entityName] = $resourceTreeTraverser;
    }

    /**
     * Retourne un TreeTraverser pour l'entité d'une ressource donnée (retourne null si pas trouvé)
     * @param User_Model_Resource_Entity $resource Cherche le TreeTraverser pour la ressource
     * @return User_Service_ACL_ResourceTreeTraverser|null
     */
    protected function getResourceTreeTraverser(User_Model_Resource_Entity $resource)
    {
        $entity = $resource->getEntity();
        foreach ($this->resourceTreeTraversers as $entityName => $resourceTreeTraverser) {
            // Si la ressource représente une entité, on vérifie qu'elle est de la bonne classe
            if ($entity && $entity instanceof $entityName) {
                return $resourceTreeTraverser;
            }
            // Si la ressource ne représente pas une entité spécifique, on vérifie si le nom correspond
            if ($entity === null && $resource->getEntityName() == $entityName) {
                return $resourceTreeTraverser;
            }
        }
        return null;
    }

    /**
     * Renvoie le tableau contenant les ressources parents de chaque ressource du tableau.
     * @param User_Model_Resource[] $resources
     * @return User_Model_Resource_Entity[] ressources parents
     */
    public function getParentResources(array $resources)
    {
        $parentResources = [];
        foreach ($resources as $resource) {
            if ($resource instanceof User_Model_Resource_Entity) {
                $resourceResolver = $this->getResourceTreeTraverser($resource);
                if ($resourceResolver) {
                    // Pour chaque ressource, récupère tous ses parents
                    $parentResources += $resourceResolver->getParentResources($resource);
                }
            }
        }
        return $parentResources;
    }

    /**
     * Renvoie le tableau contenant les ressources enfant de chaque ressource du tableau.
     * @param User_Model_Resource[] $resources
     * @return User_Model_Resource_Entity[] ressources enfant
     */
    public function getChildResources(array $resources)
    {
        $childResources = [];
        foreach ($resources as $resource) {
            if ($resource instanceof User_Model_Resource_Entity) {
                $resourceResolver = $this->getResourceTreeTraverser($resource);
                if ($resourceResolver) {
                    // Pour chaque ressource, récupère tous ses parents
                    $childResources += $resourceResolver->getChildResources($resource);
                }
            }
        }
        return $childResources;
    }

    /**
     * @return User_Service_ACL
     */
    public static function getInstance()
    {
        return parent::getInstance();
    }

}
