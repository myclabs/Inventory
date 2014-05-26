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
     * @param User   $user
     * @param string $taskLabel
     */
    public function notifyTaskFinished(User $user, $taskLabel);

    /**
     * Notify a user that a task has errored.
     *
     * @param User   $user
     * @param string $taskLabel
     */
    public function notifyTaskError(User $user, $taskLabel);
}
