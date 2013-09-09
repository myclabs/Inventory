<?php

namespace Keyword\Domain;

use Core\Domain\Translatable\TranslatableEntity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

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

        $this->setRef(empty($ref) ? \Core_Tools::refactor($label) : $ref);
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
        if (is_null($ref)) {
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
     * Ajoute une Association où le Keyword agit en tant qu'object.
     *
     * @param Association $association
     * @throws \Core_Exception_InvalidArgument
     */
    public function addAssociationAsObject(Association $association)
    {
        if ($association->getObject() !== $this) {
            throw new \Core_Exception_InvalidArgument();
        }

        if (!($this->hasAssociationAsObject($association))) {
            $this->objectAssociations->add($association);
        }
    }

    /**
     * Vérifie sur le Keyword possède l'association donnée en tant qu'objet.
     *
     * @param Association $association
     *
     * @return bool
     */
    public function hasAssociationAsObject(Association $association)
    {
        return $this->objectAssociations->contains($association);
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
     * Ajoute une Association où le Keyword agit en tant que sujet.
     *
     * @param Association $association
     * @throws \Core_Exception_InvalidArgument
     */
    public function addAssociationAsSubject(Association $association)
    {
        if ($association->getSubject() !== $this) {
            throw new \Core_Exception_InvalidArgument();
        }

        if (!($this->hasAssociationsAsSubject($association))) {
            $this->subjectAssociations->add($association);
        }
    }

    /**
     * Vérifie sur le Keyword possède l'association donnée en tant que sujet.
     *
     * @param Association $association
     *
     * @return bool
     */
    public function hasAssociationAsSubject(Association $association)
    {
        return $this->subjectAssociations->contains($association);
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
     * Compte le nombre de relation possédant ce Keyword en tant qu'objet ou sujet.
     *
     * @return int
     */
    public function countAssociations()
    {
        return $this->countAssociationsAsObject() + $this->countAssociationsAsSubject();
    }

}
