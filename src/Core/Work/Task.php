<?php

/**
 * Représente une tâche abstraite
 *
 * @author  matthieu.napoli
 */
abstract class Core_Work_Task
{
    /**
     * @var Core_Work_TaskContext|null
     */
    private $context;

    /**
     * "Label" of the task for user notifications. If null, no notifications are sent.
     * @var null|string
     */
    private $taskLabel;

    /**
     * @param Core_Work_TaskContext|null $context
     */
    public function setContext($context)
    {
        $this->context = $context;
    }

    /**
     * @return Core_Work_TaskContext|null
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * "Label" of the task for user notifications. If null, no notifications are sent.
     * @return null|string
     */
    public function getTaskLabel()
    {
        return $this->taskLabel;
    }

    /**
     * "Label" of the task for user notifications. If null, no notifications are sent.
     * @param null|string $taskLabel
     */
    public function setTaskLabel($taskLabel)
    {
        $this->taskLabel = $taskLabel;
    }
}
