<?php
/**
 * @author  matthieu.napoli
 * @author  hugo.charbonnier
 * @author  thibaud.rolland
 * @package AF
 */

use Core\Annotation\Secure;
use Doctrine\DBAL\DBALException;

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
        /** @var $af AF_Model_AF */
        $af = AF_Model_AF::load($this->getParam('id'));
        $algos = $af->getAlgos();
        foreach ($algos as $algo) {
            if ($algo instanceof Algo_Model_Condition_Elementary) {
                $data = [];
                $data['index'] = $algo->getId();
                $data['ref'] = $algo->getRef();
                $data['input'] = $this->cellList($algo->getInputRef());
                if ($algo instanceof Algo_Model_Condition_Elementary_Numeric) {
                    $data['relation'] = $this->cellList($algo->getRelation());
                    $data['value'] = $algo->getValue();
                } elseif ($algo instanceof Algo_Model_Condition_Elementary_Boolean) {
                    $data['relation'] = $this->cellList(AF_Model_Condition_Elementary::RELATION_EQUAL);
                    if ($algo->getValue()) {
                        $data['value'] = 'Coché';
                    } else {
                        $data['value'] = 'Décoché';
                    }
                } elseif ($algo instanceof Algo_Model_Condition_Elementary_Select_Single) {
                    $data['relation'] = $this->cellList($algo->getRelation());
                    $data['value'] = $algo->getValue();
                } elseif ($algo instanceof Algo_Model_Condition_Elementary_Select_Multi) {
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
        /** @var $af AF_Model_AF */
        $af = AF_Model_AF::load($this->getParam('id'));
        $ref = $this->getAddElementValue('ref');
        if (empty($ref)) {
            $this->setAddElementErrorMessage('ref', __('UI', 'formValidation', 'emptyRequiredField'));
        }
        $inputRef = $this->getAddElementValue('input');
        if (empty($inputRef)) {
            $this->setAddElementErrorMessage('input', __('UI', 'formValidation', 'emptyRequiredField'));
        }
        /** @var $input AF_Model_Component_Field */
        $input = AF_Model_Component_Field::loadByRef($inputRef, $af);
        // Pas d'erreurs
        if (empty($this->_addErrorMessages)) {
            switch (get_class($input)) {
                case 'AF_Model_Component_Numeric':
                    $algo = new Algo_Model_Condition_Elementary_Numeric();
                    break;
                case 'AF_Model_Component_Checkbox':
                    $algo = new Algo_Model_Condition_Elementary_Boolean();
                    break;
                case 'AF_Model_Component_Select_Single':
                    $algo = new Algo_Model_Condition_Elementary_Select_Single();
                    break;
                case 'AF_Model_Component_Select_Multi':
                    $algo = new Algo_Model_Condition_Elementary_Select_Multi();
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
        /** @var $algo Algo_Model_Condition_Elementary */
        $algo = Algo_Model_Condition_Elementary::load($this->update['index']);
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
        /** @var $algo Algo_Model_Condition_Elementary */
        $algo = Algo_Model_Condition_Elementary::load($this->getParam('index'));
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
        /** @var $af AF_Model_AF */
        $af = AF_Model_AF::load($this->getParam('id'));
        $query = new Core_Model_Query();
        $query->filter->addCondition(AF_Model_Component_Field::QUERY_AF, $af);
        /** @var $fieldList AF_Model_Component_Field[] */
        $fieldList = AF_Model_Component_Field::loadList($query);

        $this->addElementList(null, '');
        foreach ($fieldList as $field) {
            $this->addElementList($field->getRef(), $field->getLabel());
        }
        $this->send();
    }

}
