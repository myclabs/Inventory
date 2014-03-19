<?php

namespace Inventory\Command\PopulateDB\BasicDataSet;

use Doctrine\ORM\EntityManager;
use MyCLabs\ACL\ACLManager;
use Symfony\Component\Console\Output\OutputInterface;
use User\Domain\ACL\AdminRole;
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
     * @var ACLManager
     */
    private $aclManager;

    public function run(OutputInterface $output)
    {
        $output->writeln('  <info>Populating Users</info>');

        // Crée un admin
        $admin = $this->userService->createUser('admin@myc-sense.com', 'myc-53n53');
        $admin->setLastName('Système');
        $admin->setFirstName('Administrateur');
        $admin->save();
        $this->entityManager->flush();

        $this->aclManager->grant($admin, new AdminRole($admin));
    }
}
