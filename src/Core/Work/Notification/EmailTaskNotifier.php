<?php

namespace Core\Work\Notification;

use User_Model_User;
use User_Service_User;

/**
 * Notifies users about background tasks via email.
 *
 * @author matthieu.napoli
 */
class EmailTaskNotifier implements TaskNotifier
{
    /**
     * @var User_Service_User
     */
    private $userService;

    /**
     * @var string
     */
    private $applicationName;

    public function __construct(User_Service_User $userService, $applicationName)
    {
        $this->userService = $userService;
        $this->applicationName = $applicationName;
    }

    /**
     * {@inheritdoc}
     */
    public function notifyTaskFinished(User_Model_User $user, $taskLabel)
    {
        $subject = __('Core', 'backgroundTasks', 'emailNotificationTitle', ['TASK_NAME' => $taskLabel]);
        $content = __('Core', 'backgroundTasks', 'emailNotificationContent', [
            'TASK_NAME'        => $taskLabel,
            'APPLICATION_NAME' => $this->applicationName,
        ]);

        $this->userService->sendEmail($user, $subject, $content);
    }

    /**
     * {@inheritdoc}
     */
    public function notifyTaskError(User_Model_User $user, $taskLabel)
    {
        $subject = __('Core', 'backgroundTasks', 'emailNotificationErrorTitle', ['TASK_NAME' => $taskLabel]);
        $content = __('Core', 'backgroundTasks', 'emailNotificationErrorContent', [
             'TASK_NAME'        => $taskLabel,
             'APPLICATION_NAME' => $this->applicationName,
        ]);

        $this->userService->sendEmail($user, $subject, $content);
    }
}
