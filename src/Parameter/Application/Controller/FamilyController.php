<?php

use Core\Annotation\Secure;
use Parameter\Domain\Family\Family;
use Parameter\Domain\Category;

/**
 * Controleur des familles
 * @author matthieu.napoli
 */
class Parameter_FamilyController extends Core_Controller
{
    use UI_Controller_Helper_Form;

    /**
     * Arbre des familles en édition
     * @Secure("editParameter")
     */
    public function treeEditAction()
    {
        $this->forward('tree', 'family', 'parameter', array('mode' => 'edition'));
    }

    /**
     * Arbre des familles
     * @Secure("viewParameter")
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
     * @Secure("editParameter")
     */
    public function listEditAction()
    {
        $this->forward('list', 'family', 'parameter', array('mode' => 'edition'));
    }

    /**
     * Liste des familles
     * @Secure("viewParameter")
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
     * @Secure("viewParameterFamily")
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
     * @Secure("editParameterFamily")
     */
    public function editAction()
    {
        $this->forward('details', 'family', 'parameter', array('mode' => 'edition'));
    }

    /**
     * Suppression d'une famille
     * AJAX
     * @Secure("deleteParameterFamily")
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
