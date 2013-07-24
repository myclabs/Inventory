<?php
/**
 * @author  matthieu.napoli
 * @author  hugo.charbonnier
 * @author  thibaud.rolland
 * @package AF
 */

use Core\Annotation\Secure;
use Unit\UnitAPI;

/**
 * @package AF
 */
class AF_Datagrid_Edit_Algos_NumericExpressionController extends UI_Controller_Datagrid
{

    /**
     * (non-PHPdoc)
     * @see UI_Controller_Datagrid::getelementsAction()
     * @Secure("editAF")
     */
    public function getelementsAction()
    {
        /** @var $af AF_Model_AF */
        $af = AF_Model_AF::load($this->getParam('id'));
        $algos = $af->getAlgos();
        foreach ($algos as $algo) {
            if ($algo instanceof Algo_Model_Numeric_Expression) {
                $data = [];
                $data['index'] = $algo->getId();
                $data['ref'] = $algo->getRef();
                $data['label'] = $algo->getLabel();
                $data['unit'] = $this->cellText($algo->getUnit()->getRef(), $algo->getUnit()->getSymbol());
                $data['expression'] = $this->cellLongText('af/edit_algos/popup-expression/id/' . $algo->getId(),
                                                          'af/datagrid_edit_algos_numeric-expression/get-expression/id/'
                                                              . $algo->getId(),
                                                          __('TEC', 'name', 'expression'),
                                                          'zoom-in');
                $contextIndicator = $algo->getContextIndicator();
                if ($contextIndicator) {
                    $ref = $contextIndicator->getContext()->getRef()
                        . "#" . $contextIndicator->getIndicator()->getRef();
                    $data['contextIndicator'] = $this->cellList($ref);
                }
                $data['resultIndex'] = $this->cellPopup($this->_helper->url('popup-indexation',
                                                                            'edit_algos',
                                                                            'af',
                                                                            ['id' => $algo->getId()]),
                                                        '<i class="icon-zoom-in"></i> ' . __('Algo',
                                                                                             'name',
                                                                                             'indexation'));
                $this->addLine($data);
            }
        }
        $this->send();
    }

    /**
     * (non-PHPdoc)
     * @see UI_Controller_Datagrid::addelementAction()
     * @Secure("editAF")
     */
    public function addelementAction()
    {
        /** @var $af AF_Model_AF */
        $af = AF_Model_AF::load($this->getParam('id'));
        $ref = $this->getAddElementValue('ref');
        if (empty($ref)) {
            $this->setAddElementErrorMessage('ref', __('UI', 'formValidation', 'emptyRequiredField'));
        }
        try {
            $unitRef = $this->getAddElementValue('unit');
            if (empty($unitRef)) {
                $this->setAddElementErrorMessage('unit', __('UI', 'formValidation', 'invalidUnit'));
            }
            $unit = new UnitAPI($unitRef);
            $unit->getNormalizedUnit();
        } catch (Core_Exception_NotFound $e) {
            $this->setAddElementErrorMessage('unit', __('UI', 'formValidation', 'invalidUnit'));
        }
        // Pas d'erreurs
        if (empty($this->_addErrorMessages)) {
            $algo = new Algo_Model_Numeric_Expression();
            try {
                $algo->setRef($ref);
            } catch (Core_Exception_User $e) {
                $this->setAddElementErrorMessage('ref', $e->getMessage());
                $this->send();
                return;
            }
            $algo->setLabel($this->getAddElementValue('label'));
            try {
                $algo->setExpression($this->getAddElementValue('expression'));
            } catch (TEC_Model_InvalidExpressionException $e) {
                $this->setAddElementErrorMessage('expression',
                                                 __('AF', 'configTreatmentMessage', 'invalidExpression')
                                                     . "<br>" . implode("<br>", $e->getErrors()));
                $this->send();
                return;
            }
            /** @noinspection PhpUndefinedVariableInspection */
            $algo->setUnit($unit);
            $algo->save();
            $af->addAlgo($algo);
            $af->save();
            try {
                $this->entityManager->flush();
            } catch (Core_ORM_DuplicateEntryException $e) {
                $this->setAddElementErrorMessage('ref', __('UI', 'formValidation', 'alreadyUsedIdentifier'));
                $this->send();
                return;
            }
            $this->message = __('UI', 'message', 'added');
        }
        $this->send();
    }

