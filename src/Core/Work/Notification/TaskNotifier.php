<?php

namespace Core\Work\Notification;

use User\Domain\User;

/**
 * Notifies users about background tasks.
 *
 * @author matthieu.napoli
 */
interface TaskNotifier
{
    /**
     * Notify a user that a task has finished.
     *
     * @param \User\Domain\User $user
     * @param string          $taskLabel
     */
    function notifyTaskFinished(User $user, $taskLabel);

    /**
     * Notify a user that a task has errored.
     *
     * @param \User\Domain\User $user
     * @param string          $taskLabel
     */
    function notifyTaskError(User $user, $taskLabel);
}
