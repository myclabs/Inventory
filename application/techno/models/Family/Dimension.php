<?php
/**
 * @author  simon.rieu
 * @author  maxime.fourt
 * @author  matthieu.napoli
 * @package Techno
 */

use \Doctrine\Common\Collections\Collection;
use \Doctrine\Common\Collections\ArrayCollection;
use \Doctrine\Common\Collections\Criteria;

/**
 * Classe Dimension
 * @package Techno
 */
class Techno_Model_Family_Dimension extends Core_Model_Entity
{

    use Core_Strategy_Ordered;

    const QUERY_MEANING = 'meaning';

    /**
     * Orientation de la dimension
     */
    const ORIENTATION_HORIZONTAL = 1;
    const ORIENTATION_VERTICAL = 2;

    /**
     * @var int
     */
    protected $id;

    /**
     * @var Techno_Model_Family
     */
    protected $family;

    /**
     * Meaning correspondant à la dimension
     * @var Techno_Model_Meaning
     */
    protected $meaning;

    /**
     * Orientation de la dimension
     * @var int
     */
    protected $orientation;

    /**
     * Membres de la dimension
     * @var Collection
     */
    protected $members;

    /**
     * Requête
     * @var string
     */
    protected $query;

    /**
     * Construction d'une dimension
     * @param Techno_Model_Family  $family
     * @param Techno_Model_Meaning $meaning
     * @param int                  $orientation
     * @param string               $query
     * @throws Core_Exception_InvalidArgument
     */
    public function __construct(Techno_Model_Family $family, Techno_Model_Meaning $meaning, $orientation, $query = null)
    {
        $this->members = new ArrayCollection();
        $this->meaning = $meaning;
        if ($orientation != self::ORIENTATION_HORIZONTAL && $orientation != self::ORIENTATION_VERTICAL) {
            throw new Core_Exception_InvalidArgument("Unknown orientation type");
        }
        $this->orientation = $orientation;
        $this->query = $query;
        $this->family = $family;
        // Ajout réciproque à la famille
        $family->addDimension($this);
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return Techno_Model_Family
     */
    public function getFamily()
    {
        return $this->family;
    }

    /**
     * @param Techno_Model_Meaning $meaning
     */
    public function setMeaning(Techno_Model_Meaning $meaning)
    {
        $this->meaning = $meaning;
    }

    /**
     * @return Techno_Model_Meaning
     */
    public function getMeaning()
    {
        return $this->meaning;
    }

    /**
     * Définit l'orientation de la dimension
     * - self::ORIENTATION_HORIZONTAL
     * - self::ORIENTATION_VERTICAL
     *
     * Entraine la recréation des cellules de la famille
     * @param int $orientation
     * @throws Core_Exception_InvalidArgument
     * @return void
     */
    public function setOrientation($orientation)
    {
        if ($orientation != self::ORIENTATION_HORIZONTAL && $orientation != self::ORIENTATION_VERTICAL) {
            throw new Core_Exception_InvalidArgument("Unknown orientation type");
        }
        $this->deletePosition();
        $this->orientation = $orientation;
        $this->setPosition();
        // Reconstruit les cellules de la famille
        $this->getFamily()->buildCells();
    }

    /**
     * Retourne l'orientation de la dimension
     * - self::ORIENTATION_HORIZONTAL
     * - self::ORIENTATION_VERTICAL
     * @return int
     */
    public function getOrientation()
    {
        return $this->orientation;
    }

    /**
     * Retourne la liste des membres de la dimension
     * @return Collection|Techno_Model_Family_Member[]
     */
    public function getMembers()
    {
        return $this->members;
    }

    /**
     * Retourne un membre de la dimension en le recherchant par son mot-clé
     * @param Keyword_Model_Keyword $keyword
     * @throws Core_Exception_NotFound
     * @return Techno_Model_Family_Member
     */
    public function getMember(Keyword_Model_Keyword $keyword)
    {
        // Filtre la collection sur le keyword du membre
        $results = $this->members->filter(
            function (Techno_Model_Family_Member $member) use ($keyword) {
                return ($member->getKeyword()->getRef() == $keyword->getRef());
            }
        );
        if (count($results) > 0) {
            return $results->first();
        }
        throw new Core_Exception_NotFound("Le membre $keyword est introuvable dans cette dimension");
    }

    /**
     * Ajoute un membre à la dimension
     *
     * Entraine la recréation des cellules de la famille
     * @param Techno_Model_Family_Member $member
     */
    public function addMember(Techno_Model_Family_Member $member)
    {
        if (!$this->hasMember($member)) {
            $this->members->add($member);

            // Ajoute les cellules associées
            $this->getFamily()->addCellsForNewMember($member);
        }
    }

    /**
     * @param Techno_Model_Family_Member $member
     * @return boolean
     */
    public function hasMember(Techno_Model_Family_Member $member)
    {
        return $this->members->contains($member);
    }

    /**
     * Supprime un membre de la dimension
     *
     * Entraine la recréation des cellules de la famille
     * @param Techno_Model_Family_Member $member
     */
    public function removeMember(Techno_Model_Family_Member $member)
    {
        if ($this->hasMember($member)) {
            $this->members->removeElement($member);
        }
    }

    /**
     * @param string $query
     */
    public function setQuery($query)
    {
        $this->query = $query;
    }

    /**
     * @return string
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * @return string Label du meaning de la dimension
     */
    public function getLabel()
    {
        return $this->getMeaning()->getLabel();
    }

    /**
     * Fonction appelé avant un persist de l'objet (défini dans le mapper).
     */
    public function preSave()
    {
        try {
            $this->checkHasPosition();
        } catch (Core_Exception_UndefinedAttribute $e) {
            $this->setPosition();
        }
    }

    /**
     * Fonction appelé avant un update de l'objet (défini dans le mapper).
     */
    public function preUpdate()
    {
        $this->checkHasPosition();
    }

    /**
     * Fonction appelé avant un delete de l'objet (défini dans le mapper).
     */
    public function preDelete()
    {
        $this->deletePosition();
    }

    /**
     * Fonction appelé après un load de l'objet (défini dans le mapper).
     */
    public function postLoad()
    {
        $this->updateCachePosition();
    }

    /**
     * Renvoie les valeurs du contexte pour l'objet.
     * @throws Core_Exception_InvalidArgument
     * @return array
     */
    protected function getContext()
    {
        if ($this->family->getId() == null) {
            throw new Core_Exception_InvalidArgument("La famille de la dimension doit être persistée et flushée");
        }
        return [
            'family'      => $this->family,
            'orientation' => $this->orientation,
        ];
    }

}
