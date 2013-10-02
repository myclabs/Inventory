<?php

namespace Core\Work\Notification;

use User_Model_User;

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
     * @param User_Model_User $user
     * @param string          $taskLabel
     */
    function notifyTaskFinished(User_Model_User $user, $taskLabel);

    /**
     * Notify a user that a task has errored.
     *
     * @param User_Model_User $user
     * @param string          $taskLabel
     */
    function notifyTaskError(User_Model_User $user, $taskLabel);
}
