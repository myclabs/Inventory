<?php
/**
 * @author  matthieu.napoli
 * @package AF
 */

use Core\Annotation\Secure;

/**
 * @package AF
 */
class AF_Edit_AlgosController extends Core_Controller_Ajax
{

    /**
     * Affichage de l'expression d'un algo
     * @Secure("editAF")
     */
    public function popupExpressionAction()
    {
        $this->view->algo = Algo_Model_Algo::load($this->_getParam('id'));
        $this->_helper->layout()->disableLayout();
    }

    /**
     * Édition de l'indexation d'un algo
     * @Secure("editAF")
     */
    public function popupIndexationAction()
    {
        $this->view->algo = Algo_Model_Algo::load($this->_getParam('id'));
        $this->_helper->layout()->disableLayout();
    }

    /**
     * Permet de modifier une condition de type elementary avec un popup personalisé.
     * @Secure("editAF")
     */
    public function updateConditionElementaryPopupAction()
    {
        $this->view->af = AF_Model_AF::load($this->_getParam('idAf'));
        $this->view->algo = Algo_Model_Condition_Elementary::load($this->_getParam('idAlgo'));
        $this->_helper->layout()->disableLayout();
    }

    /**
     * Permet de modifier une condition de type elementary avec un popup personalisé.
     * AJAX
     * @Secure("editAF")
     */
    public function updateConditionElementarySubmitAction()
    {
        if (!$this->getRequest()->isPost()) {
            throw new Core_Exception_NotFound("Page invalide");
        }
        /** @var $af AF_Model_AF */
        $af = AF_Model_AF::load($this->_getParam('idAf'));
        $algo = Algo_Model_Condition_Elementary::load($this->_getParam('idAlgo'));

        switch (get_class($algo)) {
            case 'Algo_Model_Condition_Elementary_Numeric':
                /** @var $algo Algo_Model_Condition_Elementary_Numeric */
                $algo->setRelation($this->_getParam('relation'));
                if ($this->_getParam('value') === null || $this->_getParam('value') === '') {
                    $algo->setValue(null);
                } else {
                    $algo->setValue($this->_getParam('value'));
                }
                break;
            case 'Algo_Model_Condition_Elementary_Boolean':
                /** @var $algo Algo_Model_Condition_Elementary_Boolean */
                $algo->setValue($this->_getParam('value'));
                break;
            case 'Algo_Model_Condition_Elementary_Select_Single':
                /** @var $algo Algo_Model_Condition_Elementary_Select_Single */
                $algo->setRelation($this->_getParam('relation'));
                $algo->setValue($this->_getParam('value'));
                break;
            case 'Algo_Model_Condition_Elementary_Select_Multi':
                /** @var $algo Algo_Model_Condition_Elementary_Select_Multi */
                $algo->setRelation($this->_getParam('relation'));
                $algo->setValue($this->_getParam('value'));
                break;
        }
        $algo->save();
        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->flush();
        $this->_redirect('/af/edit/menu/id/' . $af->getId() . '/onglet/traitement');
    }

    /**
     * Édition des coordonnées d'un algo paramètre
     * @Secure("editAF")
     */
    public function popupParameterCoordinatesAction()
    {
        $this->view->af = AF_Model_AF::load($this->_getParam('idAF'));
        /** @var $algo Algo_Model_Numeric_Parameter */
        $algo = Algo_Model_Numeric_Parameter::load($this->_getParam('idAlgo'));
        try {
            $family = $algo->getFamily();
        } catch (Core_Exception_NotFound $e) {
            $family = null;
        }
        $this->view->algo = $algo;
        $this->view->family = $family;
        $this->_helper->layout()->disableLayout();
    }

}
