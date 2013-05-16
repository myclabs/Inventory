<?php
/**
 * @author guillaume.querat
 * @package Techno
 */

/**
 * Classe SourceCoeff
 * @package Techno
 * @subpackage Relation
 */

class Techno_Model_Relation_SourceCoeff
{
    const TYPE_ELEMENT = 'element';
    const TYPE_FAMILY = 'family';

    /**
     * id du coefficient
     * @var int $_idCoeff
     */
    protected $_idCoeff;

    /**
     * id de la Source
     * @var int $_idSource
     */
    protected $_idSource;

    /**
     * exposant de la relation
     * @var int $_exponent
     */
    protected $_exponent;

    /**
     * Construction d'un sourceCoeff
     * @param int $exponent
     * @param int $idCoeff
     */
    public function __construct ($exponent, $idCoeff)
    {
        // Affectation des variables
        $this->_exponent = $exponent;
        $this->_idCoeff = $idCoeff;
    }

    /**
     * Function setCoeff($coeff)
     *  Affecte le coefficient
     *  @param Techno_Model_Element_Coeff $coeff
     */
    public function setCoeff($coeff) {
        $this->_idCoeff = $coeff->getKey();
    }

    /**
     * Function getCoeff()
     *  Renvoie le coefficient
     *  @return Techno_Model_Element_Coeff
     */
    public function getCoeff() {
        return Techno_Model_Element_Coeff::load($this->_idCoeff);
    }

    /**
     * Function setExponent($exponent)
     *  Affecte l'exposant
     *  @param int $exponent
     */
    public function setExponent($exponent) {
        $this->_exponent = $exponent;
    }

    /**
     * Function getExponent()
     *  Renvoie l'exposant
     *  @return int
     */
    public function getExponent() {
        return $this->_exponent;
    }
}
