<?php

use Doctrine\ORM\EntityManager;
use MyCLabs\Work\Task\Task;
use MyCLabs\Work\TaskExecutor\TaskExecutor;

class Orga_Work_TaskExecutor_SetGranularityCellsGenerateDWCubesExecutor implements TaskExecutor
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
        if (! $task instanceof Orga_Work_Task_SetGranularityCellsGenerateDWCubes) {
            throw new InvalidArgumentException("Invalid task type provided");
        }

        $granularity = Orga_Model_Granularity::load($task->idGranularity);
        $granularity->setCellsGenerateDWCubes($task->newValue);
        $granularity->save();

        $this->entityManager->flush();
    }
}
