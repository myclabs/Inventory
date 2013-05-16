<?php
/**
 * @author guillaume.querat
 * @package Techno
 */

/**
 * Classe Source
 * @package Techno
 * @subpackage Relation
 */

class Techno_Model_Relation_Source extends Core_Model_Entity
{
    // Constantes
    const ASSOCIATION_ADDED = "added";
    const ASSOCIATION_DELETED = "delete";
    const ASSOCIATION_LOADED = "load";

    // Attributs
    /**
     * Identifiant de la source
     * @var int
     */
    protected $_id;

    /**
     * Type de la source
     * @var byte $_type
     */
    protected $_type;

    /**
     * id de l'upstream associé au process
     * @var int $_idUpstreamProcess
     */
    protected $_idUpstreamProcess;

    /**
     * id du downstream associé au process
     * @var int $_iDownstreamProcess
     */
    protected $_idDownstreamProcess;

    /**
     * id des relations dérivées associées
     * @var array(Techno_Model_Relation_Derived) $_derivedRelations
     */
    protected $_idDerived;

    /**
     * Label de la Source
     * @var string
     */
    protected $_label;

    /**
     * Array de SourceCoeffs
     * @var const[] $_sourceCoeffs
     */
    protected $_sourceCoeffs = null;

    /**
     * Tableau de relations entre components/source
     * @var Source[]
     */
    protected $_stateSourceCoeffs = null;

    /**
     * Mapper de la classe
     * @var string $_mapperNom
     */
    protected  static $_mapperNom = "Techno_Model_Relation_Mapper_Source";

    /**
     * (non-PHPdoc)
     * @see Core_Model_Entity::delete()
     */
    public function preRemove()
    {
        if ($this->_sourceCoeffs === null) {
            self::getMapper()->loadSourceCoeff($this);
        }
        parent::delete();
    }

    /**
     * Function setType($type)
     *  Affecte le type de la source
     *  @param byte $type
     */
    public function setType($type) {
        $this->_type = $type;
    }

    /**
     * Renvoie le type de la Source
     * @return byte
     */
    public function getType() {

        if ($this->_type === null) {
            throw new Core_Exception_UndefinedAttribute(
                    'Le type n\'est pas défini.'
            );
        }
        return $this->_type;
    }

