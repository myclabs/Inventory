<?php

namespace AF\Domain\Algorithm;

use Calc_UnitValue;
use Core_Model_Entity;
use Core_Tools;
use AF\Domain\Algorithm\ConfigError;

/**
 * Algorithme.
 *
 * @author matthieu.napoli
 * @author hugo.charbonnier
 * @author yoann.croizer
 */
abstract class Algo extends Core_Model_Entity
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
     * @var AlgoSet
     */
    protected $set;

    /**
     * TODO à supprimer ?
     * @deprecated
     * @var InputSet
     */
    protected $inputSet;


    /**
     * Exécution de l'algorithme
     * @param InputSet $inputSet
     * @return Calc_UnitValue|bool|string|Output Type différent selon le type d'algo
     */
    abstract public function execute(InputSet $inputSet);

    /**
     * Méthode utilisée au niveau de AF pour vérifier la configuration des algorithmes.
     * @return ConfigError[]
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
     * @return AlgoSet|null Set contenant l'algo
     */
    public function getSet()
    {
        return $this->set;
    }

    /**
     * @param AlgoSet|null $set Set contenant l'algo
     */
    public function setSet(AlgoSet $set = null)
    {
        $this->set = $set;
    }

    /**
     * Charge un Algo par son ref
     * @param AlgoSet $algoSet
     * @param string         $ref
     * @return Algo
     */
    public static function loadByRef(AlgoSet $algoSet, $ref)
    {
        return self::getEntityRepository()->loadBy(['set' => $algoSet, 'ref' => $ref]);
    }
}
