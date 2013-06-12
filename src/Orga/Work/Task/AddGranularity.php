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
     * @var bool
     */
    private $orgaTab;

    /**
     * @var bool
     */
    private $aCL;

    /**
     * @var bool
     */
    private $aFTab;

    /**
     * @var bool
     */
    private $dW;

    /**
     * @var bool
     */
    private $genericActions;

    /**
     * @var bool
     */
    private $contextActions;

    /**
     * @var bool
     */
    private $inputDocuments;

    /**
     * @param Orga_Model_Project $project
     * @param Orga_Model_Axis[] $listAxes
     * @param bool $navigability
     * @param bool $orgaTab
     * @param bool $aCL
     * @param bool $aFTab
     * @param bool $dW
     * @param bool $genericActions
     * @param bool $contextActions
     * @param bool $inputDocuments
     */
    public function __construct($project, $listAxes, $navigability, $orgaTab, $aCL, $aFTab, $dW, $genericActions, $contextActions, $inputDocuments)
    {
        $this->idProject = $project->getId();
        foreach ($listAxes as $axis) {
            $this->listAxes[] = $axis->getId();
        }
        $this->navigability = $navigability;
        $this->orgaTab = $orgaTab;
        $this->aCL = $aCL;
        $this->aFTab = $aFTab;
        $this->dW = $dW;
        $this->genericActions = $genericActions;
        $this->contextActions = $contextActions;
        $this->inputDocuments = $inputDocuments;
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
        $granularity->setCellsWithOrgaTab($this->orgaTab);
        $granularity->setCellsWithACL($this->aCL);
        $granularity->setCellsWithAFConfigTab($this->aFTab);
        $granularity->setCellsGenerateDWCubes($this->dW);
        $granularity->setCellsWithSocialGenericActions($this->genericActions);
        $granularity->setCellsWithSocialContextActions($this->contextActions);
        $granularity->setCellsWithInputDocuments($this->inputDocuments);
        $granularity->save();

        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->flush();
    }

}
