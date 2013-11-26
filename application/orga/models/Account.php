<?php

namespace Orga\Model;

use Core_Model_Entity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Orga\Model\ACL\AccountResourceTrait;
use Orga_Model_Organization;
use User\Domain\ACL\Resource\Resource;

/**
 * Compte client.
 */
class Account extends Core_Model_Entity implements Resource
{
    use AccountResourceTrait;

    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $label;

    /**
     * @var Orga_Model_Organization[]|Collection
     */
    protected $organizations;

    public function __construct($label)
    {
        $this->label = $label;
        $this->organizations = new ArrayCollection();
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
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }
}
