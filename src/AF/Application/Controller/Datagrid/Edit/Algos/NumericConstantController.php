<?php

use AF\Domain\AF;
use AF\Domain\Algorithm\Numeric\NumericConstantAlgo;
use Classification\Domain\ClassificationLibrary;
use Classification\Domain\ContextIndicator;
use Core\Annotation\Secure;
use Unit\UnitAPI;

class AF_Datagrid_Edit_Algos_NumericConstantController extends UI_Controller_Datagrid
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
            if ($algo instanceof NumericConstantAlgo) {
                $data = [];
                $data['index'] = $algo->getId();
                $data['ref'] = $algo->getRef();
                $data['label'] = $this->cellTranslatedText($algo->getLabel());
                $data['unit'] = $this->cellText(
                    $algo->getUnit()->getRef(),
                    $this->translationHelper->toString($algo->getUnit()->getSymbol())
                );
                $data['value'] = $this->cellNumber($algo->getUnitValue()->getDigitalValue());
                $data['uncertainty'] = $this->cellNumber($algo->getUnitValue()->getRelativeUncertainty());
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
        $locale = Core_Locale::loadDefault();
        // Ref validation
        $ref = $this->getAddElementValue('ref');
        if (empty($ref)) {
            $this->setAddElementErrorMessage('ref', __('UI', 'formValidation', 'emptyRequiredField'));
        }
        // Unit validation
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
        // Value validation
        $rawValue = $this->getAddElementValue('value');
        if (empty($rawValue)) {
            $this->setAddElementErrorMessage('value', __('UI', 'formValidation', 'emptyRequiredField'));
        } else {
            try {
                $value = $locale->readNumber($rawValue);
            } catch(Core_Exception_InvalidArgument $e) {
                $this->setAddElementErrorMessage('value', __('UI', 'formValidation', 'invalidNumber'));
            }
        }
        // Uncertainty validation
        try {
            $uncertainty = $locale->readInteger($this->getAddElementValue('uncertainty'));
            if ($uncertainty === null) {
                $uncertainty = 0;
            }
        } catch(Core_Exception_InvalidArgument $e) {
            $this->setAddElementErrorMessage('uncertainty', __('UI', 'formValidation', 'invalidNumber'));
        }
        // Pas d'erreurs
        if (empty($this->_addErrorMessages)) {
            $algo = new NumericConstantAlgo();
            try {
                $algo->setRef($ref);
            } catch (Core_Exception_User $e) {
                $this->setAddElementErrorMessage('ref', $e->getMessage());
                $this->send();
                return;
            }
            $this->translationHelper->set($algo->getLabel(), $this->getAddElementValue('label'));
            /** @noinspection PhpUndefinedVariableInspection */
            $algo->setUnitValue(new Calc_UnitValue($unit, $value, $uncertainty));
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
        /** @var $algo NumericConstantAlgo */
        $algo = NumericConstantAlgo::load($this->update['index']);
        $locale = Core_Locale::loadDefault();
        $newValue = $this->update['value'];
        switch ($this->update['column']) {
            case 'ref':
                $algo->setRef($newValue);
                $this->data = $algo->getRef();
                break;
            case 'label':
                $this->translationHelper->set($algo->getLabel(), $newValue);
                $this->data = $this->cellTranslatedText($algo->getLabel());
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
                $algo->setUnitValue(new Calc_UnitValue(
                    $unit,
                    $algo->getUnitValue()->getDigitalValue(),
                    $algo->getUnitValue()->getRelativeUncertainty()
                ));
                $this->data = $this->cellText(
                    $algo->getUnit()->getRef(),
                    $this->translationHelper->toString($algo->getUnit()->getSymbol())
                );
                break;
            case 'value':
                if (empty($newValue)) {
                    throw new Core_Exception_User('UI', 'formValidation', 'emptyRequiredField');
                }
                try {
                    $newValue = $locale->readNumber($newValue);
                } catch(Core_Exception_InvalidArgument $e) {
                    throw new Core_Exception_User('UI', 'formValidation', 'invalidNumber');
                }
                $unitValue = $algo->getUnitValue()->copyWithNewValue($newValue);
                $algo->setUnitValue($unitValue);
                $this->data = $unitValue->getDigitalValue();
                break;
            case 'uncertainty':
                try {
                    $newValue = $locale->readInteger($newValue);
                } catch(Core_Exception_InvalidArgument $e) {
                    throw new Core_Exception_User('UI', 'formValidation', 'invalidNumber');
                }
                $unitValue = $algo->getUnitValue()->copyWithNewUncertainty($newValue);
                $algo->setUnitValue($unitValue);
                $this->data = $unitValue->getRelativeUncertainty();
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
        /** @var $algo NumericConstantAlgo */
        $algo = NumericConstantAlgo::load($this->getParam('index'));
        $algo->delete();
        $algo->getSet()->removeAlgo($algo);
        $algo->getSet()->save();
        $this->entityManager->flush();
        $this->message = __('UI', 'message', 'deleted');
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
                    $this->translationHelper->toString($library->getLabel()) . ' > '
                    . $this->translationHelper->toString($contextIndicator->getLabel())
                );
            }
        }

        $this->send();
    }
}
