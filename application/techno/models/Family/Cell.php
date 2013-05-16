<?php
/**
 * @author  ronan.gorain
 * @author  maxime.fourt
 * @author  matthieu.napoli
 * @package Techno
 */

use Doctrine\Common\Collections\Collection;

/**
 * Cellule d'une famille
 * @package Techno
 */
class Techno_Model_Family_Cell extends Core_Model_Entity
{

    /**
     * @var int
     */
    protected $id;

    /**
     * Famille de la cellule
     * @var Techno_Model_Family
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
     * Élément choisi de la cellule
     * @var Techno_Model_Element
     */
    protected $chosenElement;

    /**
     * Constructeur
     * @param Techno_Model_Family                     $family  Famille de la cellule
     * @param Collection|Techno_Model_Family_Member[] $members Liste des membres/coordonnées de la cellule
     **/
    public function __construct(Techno_Model_Family $family, Collection $members)
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
     * @return Collection|Techno_Model_Family_Member[]
     */
    public function getMembers()
    {
        return $this->members;
    }

    /**
     * Définit l'élément choisi
     * @param Techno_Model_Element $element
     */
    public function setChosenElement($element = null)
    {
        $this->chosenElement = $element;
    }

    /**
     * Retourne L'élément choisi de la cellule (si il existe)
     * @return Techno_Model_Element|null
     */
    public function getChosenElement()
    {
        return $this->chosenElement;
    }

    /**
     * @return Techno_Model_Family
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
     * Les membres sont ordonnés dans la hashkey par le ref du keyword du meaning de la dimension
     * afin de persister l'association dimension => membre et non pas juste une liste de membres
     *
     * @param Techno_Model_Family_Member[] $members Liste des membres/coordonnées
     * @return string Hash key unique pour les coordonnées données
     */
    public static function buildMembersHashKey($members)
    {
        $membersKeywords = [];
        foreach ($members as $member) {
            $refKeywordMeaning = $member->getDimension()->getMeaning()->getRef();
            $membersKeywords[$refKeywordMeaning] = $member->getRef();
        }
        // Trie le tableau par la clé (donc l'id de la dimension)
        ksort($membersKeywords);
        return implode('|', $membersKeywords);
    }

}
