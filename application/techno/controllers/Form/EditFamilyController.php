<?php
/**
 * @author matthieu.napoli
 * @package Techno
 */

use Core\Annotation\Secure;

/**
 * @package Techno
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
        $idFamily = $this->getParam('id');
        /** @var $family Techno_Model_Family */
        $family = Techno_Model_Family::load($idFamily);
        // Validation du formulaire
        $formData = $this->getFormData('editFamily');
        $label = $formData->getValue('label');
        if (empty($label)) {
            $this->addFormError('label', __('UI', 'formValidation', 'emptyRequiredField'));
        }
        $ref = $formData->getValue('ref');
        if (empty($ref)) {
            $this->addFormError('ref', __('UI', 'formValidation', 'emptyRequiredField'));
        }
        $unit = $formData->getValue('unit');
        if (empty($unit)) {
            $this->addFormError('unit', __('UI', 'formValidation', 'emptyRequiredField'));
        }
        if (! $this->hasFormError()) {
            $family->setLabel($label);
            $family->setRef($ref);
            $family->setUnit(new Unit_API($unit));
            $this->setFormMessage(__('UI', 'message', 'updated'));
        } else {
            $this->setFormMessage('Erreur de validation du formulaire.');
        }
        $this->sendFormResponse();
    }

}
