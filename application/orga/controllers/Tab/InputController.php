<?php
/**
 * @author valentin.claras
 * @package Orga
 * @subpackage Controller
 */

use Core\Annotation\Secure;

/**
 * Controlleur des onglets de la saisie d'une cellule.
 * @author valentin.claras
 * @package Orga
 * @subpackage Controller
 */
class Orga_Tab_InputController extends Core_Controller_Ajax
{
    use UI_Controller_Helper_Form;

    /**
     * Action fournissant la vue des documents pour l'input.
     * @Secure("viewCell")
     */
    public function commentsAction()
    {
        // Désactivation du layout.
        $this->_helper->layout()->disableLayout();
        $idCell = $this->getParam('idCell');
        $this->view->idCell = $idCell;
        $orgaCell = Orga_Model_Cell::load($idCell);
        $cell = Orga_Model_Cell::loadByOrgaCell($orgaCell);

        $this->view->idCell = $idCell;
        $this->view->comments = $cell->getSocialCommentsForInputSetPrimary();
        $this->view->isUserAbleToComment = User_Service_ACL::getInstance()->isAllowed(
            $this->_helper->auth(),
            Orga_Action_Cell::INPUT(),
            $cell
        );
    }

    /**
     * Action fournissant la vue des documents pour l'input.
     * @Secure("inputCell")
     */
    public function commentAddAction()
    {
        // Désactivation du layout.
        $this->_helper->layout()->disableLayout();
        $idCell = $this->getParam('idCell');
        $this->view->idCell = $idCell;
        $orgaCell = Orga_Model_Cell::load($idCell);
        $cell = Orga_Model_Cell::loadByOrgaCell($orgaCell);

        $author = $this->_helper->auth();
        $formData = $this->getFormData('addComment');

        $content = $formData->getValue('content');
        if (empty($content)) {
            $this->addFormError('content', __('UI', 'formValidation', 'emptyRequiredField'));
        }
        if (!$this->hasFormError()) {

            // Ajoute le commentaire
            $comment = new Social_Model_Comment($author);
            $comment->setText($content);
            $comment->save();
            $cell->addSocialCommentForInputSetPrimary($comment);
            $cell->save();

            // Retourne la vue du commentaire
            $this->forward('comment-added', 'comment', 'social', ['comment' => $comment]);
            return;
        }
        $this->sendFormResponse();
    }

    /**
     * Action fournissant la vue des documents pour l'input.
     * @Secure("viewCell")
     */
    public function docsAction()
    {
        // Désactivation du layout.
        $this->_helper->layout()->disableLayout();
        $idCell = $this->getParam('idCell');
        $this->view->idCell = $idCell;
        $orgaCell = Orga_Model_Cell::load($idCell);
        $cell = Orga_Model_Cell::loadByOrgaCell($orgaCell);

        $this->view->documentLibrary = null;
        if ($cell->getGranularity()->getCellsWithInputDocs()) {
            $this->view->documentLibrary = $cell->getDocLibraryForAFInputSetsPrimary();
        } else {
            foreach ($orgaCell->getGranularity()->getBroaderGranularities() as $granularity) {
                $granularity = Orga_Model_Granularity::loadByOrgaGranularity(
                    $granularity
                );
                if ($granularity->getCellsWithInputDocs()) {
                    $parentCell = Orga_Model_Cell::loadByOrgaCell(
                        $orgaCell->getParentCellForGranularity($granularity)
                    );
                    $this->view->documentLibrary = $parentCell->getDocLibraryForAFInputSetsPrimary();
                    break;
                }
            }
        }
        $this->view->documentBibliography = $cell->getDocBibliographyForAFInputSetPrimary();
    }

}
