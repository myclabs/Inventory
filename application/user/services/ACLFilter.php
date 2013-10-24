<?php
/**
 * @author     matthieu.napoli
 * @package    User
 * @subpackage Service
 */

use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManager;

/**
 * Gestion du filtre des ACL en BDD
 * @package    User
 * @subpackage Service
 */
class User_Service_ACLFilter
{

    const TABLE_NAME = 'ACL_Filter';

    /**
     * Activation du filtrage (actif par défaut)
     * @var bool
     */
    public $enabled = true;

    /**
     * @var User_Service_ACL
     */
    private $aclService;

    /**
     * @var Connection
     */
    private $connection;


    /**
     * @param User_Service_ACL $aclService
     * @param EntityManager    $entityManager
     */
    public function __construct(User_Service_ACL $aclService, EntityManager $entityManager)
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
        /** @var $resources User_Model_Resource_Entity */
        $resources = User_Model_Resource_Entity::loadList();

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
     * @param User_Model_Resource_Entity[] $resources
     * @param bool                         $generateResourcesHierarchy
     * @param bool                         $cleanResourceFilter
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
     * @param User_Model_Resource_Entity $resource
     * @param boolean                    $generateResourcesHierarchy
     * @param boolean                    $cleanResourceFilter
     */
    public function generateForResource(
        User_Model_Resource_Entity $resource,
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
            if ($identity instanceof User_Model_User) {
                $users = array($identity);
            } elseif ($identity instanceof User_Model_Role) {
                $users = $identity->getUsers();
            }
            foreach ($users as $user) {
                // Indexe le tableau pour éviter les doublons
                $key = $authorization->getAction()->getValue() . '-' . $user->getId();
                $authorizationsToCreate[$key] = array($user, $authorization);
            }
        }

        foreach ($authorizationsToCreate as $array) {
            /** @var $authorization User_Model_Authorization */
            list($user, $authorization) = $array;
            // Crée l'enregistrement dans le filtre
            $entry = new User_Model_ACLFilterEntry($user, $authorization->getAction(), $resource);
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
     * @param User_Model_Resource_Entity $resource
     * @param boolean                    $generateResourcesHierarchy
     *
     * @return User_Model_ACLFilterEntry[]
     */
    public function getEntriesForResource(User_Model_Resource_Entity $resource, $generateResourcesHierarchy = true)
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
                if ($identity instanceof User_Model_User) {
                    $users = array($identity);
                } elseif ($identity instanceof User_Model_Role) {
                    $users = $identity->getUsers();
                }
                foreach ($users as $user) {
                    $entry = new User_Model_ACLFilterEntry($user, $authorization->getAction(), $resource);
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
     * @param User_Model_Resource_Entity $resource
     * @param bool                       $cleanResourcesHierarchy
     */
    public function cleanForResource(User_Model_Resource_Entity $resource, $cleanResourcesHierarchy = true)
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
     * @param User_Model_User $user
     */
    public function cleanForUser(User_Model_User $user)
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
     * @param User_Model_User $user
     *
     * @return User_Model_ACLFilterEntry[]
     */
    public function getEntriesForUser(User_Model_User $user)
    {
        $entries = [];

        $authorizations = $this->aclService->getAllUserAuthorizations($user);

        foreach ($authorizations as $authorization) {

            /** @var $action User_Model_Action */
            $action = $authorization['action'];
            $resource = $authorization['resource'];
            if ($resource instanceof User_Model_Resource_Entity) {
                // Ignore les ressources qui n'ont pas d'entité attribuée
                if ($resource->getEntity() !== null) {
                    $entry = new User_Model_ACLFilterEntry($user, $action, $resource);
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
     * @param User_Model_User[]|User_Model_Resource_Entity[] $objects
     */
    public function generateFor(array $objects)
    {
        /** @var $entries User_Model_ACLFilterEntry[] */
        $entries = [];

        foreach ($objects as $object) {
            if ($object instanceof User_Model_Resource_Entity) {
                $entries += $this->getEntriesForResource($object);
            }
            if ($object instanceof User_Model_User) {
                $entries += $this->getEntriesForUser($object);
            }
        }

        foreach ($entries as $entry) {
            $this->insertACLFilterEntry($entry);
        }
    }

    /**
     * @param User_Model_ACLFilterEntry $entry
     */
    private function insertACLFilterEntry(User_Model_ACLFilterEntry $entry)
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
