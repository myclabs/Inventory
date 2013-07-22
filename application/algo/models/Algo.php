<?php
/**
 * @author  matthieu.napoli
 * @author  hugo.charbonnier
 * @author  yoann.croizer
 * @package Algo
 */

/**
 * Classe Algo
 * @package Algo
 */
abstract class Algo_Model_Algo extends Core_Model_Entity
{

    const QUERY_SET = 'set';
    const QUERY_REF = 'ref';

    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $ref;

    /**
     * Set contenant l'algo
     * @var Algo_Model_Set
     */
    protected $set;

    /**
     * TODO à supprimer ?
     * @deprecated
     * @var Algo_Model_InputSet
     */
    protected $inputSet;


    /**
     * Exécution de l'algorithme
     * @param Algo_Model_InputSet $inputSet
     * @return Calc_UnitValue|bool|string|Algo_Model_Output Type différent selon le type d'algo
     */
    abstract public function execute(Algo_Model_InputSet $inputSet);

    /**
     * Méthode utilisée au niveau de AF pour vérifier la configuration des algorithmes.
     * @return Algo_ConfigError[]
     */
    public function checkConfig()
    {
        return [];
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getRef()
    {
        return $this->ref;
    }

    /**
     * @param string $ref
     */
    public function setRef($ref)
    {
        Core_Tools::checkRef($ref);
        $this->ref = (string) $ref;
    }

    /**
     * @return Algo_Model_Set|null Set contenant l'algo
     */
    public function getSet()
    {
        return $this->set;
    }

    /**
     * @param Algo_Model_Set|null $set Set contenant l'algo
     */
    public function setSet(Algo_Model_Set $set = null)
    {
        $this->set = $set;
    }

    /**
     * Charge un Algo par son ref
     * @param Algo_Model_Set $algoSet
     * @param string         $ref
     * @return Algo_Model_Algo
     */
    public static function loadByRef(Algo_Model_Set $algoSet, $ref)
    {
        return self::getEntityRepository()->loadBy(['set' => $algoSet, 'ref' => $ref]);
    }

    public function __clone()
    {
        // Nécessaire pour Doctrine
        if ($this->id) {
            $this->id = null;
        }
    }

}
