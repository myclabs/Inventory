<?php

namespace Inventory\Command\PopulateDB\BasicDataSet;

use Doctrine\ORM\EntityManager;
use Orga_Model_Organization;
use Symfony\Component\Console\Output\OutputInterface;
use User\Domain\ACL\ACLService;
use User\Domain\ACL\Resource\NamedResource;
use User\Domain\ACL\Role\AdminRole;
use User\Domain\User;
use User\Domain\UserService;

class PopulateUser
{
    /**
     * @Inject
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @Inject
     * @var UserService
     */
    private $userService;

    /**
     * @Inject
     * @var ACLService
     */
    private $aclService;

    public function run(OutputInterface $output)
    {
        $output->writeln('  <info>Populating Users</info>');

        // Ressource "Repository"
        $repository = new NamedResource('repository');
        $repository->save();
        // Ressource abstraite "tous les utilisateurs"
        $allUsers = new NamedResource(User::class);
        $allUsers->save();
        // Ressource abstraite "toutes les organisations"
        $allOrganizations = new NamedResource(Orga_Model_Organization::class);
        $allOrganizations->save();

        $this->entityManager->flush();

        // Crée un admin
        $admin = $this->userService->createUser('admin@myc-sense.com', 'myc-53n53');
        $admin->setLastName('Système');
        $admin->setFirstName('Administrateur');
        $admin->save();
        $this->aclService->addRole($admin, new AdminRole($admin));

        $this->entityManager->flush();
    }
}
