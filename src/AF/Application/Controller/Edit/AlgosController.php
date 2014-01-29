<?php

use AF\Domain\AF\AF;
use AF\Domain\Algorithm\Algo;
use AF\Domain\Algorithm\Condition\Elementary\NumericConditionAlgo;
use AF\Domain\Algorithm\Condition\Elementary\BooleanConditionAlgo;
use AF\Domain\Algorithm\Condition\Elementary\Select\SelectSingleConditionAlgo;
use AF\Domain\Algorithm\Condition\Elementary\Select\SelectMultiConditionAlgo;
use AF\Domain\Algorithm\Condition\ElementaryConditionAlgo;
use AF\Domain\Algorithm\Numeric\NumericParameterAlgo;
use Core\Annotation\Secure;

/**
 * @author matthieu.napoli
 */
class AF_Edit_AlgosController extends Core_Controller
{
    /**
     * Affichage de l'expression d'un algo
     * @Secure("editAF")
     */
    public function popupExpressionAction()
    {
        $this->view->algo = Algo::load($this->getParam('id'));
        $this->_helper->layout()->disableLayout();
    }

    /**
     * Édition de l'indexation d'un algo
     * @Secure("editAF")
     */
    public function popupIndexationAction()
    {
        $this->view->algo = Algo::load($this->getParam('id'));
        $this->_helper->layout()->disableLayout();
    }

    /**
     * Permet de modifier une condition de type elementary avec un popup personalisé.
     * @Secure("editAF")
     */
    public function updateConditionElementaryPopupAction()
    {
        $this->view->af = AF::load($this->getParam('idAf'));
        $this->view->algo = ElementaryConditionAlgo::load($this->getParam('idAlgo'));
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
        /** @var $af AF */
        $af = AF::load($this->getParam('idAf'));
        $algo = ElementaryConditionAlgo::load($this->getParam('idAlgo'));

        switch (get_class($algo)) {
            case NumericConditionAlgo::class:
                /** @var $algo NumericConditionAlgo */
                $algo->setRelation($this->getParam('relation'));
                if ($this->getParam('value') === null || $this->getParam('value') === '') {
                    $algo->setValue(null);
                } else {
                    $algo->setValue($this->getParam('value'));
                }
                break;
            case BooleanConditionAlgo::class:
                /** @var $algo BooleanConditionAlgo */
                $algo->setValue($this->getParam('value'));
                break;
            case SelectSingleConditionAlgo::class:
                /** @var $algo SelectSingleConditionAlgo */
                $algo->setRelation($this->getParam('relation'));
                $algo->setValue($this->getParam('value'));
                break;
            case SelectMultiConditionAlgo::class:
                /** @var $algo SelectMultiConditionAlgo */
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
        $this->view->af = AF::load($this->getParam('idAF'));
        /** @var $algo NumericParameterAlgo */
        $algo = NumericParameterAlgo::load($this->getParam('idAlgo'));
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
