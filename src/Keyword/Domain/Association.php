<?php

namespace Keyword\Domain;

use Core_Exception_UndefinedAttribute;

/**
 * @author valentin.claras
 */
class Association
{
    /**
     * Keyword sujet de l'association.
     *
     * @var Keyword
     */
    protected $subject = null;

    /**
     * Keyword objet de l'association.
     *
     * @var Keyword
     */
    protected $object = null;

    /**
     * Predicate de l'association.
     *
     * @var Predicate
     */
    protected $predicate = null;


    /**
     * Constructeur de la class Association.
     *
     * @param Keyword $subjectKeyword
     * @param Predicate $predicate
     * @param Keyword $objectKeyword
     */
    public function __construct(Keyword $subjectKeyword, Predicate $predicate, Keyword $objectKeyword)
    {
        $this->subject = $subjectKeyword;
        $this->predicate = $predicate;
        $this->object = $objectKeyword;

        $subjectKeyword->addAssociationAsSubject($this);
        $objectKeyword->addAssociationAsObject($this);
    }

    /**
     * Renvoi l'identifiant unique de l'Association.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Renvoi le Keyword sujet.
     *
     * @return Keyword
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * Renvoi le Keyword sujet.
     *
     * @throws \Core_Exception_UndefinedAttribute
     * @return Keyword
     */
    public function getObject()
    {
        return $this->object;
    }

    /**
     * Défini le Predicate.
     *
     * @param Predicate $predicate
     */
    public function setPredicate(Predicate $predicate)
    {
        $this->predicate = $predicate;
    }

    /**
     * Renvoi le Predicate.
     *
     * @throws \Core_Exception_UndefinedAttribute
     * @return Predicate
     */
    public function getPredicate()
    {
        return $this->predicate;
    }

}
