<?php
/**
 * @author  matthieu.napoli
 * @package Core
 */

/**
 * Représente l'appel d'une méthode d'un service
 *
 * @package Core
 */
class Orga_Work_Task_SetGranularityCellsGenerateDWCubes extends Core_Work_Task
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
        $this->idGranularity = $granularity->getKey()['id'];
        $this->newValue = (bool) $newValue;
    }

    /**
     * Execute
     */
    public function execute()
    {
        Orga_Model_Granularity::load(array('id' => $this->idGranularity))->setCellsGenerateDWCubes($this->newValue);

        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->flush();
    }

}
