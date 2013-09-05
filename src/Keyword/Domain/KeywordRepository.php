<?php

namespace Keyword\Domain;

use Core\Domain\EntityRepository;
use Core\Domain\Translatable\TranslatableEntity;
use Core\Domain\Translatable\TranslatableRepository;
use Keyword\Domain\Keyword;

/**
 * Gère les Keyword.
 * @author valentin.claras
 */
interface KeywordRepository extends EntityRepository
{
    const QUERY_REF = 'ref';
    const QUERY_LABEL = 'label';

    /**
     * Renoie les messages d'erreur concernant la validation d'une ref.
     *
     * @param string $ref
     *
     * @return mixed string null
     */
    public function getErrorMessageForRef($ref);

    /**
     * Vérifie la disponibilité d'une référence pour un keyword.
     *
     * @param string $ref
     *
     * @throws \Core_Exception_User
     */
    public function checkRef($ref);

    /**
     * Retourne un Keyword grâce à son ref.
     *
     * @param string $keywordRef
     * @throws \Core_Exception_NotFound
     * @throws \Core_Exception_TooMany
     * @return Keyword
     */
    function getOneByRef($keywordRef);

    /**
     * Charge la liste des Keyword ne possédant pas d'association en tant qu'objet.
     *
     * @return Keyword[]
     */
    function getRoots();

    /**
     * @param TranslatableEntity $entity Entité du Repository
     * @param \Core_Locale|null $locale Si null, utilise la locale par défaut
     */
    function changeLocale($entity, \Core_Locale $locale);

}
