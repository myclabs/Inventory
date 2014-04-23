<?php

use User\Domain\User;

/**
 * @author joseph.rouffet
 * @author matthieu.napoli
 */
class Orga_Model_Cell_InputComment extends Core_Model_Entity
{
    const QUERY_AUTHOR = "author";
    const QUERY_CREATION_DATE = 'creationDate';

    /**
     * @var int
     */
    protected $id;

    /**
     * @var Orga_Model_Cell
     */
    protected $cell;

    /**
     * @var User
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

    public function __construct(Orga_Model_Cell $cell, User $author)
    {
        $this->cell = $cell;
        $this->author = $author;
        $this->creationDate = new DateTime();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return Orga_Model_Cell
     */
    public function getCell()
    {
        return $this->cell;
    }

    /**
     * @return User
     */
    public function getAuthor()
    {
        return $this->author;
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
}
