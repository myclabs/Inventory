<?php

namespace Account\Domain;

use AF\Domain\AFLibrary;
use Doctrine\ORM\EntityManager;
use MyCLabs\ACL\Model\CascadingResource;
use MyCLabs\ACL\Model\ClassResource;
use MyCLabs\ACL\Model\EntityResource;
use Parameter\Domain\ParameterLibrary;

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
        $query = new \Core_Model_Query();
        $query->filter->addCondition('account', $this);

        return array_merge(
            \Orga_Model_Organization::loadList($query),
            AFLibrary::loadList($query),
            ParameterLibrary::loadList($query)
        );
    }
}
