<?php
/**
 * @author  matthieu.napoli
 * @package AF
 */

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
        $this->view->component = AF_Model_Component::load($this->getParam('id'));
        $this->_helper->layout()->disableLayout();
    }

    /**
     * Popup qui affiche les options d'un select
     * @Secure("editAF")
     */
    public function popupSelectOptionsAction()
    {
        $this->view->selectField = AF_Model_Component_Select::load($this->getParam('idSelect'));
        $this->_helper->layout()->disableLayout();
    }

}
