<?php

namespace Core\Work\Notification;

use User\Domain\User;
use User\Domain\UserService;

/**
 * Notifies users about background tasks via email.
 *
 * @author matthieu.napoli
 */
class EmailTaskNotifier implements TaskNotifier
{
    /**
     * @var UserService
     */
    private $userService;

    /**
     * @var string
     */
    private $applicationName;

    public function __construct(UserService $userService, $applicationName)
    {
        $this->userService = $userService;
        $this->applicationName = $applicationName;
    }

    /**
     * {@inheritdoc}
     */
    public function notifyTaskFinished(User $user, $taskLabel)
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
    public function notifyTaskError(User $user, $taskLabel)
    {
        $subject = __('Core', 'backgroundTasks', 'emailNotificationErrorTitle', ['TASK_NAME' => $taskLabel]);
        $content = __('Core', 'backgroundTasks', 'emailNotificationErrorContent', [
             'TASK_NAME'        => $taskLabel,
             'APPLICATION_NAME' => $this->applicationName,
        ]);

        $this->userService->sendEmail($user, $subject, $content);
    }
}
