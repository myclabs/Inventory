<?php

namespace Orga\ViewModel;

/**
 * Modèle d'une cellule pour les vues.
 */
class CellViewModel
{
    /**
     * @var int
     */
    public $id;

    /**
     * @var string
     */
    public $shortLabel;

    /**
     * @var string
     */
    public $extendedLabel;

    /**
     * @var string
     */
    public $path;

    /**
     * @var string
     */
    public $inventoryStatus;

    /**
     * @var boolean
     */
    public $canBeInputted;

    /**
     * @var string
     */
    public $inputStatus;

    /**
     * @var int
     */
    public $inputCompletion;

    /**
     * @var boolean
     */
    public $canBeAnalyzed;

    /**
     * @var int
     */
    public $dWReports;

    /**
     * @var array
     */
    public $administrators = [];
}
