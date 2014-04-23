<?php

use AF\Domain\AF;
use AF\Domain\Algorithm\Numeric\NumericExpressionAlgo;
use Classification\Domain\ClassificationLibrary;
use Classification\Domain\ContextIndicator;
use Core\Annotation\Secure;
use Unit\UnitAPI;
use TEC\Exception\InvalidExpressionException;

class AF_Datagrid_Edit_Algos_NumericExpressionController extends UI_Controller_Datagrid
{
    /**
     * @Secure("editAF")
     */
    public function getelementsAction()
    {
        /** @var $af AF */
        $af = AF::load($this->getParam('id'));
        $algos = $af->getAlgos();
        foreach ($algos as $algo) {
            if ($algo instanceof NumericExpressionAlgo) {
                $data = [];
                $data['index'] = $algo->getId();
                $data['ref'] = $algo->getRef();
                $data['label'] = $algo->getLabel();
                $data['unit'] = $this->cellText($algo->getUnit()->getRef(), $algo->getUnit()->getSymbol());
                $data['expression'] = $this->cellLongText(
                    'af/edit_algos/popup-expression/idAF/' . $af->getId() . '/algo/' . $algo->getId(),
                    'af/datagrid_edit_algos_numeric-expression/get-expression/idAF/' . $af->getId() . '/algo/'
                    . $algo->getId(),
                    __('TEC', 'name', 'expression'),
                    'zoom-in'
                );
                $contextIndicator = $algo->getContextIndicator();
                if ($contextIndicator) {
                    $data['contextIndicator'] = $this->cellList($contextIndicator->getId());
                }
                $data['resultIndex'] = $this->cellPopup(
                    $this->_helper->url('popup-indexation', 'edit_algos', 'af', [
                        'idAF' => $af->getId(),
                        'algo' => $algo->getId(),
                    ]),
                    '<i class="fa fa-search-plus"></i> ' . __('Algo', 'name', 'indexation')
                );
                $this->addLine($data);
            }
        }
        $this->send();
    }

    /**
     * @Secure("editAF")
     */
    public function addelementAction()
    {
        /** @var $af AF */
        $af = AF::load($this->getParam('id'));
        $ref = $this->getAddElementValue('ref');
        if (empty($ref)) {
            $this->setAddElementErrorMessage('ref', __('UI', 'formValidation', 'emptyRequiredField'));
        }
        $unit = new UnitAPI($this->getAddElementValue('unit'));
        if (! $unit->exists()) {
            $this->setAddElementErrorMessage('unit', __('UI', 'formValidation', 'invalidUnit'));
        }
        // Pas d'erreurs
        if (empty($this->_addErrorMessages)) {
            $algo = new NumericExpressionAlgo();
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
            } catch (InvalidExpressionException $e) {
                $this->setAddElementErrorMessage(
                    'expression',
                    __('AF', 'configTreatmentMessage', 'invalidExpression') . "<br>"
                    . implode("<br>", $e->getErrors())
                );
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
     * @Secure("editAF")
     */
    public function updateelementAction()
    {
        /** @var $algo NumericExpressionAlgo */
        $algo = NumericExpressionAlgo::load($this->update['index']);
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
                $unit = new UnitAPI($newValue);
                if (! $unit->exists()) {
                    throw new Core_Exception_User('UI', 'formValidation', 'invalidUnit');
                }
                $algo->setUnit($unit);
                $this->data = $this->cellText($algo->getUnit()->getRef(), $algo->getUnit()->getSymbol());
                break;
            case 'expression':
                try {
                    $algo->setExpression($newValue);
                } catch (InvalidExpressionException $e) {
                    throw new Core_Exception_User('AF', 'configTreatmentMessage', 'invalidExpressionWithErrors',
                                                  ['ERRORS' => implode("<br>", $e->getErrors())]);
                }
                $this->data = null;
                break;
            case 'contextIndicator':
                if ($newValue) {
                    $contextIndicator = ContextIndicator::load($newValue);
                    $algo->setContextIndicator($contextIndicator);
                    $this->data = $this->cellList($contextIndicator->getId());
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
     * @Secure("editAF")
     */
    public function deleteelementAction()
    {
        /** @var $algo NumericExpressionAlgo */
        $algo = NumericExpressionAlgo::load($this->getParam('index'));
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
        /** @var $algo NumericExpressionAlgo */
        $algo = NumericExpressionAlgo::load($this->getParam('algo'));
        $this->data = $algo->getExpression();
        $this->send();
    }

    /**
     * Renvoie la liste des contextIndicator
     * @Secure("editAF")
     */
    public function getContextIndicatorListAction()
    {
        /** @var $af AF */
        $af = AF::load($this->getParam('id'));
        $classificationLibraries = ClassificationLibrary::loadUsableInAccount($af->getLibrary()->getAccount());

        $this->addElementList(null, '');

        foreach ($classificationLibraries as $library) {
            foreach ($library->getContextIndicators() as $contextIndicator) {
                $this->addElementList(
                    $contextIndicator->getId(),
                    $library->getLabel() . ' > ' . $contextIndicator->getLabel()
                );
            }
        }

        $this->send();
    }
}
