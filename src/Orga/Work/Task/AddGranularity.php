<?php

use Core\Work\BaseTaskInterface;
use Core\Work\BaseTaskTrait;
use MyCLabs\Work\Task\Task;

class Orga_Work_Task_AddGranularity implements Task, BaseTaskInterface
{
    use BaseTaskTrait;

    /**
     * @var string
     */
    public $idOrganization;

    /**
     * @var array
     */
    public $listAxes = [];

    /**
     * @var bool
     */
    public $navigability;

    /**
     * @var bool
     */
    public $orgaTab;

    /**
     * @var bool
     */
    public $acl;

    /**
     * @var bool
     */
    public $afTab;

    /**
     * @var bool
     */
    public $dw;

    /**
     * @var bool
     */
    public $genericActions;

    /**
     * @var bool
     */
    public $contextActions;

    /**
     * @var bool
     */
    public $inputDocuments;

    /**
     * @param Orga_Model_Organization $organization
     * @param Orga_Model_Axis[] $listAxes
     * @param bool $navigability
     * @param bool $orgaTab
     * @param bool $acl
     * @param bool $afTab
     * @param bool $dw
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
        $acl,
        $afTab,
        $dw,
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
        $this->acl = $acl;
        $this->afTab = $afTab;
        $this->dw = $dw;
        $this->genericActions = $genericActions;
        $this->contextActions = $contextActions;
        $this->inputDocuments = $inputDocuments;
        if ($taskLabel) {
            $this->setTaskLabel($taskLabel);
        }
    }
}
