<?php

use Classification\Domain\ClassificationLibrary;
use Classification\Domain\ContextIndicator;
use Classification\Domain\Indicator;
use Core\Annotation\Secure;
use Core\Translation\TranslatedString;
use Unit\UnitAPI;
use Unit\IncompatibleUnitsException;

class Classification_Datagrid_IndicatorController extends UI_Controller_Datagrid
{
    /**
     * @Secure("editClassificationLibrary")
     */
    public function getelementsAction()
    {
        /** @var ClassificationLibrary $library */
        $library = ClassificationLibrary::load($this->getParam('library'));

        foreach ($library->getIndicators() as $indicator) {
            $data = array();
            $data['index'] = $indicator->getId();
            $data['label'] = $this->cellTranslatedText($indicator->getLabel());
            $data['ref'] = $this->cellText($indicator->getRef());
            $data['unit'] = $this->cellText($indicator->getUnit()->getRef(), $indicator->getUnit()->getSymbol());
            $data['ratioUnit'] = $this->cellText($indicator->getRatioUnit()->getRef(), $indicator->getRatioUnit()->getSymbol());
            $canUp = !($indicator->getPosition() === 1);
            $canDown = !($indicator->getPosition() === $indicator->getLastEligiblePosition());
            $data['position'] = $this->cellPosition($indicator->getPosition(), $canUp, $canDown);
            $this->addline($data);
        }

        $this->send();
    }

    /**
     * @Secure("editClassificationLibrary")
     */
    public function addelementAction()
    {
        $library = ClassificationLibrary::load($this->getParam('library'));
        $ref = $this->getAddElementValue('ref');
        $label = $this->getAddElementValue('label');
        $unit = new UnitAPI($this->getAddElementValue('unit'));
        if (!$unit->exists()) {
            $this->setAddElementErrorMessage('unit', __('Unit', 'message', 'incorrectUnitIdentifier'));
        }
        $ratioUnit = new UnitAPI($this->getAddElementValue('ratioUnit'));
        if (!$ratioUnit->exists()) {
            $this->setAddElementErrorMessage('ratioUnit', __('Unit', 'message', 'incorrectUnitIdentifier'));
        }
        if ($unit->exists() && $ratioUnit->exists() && !$unit->isEquivalent($ratioUnit)) {
            $this->setAddElementErrorMessage('unit', __('Unit', 'message', 'incompatibleUnits'));
            $this->setAddElementErrorMessage('ratioUnit', __('Unit', 'message', 'incompatibleUnits'));
        }

        try {
            Core_Tools::checkRef($ref);
            try {
                $library->getIndicatorByRef($ref);
                $this->setAddElementErrorMessage('ref', __('UI', 'formValidation', 'alreadyUsedIdentifier'));
            } catch (Core_Exception_NotFound $e) {
                $label = $this->translationHelper->set(new TranslatedString(), $label);
                $indicator = new Indicator($library, $ref, $label, $unit, $ratioUnit);
            }
        } catch (Core_Exception_User $e) {
            $this->setAddElementErrorMessage('ref', $e->getMessage());
        }

        if (empty($this->_addErrorMessages)) {
            $indicator->save();
            $this->message = __('UI', 'message', 'added');
        }

        $this->send();
    }

    /**
     * @Secure("editClassificationLibrary")
     */
    public function deleteelementAction()
    {
        $indicator = Indicator::load($this->delete);

        $queryContextIndicator = new Core_Model_Query();
        $queryContextIndicator->filter->addCondition(ContextIndicator::QUERY_INDICATOR, $indicator);
        if (ContextIndicator::countTotal($queryContextIndicator) > 0) {
            throw new Core_Exception_User('Classification', 'indicator', 'IndicatorIsUsedInContextIndicator');
        }

        $indicator->delete();
        $this->message = __('UI', 'message', 'deleted');
        $this->send();
    }

    /**
     * @Secure("editClassificationLibrary")
     */
    public function updateelementAction()
    {
        $library = ClassificationLibrary::load($this->getParam('library'));

        $indicator = Indicator::load($this->update['index']);
        switch ($this->update['column']) {
            case 'label':
                $this->translationHelper->set($indicator->getLabel(), $this->update['value']);
                $this->message = __('UI', 'message', 'updated');
                break;
            case 'ref':
                Core_Tools::checkRef($this->update['value']);
                try {
                    if ($library->getIndicatorByRef($this->update['value']) !== $indicator) {
                        throw new Core_Exception_User('UI', 'formValidation', 'alreadyUsedIdentifier');
                    }
                } catch (Core_Exception_NotFound $e) {
                    $indicator->setRef($this->update['value']);
                    $this->message = __('UI', 'message', 'updated');
                }
                break;
            case 'unit':
                $unit = new UnitAPI($this->update['value']);
                if ($unit->exists()) {
                    try {
                        $indicator->setUnit($unit);
                    } catch (IncompatibleUnitsException $e) {
                        throw new Core_Exception_User('Unit', 'message', 'incompatibleUnits');
                    }
                    $this->message = __('UI', 'message', 'updated');
                } else {
                    throw new Core_Exception_User('Unit', 'message', 'incorrectUnitIdentifier');
                }
                break;
            case 'ratioUnit':
                $ratioUnit = new UnitAPI($this->update['value']);
                if ($ratioUnit->exists()) {
                    try {
                        $indicator->setRatioUnit($ratioUnit);
                    } catch (IncompatibleUnitsException $e) {
                        throw new Core_Exception_User('Unit', 'message', 'incompatibleUnits');
                    }
                    $this->message = __('UI', 'message', 'updated');
                } else {
                    throw new Core_Exception_User('Unit', 'message', 'incorrectUnitIdentifier');
                }
                break;
            case 'position':
                switch ($this->update['value']) {
                    case 'goFirst':
                        $indicator->setPosition(1);
                        break;
                    case 'goUp':
                        $indicator->goUp();
                        break;
                    case 'goDown':
                        $indicator->goDown();
                        break;
                    case 'goLast':
                        $indicator->setPosition($indicator->getLastEligiblePosition());
                        break;
                    default:
                        if ($this->update['value'] > $indicator->getLastEligiblePosition()) {
                            $this->update['value'] = $indicator->getLastEligiblePosition();
                        }
                        $indicator->setPosition((int) $this->update['value']);
                        break;
                }
                $this->update['value'] = $indicator->getPosition();
                $this->message = __('UI', 'message', 'updated');
                break;
            default:
                parent::updateelementAction();
                break;
        }
        $this->data = $this->update['value'];

        $this->send(true);
    }
}
