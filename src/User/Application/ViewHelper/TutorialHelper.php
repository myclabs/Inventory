<?php

namespace User\Application\ViewHelper;

use ReflectionClass;
use Zend_Auth;
use Zend_View_Helper_Abstract;
use User\Domain\User;

/**
 * Helper pour les tutoriels
 */
class TutorialHelper extends Zend_View_Helper_Abstract
{
    const DASHBOARD = 3,
          ORGA = 5,
          AF = 7,
          DW = 11;

    /**
     * Renvoi un turorial ou null si aucun ne correspond ou le tutorial a déjà été fait par l'utilisateur
     *
     * @param string $tutorial Tutorial
     *
     * @throws \InvalidArgumentException
     * @return string|null
     */
    public function tutorial($tutorial)
    {
        $refl = new ReflectionClass(get_class());
        $constants = $refl->getConstants();
        if (!in_array($tutorial, $constants)) {
            throw new \InvalidArgumentException("Invalid tutorial constant");
        }

        $tutorialFile = strtolower(array_search($tutorial, $constants));

        $auth = Zend_Auth::getInstance();
        if (!$auth->hasIdentity()) {
            return null;
        }
        $connectedUser = User::load($auth->getIdentity());
        if (!$connectedUser->isTutorialDone($tutorial)) {
            $this->view->headScript()->appendFile('introjs/intro.min.js', 'text/javascript');
            $this->view->headLink()->prependStylesheet('introjs/introjs.min.css');
            return $this->view->partial('tutorials/'.$tutorialFile.'.phtml', 'user');
        }
        return null;
    }
}