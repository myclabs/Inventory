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
    public $inventoryStatus = null;

    /**
     * @var string
     */
    public $inventoryStatusTitle = '';

    /**
     * @var int
     */
    public $inventoryNotStartedInputsNumber = 0;

    /**
     * @var int
     */
    public $inventoryStartedInputsNumber = 0;

    /**
     * @var int
     */
    public $inventoryCompletedInputsNumber = 0;

    /**
     * @var int
     */
    public $inventoryCompletion = 0;

    /**
     * @var boolean
     */
    public $canBeInputted = false;

    /**
     * @var string
     */
    public $inputStatus = null;

    /**
     * @var string
     */
    public $inputStatusTitle = '';

    /**
     * @var int
     */
    public $inputStatusStyle = '';

    /**
     * @var int
     */
    public $inputCompletion = 0;

    /**
     * @var boolean
     */
    public $canBeAnalyzed = false;

    /**
     * @var array
     */
    public $administrators = [];
}
