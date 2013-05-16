<?php
/**
 * @author valentin.claras
 * @package    Keyword
 * @subpackage Model
 */

/**
 * @package    Keyword
 * @subpackage Model
 */
class Keyword_Model_Association extends Core_Model_Entity
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
     * @var Keyword_Model_Keyword
     */
    protected $subject = null;

    /**
     * Keyword objet de l'association.
     *
     * @var Keyword_Model_Keyword
     */
    protected $object = null;

    /**
     * Predicate de l'association.
     *
     * @var Keyword_Model_Predicate
     */
    protected $predicate = null;


    /**
     * Renvoie une Association en fonction des refs de ces composants
     *
     * @param string $subjectKeywordRef
     * @param string $objectKeywordRef
     * @param string $predicateRef
     *
     * @return Keyword_Model_Association
     */
    public static function loadByRefs($subjectKeywordRef, $objectKeywordRef, $predicateRef)
    {
        return self::getEntityRepository()->loadByRefs($subjectKeywordRef, $objectKeywordRef, $predicateRef);
    }

    /**
     * Défini le Keyword sujet.
     *
     * @param Keyword_Model_Keyword $subjectKeyword
     */
    public function setSubject(Keyword_Model_Keyword $subjectKeyword)
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
     * @return Keyword_Model_Keyword
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
     * @param Keyword_Model_Keyword $objectKeyword
     */
    public function setObject(Keyword_Model_Keyword $objectKeyword)
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
     * @return Keyword_Model_Keyword
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
     * @param Keyword_Model_Predicate $predicate
     */
    public function setPredicate(Keyword_Model_Predicate $predicate)
    {
        $this->predicate = $predicate;
    }

    /**
     * Renvoi le Predicate.
     *
     * @return Keyword_Model_Predicate
     */
    public function getPredicate()
    {
        if ($this->predicate === null) {
            throw new Core_Exception_UndefinedAttribute('The predicate has not been defined yet.');
        }
        return $this->predicate;
    }

}