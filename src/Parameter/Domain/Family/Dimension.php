<?php

namespace Parameter\Domain\Family;

use Core\Translation\TranslatedString;
use Core_Exception_InvalidArgument;
use Core_Exception_NotFound;
use Core_Exception_UndefinedAttribute;
use Core_Model_Entity;
use Core_Strategy_Ordered;
use Core_Tools;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;

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
     * @var string
     */
    protected $ref;

    /**
     * @var TranslatedString
     */
    protected $label;

    /**
     * Orientation de la dimension
     * @var int
     */
    protected $orientation;

    /**
     * Membres de la dimension
     * @var Collection|Member[]
     */
    protected $members;

    /**
     * @param Family           $family
     * @param string           $ref
     * @param TranslatedString $label
     * @param int              $orientation
     * @throws Core_Exception_InvalidArgument
     */
    public function __construct(Family $family, $ref, TranslatedString $label, $orientation)
    {
        $this->members = new ArrayCollection();
        Core_Tools::checkRef($ref);
        $this->ref = $ref;
        $this->label = $label;
        if ($orientation != self::ORIENTATION_HORIZONTAL && $orientation != self::ORIENTATION_VERTICAL) {
            throw new Core_Exception_InvalidArgument("Unknown orientation type");
        }
        $this->orientation = $orientation;
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
     * @return string
     */
    public function getRef()
    {
        return $this->ref;
    }

    /**
     * @param string $ref
     */
    public function setRef($ref)
    {
        $this->ref = $ref;

        $this->family->updateCellsHashKey();
    }

    /**
     * @return TranslatedString
     */
    public function getLabel()
    {
        return $this->label;
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
     * @param string $memberId
     * @throws Core_Exception_NotFound
     * @return Member
     */
    public function getMember($memberId)
    {
        // On caste en string parce que le criteria compare avec "==="
        $memberId = (string) $memberId;

        // Filtre la collection sur le ref du membre
        $criteria = Criteria::create();
        $criteria->where(Criteria::expr()->eq('ref', $memberId));
        /** @var Collection $results */
        $results = $this->members->matching($criteria);
        if (count($results) > 0) {
            return $results->first();
        }
        throw MemberNotFoundException::create($this->getFamily()->getRef(), $this->getRef(), $memberId);
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
