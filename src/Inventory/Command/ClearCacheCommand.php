<?php

namespace Inventory\Command;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Vide les caches.
 *
 * @author matthieu.napoli
 */
class ClearCacheCommand extends Command
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;

        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('cache:clear')
            ->setDescription('Vide les caches');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->regenerateProxies();
        $output->writeln('Doctrine proxies regenerated');
    }

    /**
     * Regénère les proxies Doctrine
     */
    private function regenerateProxies()
    {
        $proxyFactory = $this->entityManager->getProxyFactory();
        $allMetadata = $this->entityManager->getMetadataFactory()->getAllMetadata();

        $proxyFactory->generateProxyClasses($allMetadata);
    }
}
