<?php
/**
 * @author valentin.claras
 * @author matthieu.napoli
 * @package Core
 * @subpackage Plugin
 */

/**
 * Plugin pour enregistrer en base de donnée les changements effetués sur les objets.
 *
 * @package Core
 * @subpackage Plugin
 */
class Core_Plugin_Flush extends Zend_Controller_Plugin_Abstract
{

    /**
     * Permet de détected si une erreur est arrivé sur un entityManager.
     * @var string
     */
    protected $databaseErrorHappened = null;


    /**
     * {@inheritdoc}
     */
    public function postDispatch(Zend_Controller_Request_Abstract $request)
    {
        // Permet d'executer le flush une seule fois, malgré les redirections et si aucune erreur n'est survenue.
        if (count($this->getResponse()->getException()) == 0) {
            /* @var $entityManager Doctrine\ORM\EntityManager */
            // Début des transactions pour l'ensemble des EntityManagers.
            foreach (Zend_Registry::get('EntityManagers') as $poolName => $entityManager) {
                if ($entityManager->isOpen()) {
                    $entityManager->getConnection()->beginTransaction();
                }
            }
            // Lancement des transactions !
            foreach (Zend_Registry::get('EntityManagers') as $poolName => $entityManager) {
                if ($entityManager->isOpen()) {
                    try {
                        $entityManager->flush();
                    } catch (Exception $e) {
                        $this->databaseErrorHappened = $e;
                        break;
                    }
                }
            }
            // Fin de la transaction.
            foreach (Zend_Registry::get('EntityManagers') as $poolName => $entityManager) {
                if ($entityManager->isOpen()) {
                    if ($this->databaseErrorHappened !== null) {
                        $entityManager->getConnection()->rollback();
                        $entityManager->clear();
                    } else {
                        $entityManager->getConnection()->commit();
                    }
                }
            }
            // Redirection sur le controller d'erreur en cas d'erreur.
            if ($this->databaseErrorHappened !== null) {
                throw $this->databaseErrorHappened;
            }
        }
    }

}
