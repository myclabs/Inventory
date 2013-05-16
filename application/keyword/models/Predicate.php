<?php
/**
 * @author valentin.claras
 * @author bertrand.ferry
 * @package    Keyword
 * @subpackage Model
 */

/**
 * Classe metier de Predicate.
 * @package    Keyword
 * @subpackage Model
 */
class Keyword_Model_Predicate extends Core_Model_Entity// implements Log_ChangeObservable
{
    // Constantes de tri et filtres.
    const QUERY_ID = 'id';
    const QUERY_REF = 'ref';
    const QUERY_LABEL = 'label';
    const QUERY_REVERSE_REF = 'reverseRef';
    const QUERY_REVERSE_LABEL = 'reverseLabel';
    const QUERY_DESCRIPTION = 'description';

    /**
     * Identifiant unique du predicat.
     *
     * @var int
     */
    protected $id = null;

    /**
     * Identifiant textuel du predicat.
     *
     * @var string
     */
    protected $ref = null;

    /**
     * Label du predicat.
     *
     * @var string
     */
    protected $label = null;

    /**
     * Referent textuel du predicat inverse.
     *
     * @var string
     */
    protected $reverseRef = null;

    /**
     * Label du predicat inverse.
     *
     * @var string
     */
    protected $reverseLabel = null;

    /**
     * Description du predicat.
     *
     * @var string
     */
    protected $description = '';


    /**
     * Retourne le predicat correspondant a la reference donnée.
     *
     * @param string $ref
     *
     * @return Keyword_Model_Predicate
     */
    public static function loadByRef($ref)
    {
        return self::getEntityRepository()->loadBy(array('ref' => $ref));
    }

    /**
     * Retourne le predicat correspondant a la reference inverse donnée.
     *
     * @param string $reverseRef
     *
     * @return Keyword_Model_Predicate
     */
    public static function loadByReverseRef($reverseRef)
    {
        return self::getEntityRepository()->loadBy(array('reverseRef' => $reverseRef));
    }

    /**
     * Défini la référence du Keyword.
     *
     * @param string $ref
     */
    public function setRef($ref)
    {
        $this->ref = $ref;
    }

    /**
     * Renvoi la référence du Keyword.
     *
     * @return string
     */
    public function getRef()
    {
        if ($this->ref === null) {
            throw new Core_Exception_UndefinedAttribute('The predicate reference has not been defined yet.');
        }
        return $this->ref;
    }

    /**
     * Défini le label.
     *
     * @param string $label
     */
    public function setLabel($label)
    {
        $this->label = $label;
    }

    /**
     * Renvoi le label.
     *
     * @return string
     */
    public function getLabel()
    {
        if ($this->label === null) {
            throw new Core_Exception_UndefinedAttribute('The predicate label has not been defined yet.');
        }
        return $this->label;
    }

    /**
     * Défini la référence inverse.
     *
     * @param string $revRef
     */
    public function setReverseRef($revRef)
    {
        $this->reverseRef = $revRef;
    }

    /**
     * Renvoi la référence inverse.
     *
     * @return string
     */
    public function getReverseRef()
    {
        if ($this->ref === null) {
            throw new Core_Exception_UndefinedAttribute('The predicate reverse reference has not been defined yet.');
        }
        return $this->reverseRef;
    }

    /**
     * Défini le label inverse.
     *
     * @param string $revLabel
     */
    public function setReverseLabel($revLabel)
    {
        $this->reverseLabel = $revLabel;
    }

    /**
     * Renvoi le label inverse.
     *
     * @return string
     */
    public function getReverseLabel()
    {
        if ($this->reverseLabel === null) {
            throw new Core_Exception_UndefinedAttribute('The predicate reverse label has not been defined yet.');
        }
        return $this->reverseLabel;
    }

    /**
     * Défini la description.
     *
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * Renvoi la description.
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

}