<?php

namespace AF\Model;

use AF_Model_AF;
use Core_Model_Entity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * Catalogue d'AF
 */
class AFCatalog extends Core_Model_Entity
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $label;

    /**
     * @var AF_Model_AF[]|Collection
     */
    protected $afList;

    public function __construct($label)
    {
        $this->label = $label;
        $this->afList = new ArrayCollection();
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
     * @param string $label
     */
    public function setLabel($label)
    {
        $this->label = $label;
    }

    /**
     * @return AF_Model_AF[]
     */
    public function getAfList()
    {
        return $this->afList;
    }

    public function addAF(AF_Model_AF $af)
    {
        $this->afList->add($af);
    }
}
