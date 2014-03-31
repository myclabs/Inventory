<?php

namespace Classification\Domain;

use Core_Exception_InvalidArgument;
use Core_Exception_NotFound;
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
     * @var int
     */
    protected $id;

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
     * @var Collection|Axis[]
     */
    protected $axes;

    /**
     * @var ClassificationLibrary
     */
    protected $library;

    public function __construct(ClassificationLibrary $library, Context $context, Indicator $indicator)
    {
        $this->library = $library;
        $this->context = $context;
        $this->indicator = $indicator;

        $this->axes = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return Context
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * @return Indicator
     */
    public function getIndicator()
    {
        return $this->indicator;
    }

    /**
     * @param Axis $axis
     * @throws Core_Exception_InvalidArgument
     */
    public function addAxis(Axis $axis)
    {
        if (!($this->hasAxis($axis))) {
            foreach ($this->getAxes() as $existentAxis) {
                if ($existentAxis->isBroaderThan($axis) || $existentAxis->isNarrowerThan($axis)) {
                    throw new Core_Exception_InvalidArgument('Axis must be transverse');
                }
            }

            $this->axes->add($axis);
        }
    }

    /**
     * @param Axis $axis
     * @return boolean
     */
    public function hasAxis(Axis $axis)
    {
        return $this->axes->contains($axis);
    }

    /**
     * @param Axis $axis
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
     * @return Axis[]
     */
    public function getAxes()
    {
        return $this->axes->toArray();
    }

    /**
     * @return ClassificationLibrary
     */
    public function getLibrary()
    {
        return $this->library;
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
