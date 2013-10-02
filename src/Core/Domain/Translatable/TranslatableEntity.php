<?php

namespace Core\Domain\Translatable;

use Core_Locale;

/**
 * Entité comprenant des champs traduits.
 *
 * @author matthieu.napoli
 */
trait TranslatableEntity
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
        if ($locale !== null && $locale->getId() != $this->translationLocale) {
            $this->translationLocale = $locale->getId();
        } else {
            $this->translationLocale = null;
        }
    }
}
