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
     * @var string
     */
    public $inventoryStatusTitle;

    /**
     * @var int
     */
    public $inventoryNotStartedInputsNumber;

    /**
     * @var int
     */
    public $inventoryStartedInputsNumber;

    /**
     * @var int
     */
    public $inventoryCompletedInputsNumber;

    /**
     * @var int
     */
    public $inventoryCompletion;

    /**
     * @var boolean
     */
    public $canBeInputted;

    /**
     * @var string
     */
    public $inputStatus;

    /**
     * @var string
     */
    public $inputStatusTitle;

    /**
     * @var int
     */
    public $inputStatusStyle;

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
