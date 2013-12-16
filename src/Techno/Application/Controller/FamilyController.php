<?php

use Core\Annotation\Secure;
use Techno\Domain\Family\Family;
use Techno\Domain\Category;

/**
 * Controleur des familles
 * @author matthieu.napoli
 */
class Techno_FamilyController extends Core_Controller
{
    use UI_Controller_Helper_Form;

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
     * Suppression d'une famille
     * AJAX
     * @Secure("editTechno")
     */
    public function deleteAction()
    {
        $idFamily = $this->getParam('id');
        /** @var $family Family */
        $family = Family::load($idFamily);
        $family->delete();
        $this->entityManager->flush();
        UI_Message::getInstance()->addMessage(__('UI', 'message', 'deleted'), UI_Message::TYPE_SUCCESS);
        $this->sendJsonResponse([
            'message' => __('UI', 'message', 'deleted'),
            'type'    => 'success',
        ]);
    }
}
