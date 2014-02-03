<?php

use AF\Domain\Component\Component;
use AF\Domain\Component\Select;
use Core\Annotation\Secure;

/**
 * @author matthieu.napoli
 */
class AF_Edit_ComponentsController extends Core_Controller
{
    /**
     * Popup qui affiche l'aide d'un composant
     * @Secure("editAF")
     */
    public function popupHelpAction()
    {
        $this->view->component = Component::load($this->getParam('id'));
        $this->_helper->layout()->disableLayout();
    }

    /**
     * Popup qui affiche les options d'un select
     * @Secure("editAF")
     */
    public function popupSelectOptionsAction()
    {
        $this->view->selectField = Select::load($this->getParam('idSelect'));
        $this->_helper->layout()->disableLayout();
    }
}
