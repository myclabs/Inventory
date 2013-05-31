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
    private $idProject;

    /**
     * @var array
     */
    private $listAxes = array();

    /**
     * @var bool
     */
    private $navigability;

    /**
     * @param Orga_Model_Project $project
     * @param Orga_Model_Axis[] $project
     * @param bool $navigability
     */
    public function __construct($project, $listAxes, $navigability)
    {
        $this->idProject = $project->getKey()['id'];
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
        $project = Orga_Model_Project::load(array('id' => $this->idProject));
        $axes = array();
        foreach ($this->listAxes as $idAxis) {
            $axes[] = Orga_Model_Axis::load(array('id' => $idAxis));
        }
        $granularity = new Orga_Model_Granularity($project, $axes);
        $granularity->setNavigability($this->navigability);
        $granularity->save();

        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->flush();
    }

}
