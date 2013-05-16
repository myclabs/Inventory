<?php
/**
 * @author     matthieu.napoli
 * @package    User
 * @subpackage Model
 */

/**
 * Ressource nommée
 * @package    User
 * @subpackage Model
 */
class User_Model_Resource_Named extends User_Model_Resource
{

    /**
     * @var string
     */
    protected $name;


    /**
     * Retourne la ressource à partir de son nom
     *
     * @param string $name
     *
     * @return User_Model_Resource_Named
     */
    public static function loadByName($name)
    {
        $query = new Core_Model_Query();
        $query->filter->addCondition('name', $name);
        $list = self::loadList($query);
        if (count($list) == 0) {
            return null;
        }
        return current($list);
    }

    /**
     * @param string $name
     */
    public function __construct($name = null)
    {
        parent::__construct();
        if ($name) {
            $this->setName($name);
        }
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = (string) $name;
    }

}
