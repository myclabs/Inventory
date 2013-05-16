<?php
/**
 * @author guillaume.querat
 * @package Techno
 */

/**
 * Classe Derived
 * @package Techno
 * @subpackage Relation
 */

class Techno_Model_Relation_Derived extends Core_Model_Entity
{
    const ASSOCIATION_ADDED = "added";
    const ASSOCIATION_LOADED = "loaded";
    const ASSOCIATION_DELETED = "deleted";

    /**
     * Identifiant de la dérivée
     * @var int $_id
     */
    protected $_id;

    /**
     * Relation source
     * @var int $_idSource
     */
    protected $_idSource;

    /**
     * Id du process aval
     * @var int $_idUpstreamProcess
     */
    protected $_idUpstreamProcess;

    /**
     * Id du process amont
     * @var int $_idDownstreamProcess
     */
    protected $_idDownstreamProcess;

    /**
     * Coefficient dérivés
     * @var array(int) $_derivedCoeffs
     */
    protected $_derivedCoeffs;

    /**
     * Tableau de relations entre coeff/derived
     * @var Techno_Model_Relation_DerivedCoeff[]
     */
    protected $_stateDerivedCoeffs;

    /**
     * Mapper de la classe
     * @var string $_mapperNom
     */
    protected  static $_mapperNom = "Techno_Model_Relation_Mapper_Derived";

    /**
     * Construction d'une source
     */
    public function __construct ()
    {
        // Constructeur vide
    }

    /**
     * Function setSource($source)
     *  Affecte la Source
     *  @param Techno_Model_Relation_Source $source
     */
    public function setSource($source) {
        $this->_idSource = $source->getKey();
    }

    /**
     * Function getSource()
     *  Renvoie la source de la relation
     *  return Techno_Model_Relation_Source
     */
    public function getSource() {
        return Techno_Model_Relation_Source::load($this->_idSource);
    }

    /**
     * Function setUpstreamProcess($upstreamProcess)
     *  Affecte le process Amont
     *  @param Techno_Model_Element_Process $upstreamProcess
     */
    public function setUpstreamProcess($upstreamProcess) {
        $this->_idUpstreamProcess = $upstreamProcess->getKey();
    }

    /**
     * Function getUpstreamProcess()
     *  Renvoie le Process Amont
     *  @return Techno_Model_Element_Process
     */
    public function getUpstreamProcess() {
        return Techno_Model_Element_Process::load($this->_idUpstreamProcess);
    }

    /**
     * Function setDownstreamProcess($downstreamProcess)
     *  Affecte le process Aval
     *  @param Techno_Model_Element_Process $downstreamProcess
     */
    public function setDownstreamProcess($downstreamProcess) {
        $this->_idDownstreamProcess = $downstreamProcess->getKey();
    }

    /**
     * Function getDownstreamProcess()
     *  Renvoie le process Aval
     *  @return Techno_Model_Element_Process
     */
    public function getDownstreamProcess() {
        return Techno_Model_Element_Process::load($this->_idDownstreamProcess);
    }

    /**
     * Function addCoeff(Techno_Model_Element_Coeff $coeff, $exponent)
     *  Ajoute un coefficient à la relation dérivé
     *  @param Techno_Model_Element_Coeff $coeff
     *  @param int $exponent
     */
    public function addCoeff(Techno_Model_Element_Coeff $coeff, $exponent) {
        if ($this->_derivedCoeffs === null) {
            // Chargement des DerivedCoeffs et Coeff
            self::getMapper()->loadDerivedCoeff($this);
        }
        // S'il est présent dans le tableau
        if (array_key_exists($coeff->getKey(), $this->_derivedCoeffs)
                && $this->_derivedCoeffs[$coeff->getKey()] == self::ASSOCIATION_DELETED
        ) {
            $this->_stateDerivedCoeffs[$coeff->getKey()] = self::ASSOCIATION_LOADED;
        }
        else if (array_key_exists($coeff->getKey(), $this->_derivedCoeffs)) {
            throw new Core_Exception_Duplicate('Impossible to add a coeff in a derived relation twice');
        }
        else if (!(array_key_exists($coeff->getKey(), $this->_derivedCoeffs))) {// S'il n'est pas présent
            $this->_derivedCoeffs[$coeff->getKey()] = new Techno_Model_Relation_DerivedCoeff($exponent, $coeff);
            $this->_stateDerivedCoeffs[$coeff->getKey()] = self::ASSOCIATION_ADDED;
        }
    }

    /**
     * Function hasCoeff(Techno_Model_Element_Coeff $coeff)
     *  Teste l'existence d'un coefficient dans la liste des derived coeff
     *  @param Techno_Model_Component $coeff
     */
    public function hasCoeff(Techno_Model_Element_Coeff $coeff) {
        if ($this->_derivedCoeffs === null) {
            // Chargement des DerivedCoeffs et Components
            self::getMapper()->loadDerivedCoeff($this);
        }
        if (array_key_exists($coeff->getKey(), $this->_derivedCoeffs)
                && $this->_stateDerivedCoeffs[$coeff->getKey()] !== self::ASSOCIATION_DELETED
        ) {
            return true;
        }
        else {
            return false;
        }
    }

    /**
     * Function removeCoeff(Techno_Model_Element_Coeff $coeff)
     *  Supprime un coeff de la relation dérivé
     *  @param Techno_Model_Component $coeff
     */
    public function removeCoeff(Techno_Model_Element_Coeff $coeff) {
        if ($this->_derivedCoeffs === null) {
            // Chargement des SourceCoeffs et Components
            self::getMapper()->loadDerivedCoeff($this);
        }
        // Si le component vient d'etre ajouté, on le supprime du tableau
        if (array_key_exists($coeff->getKey(), $this->_derivedCoeffs)
                && $this->_stateDerivedCoeffs[$coeff->getKey()] === self::ASSOCIATION_ADDED
        ) {
            unset($this->_derivedCoeffs[$coeff->getKey()]);
            unset($this->_stateDerivedCoeffs[$coeff->getKey()]);
        }
        // Sinon on le marque en statut "A supprimer"
        else if (array_key_exists($coeff->getKey(), $this->_derivedCoeffs)) {
            $this->_stateDerivedCoeffs[$coeff->getKey()] = self::ASSOCIATION_DELETED;
        }
    }
}
