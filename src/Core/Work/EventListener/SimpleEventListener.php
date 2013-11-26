<?php

namespace Core\Work\EventListener;

use MyCLabs\Work\Task\Task;

/**
 * Event listener pour le SimpleWorker
 *
 * @author matthieu.napoli
 */
class SimpleEventListener extends \MyCLabs\Work\EventListener
{
    /**
     * {@inheritdoc}
     */
    public function beforeTaskExecution(Task $task)
    {
        set_time_limit(0);
    }
}
