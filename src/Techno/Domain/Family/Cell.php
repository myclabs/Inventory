<?php

namespace Techno\Domain\Family;

use Calc_UnitValue;
use Calc_Value;
use Core_Model_Entity;
use Doctrine\Common\Collections\Collection;

/**
 * Cellule d'une famille.
 *
 * @author ronan.gorain
 * @author maxime.fourt
 * @author matthieu.napoli
 */
class Cell extends Core_Model_Entity
{
    /**
     * @var int
     */
    protected $id;

    /**
     * Famille de la cellule
     * @var Family
     */
    protected $family;

    /**
     * Membres de la cellule
     * @var Collection
     */
    protected $members;

    /**
     * Représentation en chaine de caractère des membres de la cellule
     * @var string
     */
    protected $membersHashKey;

    /**
     * Valeur dans cette cellule.
     * @var Calc_Value|null $value
     */
    protected $value;

    /**
     * @param Family              $family  Famille de la cellule
     * @param Collection|Member[] $members Liste des membres/coordonnées de la cellule
     **/
    public function __construct(Family $family, Collection $members)
    {
        $this->family = $family;
        $this->members = $members;
        $this->updateMembersHashKey();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Retourne la liste des coordonnées de la cellule
     * @return Collection|Member[]
     */
    public function getMembers()
    {
        return $this->members;
    }

    /**
     * Définit la valeur dans cette cellule.
     * @param Calc_Value|null $value
     */
    public function setValue(Calc_Value $value = null)
    {
        $this->value = $value;
    }

    /**
     * Retourne la valeur associée à cette cellule.
     * @return Calc_Value|null
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return Family
     */
    public function getFamily()
    {
        return $this->family;
    }

    /**
     * Retourne la clé de hashage des membres de la cellule
     * @return string
     */
    public function getMembersHashKey()
    {
        return $this->membersHashKey;
    }

    /**
     * Met à jour la clé de hashage
     */
    public function updateMembersHashKey()
    {
        $this->membersHashKey = self::buildMembersHashKey($this->members);
    }

    /**
     * Construit une chaine de caractère représentant les coordonnées (= les membres)
     *
     * Les membres sont ordonnés dans la hashkey par le ref de la dimension
     * afin de persister l'association dimension => membre et non pas juste une liste de membres
     *
     * @param Member[] $members Liste des membres/coordonnées
     * @return string Hash key unique pour les coordonnées données
     */
    public static function buildMembersHashKey($members)
    {
        $membersId = [];
        foreach ($members as $member) {
            $dimensionId = $member->getDimension()->getRef();
            $membersId[$dimensionId] = $member->getRef();
        }
        // Trie le tableau par la clé (donc l'id de la dimension)
        ksort($membersId);
        return implode('|', $membersId);
    }
}
