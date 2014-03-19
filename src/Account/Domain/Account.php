<?php

namespace Account\Domain;

use Doctrine\ORM\EntityManager;
use MyCLabs\ACL\Model\CascadingResource;
use MyCLabs\ACL\Model\ClassResource;
use MyCLabs\ACL\Model\EntityResource;

/**
 * Compte client/d'entreprise.
 *
 * @author matthieu.napoli
 */
class Account implements EntityResource, CascadingResource
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

    /**
     * {@inheritdoc}
     */
    public function getParentResources(EntityManager $entityManager)
    {
        return [
            new ClassResource(get_class()),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getSubResources(EntityManager $entityManager)
    {
        // TODO retourner les organisations
        return [];
    }
}
