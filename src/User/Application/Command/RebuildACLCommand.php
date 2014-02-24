<?php

namespace User\Application\Command;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use User\Domain\ACL\ACLService;

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
     * @var ACLService
     */
    private $aclService;

    public function __construct(EntityManager $entityManager, ACLService $aclService)
    {
        $this->entityManager = $entityManager;
        $this->aclService = $aclService;

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
            $this->aclService->rebuildAuthorizations($output);
        } catch (\Exception $e) {
            $this->entityManager->rollback();
            throw $e;
        }

        $this->entityManager->flush();
        $this->entityManager->commit();
    }
}
