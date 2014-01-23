<?php

namespace User\Application\ViewHelper;

use Zend_Auth;
use Zend_View_Helper_Abstract;
use User\Domain\User;

/**
 * Helper pour les tutoriels
 */
class TutorialHelper extends Zend_View_Helper_Abstract
{
    /**
     * Renvoi un turorial ou null si aucun ne correspond ou le tutorial a déjà été fait par l'utilisateur
     *
     * @param string $tutorial Tutorial
     *
     * @return string|null
     */
    public function tutorial($tutorial)
    {
        switch ($tutorial) {
            case 'orga':
                $tutorialInt = 3;
                break;
            case 'af':
                $tutorialInt = 5;
                break;
            case 'dw':
                $tutorialInt = 7;
                break;
            default:
                return null;
        }
        $auth = Zend_Auth::getInstance();
        if (!$auth->hasIdentity()) {
            return null;
        }
        $connectedUser = User::load($auth->getIdentity());
        if (!$connectedUser->isTutorialDone($tutorialInt)) {
            $this->view->headScript()->appendFile('introjs/intro.min.js', 'text/javascript');
            $this->view->headLink()->prependStylesheet('introjs/introjs.min.css');
            return $this->view->partial('tutorials/'.$tutorial.'.phtml', 'user');
        }
        return null;
    }

}