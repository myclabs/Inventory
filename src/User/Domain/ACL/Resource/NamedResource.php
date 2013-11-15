<?php

namespace User\Domain\ACL\Resource;

use Core_Model_Entity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use User\Domain\ACL\Authorization\NamedResourceAuthorization;

/**
 * Named resource: identified by a name.
 */
class NamedResource extends Core_Model_Entity implements Resource
{
    use ResourceTrait;

    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var NamedResourceAuthorization[]|Collection
     */
    protected $acl;

    public function __construct($name)
    {
        $this->name = $name;
        $this->acl = new ArrayCollection();
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
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return self
     */
    public static function loadByName($name)
    {
        return self::getEntityRepository()->loadBy(['name' => $name]);
    }
}
