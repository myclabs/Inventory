<?php

use Doctrine\ORM\EntityManager;
use MyCLabs\Work\Task\Task;
use MyCLabs\Work\TaskExecutor\TaskExecutor;

class Orga_Work_TaskExecutor_AddMemberExecutor implements TaskExecutor
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
        if (! $task instanceof Orga_Work_Task_AddMember) {
            throw new InvalidArgumentException("Invalid task type provided");
        }

        $member = new Orga_Model_Member(Orga_Model_Axis::load($task->idAxis));
        $member->setRef($task->ref);
        $member->setLabel($task->label);
        foreach ($task->listBroaderMembers as $idBroaderMember) {
            $member->addDirectParent(Orga_Model_Member::load($idBroaderMember));
        }
        $member->save();

        $this->entityManager->flush();
    }
}
