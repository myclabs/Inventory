<?php
/**
 * @author     matthieu.napoli
 * @package    Core
 * @subpackage Test
 */

/**
 * Test des champs traduits
 * @package    Core
 * @subpackage Test
 */
class Inventory_Model_Translated extends Core_Model_Entity
{

    use Core_Model_Entity_Translatable;

    const QUERY_NAME = 'name';

    /**
     * @var integer
     */
    protected $id;

    /**
     * @var String
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
