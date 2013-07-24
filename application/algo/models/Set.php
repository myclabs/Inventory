<?php
/**
 * @author  matthieu.napoli
 * @author  thibaud.rolland
 * @author  yoann.croizer
 * @package Algo
 */

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\PersistentCollection;

/**
 * Classe objet métier Algo_Model_Set
 * @package Algo
 */
class Algo_Model_Set extends Core_Model_Entity
{

    /**
     * @var int
     */
    protected $id;

    /**
     * @var PersistentCollection|Collection|Algo_Model_Algo[]
     */
    protected $algos;


    /**
     * Constructeur
     */
    public function __construct()
    {
        $this->algos = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get algos
     * @return Algo_Model_Algo[]
     */
    public function getAlgos()
    {
        return $this->algos;
    }

    /**
     * @param Algo_Model_Algo $algo
     */
    public function addAlgo(Algo_Model_Algo $algo)
    {
        $this->algos->add($algo);
    }

    /**
     * @param Algo_Model_Algo $algo
     */
    public function removeAlgo(Algo_Model_Algo $algo)
    {
        if ($this->hasAlgo($algo)) {
            $this->algos->remove($algo);
        }
    }

    /**
     * @param Algo_Model_Algo $algo
     * @return bool
     */
    public function hasAlgo(Algo_Model_Algo $algo)
    {
        return $this->algos->contains($algo);
    }

    /**
     * Retourne un algo du Set par son ref
     * @param  string $ref
     * @throws Core_Exception_NotFound
     * @return Algo_Model_Algo
     */
    public function getAlgoByRef($ref)
    {
        $criteria = Criteria::create();
        $criteria->where(Criteria::expr()->eq('ref', $ref));
        /** @var $algos Collection */
        $algos = $this->algos->matching($criteria);
        if (count($algos) >= 1) {
            return $algos->first();
        }
        throw new Core_Exception_NotFound("No algo was found with ref '$ref' in this set");
    }

}
