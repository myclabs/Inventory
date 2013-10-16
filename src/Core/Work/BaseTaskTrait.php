<?php

namespace Core\Work;

use Core\Work\TaskContext;

/**
 * Représente une tâche abstraite
 *
 * @author matthieu.napoli
 */
trait BaseTaskTrait
{
    /**
     * @var TaskContext|null
     */
    private $context;

    /**
     * "Label" of the task for user notifications. If null, no notifications are sent.
     * @var null|string
     */
    private $taskLabel;

    /**
     * @param TaskContext|null $context
     */
    public function setContext($context)
    {
        $this->context = $context;
    }

    /**
     * @return TaskContext|null
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

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        return get_class();
    }
}
