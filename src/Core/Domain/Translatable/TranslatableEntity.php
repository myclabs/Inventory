<?php
/**
 * @author     matthieu.napoli
 * @package    Core
 * @subpackage Domain
 */

namespace Core\Domain\Translatable;

/**
 * Champs avec traductions
 *
 * @package    Core
 * @subpackage Domain
 */
trait TranslatableEntity
{
    /**
     * @var string|null Si null, utilise la locale par défaut
     */
    protected $translationLocale;


    /**
     * @param \Core_Locale|null $locale Si null, utilise la locale par défaut
     */
    public function setTranslationLocale(\Core_Locale $locale = null)
    {
        if ($locale && $locale->getId() != $this->translationLocale->getId()) {
            $this->translationLocale = $locale->getId();
        } else {
            $this->translationLocale = null;
        }
    }

}
