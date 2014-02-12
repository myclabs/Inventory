<?php

namespace Classification\Domain;

use Core_Model_Entity;
use Core_Model_Entity_Translatable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * Bibliothèque d'indicateurs.
 *
 * @author matthieu.napoli
 */
class IndicatorLibrary extends Core_Model_Entity
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
     * @param string $label
     */
    public function __construct($label)
    {
        $this->label = $label;
        $this->indicators = new ArrayCollection();
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
}
