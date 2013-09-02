<?php

namespace Keyword\Domain;

use Core_Exception_UndefinedAttribute;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * Classe metier de Keyword.
 * @author valentin.claras
 * @author bertrand.ferry
 * @author maxime.fourt
 */
class Keyword
{
    /**
     * Identifiant unique du Keyword.
     *
     * @var int
     */
    protected $id;

    /**
     * Identifiant textuel du Keyword.
     *
     * @var string
     */
    protected $ref = null;

    /**
     * Label du Keyword.
     *
     * @var string
     */
    protected $label;

    /**
     * Collection des associations avec les autres Keyword en tant qu'objet.
     *
     * @var Collection
     */
    protected $objectAssociation;

    /**
     * Collection des associations avec les autres Keyword en tant que subjet.
     *
     * @var Collection
     */
    protected $subjectAssociation;


    /**
     * Constructeur.
     */
    public function __construct()
    {
        $this->objectAssociation = new ArrayCollection();
        $this->subjectAssociation = new ArrayCollection();
    }
//
//    /**
//     * Retourne le mot-cle correspondant a la reference.
//     *
//     * @param string $ref
//     *
//     * @return Keyword
//     */
//    public static function loadByRef($ref)
//    {
//        return self::getEntityRepository()->loadBy(array('ref' => $ref));
//    }
//
//    /**
//     * Charge la liste des Keyword ne possédant pas d'association en tant qu'objet.
//     *
//     * @param Core_Model_Query $queryParameters
//     *
//     * @return Keyword[]
//     */
//    public static function loadListRoots($queryParameters = null)
//    {
//        if ($queryParameters == null) {
//            $queryParameters = new Core_Model_Query();
//            $queryParameters->order->addOrder(self::QUERY_LABEL);
//        }
//
//        return self::getEntityRepository()->loadListRoots($queryParameters);
//    }
//
//    /**
//     * Charge la liste des Keyword répondant à la requête donnée.
//     *
//     * @param string $expressionQuery
//     *
//     * @return Keyword[]
//     */
//    public static function loadListMatchingQuery($expressionQuery)
//    {
//        return self::getEntityRepository()->loadListMatchingQuery($expressionQuery);
//    }

    /**
     * Renvoi l'identifiant unique du Keyword.
     *
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
     * @param string $ref
     */
    public function setRef($ref)
    {
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
        if ($this->ref === null) {
            throw new Core_Exception_UndefinedAttribute('The keyword reference has not been defined yet.');
        }
        return $this->ref;
    }

    /**
     * Modifie le label du Keyword.
     *
     * @param string $label
     */
    public function setLabel($label)
    {
        $this->label = $label;
    }

    /**
     * Renvoi le label du Keyword.
     *
     * @throws \Core_Exception_UndefinedAttribute
     * @return string
     */
    public function getLabel()
    {
        if ($this->label === null) {
            throw new Core_Exception_UndefinedAttribute('The keyword label has not been defined yet.');
        }
        return $this->label;
    }

    /**
     * Ajoute une Association où le Keyword agit en tant qu'object.
     *
     * @param Association $association
     */
    public function addAssociationAsObject(Association $association)
    {
        if (!($this->hasAssociationAsObject($association))) {
            $this->objectAssociation->add($association);
            $association->setObject($this);
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
        return $this->objectAssociation->contains($association);
    }


    /**
     * Vérifie si le Keyword possède au moins une association en tant qu'objet.
     *
     * @return bool
     */
    public function hasAssociationsAsObject()
    {
        return !$this->objectAssociation->isEmpty();
    }

    /**
     * Compte le nombre de relation possédant ce Keyword en tant qu'objet.
     *
     * @return int
     */
    public function countAssociationsAsObject()
    {
        return $this->objectAssociation->count();
    }

    /**
     * Renvoi le tableau des associations du Keyword en tant qu'objet.
     *
     * @return array
     */
    public function getAssociationsAsObject()
    {
        return $this->objectAssociation->toArray();
    }

    /**
     * Ajoute une Association où le Keyword agit en tant que sujet.
     *
     * @param Association $association
     */
    public function addAssociationAsSubject(Association $association)
    {
        if (!($this->hasAssociationsAsSubject($association))) {
            $this->subjectAssociation->add($association);
            $association->setSubject($this);
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
        return $this->subjectAssociation->contains($association);
    }


    /**
     * Vérifie si le Keyword possède au moins une association en tant que sujet.
     *
     * @return bool
     */
    public function hasAssociationsAsSubject()
    {
        return !$this->subjectAssociation->isEmpty();
    }

    /**
     * Compte le nombre de relation possédant ce Keyword en tant que sujet.
     *
     * @return int
     */
    public function countAssociationsAsSubject()
    {
        return $this->subjectAssociation->count();
    }

    /**
     * Renvoi le tableau des associations du Keyword en tant que sujet.
     *
     * @return array
     */
    public function getAssociationsAsSubject()
    {
        return $this->subjectAssociation->toArray();
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
