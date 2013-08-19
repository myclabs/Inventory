<?php
/**
 * @author     matthieu.napoli
 * @package    Core
 * @subpackage Test
 */

/**
 * Test des champs versionnés
 * @package    Core
 * @subpackage Test
 */
class Inventory_Model_Versioned extends Core_Model_Entity
{

    /**
     * @var integer
     */
    protected $id;

    /**
     * @var string
     */
    protected $name;


    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

}
