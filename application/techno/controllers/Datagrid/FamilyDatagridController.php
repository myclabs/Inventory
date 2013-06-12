<?php
/**
 * @author matthieu.napoli
 * @package Techno
 */

use Core\Annotation\Secure;

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
            if ($family instanceof Techno_Model_Family_Coeff) {
                $data['type'] = $this->cellList('coeff');
            } else {
                $data['type'] = $this->cellList('process');
            }
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
        $type = $this->getAddElementValue('type');
        if (empty($type)) {
            $this->setAddElementErrorMessage('type', __('UI', 'formValidation', 'emptyRequiredField'));
        }
        $refUnit = $this->getAddElementValue('unit');
        if (empty($refUnit)) {
            $this->setAddElementErrorMessage('unit', __('UI', 'formValidation', 'emptyRequiredField'));
        } else {
            $unit = new Unit_API($refUnit);
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

            switch($type) {
                case 'coeff':
                    $family = new Techno_Model_Family_Coeff();
                    break;
                case 'process':
                    $family = new Techno_Model_Family_Process();
                    break;
                default:
                    throw new Core_Exception("Unknown family type");
            }
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
