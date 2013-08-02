<?php

namespace Keyword\Domain;

use Core_Model_Entity;
use Core_Exception_TooMany;
use Core_Exception_UndefinedAttribute;

/**
 * @author valentin.claras
 */
class Association extends Core_Model_Entity
{
    // Constantes de tri et filtres.
    const QUERY_SUBJECT = 'subject';
    const QUERY_OBJECT = 'object';
    const QUERY_PREDICATE = 'predicate';

    /**
     * Identifiant unique du Keyword.
     *
     * @var int
     */
    protected $id;

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
     * Renvoie une Association en fonction des refs de ces composants
     *
     * @param string $subjectKeywordRef
     * @param string $objectKeywordRef
     * @param string $predicateRef
     *
     * @return Association
     */
    public static function loadByRefs($subjectKeywordRef, $objectKeywordRef, $predicateRef)
    {
        return self::getEntityRepository()->loadByRefs($subjectKeywordRef, $objectKeywordRef, $predicateRef);
    }

    /**
     * Défini le Keyword sujet.
     *
     * @param Keyword $subjectKeyword
     */
    public function setSubject(Keyword $subjectKeyword)
    {
        if ($this->subject !== $subjectKeyword) {
            if ($this->subject !== null) {
                throw new Core_Exception_TooMany('The subject has already been defined.');
            }
            $this->subject = $subjectKeyword;
            $subjectKeyword->addAssociationAsSubject($this);
        }
    }

    /**
     * Renvoi le Keyword sujet.
     *
     * @return Keyword
     */
    public function getSubject()
    {
        if ($this->subject === null) {
            throw new Core_Exception_UndefinedAttribute('The subject keyword has not been defined yet.');
        }
        return $this->subject;
    }

    /**
     * Défini le Keyword objet.
     *
     * @param Keyword $objectKeyword
     */
    public function setObject(Keyword $objectKeyword)
    {
        if ($this->object !== $objectKeyword) {
            if ($this->object !== null) {
                throw new Core_Exception_TooMany('The object has already been defined.');
            }
            $this->object = $objectKeyword;
            $objectKeyword->addAssociationAsObject($this);
        }
    }

    /**
     * Renvoi le Keyword sujet.
     *
     * @return Keyword
     */
    public function getObject()
    {
        if ($this->object === null) {
            throw new Core_Exception_UndefinedAttribute('The object keyword has not been defined yet.');
        }
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
     * @return Predicate
     */
    public function getPredicate()
    {
        if ($this->predicate === null) {
            throw new Core_Exception_UndefinedAttribute('The predicate has not been defined yet.');
        }
        return $this->predicate;
    }

}
