<?php

use Core\Annotation\Secure;
use Parameter\Domain\Family\Family;

/**
 * @author matthieu.napoli
 */
class Parameter_ElementController extends Core_Controller
{
    use UI_Controller_Helper_Form;

    /**
     * Détails d'un élément
     * @Secure("editParameter")
     */
    public function detailsAction()
    {
        $family = Family::load($this->getParam('idFamily'));

        $coordinates = json_decode($this->getParam('coordinates'));
        $cell = $family->getCell($this->getMembersFromCoordinates($family, $coordinates));

        $value = $cell->getValue();

        $value = $value ?: new Calc_Value();

        $this->view->assign('family', $family);
        $this->view->assign('coordinates', $coordinates);
        $this->view->assign('value', $value);
        $this->_helper->layout()->disableLayout();
    }

    /**
     * Modification d'un élément
     * @Secure("editParameter")
     */
    public function editSubmitAction()
    {
        $locale = Core_Locale::loadDefault();

        $formData = $this->getFormData('element_editForm');

        $family = Family::load($formData->getValue('idFamily'));

        $coordinates = json_decode($formData->getValue('coordinates'));
        $cell = $family->getCell($this->getMembersFromCoordinates($family, $coordinates));

        // Validation du formulaire
        try {
            $digitalValue = $locale->readNumber($formData->getValue('digitalValue'));
            if (is_null($digitalValue)) {
                $this->addFormError('digitalValue', __('UI', 'formValidation', 'emptyRequiredField'));
            }
        } catch (Core_Exception_InvalidArgument $e) {
            $this->addFormError('digitalValue', __('UI', 'formValidation', 'invalidNumber'));
        }
        try {
            $uncertainty = $locale->readInteger($formData->getValue('uncertainty'));
        } catch (Core_Exception_InvalidArgument $e) {
            $this->addFormError('uncertainty', __('UI', 'formValidation', 'invalidUncertainty'));
        }

        if (! $this->hasFormError()) {
            /** @noinspection PhpUndefinedVariableInspection */
            if ($digitalValue !== null && $uncertainty === null) {
                $uncertainty = 0;
            }
            /** @noinspection PhpUndefinedVariableInspection */
            $value = new Calc_Value($digitalValue, $uncertainty);
            $cell->setValue($value);
            $cell->save();
            $this->entityManager->flush();
            $this->setFormMessage(__('UI', 'message', 'updated'));

            $this->sendFormResponse([
                'elementId' => implode('-', (array) $coordinates),
                'value' => $locale->formatNumber($value->getDigitalValue(), 3),
                'uncertainty' => $locale->formatUncertainty($value->getRelativeUncertainty()),
            ]);
            return;
        }

        $this->setFormMessage('Erreur de validation du formulaire.');
        $this->sendFormResponse();
    }

    private function getMembersFromCoordinates(family $family, $coordinates)
    {
        $members = [];

        foreach ($coordinates as $dimensionRef => $memberRef) {
            $dimension = $family->getDimension($dimensionRef);
            $members[] = $dimension->getMember($memberRef);
        }

        return $members;
    }
}
