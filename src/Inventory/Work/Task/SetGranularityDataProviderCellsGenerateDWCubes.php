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
class Inventory_Work_Task_SetGranularityDataProviderCellsGenerateDWCubes extends Core_Work_Task
{

    /**
     * @var string
     */
    private $idGranularityDataProvider;

    /**
     * @var bool
     */
    private $newValue;

    /**
     * @param Inventory_Model_GranularityDataProvider $granularityDataProvider
     * @param bool $newValue
     */
    public function __construct($granularityDataProvider, $newValue)
    {
        $this->idGranularityDataProvider = $granularityDataProvider->getKey()['id'];
        $this->newValue = (bool) $newValue;
    }

    /**
     * Execute
     */
    public function execute()
    {
        Inventory_Model_GranularityDataProvider::loadByOrgaGranularity(
            array('id' => $this->idGranularityDataProvider)
        )->setCellsGenerateDWCubes($this->newValue);

        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->flush();
    }

}
