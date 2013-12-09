<?php
use User\Domain\User;

/**
 * @package Social
 */

/**
 * @author  matthieu.napoli
 * @author  joseph.rouffet
 * @package Social
 */
abstract class Social_Model_Text extends Core_Model_Entity
{

    const QUERY_AUTHOR = "author";
    const QUERY_CREATION_DATE = 'creationDate';

    /**
     * @var int
     */
    protected $id;

    /**
     * @var User|null
     */
    protected $author;

    /**
     * Contenu textuel
     * @var string
     */
    protected $text;

    /**
     * @var DateTime
     */
    protected $creationDate;


    /**
     * Assigne la date du jour de création de l'instance à creationDate
     * @param User|null $author Auteur
     */
    public function __construct(User $author = null)
    {
        $this->setCreationDate(new DateTime());
        $this->setAuthor($author);
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return User|null
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * @param User|null $author
     */
    public function setAuthor(User $author = null)
    {
        $this->author = $author;
    }

    /**
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * @param string $text
     */
    public function setText($text)
    {
        $this->text = (string) $text;
    }

    /**
     * @return DateTime
     */
    public function getCreationDate()
    {
        return $this->creationDate;
    }

    /**
     * @param DateTime $creationDate
     */
    public function setCreationDate(DateTime $creationDate)
    {
        $this->creationDate = $creationDate;
    }

}
