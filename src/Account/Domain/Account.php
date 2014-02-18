<?php

namespace Account\Domain;

/**
 * Compte client/d'entreprise.
 *
 * @author matthieu.napoli
 */
class Account
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
}
