<?php
/**
 * @package Social
 */

use Doc\Domain\Bibliography;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @author  joseph.rouffet
 * @author  matthieu.napoli
 * @package Social
 */
abstract class Social_Model_Action extends Core_Model_Entity
{

    const QUERY_LABEL = 'label';

    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $label;

    /**
     * Description de l'action
     * @var string
     */
    protected $description;

    /**
     * Commentaires
     * @var Collection|Social_Model_Comment[]
     */
    protected $comments;

    /**
     * @var Bibliography
     */
    protected $documentBibliography;


    /**
     * Constructeur
     */
    public function __construct()
    {
        $this->comments = new ArrayCollection();
        $this->documentBibliography = new Bibliography();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $label
     */
    public function setLabel($label)
    {
        $this->label = (string) $label;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = (string) $description;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
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

    /**
     * @return Bibliography
     */
    public function getDocumentBibliography()
    {
        return $this->documentBibliography;
    }

}
