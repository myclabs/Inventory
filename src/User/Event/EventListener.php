<?php
/**
 * @author matthieu.napoli
 */

namespace User\Event;

use User_Model_User;
use Zend_Auth;

/**
 * Écoute les UserEvent pour les enrichir avec l'utilisateur connecté
 */
class EventListener
{
    /**
     * @var User_Model_User|null
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
     * @return User_Model_User|null
     */
    private function getUser()
    {
        if ($this->user === null) {
            $auth = Zend_Auth::getInstance();

            if ($auth->hasIdentity()) {
                $this->user = User_Model_User::load($auth->getIdentity());
            }
        }

        return $this->user;
    }
}
