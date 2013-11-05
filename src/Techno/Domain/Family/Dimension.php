<?php

namespace Techno\Domain\Family;

use Core_Exception_InvalidArgument;
use Core_Exception_NotFound;
use Core_Exception_UndefinedAttribute;
use Core_Model_Entity;
use Core_Strategy_Ordered;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Keyword\Application\Service\KeywordDTO;
use Techno\Domain\Meaning;

/**
 * Dimension d'une famille.
 *
 * @author simon.rieu
 * @author maxime.fourt
 * @author matthieu.napoli
 */
class Dimension extends Core_Model_Entity
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
     * @var Family
     */
    protected $family;

    /**
     * Meaning correspondant à la dimension
     * @var Meaning
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
     * @param Family  $family
     * @param Meaning $meaning
     * @param int     $orientation
     * @param string  $query
     * @throws Core_Exception_InvalidArgument
     */
    public function __construct(Family $family, Meaning $meaning, $orientation, $query = null)
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
     * @return Family
     */
    public function getFamily()
    {
        return $this->family;
    }

    /**
     * @param Meaning $meaning
     */
    public function setMeaning(Meaning $meaning)
    {
        $this->meaning = $meaning;
    }

    /**
     * @return Meaning
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
     * @return Collection|Member[]
     */
    public function getMembers()
    {
        return $this->members;
    }

    /**
     * Retourne un membre de la dimension en le recherchant par son mot-clé
     * @param KeywordDTO $keyword
     * @throws Core_Exception_NotFound
     * @return Member
     */
    public function getMember(KeywordDTO $keyword)
    {
        // Filtre la collection sur le keyword du membre
        $results = $this->members->filter(
            function (Member $member) use ($keyword) {
                return ($member->getKeyword()->getRef() == $keyword->getRef());
            }
        );
        if (count($results) > 0) {
            return $results->first();
        }
        throw new Core_Exception_NotFound("Le membre $keyword->getRef() est introuvable dans cette dimension");
    }

    /**
     * Ajoute un membre à la dimension
     *
     * Entraine la recréation des cellules de la famille
     * @param Member $member
     */
    public function addMember(Member $member)
    {
        if (!$this->hasMember($member)) {
            $this->members->add($member);

            // Ajoute les cellules associées
            $this->getFamily()->addCellsForNewMember($member);
        }
    }

    /**
     * @param Member $member
     * @return boolean
     */
    public function hasMember(Member $member)
    {
        return $this->members->contains($member);
    }

    /**
     * Supprime un membre de la dimension
     *
     * Entraine la recréation des cellules de la famille
     * @param Member $member
     */
    public function removeMember(Member $member)
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
     * Fonction appelée avant un persist de l'objet (défini dans le mapper).
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
     * Fonction appelée avant un update de l'objet (défini dans le mapper).
     */
    public function preUpdate()
    {
        $this->checkHasPosition();
    }

    /**
     * Fonction appelée avant un delete de l'objet (défini dans le mapper).
     */
    public function preDelete()
    {
        $this->deletePosition();
    }

    /**
     * Fonction appelée après un load de l'objet (défini dans le mapper).
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
