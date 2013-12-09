<?php

use User\Domain\User;

/**
 * Service de gestion des commentaires.
 *
 * @author matthieu.napoli
 */
class Social_Service_CommentService
{
    public function addComment(User $author, $content)
    {
        $comment = new Social_Model_Comment($author);
        $comment->setText($content);
        $comment->save();

        return $comment;
    }

    public function editComment($id, $content)
    {
        $comment = Social_Model_Comment::load($id);
        $comment->setText($content);
    }

    public function deleteComment($id)
    {
        /** @var Social_Model_Comment $comment */
        $comment = Social_Model_Comment::load($id);

        $comment->delete();
    }
}
