<?php

use Doctrine\ORM\EntityManager;

/**
 * Service de gestion des commentaires.
 *
 * @author matthieu.napoli
 */
class Social_Service_CommentService
{
    /**
     * @var User_Service_ACL
     */
    private $aclService;

    /**
     * @var EntityManager
     */
    private $entityManager;

    public function __construct(User_Service_ACL $aclService, EntityManager $entityManager)
    {
        $this->aclService = $aclService;
        $this->entityManager = $entityManager;
    }

    public function addComment(User_Model_User $author, $content)
    {
        $this->entityManager->beginTransaction();

        $comment = new Social_Model_Comment($author);
        $comment->setText($content);
        $comment->save();
        $this->entityManager->flush();

        // Crée la ressource
        $resource = new User_Model_Resource_Entity();
        $resource->setEntity($comment);
        $resource->save();
        $this->entityManager->flush();

        // Donne le droit à l'auteur de modifier et supprimer le commentaire
        $this->aclService->allow($author, User_Model_Action_Default::EDIT(), $comment);
        $this->aclService->allow($author, User_Model_Action_Default::DELETE(), $comment);

        $this->entityManager->flush();
        $this->entityManager->commit();

        return $comment;
    }

    public function editComment($id, $content)
    {
        $comment = Social_Model_Comment::load($id);
        $comment->setText($content);

        $this->entityManager->flush();
    }

    public function deleteComment($id)
    {
        $this->entityManager->beginTransaction();

        /** @var Social_Model_Comment $comment */
        $comment = Social_Model_Comment::load($id);

        // Supprime les droits
        $this->aclService->disallow($comment->getAuthor(), User_Model_Action_Default::EDIT(), $comment);
        $this->aclService->disallow($comment->getAuthor(), User_Model_Action_Default::DELETE(), $comment);
        $this->entityManager->flush();

        // Supprime la ressource
        $resource = User_Model_Resource_Entity::loadByEntity($comment);
        if ($resource) {
            $resource->delete();
        }

        $comment->delete();

        $this->entityManager->flush();
        $this->entityManager->commit();
    }
}
