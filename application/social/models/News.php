<?php
/**
 * @package Social
 */

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @author  joseph.rouffet
 * @author  matthieu.napoli
 * @package Social
 */
class Social_Model_News extends Social_Model_Text
{

    const QUERY_TITLE = 'title';
    const QUERY_PUBLICATION_DATE = 'publicationDate';

    // Constante pour la longueur du texte à afficher
    const LENGTH_TITLE_DATAGRID = 30;
    const LENGTH_TITLE_PREVIEW = 30;
    const LENGTH_NEWS_DATAGRID = 50;
    const LENGTH_NEWS_PREVIEW = 200;

    /**
     * Titre de la news
     * @var string
     */
    protected $title;

    /**
     * @var DateTime
     */
    protected $publicationDate;

    /**
     * Est-ce que la news est publiée ou est-ce un brouillon
     * @var boolean
     */
    protected $published = false;

    /**
     * Commentaires de la news
     * @var Collection|Social_Model_Comment[]
     */
    protected $comments;


    /**
     * {@inheritdoc}
     */
    public function __construct(User_Model_User $author = null)
    {
        parent::__construct($author);
        $this->comments = new ArrayCollection();
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
    public function isPublished()
    {
        return $this->published;
    }

    /**
     * @param boolean $published
     */
    public function setPublished($published)
    {
        $this->published = (boolean) $published;
    }

    /**
     * @return DateTime
     */
    public function getPublicationDate()
    {
        return $this->publicationDate;
    }

    /**
     * @param DateTime $publicationDate
     */
    public function setPublicationDate(DateTime $publicationDate)
    {
        $this->publicationDate = $publicationDate;
    }

    /**
     * @return Social_Model_Comment[]
     */
    public function getComments()
    {
        return $this->comments;
    }

    /**
     * @param Social_Model_Comment $comment
     */
    public function addComment(Social_Model_Comment $comment)
    {
        if (! $this->comments->contains($comment)) {
            $this->comments->add($comment);
        }
    }

}
