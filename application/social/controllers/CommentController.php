<?php
/**
 * @author  matthieu.napoli
 * @package Social
 */

use Core\Annotation\Secure;

/**
 * @package Social
 */
class Social_CommentController extends Core_Controller_Ajax
{

    use UI_Controller_Helper_Form;

    /**
     * Retourne la vue d'un commentaire après son ajout
     * @Secure("loggedIn")
     */
    public function commentAddedAction()
    {
        /** @var $comment Social_Model_Comment */
        $comment = $this->getParam('comment');

        $this->view->comment = $comment;
        $data = $this->view->render('comment/view.phtml');

        $this->setFormMessage(__('UI', 'message', 'added'));
        $this->sendFormResponse($data);
    }

}
