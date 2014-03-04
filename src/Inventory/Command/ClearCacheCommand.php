<?php

namespace Inventory\Command;

use Doctrine\Common\Cache\Cache;
use Doctrine\Common\Cache\MemcachedCache;
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

    /**
     * @var Cache
     */
    private $cache;

    public function __construct(EntityManager $entityManager, Cache $cache)
    {
        $this->entityManager = $entityManager;
        $this->cache = $cache;

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

        if ($this->cache instanceof MemcachedCache) {
            $this->clearMemcached($this->cache);
            $output->writeln('Doctrine proxies regenerated');
        }
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

    /**
     * Vide le cache Memcached
     *
     * @todo Éviter de vider tout Memcached (ce qui vide le cache de toutes les applications et vide les sessions)
     */
    private function clearMemcached(MemcachedCache $cache)
    {
        $memcached = $cache->getMemcached();
        $memcached->flush();
    }
}
