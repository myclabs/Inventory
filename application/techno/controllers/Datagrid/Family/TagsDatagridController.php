<?php
/**
 * @author matthieu.napoli
 * @package Techno
 */

use Core\Annotation\Secure;
use DI\Annotation\Inject;
use Keyword\Application\Service\KeywordService;

/**
 * @package Techno
 */
class Techno_Datagrid_Family_TagsDatagridController extends UI_Controller_Datagrid
{
    /**
     * @Inject
     * @var KeywordService
     */
    protected $keywordService;

    /**
     * (non-PHPdoc)
     * @see UI_Controller_Datagrid::getelementsAction()
     * @Secure("editTechno")
     */
    public function getelementsAction()
    {
        /** @var $family Techno_Model_Family */
        $family = Techno_Model_Family::load($this->getParam('idFamily'));
        $tags = $family->getTags();

        foreach ($tags as $tag) {
            /** @var $tag Techno_Model_Tag */
            $data = [];
            $data['index'] = $tag->getId();
            $data['meaning'] = $tag->getMeaning()->getId();
            $data['value'] = $this->cellList($tag->getValueLabel(), $tag->getValueLabel());
            // Seule les valeurs en erreur sont éditables
            $this->editableCell($data['value'], false);
            try {
                $tag->getValue();
            } catch (Core_Exception_NotFound $e) {
                $this->editableCell($data['value'], true);
            }
            $this->addLine($data);
        }

        $this->totalElements = count($tags);
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
        $idMeaning = $this->getAddElementValue('meaning');
        if (empty($idMeaning)) {
            $this->setAddElementErrorMessage('meaning', __('UI', 'formValidation', 'emptyRequiredField'));
        }
        $refValue = $this->getAddElementValue('value');
        if (empty($refValue)) {
            $this->setAddElementErrorMessage('value', __('UI', 'formValidation', 'emptyRequiredField'));
        } else {
            try {
                $value = $this->keywordService->get($refValue);
            } catch (Core_Exception_NotFound $e) {
                $this->setAddElementErrorMessage('value', __('Techno', 'formValidation', 'unknownKeywordRef'));
            }
        }
        // Pas d'erreurs
        if (empty($this->_addErrorMessages)) {
            $tag = new Techno_Model_Tag();
            $tag->setMeaning(Techno_Model_Meaning::load($idMeaning));
            /** @noinspection PhpUndefinedVariableInspection */
            $tag->setValue($value);
            $tag->save();
            /** @var $family Techno_Model_Family */
            $family = Techno_Model_Family::load($this->getParam('idFamily'));
            $family->addTag($tag);
            $family->save();
            $this->entityManager->flush();
            $this->message = __('UI', 'message', 'added');
        }
        $this->send();
    }

    /**
     * (non-PHPdoc)
     * @see UI_Controller_Datagrid::updateelementAction()
     * @Secure("editTechno")
     */
    public function updateelementAction()
    {
        /** @var $tag Techno_Model_Tag */
        $tag = Techno_Model_Tag::load($this->getParam('index'));

        $newValue = $this->update['value'];
        switch($this->update['column']) {
            case 'value':
                try {
                    $keyword = $this->keywordService->get($newValue);
                    $tag->setValue($keyword);
                    $this->data = $keyword->getRef();
                } catch (Core_Exception_NotFound $e) {
                    throw new Core_Exception_User('Techno', 'formValidation', 'unknownKeywordRef');
                }
                break;
        }
        $tag->save();
        $this->entityManager->flush();
        $this->message = __('UI', 'message', 'updated');
        $this->send();
    }

    /**
     * (non-PHPdoc)
     * @see UI_Controller_Datagrid::deleteelementAction()
     * @Secure("editTechno")
     */
    public function deleteelementAction()
    {
        /** @var $family Techno_Model_Family */
        $family = Techno_Model_Family::load($this->getParam('idFamily'));
        /** @var $tag Techno_Model_Tag */
        $tag = Techno_Model_Tag::load($this->getParam('index'));
        $family->removeTag($tag);
        $this->entityManager->flush();
        $this->message = __('UI', 'message', 'deleted');
        $this->send();
    }

}
