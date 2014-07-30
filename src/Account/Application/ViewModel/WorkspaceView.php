<?php

namespace Account\Application\ViewModel;

/**
 * Représentation simplifiée de la vue d'un workspace pour un utilisateur.
 *
 * @author matthieu.napoli
 */
class WorkspaceView
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
}
