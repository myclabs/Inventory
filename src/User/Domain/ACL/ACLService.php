<?php

namespace User\Domain\ACL;

use Core_Exception;
use Core_Exception_InvalidArgument;
use Core_Model_Entity;
use Core_Tools;
use Psr\Log\LoggerInterface;
use User\Domain\ACL\Action\Action;
use User\Domain\ACL\Resource;
use User\Domain\ACL\Resource\EntityResource;
use User\Domain\User;
use User\Domain\ACL\ResourceTreeTraverser;

/**
 * Droits d'accès.
 *
 * @author matthieu.napoli
 */
class ACLService
{
    /**
     * @var ResourceTreeTraverser[]
     */
    private $resourceTreeTraversers = [];

    /**
     * @var LoggerInterface
     */
    private $logger;


    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Vérifie une autorisation d'accès à une ressource
     *
     * Retourne un résultat sous forme de booléen (accès autorisé ou non).
     * Si aucune règle n'a été trouvée, retourne false.
     *
     * @param SecurityIdentity           $identity Demandeur de l'accès
     * @param Action                     $action   Action demandée
     * @param Resource|Core_Model_Entity $target   Ressource ou entité
     *
     * @throws Core_Exception
     * @return boolean
     */
    public function isAllowed(SecurityIdentity $identity, Action $action, $target)
    {
        if ($identity instanceof User) {
            $identities = [$identity];
            $identities = array_merge($identities, $identity->getRoles());
        } elseif ($identity instanceof Role) {
            $identities = array($identity);
        } else {
            throw new Core_Exception("Unsupported case");
        }

        if ($target instanceof Resource) {
            $resources = [$target];
        } else {
            $resource = $this->getResourceForEntity($target);
            if ($resource === null) {
                $this->logger->notice('ACLService::isAllowed called on object without an associated resource');
                return false;
            }
            $resources = [$resource];
        }

        // Héritage des ressources
        $resources = array_merge($resources, $this->getAllParentResources($resources));

        // TODO Éviter la boucle en faisant 1 seule requête BDD
        foreach ($resources as $resource) {
            foreach ($identities as $identity) {
                // Cherche l'autorisation
                $authorization = Authorization::search($identity, $action, $resource);
                if ($authorization) {
                    return true;
                }
            }
        }

        // Aucune règle trouvée
        return false;
    }

    /**
     * Crée une autorisation
     *
     * @param SecurityIdentity           $identity
     * @param Action                     $action
     * @param Resource|Core_Model_Entity $target
     * @return Authorization
     * @throws Core_Exception_InvalidArgument
     */
    public function allow(
        SecurityIdentity $identity,
        Action $action,
        Core_Model_Entity $target
    ) {
        if ($target instanceof Resource) {
            $resource = $target;
        } else {
            $resource = $this->getResourceForEntity($target);
            if ($resource === null) {
                throw new Core_Exception_InvalidArgument("No resource found for $target");
            }
        }
        $authorization = new Authorization($identity, $action, $resource);
        $authorization->save();
        return $authorization;
    }

    /**
     * Révoque une autorisation
     *
     * @param SecurityIdentity           $identity
     * @param Action                     $action
     * @param Resource|Core_Model_Entity $target
     * @throws Core_Exception_InvalidArgument
     */
    public function disallow(
        SecurityIdentity $identity,
        Action $action,
        Core_Model_Entity $target
    ) {
        if ($target instanceof Resource) {
            $resource = $target;
        } else {
            $resource = $this->getResourceForEntity($target);
            if ($resource === null) {
                throw new Core_Exception_InvalidArgument("No resource found for $target");
            }
        }
        $authorization = Authorization::search($identity, $action, $resource);
        if ($authorization) {
            // Retire l'autorisation de l'identité et de la ressource
            $identity->removeAuthorization($authorization);
            $resource->removeAuthorization($authorization);
            $authorization->delete();
        }
    }

    /**
     * @param Core_Model_Entity $entity
     * @return EntityResource|null
     */
    public function getResourceForEntity(Core_Model_Entity $entity)
    {
        return EntityResource::loadByEntity($entity);
    }

    /**
     * Retourne la liste de toutes les autorisations d'une ressource,
     * y compris celles héritées des ressources parent
     *
     * @param Resource $resource
     *
     * @return Authorization[]
     */
    public function getAllAuthorizationsForResource(Resource $resource)
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
        foreach ($this->getAllParentResources([$resource]) as $parentResource) {
            $authorizations = array_merge($authorizations, $this->getAllAuthorizationsForResource($parentResource));
        }

        return $authorizations;
    }

    /**
     * Retourne la liste de toutes les autorisations d'un utilisateur,
     * prenant en compte ses rôles et l'héritage des ressources.
     *
     * @param User $user
     *
     * @return array array('action' => $action, 'resource' => $resource)[]
     */
    public function getAllUserAuthorizations(User $user)
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
            /** @var $action Action */
            $action = $authorization['action'];
            $children = $this->getAllChildResources([$resource]);
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
     * @param ResourceTreeTraverser $resourceTreeTraverser
     */
    public function setResourceTreeTraverser(
        $entityName,
        ResourceTreeTraverser $resourceTreeTraverser
    ) {
        $this->resourceTreeTraversers[$entityName] = $resourceTreeTraverser;
    }

    /**
     * Retourne un TreeTraverser pour l'entité d'une ressource donnée (retourne null si pas trouvé)
     * @param EntityResource $resource Cherche le TreeTraverser pour la ressource
     * @return ResourceTreeTraverser|null
     */
    protected function getResourceTreeTraverser(EntityResource $resource)
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
     * @param \Resource[] $resources
     * @return EntityResource[] ressources parents
     */
    public function getAllParentResources(array $resources)
    {
        $parentResources = [];

        foreach ($resources as $resource) {
            if (!$resource instanceof EntityResource) {
                continue;
            }
            $resourceResolver = $this->getResourceTreeTraverser($resource);
            if ($resourceResolver) {
                // Pour chaque ressource, récupère tous ses parents
                $parentResources = array_merge($parentResources, $resourceResolver->getAllParentResources($resource));
            }
        }

        // Supprime les doublons
        return Core_Tools::arrayFilterDuplicates($parentResources);
    }

    /**
     * Renvoie le tableau contenant les ressources enfant de chaque ressource du tableau.
     * @param \Resource[] $resources
     * @return EntityResource[] ressources enfant
     */
    public function getAllChildResources(array $resources)
    {
        $childResources = [];

        foreach ($resources as $resource) {
            if (!$resource instanceof EntityResource) {
                continue;
            }
            $resourceResolver = $this->getResourceTreeTraverser($resource);
            if ($resourceResolver) {
                // Pour chaque ressource, récupère tous ses parents
                $childResources = array_merge($childResources, $resourceResolver->getAllChildResources($resource));
            }
        }

        // Supprime les doublons
        return Core_Tools::arrayFilterDuplicates($childResources);
    }
}
