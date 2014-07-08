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
     * @var string[]
     */
    public $members;

    /**
     * @var bool
     */
    public $relevant;

    /**
     * @var string
     */
    public $tag;

    /**
     * @var array
     */
    public $administrators = [];

    /**
     * @var boolean
     */
    public $showUsers = false;

    /**
     * @var int
     */
    public $numberUsers = 0;

    /**
     * @var boolean
     */
    public $showReports = false;

    /**
     * @var boolean
     */
    public $showExports = false;

    /**
     * @var boolean
     */
    public $showInventory = false;

    /**
     * @var boolean
     */
    public $showInventoryProgress = false;

    /**
     * @var bool
     */
    public $canEditInventory = false;

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
    public $inventoryFinishedInputsNumber = 0;

    /**
     * @var int
     */
    public $inventoryCompletion = 0;

    /**
     * @var boolean
     */
    public $showInput = false;

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
    public $inputCompletion = 0;

    /**
     * @var int
     */
    public $inputInconsistencies = 0;
}
