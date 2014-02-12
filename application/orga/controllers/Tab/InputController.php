<?php

use Core\Annotation\Secure;
use DI\Annotation\Inject;
use Orga\Model\ACL\Action\CellAction;
use User\Domain\ACL\ACLService;

/**
 * @author valentin.claras
 */
class Orga_Tab_InputController extends Core_Controller
{
    use UI_Controller_Helper_Form;

    /**
     * @Inject
     * @var ACLService
     */
    private $aclService;

    /**
     * @Inject
     * @var Social_Service_CommentService
     */
    private $commentService;

    /**
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
        $this->view->currentUser = $this->_helper->auth();
        $this->view->isUserAbleToComment = $this->aclService->isAllowed(
            $this->_helper->auth(),
            CellAction::INPUT(),
            $cell
        );
    }

    /**
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
        $formData = $this->getFormData('addCommentForm');

        $content = $formData->getValue('addContent');
        if (empty($content)) {
            $this->addFormError('addContent', __('UI', 'formValidation', 'emptyRequiredField'));
        }
        if (!$this->hasFormError()) {

            // Ajoute le commentaire
            $comment = $this->commentService->addComment($author, $content);
            $cell->addSocialCommentForInputSetPrimary($comment);
            $cell->save();
            $this->entityManager->flush();

            // Retourne la vue du commentaire
            $this->forward('comment-added', 'comment', 'social', ['comment' => $comment, 'currentUser' => $author]);
            return;
        }
        $this->sendFormResponse();
    }

    /**
     * @Secure("deleteComment")
     */
    public function commentDeleteAction()
    {
        $cell = Orga_Model_Cell::load($this->getParam('idCell'));
        $comment = Social_Model_Comment::load($this->getParam('id'));

        $cell->removeSocialCommentForInputSetPrimary($comment);
        $this->commentService->deleteComment($comment->getId());

        $this->sendFormResponse();
    }

    /**
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
            $this->view->documentLibrary = $cell->getDocLibraryForAFInputSetPrimary();
        } else {
            foreach ($cell->getGranularity()->getBroaderGranularities() as $granularity) {
                if ($granularity->getCellsWithInputDocuments()) {
                    $parentCell = $cell->getParentCellForGranularity($granularity);
                    $this->view->documentLibrary = $parentCell->getDocLibraryForAFInputSetPrimary();
                    break;
                }
            }
        }
    }
}
