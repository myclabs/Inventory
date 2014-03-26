<?php

use Core\Annotation\Secure;
use Parameter\Domain\Family\Family;

class Parameter_FamilyController extends Core_Controller
{
    use UI_Controller_Helper_Form;

    /**
     * Détails d'une famille
     * @Secure("viewParameterFamily")
     */
    public function detailsAction()
    {
        $family = Family::load($this->getParam('id'));

        $this->view->assign('edit', false);
        $this->view->assign('family', $family);
        $this->setActiveMenuItemParameterLibrary($family->getLibrary()->getId());
    }

    /**
     * Édition d'une famille
     * @Secure("editParameterFamily")
     */
    public function editAction()
    {
        $family = Family::load($this->getParam('id'));

        $this->view->assign('edit', true);
        $this->view->assign('family', $family);
        $this->setActiveMenuItemParameterLibrary($family->getLibrary()->getId());

        $this->renderScript('family/details.phtml');
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
