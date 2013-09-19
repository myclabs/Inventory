<?php

use Core\Work\BaseTaskInterface;
use Core\Work\BaseTaskTrait;
use MyCLabs\Work\Task\Task;

class Orga_Work_Task_AddMember implements Task, BaseTaskInterface
{
    use BaseTaskTrait;

    /**
     * @var string
     */
    public $idAxis;

    /**
     * @var string
     */
    public $ref;

    /**
     * @var string
     */
    public $label;

    /**
     * @var array
     */
    public $listBroaderMembers = [];

    /**
     * @param Orga_Model_Axis     $axis
     * @param string              $ref
     * @param string              $label
     * @param Orga_Model_Member[] $broaderMembers
     * @param null|string         $taskLabel
     */
    public function __construct($axis, $ref, $label, $broaderMembers, $taskLabel = null)
    {
        $this->idAxis = $axis->getId();
        $this->ref = $ref;
        $this->label = $label;
        foreach ($broaderMembers as $broaderMember) {
            $this->listBroaderMembers[] = $broaderMember->getId();
        }
        if ($taskLabel) {
            $this->setTaskLabel($taskLabel);
        }
    }
}
