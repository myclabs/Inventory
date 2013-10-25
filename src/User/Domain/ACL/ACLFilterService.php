<?php

namespace User\Domain\ACL;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManager;
use User\Domain\ACL\Action\Action;
use User\Domain\ACL\Resource\EntityResource;
use User\Domain\User;

/**
 * Gestion du filtre des ACL en BDD.
 *
 * @author matthieu.napoli
 */
class ACLFilterService
{
    const TABLE_NAME = 'ACL_Filter';

    /**
     * Activation du filtrage (actif par défaut)
     * @var bool
     */
    public $enabled = true;

    /**
     * @var ACLService
     */
    private $aclService;

    /**
     * @var Connection
     */
    private $connection;


    /**
     * @param ACLService    $aclService
     * @param EntityManager $entityManager
     */
    public function __construct(ACLService $aclService, EntityManager $entityManager)
    {
        $this->aclService = $aclService;
        $this->connection = $entityManager->getConnection();
    }

    /**
     * Génère le filtre des ACL
     */
    public function generate()
    {
        if (!$this->enabled) {
            return;
        }
        $this->clean();

        // Récupère toutes les ressources
        /** @var $resources EntityResource */
        $resources = EntityResource::loadList();

        // Génère le filtre pour chaque ressource
        foreach ($resources as $resource) {
            $this->generateForResource($resource, false, false);
        }
    }

    /**
     * Vide le filtre
     */
    public function clean()
    {
        if (!$this->enabled) {
            return;
        }
        $this->connection->query('TRUNCATE TABLE ' . self::TABLE_NAME);
    }

    /**
     * Génère le filtre des ACL pour plusieurs ressources (de manière optimisée)
     *
     * @param EntityResource[] $resources
     * @param bool             $generateResourcesHierarchy
     * @param bool             $cleanResourceFilter
     */
    public function generateForResources(
        array $resources,
        $generateResourcesHierarchy = true,
        $cleanResourceFilter = true
    ) {
        if (!$this->enabled) {
            return;
        }

        // Trie par ID pour éviter les doublons
        $allResources = [];
        foreach ($resources as $resource) {
            $allResources[$resource->getId()] = $resource;
        }

        // Récupère les ressources filles
        if ($generateResourcesHierarchy) {
            foreach ($allResources as $resource) {
                $children = $this->aclService->getAllChildResources([$resource]);
                foreach ($children as $childResource) {
                    $allResources[$childResource->getId()] = $childResource;
                }
            }
        }

        // Génère le filtre pour chaque ressource
        foreach ($allResources as $resource) {
            $this->generateForResource($resource, false, $cleanResourceFilter);
        }
    }

    /**
     * Génère le filtre des ACL associé à la ressource
     *
     * @param EntityResource $resource
     * @param boolean        $generateResourcesHierarchy
     * @param boolean        $cleanResourceFilter
     */
    public function generateForResource(
        EntityResource $resource,
        $generateResourcesHierarchy = true,
        $cleanResourceFilter = true
    ) {
        // Teste si le cache est activé
        if (!$this->enabled) {
            return;
        }

        // Ignore les ressources qui n'ont pas d'entité attribuée
        if ($resource->getEntity() === null) {
            // Traite uniquement les sous-ressources
            if ($generateResourcesHierarchy) {
                $children = $this->aclService->getAllChildResources([$resource]);
                foreach ($children as $childResource) {
                    // false : ne parcourt pas la hiérarchie encore une fois
                    $this->generateForResource($childResource, false, $cleanResourceFilter);
                }
            }
            return;
        }

        // Vide le filtre associé à la ressource
        if ($cleanResourceFilter) {
            $this->cleanForResource($resource, false);
        }

        // Récupère les autorisations de la ressource
        $authorizations = $this->aclService->getAllAuthorizationsForResource($resource);

        // Suppression des doublons (même action de la même Identité)
        $indexedArray = [];
        foreach ($authorizations as $authorization) {
            $idIdentity = $authorization->getIdentity()->getId();
            $actionValue = $authorization->getAction()->getValue();
            $indexedArray[$idIdentity . '-' . $actionValue] = $authorization;
        }

        // Autorisations indexées par l'action pour éviter les doublons (même action de la même Identité)
        $authorizationsToCreate = [];

        foreach ($authorizations as $authorization) {
            $identity = $authorization->getIdentity();
            $users = [];
            if ($identity instanceof User) {
                $users = array($identity);
            } elseif ($identity instanceof Role) {
                $users = $identity->getUsers();
            }
            foreach ($users as $user) {
                // Indexe le tableau pour éviter les doublons
                $key = $authorization->getAction()->getValue() . '-' . $user->getId();
                $authorizationsToCreate[$key] = array($user, $authorization);
            }
        }

        foreach ($authorizationsToCreate as $array) {
            /** @var $authorization Authorization */
            list($user, $authorization) = $array;
            // Crée l'enregistrement dans le filtre
            $entry = new ACLFilterEntry($user, $authorization->getAction(), $resource);
            $this->insertACLFilterEntry($entry);
        }

        // Répète la même chose pour les ressources filles
        if ($generateResourcesHierarchy) {
            $children = $this->aclService->getAllChildResources([$resource]);
            foreach ($children as $childResource) {
                $this->generateForResource($childResource, $generateResourcesHierarchy, $cleanResourceFilter);
            }
        }
    }

