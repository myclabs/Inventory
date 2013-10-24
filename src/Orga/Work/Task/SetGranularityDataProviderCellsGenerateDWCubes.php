<?php

use Core\Work\BaseTaskInterface;
use Core\Work\BaseTaskTrait;
use MyCLabs\Work\Task\Task;

class Orga_Work_Task_SetGranularityCellsGenerateDWCubes implements Task, BaseTaskInterface
{
    use BaseTaskTrait;

    /**
     * @var string
     */
    public $idGranularity;

    /**
     * @var bool
     */
    public $newValue;

    /**
     * @param Orga_Model_Granularity $granularity
     * @param bool $newValue
     */
    public function __construct($granularity, $newValue)
    {
        $this->idGranularity = $granularity->getId();
        $this->newValue = (bool) $newValue;
    }
}
