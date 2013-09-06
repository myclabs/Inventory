<?php

namespace Keyword\Domain;

/**
 * Association entre des Keywords.
 * @author valentin.claras
 */
class Association
{
    /**
     * Keyword sujet de l'association.
     *
     * @var Keyword
     */
    protected $subject;

    /**
     * Keyword objet de l'association.
     *
     * @var Keyword
     */
    protected $object;

    /**
     * Predicat de l'association.
     *
     * @var Predicate
     */
    protected $predicate;


    /**
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
     * Renvoi le sujet de l'association.
     *
     * @return Keyword
     */
    public function getObject()
    {
        return $this->object;
    }

    /**
     * Renvoi le prédicat de l'association.
     *
     * @return Predicate
     */
    public function getPredicate()
    {
        return $this->predicate;
    }
}
