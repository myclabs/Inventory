<?php

use DI\Annotation\Inject;
use Doctrine\ORM\EntityManager;
use Mnapoli\Translated\Translator;
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
     * @Inject
     * @var Translator
     */
    protected $translator;

    /**
     * Helper pour les redirections.
     *
     * @var Zend_Controller_Action_Helper_Redirector
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
        // Toute cette manipulation est nécessaire pour contourner
        //  un bug de Zend Framework (les headers firebug ne sont pas envoyés sinon).
        //@see http://framework.zend.com/issues/browse/ZF-4134
        /** @var Zend_Controller_Action_Helper_Json $json */
        $json = $this->getHelper('Json');
        $json->suppressExit = true;
        $json->sendJson($reponse);
    }

    /**
     * Définit l'item qui est actif dans le menu de l'application.
     * @param string $item
     */
    protected function setActiveMenuItem($item)
    {
        $this->view->assign('activeMenu', $item);
    }

    protected function setActiveMenuItemOrganization($organizationId)
    {
        $this->setActiveMenuItem('organization-' . $organizationId);
    }

    protected function setActiveMenuItemAFLibrary($libraryId)
    {
        $this->setActiveMenuItem('af-' . $libraryId);
    }

    protected function setActiveMenuItemParameterLibrary($libraryId)
    {
        $this->setActiveMenuItem('parameter-' . $libraryId);
    }

    protected function setActiveMenuItemClassificationLibrary($libraryId)
    {
        $this->setActiveMenuItem('classification-' . $libraryId);
    }

    /**
     * Ajoute un niveau au "breadcrumb".
     * @param string $text
     * @param string $link
     */
    protected function addBreadcrumb($text, $link = null)
    {
        if (! is_array($this->view->breadcrumbs)) {
            $this->view->breadcrumbs = [];
        }
        $this->view->breadcrumbs[] = [
            'text' => $text,
            'link' => $link,
        ];
    }
}
