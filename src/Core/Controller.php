<?php
/**
 * @author     matthieu.napoli
 * @package    Core
 * @subpackage Controller
 */

/**
 * Classe abstraite de contrôleur.
 *
 * Les droits sont vérifiés automatiquement avant qu'une action soit appelée.
 *
 * @package    Core
 * @subpackage Controller
 */
abstract class Core_Controller extends Zend_Controller_Action
{
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

}
