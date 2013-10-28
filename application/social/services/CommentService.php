<?php

use Doctrine\ORM\EntityManager;
use User\Domain\ACL\Action;
use User\Domain\ACL\Resource\EntityResource;
use User\Domain\ACL\ACLService;
use User\Domain\User;

/**
 * Service de gestion des commentaires.
 *
 * @author matthieu.napoli
 */
class Social_Service_CommentService
{
    /**
     * @var ACLService
     */
    private $aclService;

    /**
     * @var EntityManager
     */
    private $entityManager;

    public function __construct(ACLService $aclService, EntityManager $entityManager)
    {
        $this->aclService = $aclService;
        $this->entityManager = $entityManager;
    }

    public function addComment(User $author, $content)
    {
        $this->entityManager->beginTransaction();

        $comment = new Social_Model_Comment($author);
        $comment->setText($content);
        $comment->save();
        $this->entityManager->flush();

        // Crée la ressource
        $resource = new EntityResource();
        $resource->setEntity($comment);
        $resource->save();
        $this->entityManager->flush();

        // Donne le droit à l'auteur de modifier et supprimer le commentaire
        $this->aclService->allow($author, Action::EDIT(), $comment);
        $this->aclService->allow($author, Action::DELETE(), $comment);

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
        $this->aclService->disallow($comment->getAuthor(), Action::EDIT(), $comment);
        $this->aclService->disallow($comment->getAuthor(), Action::DELETE(), $comment);
        $this->entityManager->flush();

        // Supprime la ressource
        $resource = EntityResource::loadByEntity($comment);
        if ($resource) {
            $resource->delete();
        }

        $comment->delete();

        $this->entityManager->flush();
        $this->entityManager->commit();
    }
}
