<?php
/**
 * @package Social
 */

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use User\Domain\User;

/**
 * @author  joseph.rouffet
 * @author  matthieu.napoli
 * @package Social
 */
class Social_Model_Message extends Social_Model_Text
{

    /**
     * Longueur du titre a afficher dans la datagrid
     */
    const LENGTH_TITLE_DATAGRID = 30;

    const QUERY_USER_RECIPIENTS = 'userRecipients';
    const QUERY_GROUP_RECIPIENTS = 'groupRecipients';
    const QUERY_TITLE = 'title';
    const QUERY_SENT = 'sent';

    /**
     * @var string
     */
    protected $title;

    /**
     * @var Collection|User[]
     */
    protected $userRecipients;

    /**
     * @var Collection|Social_Model_UserGroup[]
     */
    protected $groupRecipients;

    /**
     * Est-ce que le message est envoyé ou un brouillon
     * @var boolean
     */
    protected $sent = false;


    /**
     * Charge une liste de messages par son auteur
     * @param User $author
     * @return Social_Model_Message[]
     */
    static public function loadByAuthor(User $author)
    {
        $query = new Core_Model_Query();
        $query->filter->addCondition(self::QUERY_AUTHOR, $author);
        return self::loadList($query);
    }

    /**
     * @param User|null $author Auteur du message
     * @param string $title Titre du message
     */
    public function __construct(User $author = null, $title = null)
    {
        parent::__construct($author);
        $this->userRecipients = new ArrayCollection();
        $this->groupRecipients = new ArrayCollection();
        $this->setTitle($title);
    }

    /**
     * Envoie le message
     */
    public function send()
    {
        $this->sent = true;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = (string) $title;
    }

    /**
     * @return boolean
     */
    public function isSent()
    {
        return $this->sent;
    }

    /**
     * @return User[]
     */
    public function getUserRecipients()
    {
        return $this->userRecipients;
    }

    /**
     * @param User $user
     */
    public function addUserRecipient(User $user)
    {
        if (! $this->hasUserRecipient($user)) {
            $this->userRecipients->add($user);
        }
    }

    /**
     * @param User $user
     * @return boolean
     */
    function hasUserRecipient(User $user)
    {
        return $this->userRecipients->contains($user);
    }

    /**
     * @return Social_Model_UserGroup[]
     */
    public function getGroupRecipients()
    {
        return $this->groupRecipients;
    }

    /**
     * @param Social_Model_UserGroup $userGroup
     */
    public function addGroupRecipient(Social_Model_UserGroup $userGroup)
    {
        if (! $this->hasGroupRecipient($userGroup)) {
            $this->groupRecipients->add($userGroup);
        }
    }

    /**
     * @param Social_Model_UserGroup $userGroup
     * @return boolean
     */
    public function hasGroupRecipient(Social_Model_UserGroup $userGroup)
    {
        return $this->groupRecipients->contains($userGroup);
    }

}
