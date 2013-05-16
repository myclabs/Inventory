<?php
/**
 * @author guillaume.querat
 * @package Techno
 */

/**
 * Classe DerivedCoeff
 * @package Techno
 * @subpackage Relation
 */

class Techno_Model_Relation_DerivedCoeff
{
    /**
     * id du coefficient
     * @var int $_idCoeff
     */
    protected $_idCoeff;

    /**
     * id de la dérivée
     * @var int $_idDerived
     */
    protected $_idDerived;

    /**
     * exposant de la relation
     * @var int $_exponent
     */
    protected $_exponent;

    /**
     * Mapper de la classe
     * @var string $_mapperNom
     */
    protected  static $_mapperNom = "Techno_Model_Mapper_Relation_DerivedCoeff";

    /**
     * Construction d'un sourceCoeff
     * @param int $exponent
     * @param Techno_Model_Element_Coeff $coeff
     */
    public function __construct ($exponent, $coeff)
    {
        // Affectation des variables
        $this->_exponent = $exponent;
        $this->_idCoeff = $coeff->getKey();
    }

    /**
     * Function setCoeff($coeff)
     *  Affecte le coefficient
     *  @param Techno_Model_Element_Coeff $coeff
     */
    public function setCoeff($coeff) {
        $this->_idCoeff = $idCoeff->getKey();
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
