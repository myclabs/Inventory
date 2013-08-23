<?php

use DI\Annotation\Inject;
use Doctrine\ORM\EntityManager;
use Psr\Log\LoggerInterface;

/**
 * Classe abstraite de contrôleur.
 *
 * Les droits sont vérifiés automatiquement avant qu'une action soit appelée.
 *
 * @author matthieu.napoli
 */
abstract class Core_Controller extends Zend_Controller_Action
{

    /**
     * @Inject
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @Inject
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Helper pour les redirections.
     *
     * @var $this->_helper->getHelper('Redirector');
     */
    protected $redirector;


    /**
     * Procédures d'initialisation pour chaque page.
     *
     * Charge les helpers
     *  à la vue et à cette instance de contrôleur.
     */
    public function init()
    {
        // Charge les helpers d'action.
        $this->redirector = $this->_helper->getHelper('Redirector');
    }

    /**
     * Envoie une réponse ajax encodée en Json.
     *
     * @param mixed $reponse N'importe quel type de variable.
     */
    public function sendJsonResponse($reponse)
    {
        /** @var Zend_Controller_Action_Helper_Json $json */
        $json = $this->getHelper('Json');
        $json->sendJson($reponse);
    }

}
