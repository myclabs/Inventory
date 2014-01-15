<?php

use Doctrine\ORM\EntityManager;

/**
 * Mise à jour de la BDD
 *
 * @author valentin.claras
 * @author matthieu.napoli
 */
class Inventory_Update extends Core_Script_Action
{
    protected function runEnvironment($environment)
    {
        $c = \Core\ContainerSingleton::getContainer();
        $entityManager = $c->get(EntityManager::class);

        $this->updateDatabase($entityManager);
        echo "\t\tBase ".$c->get('db.name')." updated.".PHP_EOL;

        // Génère les proxies si ils sont écrits sur disque
        switch ($environment) {
            case 'test':
            case 'production':
                $this->generateProxies($entityManager);
                echo "\t\tProxies generated.".PHP_EOL;
                break;
        }
    }

    /**
     * Update Doctrine
     * @param EntityManager $em
     */
    private function updateDatabase(EntityManager $em)
    {
        // Utilisation du SchemaTool afin de créer les tables pour l'ensemble du Model.
        $schemaTool = new Doctrine\ORM\Tools\SchemaTool($em);
        $schemaTool->updateSchema($em->getMetadataFactory()->getAllMetadata());
    }

    /**
     * Génère les proxies Doctrine
     * @param Doctrine\ORM\EntityManager $em
     */
    private function generateProxies(EntityManager $em)
    {
        $proxyFactory = $em->getProxyFactory();
        $allMetadata = $em->getMetadataFactory()->getAllMetadata();

        $proxyFactory->generateProxyClasses($allMetadata);
    }
}
