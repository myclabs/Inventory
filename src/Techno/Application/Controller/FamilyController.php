<?php
/**
 * @author  matthieu.napoli
 * @package Techno
 */

use Core\Annotation\Secure;
use DI\Annotation\Inject;
use Keyword\Application\Service\KeywordService;
use Techno\Domain\Family\Family;
use Techno\Domain\Meaning;
use Techno\Domain\Category;

/**
 * Controleur des familles
 * @package Techno
 */
class Techno_FamilyController extends Core_Controller
{

    use UI_Controller_Helper_Form;

    /**
     * @Inject
     * @var KeywordService
     */
    protected $keywordService;

    /**
     * Arbre des familles en édition
     * @Secure("editTechno")
     */
    public function treeEditAction()
    {
        $this->forward('tree', 'family', 'techno', array('mode' => 'edition'));
    }

    /**
     * Arbre des familles
     * @Secure("viewTechno")
     */
    public function treeAction()
    {
        $mode = $this->getParam('mode');
        if (empty($mode)) {
            $mode = 'consultation';
        }
        $this->view->mode = $mode;
    }

    /**
     * Liste des familles en édition
     * @Secure("editTechno")
     */
    public function listEditAction()
    {
        $this->forward('list', 'family', 'techno', array('mode' => 'edition'));
    }

    /**
     * Liste des familles
     * @Secure("viewTechno")
     */
    public function listAction()
    {
        $mode = $this->getParam('mode');
        if (empty($mode)) {
            $mode = 'consultation';
        }
        $this->view->mode = $mode;
        $this->view->categoryList = Category::loadList();
    }

    /**
     * Détails d'une famille
     * @Secure("viewTechno")
     */
    public function detailsAction()
    {
        $mode = $this->getParam('mode');
        if (empty($mode)) {
            $mode = 'consultation';
        }
        if ($mode == 'consultation') {
            $this->view->edit = false;
        } else {
            $this->view->edit = true;
        }
        $this->view->mode = $mode;
        $this->view->family = Family::load($this->getParam('id'));
    }

    /**
     * Édition d'une famille
     * @Secure("editTechno")
     */
    public function editAction()
    {
        $this->forward('details', 'family', 'techno', array('mode' => 'edition'));
    }

    /**
     * Détails d'une famille - Onglet Général
     * AJAX
     * @Secure("editTechno")
     */
    public function detailsMainTabAction()
    {
        $this->view->family = Family::load($this->getParam('id'));
        $this->view->meanings = Meaning::loadList();
        $this->view->keywords = $this->keywordService->getAll();
        $this->_helper->layout()->disableLayout();
    }

    /**
     * Détails d'une famille - Onglet Éléments
     * AJAX
     * @Secure("viewTechno")
     */
    public function detailsElementsTabAction()
    {
        $mode = $this->getParam('mode');
        if (empty($mode)) {
            $mode = 'consultation';
        }
        if ($mode == 'consultation') {
            $this->view->edit = false;
        } else {
            $this->view->edit = true;
        }
        $this->view->mode = $mode;
        $this->view->family = Family::load($this->getParam('id'));
        $this->view->keywordService = $this->keywordService;
        $this->_helper->layout()->disableLayout();
    }

    /**
     * Détails d'une famille - Onglet Documentation
     * AJAX
     * @Secure("viewTechno")
     */
    public function detailsDocumentationTabAction()
    {
        $mode = $this->getParam('mode');
        if (empty($mode)) {
            $mode = 'consultation';
        }
        if ($mode == 'consultation') {
            $this->view->edit = false;
        } else {
            $this->view->edit = true;
        }
        $this->view->family = Family::load($this->getParam('id'));
        $this->_helper->layout()->disableLayout();
    }

    /**
     * AJAX
     * @Secure("editTechno")
     */
    public function submitDocumentationAction()
    {
        /** @var $family Family */
        $family = Family::load($this->getParam('id'));
        $formData = $this->getFormData('documentationForm');
        $family->setDocumentation($formData->getValue('documentation'));
        $family->save();
        $this->entityManager->flush();
        $this->setFormMessage(__('UI', 'message', 'updated'));
        $this->sendFormResponse();
    }

    /**
     * Suppression d'une famille
     * AJAX
     * @Secure("editTechno")
     */
    public function deleteAction()
    {
        $idFamily = $this->getParam('id');
        /** @var $family Family */
        $family = Family::load($idFamily);
        if ($family->hasChosenElements()) {
            throw new Core_Exception_User('Techno', 'familyDetail', 'cantDeleteFamily');
        }
        $family->delete();
        $this->entityManager->flush();
        UI_Message::getInstance()->addMessage(__('UI', 'message', 'deleted'), UI_Message::TYPE_SUCCESS);
        $this->sendJsonResponse([
                                'message' => __('UI', 'message', 'deleted'),
                                'type'    => 'success',
                                ]);
    }

}