    /**
     * Function setUpstreamProcess(Techno_Model_Element_Process $upstreamProcess)
     *  Affecte le Process Amont
     *  @param Techno_Model_Element_Process $upstreamProcess
     */
    public function setUpstreamProcess(Techno_Model_Element_Process $upstreamProcess) {
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
     * Function setDownstreamProcess(Techno_Model_Element_Process $downstreamProcess)
     *  Affecte le process Aval
     *  @param Techno_Model_Element_Process $downstreamProcess
     */
    public function setDownstreamProcess(Techno_Model_Element_Process $downstreamProcess) {
        $this->_idDownstreamProcess = $downstreamProcess->getKey();
    }

    /**
     * Function getDownstreamProcess()
     *  Renvoie le Process Aval
     *  @return Techno_Model_Element_Process
     */
    public function getDownstreamProcess() {
        return Techno_Model_Element_Process::load($this->_idDownstreamProcess);
    }

    /**
     * Renvoie les relations dérivées
     * @return array(Techno_Model_Relation_Derived)
     */
    public function getDerived() {
        $derived = array();
        foreach ($this->_idDerived as $derived) {
            $derived[] = Techno_Model_Relation_Derived::load($derived);
        }
        return $derived;
    }

    /**
     * Function setLabel($label)
     * Affecte le label de la source
     * @param string $label
     */
    public function setLabel($label) {
        $this->_label = $label;
    }

    /**
     * Function getLabel()
     * Retourne le label de la source
     * @return string
     */
    public function getLabel() {
        return $this->_label;
    }

    /**
     * Function addDerived($derived)
     *  Affecte une relation dérivée à la source
     *  @param Techno_Model_Relation_Derived $derived
     */
    public function addDerived($derived) {
        $this->_derivedRelations[] = $derived->getKey();
    }

    /**
     * Ajoute un coefficient à la relation source
     * @param Techno_Model_Component $coeff
     * @param int $exponent
     */
    public function addCoeff(Techno_Model_Component $coeff, $exponent) {
        if ($this->_sourceCoeffs === null) {
            // Chargement des SourceCoeffs et Components
            self::getMapper()->loadSourceCoeff($this);
        }
        // S'il est présent dans le tableau
        if (array_key_exists($coeff->getKey(), $this->_sourceCoeffs)
            && $this->_sourceCoeffs[$coeff->getKey()] == self::ASSOCIATION_DELETED
        ) {
            $this->_stateSourceCoeffs[$coeff->getKey()] = self::LOADED;
        }
        else if (array_key_exists($coeff->getKey(), $this->_sourceCoeffs)) {
            throw new Core_Exception_Duplicate('Impossible to add a coeff in a source relation twice');
        }
        else if (!(array_key_exists($coeff->getKey(), $this->_sourceCoeffs))) {// S'il n'est pas présent
            if ($coeff  instanceof Techno_Model_Element) {
                $type = Techno_Model_Relation_SourceCoeff::TYPE_ELEMENT;
            }
            else {
                $type = Techno_Model_Relation_SourceCoeff::TYPE_FAMILY;
            }
            $this->_sourceCoeffs[$coeff->getKey()]
                    = new Techno_Model_Relation_SourceCoeff($exponent, $coeff->getKey(), $type);
            $this->_stateSourceCoeffs[$coeff->getKey()] = self::ASSOCIATION_ADDED;
        }
    }

    /**
     * Teste l'existence d'un coefficient dans la liste des source coeff
     * @param Techno_Model_Component $coeff
     */
    public function hasCoeff(Techno_Model_Component $coeff) {
        if ($this->_sourceCoeffs === null) {
            // Chargement des SourceCoeffs et Components
            self::getMapper()->loadSourceCoeff($this);
        }
        if (array_key_exists($coeff->getKey(), $this->_sourceCoeffs)
                && $this->_stateSourceCoeffs[$coeff->getKey()] !== self::ASSOCIATION_DELETED
        ) {
            return true;
        }
        else {
            return false;
        }
    }

    /**
     * Supprime un coeff de la relation source
     * @param Techno_Model_Component $coeff
     */
    public function removeCoeff(Techno_Model_Component $coeff) {
        if ($this->_sourceCoeffs === null) {
            // Chargement des SourceCoeffs et Components
            self::getMapper()->loadSourceCoeff($this);
        }
        // Si le component vient d'etre ajouté, on le supprime du tableau
        if (array_key_exists($coeff->getKey(), $this->_sourceCoeffs)
            && $this->_stateSourceCoeffs[$coeff->getKey()] === self::ASSOCIATION_ADDED
        ) {
            unset($this->_sourceCoeffs[$coeff->getKey()]);
            unset($this->_stateSourceCoeffs[$coeff->getKey()]);
        }
        // Sinon on le marque en statut "A supprimer"
        else if (array_key_exists($coeff->getKey(), $this->_sourceCoeffs)) {
            $this->_stateSourceCoeffs[$coeff->getKey()] = self::ASSOCIATION_DELETED;
        }
    }

    /**
     * Récupère les source coeffs
     * @return Techno_Model_Relation_SourceCoeff[]
     */
    public function getSourceCoeffs() {
        if ($this->_sourceCoeffs === null) {
            self::getMapper()->loadIdComponent($this);
        }
        $sourceCoeffs = array();
        foreach ($this->_sourceCoeffs as $idCoeff => $sourceCoeff) {
            if ($this->_stateSourceCoeffs[$idCoeff] !== self::ASSOCIATION_DELETED) {
                $sourceCoeffs[] = $sourceCoeff;
            }
        }
        return $sourceCoeffs;
    }




}
