<?php

namespace Account\Application\ViewModel;

/**
 * Représentation simplifiée de la vue d'une librairie de paramètres pour un utilisateur.
 *
 * @author matthieu.napoli
 */
class ParameterLibraryView
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
     * @var bool
     */
    public $canDelete = false;

    /**
     * @param int    $id
     * @param string $label
     */
    public function __construct($id, $label)
    {
        $this->id = $id;
        $this->label = $label;
    }
}
