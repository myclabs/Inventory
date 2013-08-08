<?php
/**
 * @author     matthieu.napoli
 * @package    Default
 * @subpackage Model
 */

/**
 * Exemple simple d'objet
 * @package    Default
 * @subpackage Model
 */
class Inventory_Model_SimpleExample extends Core_Model_Entity
{

    protected $id;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

}
