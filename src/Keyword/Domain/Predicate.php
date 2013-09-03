<?php

namespace Keyword\Domain;

use Core_Exception_UndefinedAttribute;

/**
 * Classe metier de Predicate.
 * @author valentin.claras
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


    /**
     * Constructeur de la classe Predicate.
     *
     * @param string $ref
     * @param string $reverseRef
     * @param string $label
     * @param string $reverseLabel
     */
    public function __construct($ref, $reverseRef, $label='', $reverseLabel='')
    {
        $this->setRef(is_null($ref) ? \Core_Tools::checkRef($label) : $ref);
        $this->setLabel($label);
        $this->setReverseRef(is_null($reverseRef) ? \Core_Tools::checkRef($reverseLabel) : $reverseRef);
        $this->setReverseLabel($label);
    }

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
     * @throws \Core_Exception_InvalidArgument
     */
    public function setRef($ref)
    {
        if (is_null($ref)) {
            throw new \Core_Exception_InvalidArgument("A Predicate's ref can't be empty.");
        }
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
        return $this->label;
    }

    /**
     * Défini la référence inverse du Predicate.
     *
     * @param string $revRef
     * @throws \Core_Exception_InvalidArgument
     */
    public function setReverseRef($revRef)
    {
        if (is_null($revRef)) {
            throw new \Core_Exception_InvalidArgument("A Predicate's reverse ref can't be empty.");
        }
        $this->reverseRef = $revRef;
    }

    /**
     * Renvoi la référence inverse du Predicate.
     *
     * @return string
     */
    public function getReverseRef()
    {
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
     * @return string
     */
    public function getReverseLabel()
    {
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
