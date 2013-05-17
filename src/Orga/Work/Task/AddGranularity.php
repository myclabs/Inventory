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
class Orga_Work_Task_AddGranularity extends Core_Work_Task
{

    /**
     * @var string
     */
    private $idCube;

    /**
     * @var array
     */
    private $listAxes = array();

    /**
     * @var bool
     */
    private $navigability;

    /**
     * @param Orga_Model_Cube $cube
     * @param Orga_Model_Axis[] $cube
     * @param bool $navigability
     */
    public function __construct($cube, $listAxes, $navigability)
    {
        $this->idCube = $cube->getKey()['id'];
        foreach ($listAxes as $axis) {
            $this->listAxes[] = $axis->getKey()['id'];
        }
        $this->navigability = $navigability;
    }

    /**
     * Execute
     */
    public function execute()
    {
        $granularity = new Orga_Model_Granularity();
        $granularity->setCube(Orga_Model_Cube::load(array('id' => $this->idCube)));
        foreach ($this->listAxes as $idAxis) {
            $granularity->addAxis(Orga_Model_Axis::load(array('id' => $idAxis)));
        }
        $granularity->setNavigability($this->navigability);
        $granularity->save();

        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->flush();
    }

}
