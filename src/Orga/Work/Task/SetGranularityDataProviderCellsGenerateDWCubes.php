<?php
/**
 * @author  matthieu.napoli
 * @package Core
 */
use Core\Work\Task;

/**
 * Représente l'appel d'une méthode d'un service
 *
 * @package Core
 */
class Orga_Work_Task_SetGranularityCellsGenerateDWCubes extends Task
{

    /**
     * @var string
     */
    private $idGranularity;

    /**
     * @var bool
     */
    private $newValue;

    /**
     * @param Orga_Model_Granularity $granularity
     * @param bool $newValue
     */
    public function __construct($granularity, $newValue)
    {
        $this->idGranularity = $granularity->getId();
        $this->newValue = (bool) $newValue;
    }

    /**
     * Execute
     */
    public function execute()
    {
        $granularity = Orga_Model_Granularity::load($this->idGranularity);
        $granularity->setCellsGenerateDWCubes($this->newValue);
        $granularity->save();

        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->flush();
    }

}
