<?php

namespace Orga\ViewModel;

/**
 * Modèle d'une cellule pour les vues.
 */
class CellViewModel
{
    const AF_STATUS_INVENTORY_NOT_STARTED = 'statusInventoryNotStarted';
    const AF_STATUS_AF_NOT_CONFIGURED = 'statusAFNotConfigured';
    const AF_STATUS_NOT_STARTED = 'statusNotStarted';

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
     * @var string
     */
    public $inventoryStatusStyle = '';

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
    public $showInput = false;

    /**
     * @var boolean
     */
    public $showInputLink = false;

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
}
