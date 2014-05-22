<?php
/**
 * @author  matthieu.napoli
 * @author  hugo.charbonnier
 * @package AF
 */

use AF\Domain\AF;
use AF\Domain\Component\Component;
use AF\Domain\Component\Group;
use Core\Annotation\Secure;

/**
 * Groupes dans un AF
 * @package AF
 */
class AF_Datagrid_Edit_Components_GroupsController extends UI_Controller_Datagrid
{
    /**
     * @Secure("editAF")
     */
    public function getelementsAction()
    {
        /** @var $af AF */
        $af = AF::load($this->getParam('id'));
        // Filtre sur l'AF
        $this->request->filter->addCondition(Component::QUERY_AF, $af);
        // Filtre pour exclure le rootGroup
        $this->request->filter->addCondition(
            Component::QUERY_REF,
            Group::ROOT_GROUP_REF,
            Core_Model_Filter::OPERATOR_NOT_EQUAL
        );
        /** @var $groups Group[] */
        $groups = Group::loadList($this->request);
        foreach ($groups as $group) {
            $data = [];
            $data['index'] = $group->getId();
            $data['label'] = $this->cellTranslatedText($group->getLabel());
            $data['ref'] = $group->getRef();
            $data['help'] = $this->cellLongText(
                'af/edit_components/popup-help?id=' . $af->getId() . '&component=' . $group->getId(),
                'af/datagrid_edit_components_groups/get-raw-help?id=' . $af->getId()
                . '&component=' . $group->getId(),
                __('UI', 'name', 'help')
            );
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
        /** @var $af AF */
        $af = AF::load($this->getParam('id'));
        $ref = $this->getAddElementValue('ref');
        if (empty($ref)) {
            $this->setAddElementErrorMessage('ref', __('UI', 'formValidation', 'emptyRequiredField'));
        }
        $isVisible = $this->getAddElementValue('isVisible');
        if (empty($this->_addErrorMessages)) {
            $group = new Group();
            $group->setAf($af);
            try {
                $group->setRef($ref);
            } catch (Core_Exception_User $e) {
                $this->setAddElementErrorMessage('ref', $e->getMessage());
                $this->send();
                return;
            }
            $this->translator->set($group->getLabel(), $this->getAddElementValue('label'));
            $this->translator->set($group->getHelp(), $this->getAddElementValue('help'));
            $group->setVisible($isVisible);
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
        /** @var $group Group */
        $group = Group::load($this->update['index']);
        $newValue = $this->update['value'];
        switch ($this->update['column']) {
            case 'ref':
                $group->setRef($newValue);
                $this->data = $group->getRef();
                break;
            case 'label':
                $this->translator->set($group->getLabel(), $newValue);
                $this->data = $this->cellTranslatedText($group->getLabel());
                break;
            case 'help':
                $this->translator->set($group->getHelp(), $newValue);
                $this->data = null;
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
        /** @var $group Group */
        $group = Group::load($this->getParam('index'));
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
        /** @var $group Group */
        $group = Group::load($this->getParam('component'));
        $this->data = (string) $this->translator->get($group->getHelp());
        $this->send();
    }
}
