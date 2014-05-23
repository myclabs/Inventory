<?php

namespace User\Application\Command;

use Doctrine\ORM\EntityManager;
use MyCLabs\ACL\ACL;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Commande re-générant le filtre des ACL
 *
 * @author matthieu.napoli
 */
class RebuildACLCommand extends Command
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var ACL
     */
    private $acl;

    public function __construct(EntityManager $entityManager, ACL $acl)
    {
        $this->entityManager = $entityManager;
        $this->acl = $acl;

        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('acl:rebuild')
            ->setDescription('Rebuild les autorisations des ACL');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->entityManager->beginTransaction();

        try {
            $this->acl->rebuildAuthorizations();
        } catch (\Exception $e) {
            $this->entityManager->rollback();
            throw $e;
        }

        $this->entityManager->commit();
    }
}
