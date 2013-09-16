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
class Orga_Work_Task_AddGranularity extends Task
{

    /**
     * @var string
     */
    private $idOrganization;

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
     * @param Orga_Model_Organization $organization
     * @param Orga_Model_Axis[] $listAxes
     * @param bool $navigability
     * @param bool $orgaTab
     * @param bool $aCL
     * @param bool $aFTab
     * @param bool $dW
     * @param bool $genericActions
     * @param bool $contextActions
     * @param bool $inputDocuments
     * @param null|string $taskLabel
     */
    public function __construct(
        $organization,
        $listAxes,
        $navigability,
        $orgaTab,
        $aCL,
        $aFTab,
        $dW,
        $genericActions,
        $contextActions,
        $inputDocuments,
        $taskLabel = null
    ) {
        $this->idOrganization = $organization->getId();
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
        if ($taskLabel) {
            $this->setTaskLabel($taskLabel);
        }
    }

    /**
     * Execute
     */
    public function execute()
    {
        $organization = Orga_Model_Organization::load($this->idOrganization);
        $axes = array();
        foreach ($this->listAxes as $idAxis) {
            $axes[] = Orga_Model_Axis::load($idAxis);
        }
        $granularity = new Orga_Model_Granularity($organization, $axes);
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
