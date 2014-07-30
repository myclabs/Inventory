<?php

namespace Orga\Domain\Cell;

use Core_Model_Entity;
use DateTime;
use Orga\Domain\Cell;
use User\Domain\User;

/**
 * @author matthieu.napoli
 */
class CellInputComment extends Core_Model_Entity
{
    const QUERY_AUTHOR = "author";
    const QUERY_CREATION_DATE = 'creationDate';

    /**
     * @var int
     */
    protected $id;

    /**
     * @var Cell
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

    public function __construct(Cell $cell, User $author)
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
     * @return Cell
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
        $this->text = (string)$text;
    }

    /**
     * @return DateTime
     */
    public function getCreationDate()
    {
        return $this->creationDate;
    }
}
