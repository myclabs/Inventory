<?php

namespace Account\Domain;

use MyCLabs\ACL\Model\EntityResourceInterface;

/**
 * Compte client/d'entreprise.
 *
 * @author matthieu.napoli
 */
class Account implements EntityResourceInterface
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $name;

    /**
     * @param string $name Nom du compte.
     */
    public function __construct($name)
    {
        $this->name = (string) $name;
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
     */
    public function rename($name)
    {
        $this->name = (string) $name;
    }

    public function __toString()
    {
        return $this->name;
    }
}
