<?php

namespace User\Domain\ACL\Resource;

use Core_Model_Query;
use User\Domain\ACL\Resource;

/**
 * Ressource nommée.
 *
 * @author matthieu.napoli
 */
class NamedResource extends Resource
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
     * @return NamedResource
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
