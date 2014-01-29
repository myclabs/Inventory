<?php
/**
 * @author  matthieu.napoli
 * @package AF
 */

use AF\Domain\AF\Component;
use AF\Domain\AF\Component\Select;
use Core\Annotation\Secure;

/**
 * @package AF
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
