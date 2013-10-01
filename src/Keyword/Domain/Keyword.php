<?php

namespace Keyword\Domain;

use Core\Domain\Translatable\TranslatableEntity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Keyword\Domain\Predicate;
use Keyword\Domain\Association;

/**
 * Entité Keyword.
 * @author valentin.claras
 */
class Keyword
{
    use TranslatableEntity;

    /**
     * @var int
     */
    protected $id;

    /**
     * Identifiant textuel du Keyword.
     * @var string
     */
    protected $ref;

    /**
     * @var string
     */
    protected $label;

    /**
     * Associations avec les autres Keyword en tant qu'objet.
     * @var Collection|Association[]
     */
    protected $objectAssociations;

    /**
     * Associations avec les autres Keyword en tant que subjet.
     * @var Collection|Association[]
     */
    protected $subjectAssociations;


    /**
     * @param string $ref
     * @param string $label
     */
    public function __construct($ref, $label = '')
    {
        $this->objectAssociations = new ArrayCollection();
        $this->subjectAssociations = new ArrayCollection();

        $this->setRef($ref);
        $this->setLabel($label);
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Modifie la reference du Keyword.
     *
     * @param string $ref
     * @throws \Core_Exception_InvalidArgument
     */
    public function setRef($ref)
    {
        if (empty($ref)) {
            throw new \Core_Exception_InvalidArgument("A Keyword's ref can't be empty.");
        }
        $this->ref = $ref;
    }

    /**
     * Renvoi la référence du Keyword.
     *
     * @throws \Core_Exception_UndefinedAttribute
     * @return string
     */
    public function getRef()
    {
        return $this->ref;
    }

    /**
     * @param string $label
     */
    public function setLabel($label)
    {
        $this->label = $label;
    }

    /**
     * @throws \Core_Exception_UndefinedAttribute
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Ajoute une Association où le Keyword agit en tant que sujet.
     *
     * @param Predicate $predicate
     * @param Keyword $objectKeyword
     * @return Association
     */
    public function addAssociationWith(Predicate $predicate, Keyword $objectKeyword)
    {
        $association = new Association($this, $predicate, $objectKeyword);
        $this->subjectAssociations->add($association);
        $objectKeyword->objectAssociations->add($association);
        return $association;
    }

    /**
     * Retire une Association où le Keyword agit en tant que sujet.
     *
     * @param Association $association
     */
    public function removeAssociation(Association $association)
    {
        if ($this->subjectAssociations->contains($association)) {
            $this->subjectAssociations->removeElement($association);
        }
        if ($association->getObject()->objectAssociations->contains($association)) {
            $association->getObject()->objectAssociations->removeElement($association);
        }
    }

    /**
     * Vérifie si le Keyword possède au moins une association en tant que sujet.
     *
     * @return bool
     */
    public function hasAssociationsAsSubject()
    {
        return !$this->subjectAssociations->isEmpty();
    }

    /**
     * Compte le nombre de relation possédant ce Keyword en tant que sujet.
     *
     * @return int
     */
    public function countAssociationsAsSubject()
    {
        return $this->subjectAssociations->count();
    }

    /**
     * Renvoi le tableau des associations du Keyword en tant que sujet.
     *
     * @return Association[]
     */
    public function getAssociationsAsSubject()
    {
        return $this->subjectAssociations->toArray();
    }

    /**
     * Vérifie si le Keyword possède au moins une association en tant qu'objet.
     *
     * @return bool
     */
    public function hasAssociationsAsObject()
    {
        return !$this->objectAssociations->isEmpty();
    }

    /**
     * Compte le nombre de relation possédant ce Keyword en tant qu'objet.
     *
     * @return int
     */
    public function countAssociationsAsObject()
    {
        return $this->objectAssociations->count();
    }

    /**
     * Renvoi le tableau des associations du Keyword en tant qu'objet.
     *
     * @return Association[]
     */
    public function getAssociationsAsObject()
    {
        return $this->objectAssociations->toArray();
    }

    /**
     * Compte le nombre de relation possédant ce Keyword en tant qu'objet ou sujet.
     *
     * @return int
     */
    public function countAssociations()
    {
        return $this->countAssociationsAsObject() + $this->countAssociationsAsSubject();
    }

}
