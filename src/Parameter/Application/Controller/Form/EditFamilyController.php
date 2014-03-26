<?php

use Core\Annotation\Secure;
use Parameter\Domain\Family\Family;
use Unit\UnitAPI;

/**
 * @author matthieu.napoli
 */
class Parameter_Form_EditFamilyController extends Core_Controller
{
    use UI_Controller_Helper_Form;

    /**
     * Soumission du formulaire
     * @Secure("editParameterFamily")
     */
    public function submitAction()
    {
        /** @var $family Family */
        $family = Family::load($this->getParam('id'));

        // Label
        $label = $this->getParam('label');
        if (empty($label)) {
            $this->addFormError('label', __('UI', 'formValidation', 'emptyRequiredField'));
        }
        // Ref
        $ref = $this->getParam('ref');
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
        // Unit
        $refUnit = $this->getParam('unit');
        if (empty($refUnit)) {
            $this->addFormError('unit', __('UI', 'formValidation', 'emptyRequiredField'));
        } else {
            $unit = new UnitAPI($refUnit);
            if (! $unit->exists()) {
                $this->addFormError('unit', __('UI', 'formValidation', 'invalidUnit'));
            }
        }
        // Documentation
        $documentation = $this->getParam('documentation');

        if (! $this->hasFormError()) {
            $family->setLabel($label);
            $family->setRef($ref);
            $family->setUnit(new UnitAPI($refUnit));
            $family->setDocumentation($documentation);
            $this->setFormMessage(__('UI', 'message', 'updated'));
        } else {
            $this->setFormMessage('Erreur de validation du formulaire.');
        }

        $this->sendFormResponse();
    }
}
