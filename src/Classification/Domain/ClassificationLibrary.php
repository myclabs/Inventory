<?php

namespace Classification\Domain;

use Core_Model_Entity;
use Core_Model_Entity_Translatable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * Bibliothèque de classification.
 *
 * @author matthieu.napoli
 */
class ClassificationLibrary extends Core_Model_Entity
{
    use Core_Model_Entity_Translatable;

    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $label;

    /**
     * @var Indicator[]|Collection
     */
    protected $indicators;

    /**
     * @var Axis[]|Collection
     */
    protected $axes;

    /**
     * @var Context[]|Collection
     */
    protected $contexts;

    /**
     * @var Context[]|Collection
     */
    protected $contextIndicators;

    /**
     * @param string $label
     */
    public function __construct($label)
    {
        $this->label = $label;

        $this->indicators = new ArrayCollection();
        $this->axes = new ArrayCollection();
        $this->contexts = new ArrayCollection();
        $this->contextIndicators = new ArrayCollection();
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
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @return Indicator[]
     */
    public function getIndicators()
    {
        return $this->indicators->toArray();
    }

    public function addIndicator(Indicator $indicator)
    {
        $this->indicators->add($indicator);
    }

    public function removeIndicator(Indicator $indicator)
    {
        $this->indicators->removeElement($indicator);
    }

    /**
     * @return Axis[]
     */
    public function getAxes()
    {
        return $this->axes->toArray();
    }

    public function addAxis(Axis $axis)
    {
        $this->axes[] = $axis;
    }

    public function removeAxis(Axis $axis)
    {
        $this->axes->removeElement($axis);
    }

    /**
     * @return Context[]
     */
    public function getContexts()
    {
        return $this->contexts->toArray();
    }

    public function addContext(Context $context)
    {
        $this->contexts[] = $context;
    }

    public function removeContext(Context $context)
    {
        $this->contexts->removeElement($context);
    }

    /**
     * @return ContextIndicator[]
     */
    public function getContextIndicators()
    {
        return $this->contextIndicators->toArray();
    }

    public function addContextIndicator(ContextIndicator $contextIndicator)
    {
        $this->contextIndicators[] = $contextIndicator;
    }

    public function removeContextIndicator(ContextIndicator $contextIndicator)
    {
        $this->contextIndicators->removeElement($contextIndicator);
    }
}
