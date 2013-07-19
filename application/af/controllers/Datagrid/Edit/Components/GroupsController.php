<?php
/**
 * @author  matthieu.napoli
 * @author  hugo.charbonnier
 * @package AF
 */

use Core\Annotation\Secure;

/**
 * Groupes dans un AF
 * @package AF
 */
class AF_Datagrid_Edit_Components_GroupsController extends UI_Controller_Datagrid
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
        // Filtre sur l'AF
        $this->request->filter->addCondition(AF_Model_Component::QUERY_AF, $af);
        // Filtre pour exclure le rootGroup
        $this->request->filter->addCondition(AF_Model_Component::QUERY_REF,
                                             AF_Model_Component_Group::ROOT_GROUP_REF,
                                             Core_Model_Filter::OPERATOR_NOT_EQUAL);
        /** @var $groups AF_Model_Component_Group[] */
        $groups = AF_Model_Component_Group::loadList($this->request);
        foreach ($groups as $group) {
            $data = [];
            $data['index'] = $group->getId();
            $data['label'] = $group->getLabel();
            $data['ref'] = $group->getRef();
            $data['help'] = $this->cellLongText('af/edit_components/popup-help/id/' . $group->getId(),
                                                ' af/datagrid_edit_components_groups/get-raw-help/id/'
                                                    . $group->getId(),
                                                __('UI', 'name', 'help'),
                                                'zoom-in');
            $data['isVisible'] = $group->isVisible();
            $data['foldaway'] = $group->getFoldaway();
            $this->addLine($data);
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
        $foldaway = $this->getAddElementValue('foldaway');
        $isVisible = $this->getAddElementValue('isVisible');
        if (empty($this->_addErrorMessages)) {
            $group = new AF_Model_Component_Group();
            $group->setAf($af);
            try {
                $group->setRef($ref);
            } catch (Core_Exception_User $e) {
                $this->setAddElementErrorMessage('ref', $e->getMessage());
                $this->send();
                return;
            }
            $group->setLabel($this->getAddElementValue('label'));
            $group->setFoldaway($foldaway);
            $group->setVisible($isVisible);
            $group->setHelp($this->getAddElementValue('help'));
            $group->save();
            $af->addComponent($group);

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
        /** @var $group AF_Model_Component_Group */
        $group = AF_Model_Component_Group::load($this->update['index']);
        $newValue = $this->update['value'];
        switch ($this->update['column']) {
            case 'ref':
                $group->setRef($newValue);
                $this->data = $group->getRef();
                break;
            case 'label':
                $group->setLabel($newValue);
                $this->data = $group->getLabel();
                break;
            case 'help':
                $group->setHelp($newValue);
                $this->data = $this->cellLongText('af/edit_components/popup-help/id/' . $group->getId(),
                                                  ' af/datagrid_edit_components_groups/get-raw-help/id/'
                                                      . $group->getId(),
                                                  __('UI', 'name', 'help'),
                                                  'zoom-in');
                break;
            case 'isVisible':
                $group->setVisible($newValue);
                $this->data = $group->isVisible();
                break;
            case 'foldaway':
                $group->setFoldaway($newValue);
                $this->data = $group->getFoldaway();
                break;
        }
        $group->save();
        $this->entityManager->flush();
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
        /** @var $group AF_Model_Component_Group */
        $group = AF_Model_Component_Group::load($this->getParam('index'));
        $group->delete();
        try {
            $this->entityManager->flush();
        } catch (Core_ORM_ForeignKeyViolationException $e) {
            throw new Core_Exception_User('AF', 'configComponentMessage', 'groupNotEmptyDeletionDenied');
        }
        $this->message = __('UI', 'message', 'deleted');
        $this->send();
    }

    /**
     * Retourne le texte brut de l'aide d'un groupe
     * @Secure("editAF")
     */
    public function getRawHelpAction()
    {
        /** @var $group AF_Model_Component_Group */
        $group = AF_Model_Component_Group::load($this->getParam('id'));
        $this->data = $group->getHelp();
        $this->send();
    }

}
