<?php
/**
 * @author  matthieu.napoli
 * @package AF
 */

use Core\Annotation\Secure;

/**
 * @package AF
 */
class AF_Edit_AlgosController extends Core_Controller
{

    /**
     * Affichage de l'expression d'un algo
     * @Secure("editAF")
     */
    public function popupExpressionAction()
    {
        $this->view->algo = Algo_Model_Algo::load($this->getParam('id'));
        $this->_helper->layout()->disableLayout();
    }

    /**
     * Édition de l'indexation d'un algo
     * @Secure("editAF")
     */
    public function popupIndexationAction()
    {
        $this->view->algo = Algo_Model_Algo::load($this->getParam('id'));
        $this->_helper->layout()->disableLayout();
    }

    /**
     * Permet de modifier une condition de type elementary avec un popup personalisé.
     * @Secure("editAF")
     */
    public function updateConditionElementaryPopupAction()
    {
        $this->view->af = AF_Model_AF::load($this->getParam('idAf'));
        $this->view->algo = Algo_Model_Condition_Elementary::load($this->getParam('idAlgo'));
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
        $af = AF_Model_AF::load($this->getParam('idAf'));
        $algo = Algo_Model_Condition_Elementary::load($this->getParam('idAlgo'));

        switch (get_class($algo)) {
            case 'Algo_Model_Condition_Elementary_Numeric':
                /** @var $algo Algo_Model_Condition_Elementary_Numeric */
                $algo->setRelation($this->getParam('relation'));
                if ($this->getParam('value') === null || $this->getParam('value') === '') {
                    $algo->setValue(null);
                } else {
                    $algo->setValue($this->getParam('value'));
                }
                break;
            case 'Algo_Model_Condition_Elementary_Boolean':
                /** @var $algo Algo_Model_Condition_Elementary_Boolean */
                $algo->setValue($this->getParam('value'));
                break;
            case 'Algo_Model_Condition_Elementary_Select_Single':
                /** @var $algo Algo_Model_Condition_Elementary_Select_Single */
                $algo->setRelation($this->getParam('relation'));
                $algo->setValue($this->getParam('value'));
                break;
            case 'Algo_Model_Condition_Elementary_Select_Multi':
                /** @var $algo Algo_Model_Condition_Elementary_Select_Multi */
                $algo->setRelation($this->getParam('relation'));
                $algo->setValue($this->getParam('value'));
                break;
        }
        $algo->save();
        $this->entityManager->flush();
        $this->redirect('/af/edit/menu/id/' . $af->getId() . '/onglet/traitement');
    }

    /**
     * Édition des coordonnées d'un algo paramètre
     * @Secure("editAF")
     */
    public function popupParameterCoordinatesAction()
    {
        $this->view->af = AF_Model_AF::load($this->getParam('idAF'));
        /** @var $algo Algo_Model_Numeric_Parameter */
        $algo = Algo_Model_Numeric_Parameter::load($this->getParam('idAlgo'));
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
