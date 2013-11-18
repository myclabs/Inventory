<?php

namespace User\Domain\Event;

use User\Domain\Event\UserEvent;
use User\Domain\User;
use Zend_Auth;

/**
 * Écoute les UserEvent pour les enrichir avec l'utilisateur connecté.
 *
 * @author matthieu.napoli
 */
class EventListener
{
    /**
     * @var \User\Domain\User|null
     */
    private $user;

    /**
     * @param UserEvent $event
     */
    public function onUserEvent(UserEvent $event)
    {
        $user = $this->getUser();

        if ($user) {
            $event->setUser($user);
        }
    }

    /**
     * @return \User\Domain\User|null
     */
    private function getUser()
    {
        if ($this->user === null) {
            $auth = Zend_Auth::getInstance();

            if ($auth->hasIdentity()) {
                $this->user = User::load($auth->getIdentity());
            }
        }

        return $this->user;
    }
}