    /**
     * (non-PHPdoc)
     * @see UI_Controller_Datagrid::updateelementAction()
     * @Secure("editAF")
     */
    public function updateelementAction()
    {
        /** @var $algo Algo_Model_Numeric_Expression */
        $algo = Algo_Model_Numeric_Expression::load($this->update['index']);
        $newValue = $this->update['value'];
        switch ($this->update['column']) {
            case 'ref':
                $algo->setRef($newValue);
                $this->data = $algo->getRef();
                break;
            case 'label':
                $algo->setLabel($newValue);
                $this->data = $algo->getLabel();
                break;
            case 'unit':
                try {
                    if (empty($newValue)) {
                        throw new Core_Exception_User('UI', 'formValidation', 'invalidUnit');
                    }
                    $unit = new UnitAPI($newValue);
                    $unit->getNormalizedUnit();
                } catch (Core_Exception_NotFound $e) {
                    throw new Core_Exception_User('UI', 'formValidation', 'invalidUnit');
                }
                $algo->setUnit($unit);
                $this->data = $this->cellText($algo->getUnit()->getRef(), $algo->getUnit()->getSymbol());
                break;
            case 'expression':
                try {
                    $algo->setExpression($newValue);
                } catch (TEC_Model_InvalidExpressionException $e) {
                    throw new Core_Exception_User('AF', 'configTreatmentMessage', 'invalidExpressionWithErrors',
                                                  ['ERRORS' => implode("<br>", $e->getErrors())]);
                }
                $this->data = $this->cellLongText('af/edit_algos/popup-expression/id/' . $algo->getId(),
                                                  'af/datagrid_edit_algos_numeric-expression/get-expression/id/'
                                                      . $algo->getId(),
                                                  __('TEC', 'name', 'expression'),
                                                  'zoom-in');
                break;
            case 'contextIndicator':
                if ($newValue) {
                    $contextIndicator = $this->getContextIndicatorByRef($newValue);
                    $algo->setContextIndicator($contextIndicator);
                } else {
                    $algo->setContextIndicator(null);
                }
                break;
        }
        $algo->save();
        try {
            $this->entityManager->flush();
        } catch (Core_ORM_DuplicateEntryException $e) {
            throw new Core_Exception_User('UI', 'formValidation', 'alreadyUsedIdentifier');
        }
        $this->message = __('UI', 'message', 'updated');
        $this->send();
    }

    /**
     * (non-PHPdoc)
     * @see UI_Controller_Datagrid::deleteelementAction()
     * @Secure("editAF")
     */
    public function deleteelementAction()
    {
        /** @var $algo Algo_Model_Numeric_Expression */
        $algo = Algo_Model_Numeric_Expression::load($this->getParam('index'));
        $algo->delete();
        $algo->getSet()->removeAlgo($algo);
        $algo->getSet()->save();
        $this->entityManager->flush();
        $this->message = __('UI', 'message', 'deleted');
        $this->send();
    }

    /**
     * Fonction permettant de récupérer la forme brute de l'expression
     * @Secure("editAF")
     */
    public function getExpressionAction()
    {
        /** @var $algo Algo_Model_Numeric_Expression */
        $algo = Algo_Model_Numeric_Expression::load($this->getParam('id'));
        $this->data = $algo->getExpression();
        $this->send();
    }

    /**
     * Renvoie la liste des contextIndicator
     * @Secure("editAF")
     */
    public function getContextIndicatorListAction()
    {
        $this->addElementList(null, '');
        /** @var $contextIndicators Classif_Model_ContextIndicator[] */
        $contextIndicators = Classif_Model_ContextIndicator::loadList();
        foreach ($contextIndicators as $contextIndicator) {
            $this->addElementList($this->getContextIndicatorRef($contextIndicator),
                                  $this->getContextIndicatorLabel($contextIndicator));
        }
        $this->send();
    }

    /**
     * @param Classif_Model_ContextIndicator $contextIndicator
     * @return string
     */
    private function getContextIndicatorRef(Classif_Model_ContextIndicator $contextIndicator)
    {
        return $contextIndicator->getContext()->getRef()
            . '#' . $contextIndicator->getIndicator()->getRef();
    }

    /**
     * @param string $ref
     * @return Classif_Model_ContextIndicator
     */
    private function getContextIndicatorByRef($ref)
    {
        if (empty($ref)) {
            return null;
        }
        list($refContext, $refIndicator) = explode('#', $ref);
        return Classif_Model_ContextIndicator::loadByRef($refContext, $refIndicator);
    }

    /**
     * @param Classif_Model_ContextIndicator $contextIndicator
     * @return string
     */
    private function getContextIndicatorLabel(Classif_Model_ContextIndicator $contextIndicator)
    {
        return $contextIndicator->getIndicator()->getLabel() . ' - ' . $contextIndicator->getContext()->getLabel();
    }

}
