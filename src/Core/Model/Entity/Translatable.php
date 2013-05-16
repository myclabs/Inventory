<?php
/**
 * @author     matthieu.napoli
 * @package    Core
 * @subpackage Model
 */

/**
 * Champs avec traductions
 *
 * @package    Core
 * @subpackage Model
 */
trait Core_Model_Entity_Translatable
{

    /**
     * @var string|null Si null, utilise la locale par défaut
     */
    protected $translationLocale;

    /**
     * @param Core_Locale|null $locale Si null, utilise la locale par défaut
     */
    public function setTranslationLocale(Core_Locale $locale = null)
    {
        if ($locale) {
            $this->translationLocale = $locale->getId();
        } else {
            $this->translationLocale = null;
        }
    }

    /**
     * @param Core_Locale|null $locale Si null, utilise la locale par défaut
     */
    public function reloadWithLocale(Core_Locale $locale = null)
    {
        $this->setTranslationLocale($locale);
        self::getEntityManager()->refresh($this);
    }

}
