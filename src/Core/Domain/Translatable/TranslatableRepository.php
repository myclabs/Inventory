<?php

namespace Core\Domain\Translatable;

use Core_Locale;

/**
 * Repository d'une entité contenant des champs traduits.
 *
 * @author valentin.claras
 */
trait TranslatableRepository
{
    /**
     * @param TranslatableEntity $entity Entité du Repository
     * @param Core_Locale|null $locale Si null, utilise la locale par défaut
     */
    public function changeLocale($entity, Core_Locale $locale = null)
    {
        $entity->setTranslationLocale($locale);
        $this->getEntityManager()->refresh($entity);
    }

    /**
     * @return \Doctrine\ORM\EntityManager
     */
    protected abstract function getEntityManager();
}
