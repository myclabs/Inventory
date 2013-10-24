<?php

namespace Orga\ViewModel;

/**
 * Modèle d'une organisation pour les vues.
 */
class OrganizationViewModel
{
    /**
     * @var int
     */
    public $id;

    /**
     * @var string
     */
    public $label;

    /**
     * @var boolean
     */
    public $canBeEdited;

    /**
     * @var boolean
     */
    public $canBeDeleted;

    /**
     * @var string[]
     */
    public $rootAxesLabels = [];

    /**
     * @var string
     */
    public $inventory;

    /**
     * @var string
     */
    public $link;
}
