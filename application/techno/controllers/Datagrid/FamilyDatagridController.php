<?php
/**
 * @author matthieu.napoli
 * @package Techno
 */

use Core\Annotation\Secure;
use Unit\UnitAPI;

/**
 * @package Techno
 */
class Techno_Datagrid_FamilyDatagridController extends UI_Controller_Datagrid
{

    /**
     * (non-PHPdoc)
     * @see UI_Controller_Datagrid::getelementsAction()
     * @Secure("viewTechno")
     */
    public function getelementsAction()
    {
        // Récupération des familles
        $families = Techno_Model_Family::loadList($this->request);

        foreach ($families as $family) {
            /** @var $family Techno_Model_Family */
            $data = [];
            $data['category'] = $family->getCategory()->getId();
            $data['label'] = $family->getLabel();
            $data['ref'] = $family->getRef();
            $data['unit'] = $family->getValueUnit()->getSymbol();
            $tags = [];
            foreach ($family->getTags() as $tag) {
                $tags[] = $tag->getValueLabel();
            }
            $data['tags'] = implode(', ', $tags);
            $tags = [];
            foreach ($family->getCellsCommonTags() as $tag) {
                $tags[] = $tag->getValue()->getLabel();
            }
            $data['cellsCommonTags'] = implode(', ', $tags);
            if ($this->getParam('mode') == 'edition') {
                $data['detail'] = $this->cellLink(
                    $this->_helper->url('edit', 'family', 'techno', ['id' => $family->getId()]));
            } else {
                $data['detail'] = $this->cellLink(
                    $this->_helper->url('details', 'family', 'techno', ['id' => $family->getId()]));
            }
            $this->addLine($data);
        }

        $this->totalElements = Techno_Model_Family::countTotal($this->request);
        $this->send();
    }

    /**
     * (non-PHPdoc)
     * @see UI_Controller_Datagrid::addelementAction()
     * @Secure("editTechno")
     */
    public function addelementAction()
    {
        // Validation du formulaire
        $idCategory = $this->getAddElementValue('category');
        if (empty($idCategory)) {
            $this->setAddElementErrorMessage('category', __('UI', 'formValidation', 'emptyRequiredField'));
        }
        $ref = $this->getAddElementValue('ref');
        if (empty($ref)) {
            $this->setAddElementErrorMessage('ref', __('UI', 'formValidation', 'emptyRequiredField'));
        }
        $label = $this->getAddElementValue('label');
        if (empty($label)) {
            $this->setAddElementErrorMessage('label', __('UI', 'formValidation', 'emptyRequiredField'));
        }
        $refUnit = $this->getAddElementValue('unit');
        if (empty($refUnit)) {
            $this->setAddElementErrorMessage('unit', __('UI', 'formValidation', 'emptyRequiredField'));
        } else {
            $unit = new UnitAPI($refUnit);
            try {
                $unit->getNormalizedUnit();
            } catch (Exception $e) {
                $this->setAddElementErrorMessage('unit', __('UI', 'formValidation', 'invalidUnit'));
            }
        }

        // Pas d'erreurs
        if (empty($this->_addErrorMessages)) {
            /** @var $category Techno_Model_Category */
            $category = Techno_Model_Category::load($idCategory);

            $family = new Techno_Model_Family_Coeff();
            // Ref
            try {
                $family->setRef($ref);
            } catch (Exception $e) {
                $this->setAddElementErrorMessage('ref', __('Core', 'exception', 'unauthorizedRef'));
                $this->send();
                return;
            }
            $family->setLabel($label);
            /** @noinspection PhpUndefinedVariableInspection */
            $family->setBaseUnit($unit->getNormalizedUnit());
            $family->setUnit($unit);
            $family->setCategory($category);
            $family->save();
            try {
                $this->entityManager->flush();
                $this->message = __('UI', 'message', 'added');
            } catch (Exception $e) {
                $this->setAddElementErrorMessage('ref', __('UI', 'formValidation', 'alreadyUsedIdentifier'));
            }
        }
        $this->send();
    }

}
