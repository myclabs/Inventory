<?php

namespace AF\Architecture\Service;

use AF\Domain\AF;
use AF\Domain\InputSet\PrimaryInputSet;
use Zend_Session_Namespace;

/**
 * Service permettant de stocker un InputSet en session.
 *
 * @author matthieu.napoli
 */
class InputSetSessionStorage
{
    const SESSION_EXPIRATION = 3600;

    /**
     * @param AF   $af
     * @param bool $createIfNotFound Si l'InputSet n'est pas trouvé, en crée un nouveau automatiquement
     * @return PrimaryInputSet|null
     */
    public function getInputSet(AF $af, $createIfNotFound = true)
    {
        // On cherche la saisie en session
        $session = $this->getSessionStorage();
        if (!isset($session->inputSet[$af->getId()])) {
            if ($createIfNotFound) {
                $inputSet = new PrimaryInputSet($af);
                /** @noinspection PhpUndefinedFieldInspection */
                $session->inputSet[$af->getId()] = $inputSet;
            } else {
                $inputSet = null;
            }
        } else {
            $inputSet = $session->inputSet[$af->getId()];
        }
        return $inputSet;
    }

    /**
     * @param AF              $af
     * @param PrimaryInputSet $inputSet
     */
    public function saveInputSet(AF $af, PrimaryInputSet $inputSet)
    {
        $session = $this->getSessionStorage();
        /** @noinspection PhpUndefinedFieldInspection */
        $session->inputSet[$af->getId()] = $inputSet;
    }

    /**
     * @return Zend_Session_Namespace
     */
    protected function getSessionStorage()
    {
        $session = new Zend_Session_Namespace(get_class());
        $session->setExpirationSeconds(self::SESSION_EXPIRATION);
        if (!is_array($session->inputSet)) {
            /** @noinspection PhpUndefinedFieldInspection */
            $session->inputSet = [];
        }
        return $session;
    }
}
