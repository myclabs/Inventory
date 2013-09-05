<?php
/**
 * @author     valentin.claras
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
trait TranslatableRepository
{
    /**
     * @param TranslatableEntity $entity Entité du Repository
     * @param \Core_Locale|null $locale Si null, utilise la locale par défaut
     */
    public function changeLocale($entity, \Core_Locale $locale = null)
    {
        $entity->setTranslationLocale($locale);
        $this->getEntityManager()->refresh($entity);
    }

}
