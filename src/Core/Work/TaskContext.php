<?php

namespace Core\Work;

use Core_Locale;

/**
 * Représente le contexte d'exécution d'une tâche
 *
 * @author matthieu.napoli
 */
class TaskContext
{
    /**
     * @var string
     */
    private $userLocaleId;

    /**
     * @var int
     */
    private $userId;

    /**
     * @param Core_Locale $userLocale
     */
    public function setUserLocale(Core_Locale $userLocale)
    {
        $this->userLocaleId = $userLocale->getId();
    }

    /**
     * @return Core_Locale
     */
    public function getUserLocale()
    {
        return Core_Locale::load($this->userLocaleId);
    }

    /**
     * @return int
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * @param int $userId
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;
    }
}
