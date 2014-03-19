<?php

namespace Account\Application\ViewModel;

/**
 * Représentation simplifiée de la vue d'une librairie d'AF pour un utilisateur.
 *
 * @author matthieu.napoli
 */
class AFLibraryView
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
     * @param int    $id
     * @param string $label
     */
    public function __construct($id, $label)
    {
        $this->id = $id;
        $this->label = $label;
    }
}
