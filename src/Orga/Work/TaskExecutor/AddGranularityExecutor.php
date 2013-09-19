<?php

use Doctrine\ORM\EntityManager;
use MyCLabs\Work\Task\Task;
use MyCLabs\Work\TaskExecutor\TaskExecutor;

class Orga_Work_TaskExecutor_AddGranularityExecutor implements TaskExecutor
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(Task $task)
    {
        if (! $task instanceof Orga_Work_Task_AddGranularity) {
            throw new InvalidArgumentException("Invalid task type provided");
        }

        $organization = Orga_Model_Organization::load($task->idOrganization);
        $axes = [];
        foreach ($task->listAxes as $idAxis) {
            $axes[] = Orga_Model_Axis::load($idAxis);
        }
        $granularity = new Orga_Model_Granularity($organization, $axes);
        $granularity->setNavigability($task->navigability);
        $granularity->setCellsWithOrgaTab($task->orgaTab);
        $granularity->setCellsWithACL($task->acl);
        $granularity->setCellsWithAFConfigTab($task->afTab);
        $granularity->setCellsGenerateDWCubes($task->dw);
        $granularity->setCellsWithSocialGenericActions($task->genericActions);
        $granularity->setCellsWithSocialContextActions($task->contextActions);
        $granularity->setCellsWithInputDocuments($task->inputDocuments);
        $granularity->save();

        $this->entityManager->flush();
    }
}
