<?php
/**
 * @author  matthieu.napoli
 * @package Social
 */

use Core\Annotation\Secure;

/**
 * @package Social
 */
class Social_CommentController extends Core_Controller
{
    use UI_Controller_Helper_Form;

    /**
     * @Inject
     * @var Social_Service_CommentService
     */
    private $commentService;

    /**
     * Retourne la vue d'un commentaire après son ajout
     * @Secure("loggedIn")
     */
    public function commentAddedAction()
    {
        /** @var $comment Social_Model_Comment */
        $comment = $this->getParam('comment');

        $this->view->assign('comment', $comment);
        $this->view->assign('currentUser', $this->_helper->auth());
        $data = $this->view->render('comment/view.phtml');

        $this->setFormMessage(__('UI', 'message', 'added'));
        $this->sendFormResponse($data);
    }

    /**
     * @Secure("editComment")
     */
    public function editCommentAction()
    {
        $content = $this->getFormData('editCommentForm')->getValue('editContent');
        if (empty($content)) {
            $this->addFormError('editContent', __('UI', 'formValidation', 'emptyRequiredField'));
            $this->sendFormResponse();
            return;
        }

        $this->commentService->editComment($this->getParam('id'), $content);

        $comment = Social_Model_Comment::load($this->getParam('id'));
        $this->view->assign('comment', $comment);
        $this->view->assign('currentUser', $this->_helper->auth());
        $data['html'] = $this->view->render('comment/view.phtml');
        $data['commentId'] = $this->getParam('id');

        $this->sendFormResponse($data);
    }

    /**
     * Retourne le contenu textuel d'un commentaire
     * @Secure("loggedIn")
     */
    public function getCommentContentAction()
    {
        $comment = Social_Model_Comment::load($this->getParam('id'));

        $this->sendFormResponse($comment->getText());
    }
}
