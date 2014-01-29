<?php
/**
 * @author  matthieu.napoli
 * @author  hugo.charbonnier
 * @author  thibaud.rolland
 * @package AF
 */

use AF\Domain\AF\AF;
use AF\Domain\AF\Component\NumericField;
use AF\Domain\AF\Component\Checkbox;
use AF\Domain\AF\Component\Field;
use AF\Domain\AF\Component\Select\SelectSingle;
use AF\Domain\AF\Component\Select\SelectMulti;
use AF\Domain\AF\Condition\ElementaryCondition;
use AF\Domain\Algorithm\Condition\Elementary\NumericConditionAlgo;
use AF\Domain\Algorithm\Condition\Elementary\BooleanConditionAlgo;
use AF\Domain\Algorithm\Condition\Elementary\Select\SelectSingleConditionAlgo;
use AF\Domain\Algorithm\Condition\Elementary\Select\SelectMultiConditionAlgo;
use AF\Domain\Algorithm\Condition\ElementaryConditionAlgo;
use Core\Annotation\Secure;

/**
 * @package AF
 */
class AF_Datagrid_Edit_Algos_ConditionElementaryController extends UI_Controller_Datagrid
{
    /**
     * (non-PHPdoc)
     * @see UI_Controller_Datagrid::getelementsAction()
     * @Secure("editAF")
     */
    public function getelementsAction()
    {
        /** @var $af AF */
        $af = AF::load($this->getParam('id'));
        $algos = $af->getAlgos();
        foreach ($algos as $algo) {
            if ($algo instanceof ElementaryConditionAlgo) {
                $data = [];
                $data['index'] = $algo->getId();
                $data['ref'] = $algo->getRef();
                $data['input'] = $this->cellList($algo->getInputRef());
                if ($algo instanceof NumericConditionAlgo) {
                    $data['relation'] = $this->cellList($algo->getRelation());
                    $data['value'] = $algo->getValue();
                } elseif ($algo instanceof BooleanConditionAlgo) {
                    $data['relation'] = $this->cellList(ElementaryCondition::RELATION_EQUAL);
                    if ($algo->getValue()) {
                        $data['value'] = 'Coché';
                    } else {
                        $data['value'] = 'Décoché';
                    }
                } elseif ($algo instanceof SelectSingleConditionAlgo) {
                    $data['relation'] = $this->cellList($algo->getRelation());
                    $data['value'] = $algo->getValue();
                } elseif ($algo instanceof SelectMultiConditionAlgo) {
                    $data['relation'] = $this->cellList($algo->getRelation());
                    $data['value'] = $algo->getValue();
                }
                $data['editValue'] = $this->cellPopup($this->_helper->url('update-condition-elementary-popup',
                                                                          'edit_algos',
                                                                          'af',
                                                                          [
                                                                          'idAf'   => $af->getId(),
                                                                          'idAlgo' => $algo->getId()
                                                                          ]),
                                                      __('UI', 'verb', 'edit'),
                                                      'pencil');
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
        /** @var $af AF */
        $af = AF::load($this->getParam('id'));
        $ref = $this->getAddElementValue('ref');
        if (empty($ref)) {
            $this->setAddElementErrorMessage('ref', __('UI', 'formValidation', 'emptyRequiredField'));
        }
        $inputRef = $this->getAddElementValue('input');
        if (empty($inputRef)) {
            $this->setAddElementErrorMessage('input', __('UI', 'formValidation', 'emptyRequiredField'));
        }
        // Pas d'erreurs
        if (empty($this->_addErrorMessages)) {
            /** @var $input Field */
            $input = Field::loadByRef($inputRef, $af);
            switch (get_class($input)) {
                case NumericField::class:
                    $algo = new NumericConditionAlgo();
                    break;
                case Checkbox::class:
                    $algo = new BooleanConditionAlgo();
                    break;
                case SelectSingle::class:
                    $algo = new SelectSingleConditionAlgo();
                    break;
                case SelectMulti::class:
                    $algo = new SelectMultiConditionAlgo();
                    break;
                default:
                    throw new Core_Exception("Unhandled field type");
            }
            try {
                $algo->setRef($ref);
            } catch (Core_Exception_User $e) {
                $this->setAddElementErrorMessage('ref', $e->getMessage());
                $this->send();
                return;
            }
            $algo->setInputRef($input->getRef());
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
        /** @var $algo ElementaryConditionAlgo */
        $algo = ElementaryConditionAlgo::load($this->update['index']);
        $newValue = $this->update['value'];
        switch ($this->update['column']) {
            case 'ref':
                $algo->setRef($newValue);
                $this->data = $algo->getRef();
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
        /** @var $algo ElementaryConditionAlgo */
        $algo = ElementaryConditionAlgo::load($this->getParam('index'));
        $algo->delete();
        $algo->getSet()->removeAlgo($algo);
        $algo->getSet()->save();
        $this->entityManager->flush();
        $this->message = __('UI', 'message', 'deleted');
        $this->send();
    }

    /**
     * Renvoie la liste des composants du formulaire
     * @Secure("editAF")
     */
    public function getFieldListAction()
    {
        /** @var $af AF */
        $af = AF::load($this->getParam('id'));
        $query = new Core_Model_Query();
        $query->filter->addCondition(Field::QUERY_AF, $af);
        /** @var $fieldList Field[] */
        $fieldList = Field::loadList($query);

        $this->addElementList(null, '');
        foreach ($fieldList as $field) {
            $this->addElementList($field->getRef(), $field->getLabel());
        }
        $this->send();
    }
}
