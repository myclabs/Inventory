<?php

namespace AF\Domain\Algorithm;

use AF\Domain\Algorithm\Condition\ElementaryConditionAlgo;
use Core_Exception_NotFound;
use Core_Model_Entity;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\PersistentCollection;

/**
 * @author matthieu.napoli
 * @author thibaud.rolland
 * @author yoann.croizer
 */
class AlgoSet extends Core_Model_Entity
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var PersistentCollection|Collection|Algo[]
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
     * @return Algo[]
     */
    public function getAlgos()
    {
        return $this->algos;
    }

    /**
     * @param Algo $algo
     */
    public function addAlgo(Algo $algo)
    {
        $this->algos->add($algo);
    }

    /**
     * @param Algo $algo
     */
    public function removeAlgo(Algo $algo)
    {
        if ($this->hasAlgo($algo)) {
            $this->algos->remove($algo);
        }
    }

    /**
     * @param Algo $algo
     * @return bool
     */
    public function hasAlgo(Algo $algo)
    {
        return $this->algos->contains($algo);
    }

    /**
     * Retourne un algo du Set par son ref
     * @param  string $ref
     * @throws Core_Exception_NotFound
     * @return Algo
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

    /**
     * Vérifie si un inputRef a une condition portant sur un input
     *
     * @param string $inputRef Reférence de l'input
     * @return bool
     */
    public function hasConditionOnInputRef($inputRef)
    {
        return $this->algos->exists(function ($key, $element) use ($inputRef) {
            return $element instanceof ElementaryConditionAlgo
                && $element->getInputRef() == $inputRef;
        });
    }
}
