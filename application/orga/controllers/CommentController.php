<?php

use Core\Annotation\Secure;
use Social\ViewModel\CommentView;
use User\Application\HttpNotFoundException;
use User\Domain\User;

class Orga_CommentController extends Core_Controller
{
    use UI_Controller_Helper_Form;

    /**
     * @Inject
     * @var Social_Service_CommentService
     */
    private $commentService;

    /**
     * @Secure("viewCell")
     */
    public function listAction()
    {
        /** @var User $connectedUser */
        $connectedUser = $this->_helper->auth();

        /** @var Orga_Model_Cell $cell */
        $cell = Orga_Model_Cell::load($this->getParam('idCell'));

        $comments = [];
        foreach ($cell->getSocialCommentsForInputSetPrimary() as $comment) {
            $commentView = new CommentView();
            $commentView->id = $comment->getId();
            $commentView->text = $comment->getText();
            $commentView->html = Core_Tools::textile($comment->getText());
            $commentView->author = $comment->getAuthor()->getName();
            $commentView->date = Core_Locale::loadDefault()->formatDate($comment->getCreationDate());
            $commentView->canBeEdited = $comment->getAuthor() === $connectedUser;
            $comments[] = $commentView;
        }

        $this->sendJsonResponse($comments);
    }

    /**
     * @Secure("inputCell")
     */
    public function newAction()
    {
        if (! $this->_request->isPost()) {
            throw new HttpNotFoundException;
        }

        /** @var User $connectedUser */
        $connectedUser = $this->_helper->auth();

        /** @var Orga_Model_Cell $cell */
        $cell = Orga_Model_Cell::load($this->getParam('idCell'));

        $content = $this->getParam('text');
        if (empty($content)) {
            $this->_response->setHttpResponseCode(400);
            $this->sendJsonResponse(__('UI', 'formValidation', 'emptyRequiredField'));
            return;
        }

        // Ajoute le commentaire
        $comment = $this->commentService->addComment($connectedUser, $content);
        $cell->addSocialCommentForInputSetPrimary($comment);
        $cell->save();
        $this->entityManager->flush();

        $this->sendJsonResponse(__('UI', 'message', 'added'));
    }

    /**
     * @Secure("editComment")
     */
    public function editAction()
    {
        if (! $this->_request->isPost()) {
            throw new HttpNotFoundException;
        }

        $this->commentService->editComment($this->getParam('id'), $this->getParam('text'));

        $this->sendJsonResponse(__('UI', 'message', 'updated'));
    }

    /**
     * @Secure("deleteComment")
     */
    public function deleteAction()
    {
        if (! $this->_request->isPost()) {
            throw new HttpNotFoundException;
        }

        /** @var Orga_Model_Cell $cell */
        $cell = Orga_Model_Cell::load($this->getParam('idCell'));

        $comment = Social_Model_Comment::load($this->getParam('id'));

        $cell->removeSocialCommentForInputSetPrimary($comment);
        $this->commentService->deleteComment($comment->getId());

        $this->sendJsonResponse(__('UI', 'message', 'deleted'));
    }
}
