<?php
/**
 * @author valentin.claras
 * @package Orga
 * @subpackage Controller
 */

use Core\Annotation\Secure;
use DI\Annotation\Inject;

/**
 * Controlleur des onglets de la saisie d'une cellule.
 * @author valentin.claras
 * @package Orga
 * @subpackage Controller
 */
class Orga_Tab_InputController extends Core_Controller
{
    use UI_Controller_Helper_Form;

    /**
     * @Inject
     * @var User_Service_ACL
     */
    private $aclService;

    /**
     * @Inject
     * @var Social_Service_CommentService
     */
    private $commentService;

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
        $cell = Orga_Model_Cell::load($idCell);

        $this->view->idCell = $idCell;
        $this->view->comments = $cell->getSocialCommentsForInputSetPrimary();
        $this->view->isUserAbleToComment = $this->aclService->isAllowed(
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
        $cell = Orga_Model_Cell::load($idCell);

        $author = $this->_helper->auth();
        $formData = $this->getFormData('addComment');

        $content = $formData->getValue('content');
        if (empty($content)) {
            $this->addFormError('content', __('UI', 'formValidation', 'emptyRequiredField'));
        }
        if (!$this->hasFormError()) {

            // Ajoute le commentaire
            $comment = $this->commentService->addComment($author, $content);
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
        $cell = Orga_Model_Cell::load($idCell);

        $this->view->documentLibrary = null;
        if ($cell->getGranularity()->getCellsWithInputDocuments()) {
            $this->view->documentLibrary = $cell->getDocLibraryForAFInputSetsPrimary();
        } else {
            foreach ($cell->getGranularity()->getBroaderGranularities() as $granularity) {
                if ($granularity->getCellsWithInputDocuments()) {
                    $parentCell = $cell->getParentCellForGranularity($granularity);
                    $this->view->documentLibrary = $parentCell->getDocLibraryForAFInputSetsPrimary();
                    break;
                }
            }
        }
        $this->view->documentBibliography = $cell->getDocBibliographyForAFInputSetPrimary();
    }

}
