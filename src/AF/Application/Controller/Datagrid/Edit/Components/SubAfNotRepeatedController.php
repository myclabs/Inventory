<?php
/**
 * @author  matthieu.napoli
 * @author  hugo.charbonnier
 * @author  thibaud.rolland
 * @package AF
 */

use AF\Domain\AF;
use AF\Domain\Component\Component;
use AF\Domain\Component\SubAF\NotRepeatedSubAF;
use Core\Annotation\Secure;

class AF_Datagrid_Edit_Components_SubAfNotRepeatedController extends UI_Controller_Datagrid
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
        /** @var $subAFList NotRepeatedSubAF[] */
        $subAFList = NotRepeatedSubAF::loadList($this->request);
        foreach ($subAFList as $subAF) {
            $data = [];
            $data['index'] = $subAF->getId();
            $data['label'] = $this->cellTranslatedText($subAF->getLabel());
            $data['ref'] = $subAF->getRef();
            $data['help'] = $this->cellLongText(
                'af/edit_components/popup-help?id=' . $af->getId() . '&component=' . $subAF->getId(),
                'af/datagrid_edit_components_sub-af-not-repeated/get-raw-help?id=' . $af->getId()
                . '&component=' . $subAF->getId(),
                __('UI', 'name', 'help')
            );
            $data['isVisible'] = $subAF->isVisible();
            $data['targetAF'] = $subAF->getCalledAF()->getId();
            $data['foldaway'] = $subAF->getFoldaway();
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
        if (empty($isVisible)) {
            $this->setAddElementErrorMessage('isVisible', __('UI', 'formValidation', 'emptyRequiredField'));
        }
        // Pas d'erreurs
        if (empty($this->_addErrorMessages)) {
            $subAF = new NotRepeatedSubAF();
            $subAF->setAf($af);
            try {
                $subAF->setRef($ref);
            } catch (Core_Exception_User $e) {
                $this->setAddElementErrorMessage('ref', $e->getMessage());
                $this->send();
                return;
            }
            $this->translator->set($subAF->getLabel(), $this->getAddElementValue('label'));
            $this->translator->set($subAF->getHelp(), $this->getAddElementValue('help'));
            $subAF->setVisible($isVisible);
            /** @var $calledAF AF */
            $calledAF = AF::load($this->getAddElementValue('targetAF'));
            $subAF->setCalledAF($calledAF);
            $af->addComponent($subAF);

            $subAF->save();
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
        /** @var $subAF NotRepeatedSubAF */
        $subAF = NotRepeatedSubAF::load($this->update['index']);
        $newValue = $this->update['value'];
        switch ($this->update['column']) {
            case 'ref':
                $subAF->setRef($newValue);
                $this->data = $subAF->getRef();
                break;
            case 'label':
                $this->translator->set($subAF->getLabel(), $newValue);
                $this->data = $this->cellTranslatedText($subAF->getLabel());
                break;
            case 'help':
                $this->translator->set($subAF->getHelp(), $newValue);
                $this->data = null;
                break;
            case 'isVisible':
                $subAF->setVisible($newValue);
                $this->data = $subAF->isVisible();
                break;
            case 'foldaway':
                $subAF->setFoldaway($newValue);
                $this->data = $subAF->getFoldaway();
                break;
            case 'targetAF':
                $subAF->setCalledAF(AF::load($newValue));
                $this->data = $subAF->getCalledAF()->getId();
                break;
        }
        $subAF->save();
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
        /** @var $subAF NotRepeatedSubAF */
        $subAF = NotRepeatedSubAF::load($this->getParam('index'));
        $subAF->delete();
        $this->entityManager->flush();
        $this->message = __('UI', 'message', 'deleted');
        $this->send();
    }

    /**
     * Retourne le texte brut de l'aide
     * @Secure("editAF")
     */
    public function getRawHelpAction()
    {
        /** @var $subAF NotRepeatedSubAF */
        $subAF = NotRepeatedSubAF::load($this->getParam('component'));
        $this->data = $this->translator->toString($subAF->getHelp());
        $this->send();
    }
}
