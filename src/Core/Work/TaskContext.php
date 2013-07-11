<?php
/**
 * @author  matthieu.napoli
 * @package Core
 */

/**
 * Représente le contexte d'exécution d'une tâche
 *
 * @package Core
 */
class Core_Work_TaskContext
{
    /**
     * @var string
     */
    private $userLocaleId;

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
}