    /**
     * Retourne les autorisations liées à une ressource et ses enfants
     *
     * @param EntityResource $resource
     * @param boolean        $generateResourcesHierarchy
     *
     * @return ACLFilterEntry[]
     */
    public function getEntriesForResource(EntityResource $resource, $generateResourcesHierarchy = true)
    {
        $entries = [];

        if ($resource->getEntity() !== null) {

            // Récupère les autorisations de la ressource
            $authorizations = $this->aclService->getAllAuthorizationsForResource($resource);

            // Suppression des doublons (même action de la même Identité)
            $indexedArray = [];
            foreach ($authorizations as $authorization) {
                $idIdentity = $authorization->getIdentity()->getId();
                $actionValue = $authorization->getAction()->getValue();
                $indexedArray[$idIdentity . '-' . $actionValue] = $authorization;
            }

            foreach ($authorizations as $authorization) {
                $identity = $authorization->getIdentity();
                $users = [];
                if ($identity instanceof User) {
                    $users = array($identity);
                } elseif ($identity instanceof Role) {
                    $users = $identity->getUsers();
                }
                foreach ($users as $user) {
                    $entry = new ACLFilterEntry($user, $authorization->getAction(), $resource);
                    $entries[$entry->getUniqueKey()] = $entry;
                }
            }

        }

        // Répète la même chose pour les ressources filles
        if ($generateResourcesHierarchy) {
            $children = $this->aclService->getAllChildResources([$resource]);
            foreach ($children as $childResource) {
                $entries += $this->getEntriesForResource($childResource, $generateResourcesHierarchy);
            }
        }

        return $entries;
    }

    /**
     * Vide le filtre des ACL pour une ressource spécifique
     *
     * @param EntityResource $resource
     * @param bool           $cleanResourcesHierarchy
     */
    public function cleanForResource(EntityResource $resource, $cleanResourcesHierarchy = true)
    {
        // Teste si le cache est activé
        if (!$this->enabled) {
            return;
        }

        // Ignore les ressources qui n'ont pas d'entité attribuée
        if ($resource->getEntityIdentifier() !== null) {
            $this->connection->delete(
                self::TABLE_NAME,
                [
                'entityName'       => $resource->getEntityName(),
                'entityIdentifier' => $resource->getEntityIdentifier(),
                ]
            );
        }

        // Répète la même chose pour les ressources filles
        if ($cleanResourcesHierarchy) {
            $children = $this->aclService->getAllChildResources([$resource]);
            foreach ($children as $childResource) {
                // false : ne parcourt pas la hiérarchie encore une fois
                $this->cleanForResource($childResource, false);
            }
        }
    }

    /**
     * Vide le filtre des ACL pour un utilisateur
     *
     * @param User $user
     */
    public function cleanForUser(User $user)
    {
        // Teste si le cache est activé
        if (!$this->enabled) {
            return;
        }
        $this->connection->delete(self::TABLE_NAME, ['idUser' => $user->getId()]);
    }

    /**
     * Retourne les autorisations d'un utilisateur
     *
     * @param User $user
     *
     * @return ACLFilterEntry[]
     */
    public function getEntriesForUser(User $user)
    {
        $entries = [];

        $authorizations = $this->aclService->getAllUserAuthorizations($user);

        foreach ($authorizations as $authorization) {

            /** @var $action Action */
            $action = $authorization['action'];
            $resource = $authorization['resource'];
            if ($resource instanceof EntityResource) {
                // Ignore les ressources qui n'ont pas d'entité attribuée
                if ($resource->getEntity() !== null) {
                    $entry = new ACLFilterEntry($user, $action, $resource);
                    $entries[$entry->getUniqueKey()] = $entry;
                }
            }

        }

        return $entries;
    }

    /**
     * @return int Nombre d'entrées dans le filtre des ACL
     */
    public function getEntriesCount()
    {
        return $this->connection->fetchColumn('SELECT COUNT(*) FROM ' . self::TABLE_NAME, [], 0);
    }

    /**
     * @param User[]|EntityResource[] $objects
     */
    public function generateFor(array $objects)
    {
        /** @var $entries ACLFilterEntry[] */
        $entries = [];

        foreach ($objects as $object) {
            if ($object instanceof EntityResource) {
                $entries += $this->getEntriesForResource($object);
            }
            if ($object instanceof User) {
                $entries += $this->getEntriesForUser($object);
            }
        }

        foreach ($entries as $entry) {
            $this->insertACLFilterEntry($entry);
        }
    }

    /**
     * @param ACLFilterEntry $entry
     */
    private function insertACLFilterEntry(ACLFilterEntry $entry)
    {
        $data = [
            'idUser'           => $entry->getIdUser(),
            'action'           => $entry->getAction()->exportToString(),
            'entityName'       => $entry->getEntityName(),
            'entityIdentifier' => $entry->getEntityIdentifier(),
        ];
        $this->connection->insert(self::TABLE_NAME, $data);
    }
}
