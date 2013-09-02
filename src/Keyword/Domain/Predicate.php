<?php

namespace Keyword\Domain;

use Core_Exception_UndefinedAttribute;

/**
 * Classe metier de Predicate.
 * @author valentin.claras
 * @author bertrand.ferry
 */
class Predicate
{
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

//
//    /**
//     * Retourne le predicat correspondant a la reference donnée.
//     *
//     * @param string $ref
//     *
//     * @return Predicate
//     */
//    public static function loadByRef($ref)
//    {
//        return self::getEntityRepository()->loadBy(array('ref' => $ref));
//    }
//
//    /**
//     * Retourne le predicat correspondant a la reference inverse donnée.
//     *
//     * @param string $reverseRef
//     *
//     * @return Predicate
//     */
//    public static function loadByReverseRef($reverseRef)
//    {
//        return self::getEntityRepository()->loadBy(array('reverseRef' => $reverseRef));
//    }

    /**
     * Renvoi l'identifiant unique du Predicate.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Défini la référence du Predicate.
     *
     * @param string $ref
     */
    public function setRef($ref)
    {
        $this->ref = $ref;
    }

    /**
     * Renvoi la référence du Predicate.
     *
     * @throws \Core_Exception_UndefinedAttribute
     * @return string
     */
    public function getRef()
    {
        if ($this->ref === null) {
            throw new \Core_Exception_UndefinedAttribute('The predicate reference has not been defined yet.');
        }
        return $this->ref;
    }

    /**
     * Modifie le label du Predicate.
     *
     * @param string $label
     */
    public function setLabel($label)
    {
        $this->label = $label;
    }

    /**
     * Renvoi le label du Predicate.
     *
     * @throws \Core_Exception_UndefinedAttribute
     * @return string
     */
    public function getLabel()
    {
        if ($this->label === null) {
            throw new \Core_Exception_UndefinedAttribute('The predicate label has not been defined yet.');
        }
        return $this->label;
    }

    /**
     * Défini la référence inverse du Predicate.
     *
     * @param string $revRef
     */
    public function setReverseRef($revRef)
    {
        $this->reverseRef = $revRef;
    }

    /**
     * Renvoi la référence inverse du Predicate.
     *
     * @throws \Core_Exception_UndefinedAttribute
     * @return string
     */
    public function getReverseRef()
    {
        if ($this->ref === null) {
            throw new \Core_Exception_UndefinedAttribute('The predicate reverse reference has not been defined yet.');
        }
        return $this->reverseRef;
    }

    /**
     * Défini le label inverse du Predicate.
     *
     * @param string $revLabel
     */
    public function setReverseLabel($revLabel)
    {
        $this->reverseLabel = $revLabel;
    }

    /**
     * Renvoi le label inverse du Predicate.
     *
     * @throws \Core_Exception_UndefinedAttribute
     * @return string
     */
    public function getReverseLabel()
    {
        if ($this->reverseLabel === null) {
            throw new \Core_Exception_UndefinedAttribute('The predicate reverse label has not been defined yet.');
        }
        return $this->reverseLabel;
    }

    /**
     * Défini la description du Predicate.
     *
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * Renvoi la description du Predicate.
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

}
