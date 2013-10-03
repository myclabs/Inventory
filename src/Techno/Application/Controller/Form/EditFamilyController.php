<?php

use Core\Annotation\Secure;
use Techno\Domain\Family\Family;
use Unit\UnitAPI;

/**
 * @author matthieu.napoli
 */
class Techno_Form_EditFamilyController extends Core_Controller
{
    use UI_Controller_Helper_Form;

    /**
     * Soumission du formulaire
     * @Secure("editTechno")
     */
    public function submitAction()
    {
        /** @var $family Family */
        $family = Family::load($this->getParam('id'));
        // Validation du formulaire
        $formData = $this->getFormData('editFamily');
        $label = $formData->getValue('label');
        if (empty($label)) {
            $this->addFormError('label', __('UI', 'formValidation', 'emptyRequiredField'));
        }
        $ref = $formData->getValue('ref');
        if (empty($ref)) {
            $this->addFormError('ref', __('UI', 'formValidation', 'emptyRequiredField'));
        } else {
            try {
                if (Family::loadByRef($ref) !== $family) {
                    $this->addFormError('ref', __('UI', 'formValidation', 'alreadyUsedIdentifier'));
                }
            } catch (Core_Exception_NotFound $e) {
                try {
                    Core_Tools::checkRef($ref);
                } catch (Core_Exception_User $e) {
                    $this->addFormError('ref', $e->getMessage());
                }
            }
        }
        $refUnit = $formData->getValue('unit');
        if (empty($refUnit)) {
            $this->addFormError('unit', __('UI', 'formValidation', 'emptyRequiredField'));
        } else {
            $unit = new UnitAPI($refUnit);
            if (!$unit->exists() || !$unit->isEquivalent($family->getBaseUnit()->getRef())) {
                $this->addFormError('unit', __('UI', 'formValidation', 'invalidUnit'));
            }
        }
        if (! $this->hasFormError()) {
            $family->setLabel($label);
            $family->setRef($ref);
            $family->setUnit(new UnitAPI($refUnit));
            $this->setFormMessage(__('UI', 'message', 'updated'));
        } else {
            $this->setFormMessage('Erreur de validation du formulaire.');
        }
        $this->sendFormResponse();
    }
}
