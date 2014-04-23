<?php
/**
 * @author  matthieu.napoli
 * @author  hugo.charbonnier
 * @author  thibaud.rolland
 * @package AF
 */

use AF\Domain\AF;
use AF\Domain\Component\Component;
use AF\Domain\Component\SubAF\RepeatedSubAF;
use Core\Annotation\Secure;

/**
 * Conditions Controller
 * @package AF
 */
class AF_Datagrid_Edit_Components_SubAfRepeatedController extends UI_Controller_Datagrid
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
        /** @var $subAFList RepeatedSubAF[] */
        $subAFList = RepeatedSubAF::loadList($this->request);
        foreach ($subAFList as $subAF) {
            $data = [];
            $data['index'] = $subAF->getId();
            $data['label'] = $subAF->getLabel();
            $data['ref'] = $subAF->getRef();
            $data['help'] = $this->cellLongText(
                'af/edit_components/popup-help?id=' . $af->getId() . '&component=' . $subAF->getId(),
                'af/datagrid_edit_components_sub-af-repeated/get-raw-help?id=' . $af->getId()
                . '&component=' . $subAF->getId(),
                __('UI', 'name', 'help')
            );
            $data['isVisible'] = $subAF->isVisible();
            $data['targetAF'] = $subAF->getCalledAF()->getId();
            $data['foldaway'] = $subAF->getFoldaway();
            $data['repetition'] = $subAF->getMinInputNumber();
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
        $repetition = $this->getAddElementValue('repetition');
        // Pas d'erreurs
        if (empty($this->_addErrorMessages)) {
            $subAF = new RepeatedSubAF();
            $subAF->setMinInputNumber($repetition);
            $subAF->setAf($af);
            try {
                $subAF->setRef($ref);
            } catch (Core_Exception_User $e) {
                $this->setAddElementErrorMessage('ref', $e->getMessage());
                $this->send();
                return;
            }
            $subAF->setLabel($this->getAddElementValue('label'));
            $subAF->setVisible($isVisible);
            $subAF->setHelp($this->getAddElementValue('help'));
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
        /** @var $subAF RepeatedSubAF */
        $subAF = RepeatedSubAF::load($this->update['index']);
        $newValue = $this->update['value'];
        switch ($this->update['column']) {
            case 'ref':
                $subAF->setRef($newValue);
                $this->data = $subAF->getRef();
                break;
            case 'label':
                $subAF->setLabel($newValue);
                $this->data = $subAF->getLabel();
                break;
            case 'help':
                $subAF->setHelp($newValue);
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
            case 'repetition':
                $subAF->setMinInputNumber($newValue);
                $this->data = $subAF->getMinInputNumber();
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
     * @Secure("editAF")
     */
    public function deleteelementAction()
    {
        /** @var $subAF RepeatedSubAF */
        $subAF = RepeatedSubAF::load($this->getParam('index'));
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
        /** @var $subAF RepeatedSubAF */
        $subAF = RepeatedSubAF::load($this->getParam('component'));
        $this->data = $subAF->getHelp();
        $this->send();
    }
}
