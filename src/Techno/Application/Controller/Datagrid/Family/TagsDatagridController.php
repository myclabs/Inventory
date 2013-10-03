<?php

use Core\Annotation\Secure;
use DI\Annotation\Inject;
use Keyword\Application\Service\KeywordService;
use Techno\Domain\Family\Family;
use Techno\Domain\Meaning;
use Techno\Domain\Tag;

/**
 * @author matthieu.napoli
 */
class Techno_Datagrid_Family_TagsDatagridController extends UI_Controller_Datagrid
{
    /**
     * @Inject
     * @var KeywordService
     */
    protected $keywordService;

    /**
     * @Secure("editTechno")
     */
    public function getelementsAction()
    {
        /** @var $family Family */
        $family = Family::load($this->getParam('idFamily'));
        $tags = $family->getTags();

        foreach ($tags as $tag) {
            /** @var $tag Tag */
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
            $tag = new Tag();
            $tag->setMeaning(Meaning::load($idMeaning));
            /** @noinspection PhpUndefinedVariableInspection */
            $tag->setValue($value);
            $tag->save();
            /** @var $family Family */
            $family = Family::load($this->getParam('idFamily'));
            $family->addTag($tag);
            $family->save();
            $this->entityManager->flush();
            $this->message = __('UI', 'message', 'added');
        }
        $this->send();
    }

    /**
     * @Secure("editTechno")
     */
    public function updateelementAction()
    {
        /** @var $tag Tag */
        $tag = Tag::load($this->getParam('index'));

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
     * @Secure("editTechno")
     */
    public function deleteelementAction()
    {
        /** @var $family Family */
        $family = Family::load($this->getParam('idFamily'));
        /** @var $tag Tag */
        $tag = Tag::load($this->getParam('index'));
        $family->removeTag($tag);
        $this->entityManager->flush();
        $this->message = __('UI', 'message', 'deleted');
        $this->send();
    }
}
