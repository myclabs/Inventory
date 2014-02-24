<?php

namespace User\Domain\ACL;

use Doctrine\ORM\EntityManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Output\OutputInterface;
use User\Domain\ACL\Authorization\Authorization;
use User\Domain\ACL\Resource\Resource;
use User\Domain\ACL\Role\OptimizedRole;
use User\Domain\ACL\Role\Role;
use User\Domain\User;

/**
 * Service gérant les droits d'accès.
 *
 * @author matthieu.napoli
 */
class ACLService
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(EntityManager $entityManager, LoggerInterface $logger)
    {
        $this->entityManager = $entityManager;
        $this->logger = $logger;
    }

    /**
     * Vérifie une autorisation d'accès à une ressource.
     *
     * Retourne un résultat sous forme de booléen (accès autorisé ou non).
     *
     * @param User                               $user     Demandeur de l'accès
     * @param Action                             $action   Action demandée
     * @param \User\Domain\ACL\Resource\Resource $resource Resource
     *
     * @throws \RuntimeException
     * @return boolean
     */
    public function isAllowed(User $user, Action $action, Resource $resource)
    {
        if ($resource->getId() == null) {
            throw new \RuntimeException(
                "La ressource " . get_class($resource)
                . " doit être persistée (id non null) pour pouvoir tester les autorisations"
            );
        }

        $repository = $this->entityManager->getRepository(get_class($resource));
        $qb = $repository->createQueryBuilder('r');
        $qb->select('count(r.id)')
            ->innerJoin('r.acl', 'auth')
            ->where('r.id = :resourceId')
            ->andWhere('auth.actionId = :actionId')
            ->andWhere('auth.user = :user');
        $qb->setParameter('resourceId', $resource->getId());
        $qb->setParameter('actionId', $action->exportToString());
        $qb->setParameter('user', $user);

        return ($qb->getQuery()->getSingleScalarResult() > 0);
    }

    /**
     * Ajoute un role à un utilisateur.
     *
     * Pour des raisons de performances, cette opération flush() et modifie la BDD directement.
     * Si on veut ensuite utiliser le filtre des ACL, il faudra donc recharger les ressources depuis la BDD.
     *
     * @param User $user
     * @param Role $role
     */
    public function addRole(User $user, Role $role)
    {
        $user->addRole($role);
        $role->save();

        if ($role instanceof OptimizedRole) {
            $this->entityManager->flush($role);
            $authorizations = $role->optimizedBuildAuthorizations();

            $this->insertAuthorizations($authorizations);
            return;
        }

        $role->buildAuthorizations();
    }

    /**
     * Retire un role d'un utilisateur.
     *
     * @param User $user
     * @param Role $role
     */
    public function removeRole(User $user, Role $role)
    {
        $user->removeRole($role);
        $role->delete();
    }

    /**
     * Regénère la liste des autorisations.
     *
     * @param OutputInterface|null $output Si non null, est utilisé pour donner plus d'informations.
     */
    public function rebuildAuthorizations(OutputInterface $output = null)
    {
        // Vide les autorisations
        if ($output) {
            $output->writeln('<comment>Clearing all authorizations</comment>');
        }
        foreach (Authorization::loadList() as $authorization) {
            $authorization->delete();
        }
        $this->entityManager->flush();
        $this->entityManager->clear();
        if ($output) {
            $output->writeln(sprintf('<info>%d authorizations left in database</info>', Authorization::countTotal()));
        }

        // Regénère les roles "non optimisés" qui utilisent les objets
        if ($output) {
            $output->writeln('<comment>Rebuilding authorizations for non-optimized roles</comment>');
        }
        foreach (User::loadList() as $user) {
            /** @var User $user */
            foreach ($user->getRoles() as $role) {
                if (! $role instanceof OptimizedRole) {
                    $role->buildAuthorizations();
                }
            }
        }
        $this->entityManager->flush();
        $this->entityManager->clear();
        if ($output) {
            $output->writeln(sprintf('<info>%d authorizations inserted</info>', Authorization::countTotal()));
        }

        // Regénère les "roles optimisés", ceux qui insèrent directement en BDD
        if ($output) {
            $output->writeln('<comment>Rebuilding authorizations for optimized roles</comment>');
        }
        foreach (User::loadList() as $user) {
            /** @var User $user */
            foreach ($user->getRoles() as $role) {
                if ($role instanceof OptimizedRole) {
                    $this->insertAuthorizations($role->optimizedBuildAuthorizations());
                }
            }
        }
        $this->entityManager->clear();
        if ($output) {
            $output->writeln(sprintf('<info>%d authorizations in total</info>', Authorization::countTotal()));
        }
    }

    /**
     * Insère une autorisation directement en BDD sans passer par l'ORM.
     *
     * @param Authorization[] $authorizations
     * @throws \Core_Exception_InvalidArgument La ressource doit être persistée
     * @throws \Exception
     */
    private function insertAuthorizations($authorizations)
    {
        $connection = $this->entityManager->getConnection();

        $tableNames = [];
        $rootTableNames = [];
        $discriminatorValues = [];

        $connection->beginTransaction();

        $i = 0;

        foreach ($authorizations as $authorization) {
            $class = get_class($authorization);
            if (! isset($tableNames[$class])) {
                $metadata = $this->entityManager->getClassMetadata(get_class($authorization));
                $rootMetadata = $this->entityManager->getClassMetadata($metadata->rootEntityName);

                $tableNames[$class] = $metadata->getTableName();
                $rootTableNames[$class] = $rootMetadata->getTableName();
                $discriminatorValues[$class] = $metadata->discriminatorValue;
            }

            $resourceId = $authorization->getResource()->getId();
            if (! $resourceId > 0) {
                throw new \Core_Exception_InvalidArgument(
                    "La ressource " . get_class($authorization->getResource())
                    . " doit être persistée (id non null) pour pouvoir y appliquer des autorisations"
                );
            }

            // Insert in Authorization table
            $parent = $authorization->getParentAuthorization();
            $data = [
                'role_id'                => $authorization->getRole()->getId(),
                'user_id'                => $authorization->getUser()->getId(),
                'actionId'               => $authorization->getActionId(),
                'parentAuthorization_id' => $parent ? $parent->getId() : null,
                'type'                   => $discriminatorValues[$class],
            ];
            $connection->insert($rootTableNames[$class], $data);
            $id = $connection->lastInsertId();

            $authorization->setId($id);

            // Insert in child table
            $data = [
                'id' => $id,
                'resource_id' => $resourceId,
            ];
            $connection->insert($tableNames[$class], $data);

            // Commit every 1000 inserts to avoid locking the table too long
            if (($i % 1000) === 0) {
                $connection->commit();
                $connection->beginTransaction();
            }

            $i++;
        }

        $connection->commit();
    }
}
