<?php

namespace Core\Work\EventListener;

use Core\Work\BaseTaskInterface;
use Core\Work\TaskContext;
use Core_Locale;
use MyCLabs\Work\Dispatcher\Event\DispatcherEventListener;
use MyCLabs\Work\Task\Task;
use Zend_Auth;

/**
 * Event listener pour le RabbitMQWorker
 *
 * @author matthieu.napoli
 */
class RabbitMQDispatcherEventListener implements DispatcherEventListener
{
    /**
     * {@inheritdoc}
     */
    public function beforeTaskDispatched(Task $task)
    {
        // Sauvegarde le contexte
        if ($task instanceof BaseTaskInterface) {
            $context = new TaskContext();

            // Locale
            $context->setUserLocale(Core_Locale::loadDefault());

            // User
            $auth = Zend_Auth::getInstance();
            if ($auth->hasIdentity()) {
                $context->setUserId($auth->getIdentity());
            }

            $task->setContext($context);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function beforeTaskSerialization(Task $task)
    {
    }
}
