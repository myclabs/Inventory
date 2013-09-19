<?php

namespace Core\Work;

use Core\Work\TaskContext;

/**
 * Représente une tâche abstraite
 *
 * @author matthieu.napoli
 */
interface BaseTaskInterface
{
    /**
     * @param TaskContext|null $context
     */
    public function setContext($context);

    /**
     * @return TaskContext|null
     */
    public function getContext();

    /**
     * "Label" of the task for user notifications. If null, no notifications are sent.
     * @return null|string
     */
    public function getTaskLabel();

    /**
     * "Label" of the task for user notifications. If null, no notifications are sent.
     * @param null|string $taskLabel
     */
    public function setTaskLabel($taskLabel);
}
