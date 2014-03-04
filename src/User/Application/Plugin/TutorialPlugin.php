<?php

namespace User\Application\Plugin;

use Zend_Controller_Plugin_Abstract;
use Zend_Controller_Request_Abstract;
use Zend_Layout;

/**
 * Définition du plugin pour les tutoriaux
 */
class TutorialPlugin extends Zend_Controller_Plugin_Abstract
{
    /**
     * @Inject("feature.register")
     * @var bool
     */
    private $featureRegister;

    /**
     * Méthode appelée avant qu'une action ne soit distribuée par le distributeur.
     * Indique au layout que le tutorial doit être actif
     *
     * @param Zend_Controller_Request_Abstract $request Requête HTTP
     * @return void
     */
    public function preDispatch(Zend_Controller_Request_Abstract $request)
    {
        $layout = Zend_Layout::getMvcInstance();
        $view = $layout->getView();
        if ($this->featureRegister) {
            $view->tutorial = true;
        }
    }
}
