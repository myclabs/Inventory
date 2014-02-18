<?php

namespace Classification\Domain;

use Core_Exception_InvalidArgument;
use Core_Exception_NotFound;
use Core_Exception_TooMany;
use Core_Model_Entity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine;
use Doctrine\ORM\NoResultException;

/**
 * Indicateur de classification contextualisé.
 *
 * @author valentin.claras
 * @author cyril.perraud
 */
class ContextIndicator extends Core_Model_Entity
{
    // Constantes de tris et de filtres.
    const QUERY_CONTEXT = 'context';
    const QUERY_INDICATOR = 'indicator';

    /**
     * Contexte de l'indicateur.
     *
     * @var Context
     */
    protected $context;

    /**
     * Indicateur.
     *
     * @var Indicator
     */
    protected $indicator;

    /**
     * Collection d'axes regroupé dans l'indicateur contextualisé.
     *
     * @var Collection|IndicatorAxis[]
     */
    protected $axes;

    public function __construct()
    {
        $this->axes = new ArrayCollection();
    }

    /**
     * @param Context $context
     */
    public function setContext($context)
    {
        if ($this->context !== null) {
            throw new Core_Exception_TooMany('The Context has already been defined');
        }
        $this->context = $context;
    }

    /**
     * @return Context
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * @param Indicator $indicator
     */
    public function setIndicator($indicator)
    {
        if ($this->indicator !== null) {
            throw new Core_Exception_TooMany('The Indicator has already been defined');
        }
        $this->indicator = $indicator;
    }

    /**
     * @return Indicator
     */
    public function getIndicator()
    {
        return $this->indicator;
    }

    /**
     * @param IndicatorAxis $axis
     * @throws Core_Exception_InvalidArgument
     */
    public function addAxis(IndicatorAxis $axis)
    {
        if (!($this->hasAxis($axis))) {
            foreach ($this->getAxes() as $existentAxis) {
                if ($existentAxis->isBroaderThan($axis) || $existentAxis->isNarrowerThan($axis)) {
                    throw new Core_Exception_InvalidArgument('Axes must be transverse');
                }
            }

            $this->axes->add($axis);
        }
    }

    /**
     * @param IndicatorAxis $axis
     * @return boolean
     */
    public function hasAxis(IndicatorAxis $axis)
    {
        return $this->axes->contains($axis);
    }

    /**
     * @param IndicatorAxis $axis
     */
    public function removeAxis($axis)
    {
        if ($this->hasAxis($axis)) {
            $this->axes->removeElement($axis);
        }
    }

    /**
     * @return bool Est-ce que l'indicateur contextualisé possède des axes ?
     */
    public function hasAxes()
    {
        return !$this->axes->isEmpty();
    }

    /**
     * @return IndicatorAxis[]
     */
    public function getAxes()
    {
        return $this->axes->toArray();
    }

    /**
     * Charge un indicateur contextualisé par les refs.
     *
     * @param string $refContext
     * @param string $refIndicator
     *
     * @throws Core_Exception_NotFound
     * @return ContextIndicator
     */
    public static function loadByRef($refContext, $refIndicator)
    {
        $query = self::getEntityManager()->createQuery(
            "SELECT ci FROM Classification\\Domain\\ContextIndicator ci
            LEFT JOIN ci.context c
            LEFT JOIN ci.indicator i
            WHERE c.ref = ?1 AND i.ref = ?2"
        );
        $query->setParameters([1 => $refContext, 2 => $refIndicator]);

        try {
            return $query->getSingleResult();
        } catch (NoResultException $e) {
            throw new Core_Exception_NotFound(
                "ContextIndicator not found matching context=$refContext and indicator=$refIndicator"
            );
        }
    }
}
