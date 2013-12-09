<?php

namespace Core\Log;

use User\Domain\User;

/**
 * Log processor.
 *
 * Ajoute les infos sur l'utilisateur connecté aux logs.
 *
 * @author matthieu.napoli
 */
class UserInfoProcessor
{
    /**
     * @param  array $record
     * @return array
     */
    public function __invoke(array $record)
    {
        $auth = \Zend_Auth::getInstance();

        if ($auth->hasIdentity()) {
            $user = User::load($auth->getIdentity());
            $record['extra']['user_email'] = $user->getEmail();
        } else {
            $record['extra']['user_email'] = '?';
        }

        return $record;
    }
}
