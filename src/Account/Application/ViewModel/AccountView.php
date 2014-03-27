<?php

namespace Account\Application\ViewModel;

/**
 * Représentation simplifiée de la vue d'un compte pour un utilisateur.
 *
 * @author matthieu.napoli
 */
class AccountView
{
    /**
     * @var int
     */
    public $id;

    /**
     * @var string
     */
    public $name;

    /**
     * @var OrganizationView[]
     */
    public $organizations = [];

    /**
     * @var AFLibraryView[]
     */
    public $afLibraries = [];

    /**
     * @var AFLibraryView[]
     */
    public $parameterLibraries = [];

    /**
     * @var bool
     */
    public $canEdit = false;

    /**
     * @var bool
     */
    public $canAllow = false;

    /**
     * @param int    $id
     * @param string $name
     */
    public function __construct($id, $name)
    {
        $this->id = $id;
        $this->name = $name;
    }
}
