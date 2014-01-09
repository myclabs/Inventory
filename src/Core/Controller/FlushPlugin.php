<?php

namespace Core\Controller;

use Zend_Controller_Plugin_Abstract;
use Exception;
use Zend_Controller_Request_Abstract;
use Doctrine;

/**
 * Plugin pour enregistrer en base de donnée les changements effetués sur les objets.
 *
 * @author valentin.claras
 * @author matthieu.napoli
 */
class FlushPlugin extends Zend_Controller_Plugin_Abstract
{
    /**
     * Permet de détected si une erreur est arrivé sur un entityManager.
     * @var \Exception|null
     */
    protected $databaseErrorHappened;

    /**
     * {@inheritdoc}
     */
    public function postDispatch(Zend_Controller_Request_Abstract $request)
    {
        // Permet d'executer le flush une seule fois, malgré les redirections et si aucune erreur n'est survenue.
        if (count($this->getResponse()->getException()) == 0) {
            // TODO utiliser l'injection de dépendances plutot
            $entityManager = \Core\ContainerSingleton::getEntityManager();

            if ($entityManager->isOpen()) {
                $entityManager->getConnection()->beginTransaction();
            }

            if ($entityManager->isOpen()) {
                try {
                    $entityManager->flush();
                } catch (Exception $e) {
                    $this->databaseErrorHappened = $e;
                }
            }

            if ($entityManager->isOpen()) {
                if ($this->databaseErrorHappened !== null) {
                    $entityManager->getConnection()->rollback();
                    $entityManager->clear();
                } else {
                    $entityManager->getConnection()->commit();
                }
            }

            // Redirection sur le controller d'erreur en cas d'erreur.
            if ($this->databaseErrorHappened !== null) {
                throw $this->databaseErrorHappened;
            }
        }
    }
}
