<?php

namespace Keyword\Domain;

use Core\Domain\EntityRepository;
use Core\Domain\Translatable\TranslatableEntity;
use Core\Domain\Translatable\TranslatableRepository;
use Keyword\Domain\Keyword;
use Keyword\Domain\Predicate;
use Keyword\Domain\Association;

/**
 * Gère les Keyword.
 * @author valentin.claras
 */
interface KeywordRepository extends EntityRepository
{
    const QUERY_REF = 'ref';
    const QUERY_LABEL = 'label';
    const QUERY_KEYWORD_SUBJECT = 'subject';
    const QUERY_PREDICATE = 'predicate';
    const QUERY_KEYWORD_OBJECT = 'object';

    /**
     * Renvoie les messages d'erreur concernant la validation d'une ref.
     *
     * @param string $ref
     *
     * @return mixed string null
     */
    function getErrorMessageForRef($ref);

    /**
     * Vérifie la disponibilité d'une référence pour un keyword.
     *
     * @param string $ref
     *
     * @throws \Core_Exception_User
     */
    function checkRef($ref);

    /**
     * Retourne un Keyword grâce à son ref.
     *
     * @param string $keywordRef
     * @return Keyword
     */
    function getByRef($keywordRef);

    /**
     * Charge la liste des Keyword ne possédant pas d'association en tant qu'objet.
     *
     * @return Keyword[]
     */
    function getRoots();

    /**
     * @param TranslatableEntity $keyword
     * @param \Core_Locale|null $locale Si null, utilise la locale par défaut
     */
    function changeLocale($keyword, \Core_Locale $locale);

    /**
     * Renoie les messages d'erreur concernant la validation d'une Association.
     *
     * @param Keyword $subjectKeyword
     * @param Predicate $predicate
     * @param Keyword $objectKeyword
     *
     * @return mixed string null
     */
    public function getErrorMessageForAssociation(Keyword $subjectKeyword, Predicate $predicate, Keyword $objectKeyword);

    /**
     * Vérifie la disponibilité d'une Association.
     *
     * @param Keyword $subjectKeyword
     * @param Predicate $predicate
     * @param Keyword $objectKeyword
     *
     * @throws \Core_Exception_User
     */
    public function checkAssociation(Keyword $subjectKeyword, Predicate $predicate, Keyword $objectKeyword);

    /**
     * @return Association[]
     */
    public function getAllAssociations();

    /**
     * @return int
     */
    public function countAssociations();

    /**
     * Charge une Association en fonction des refs de ses composants.
     *
     * @param Keyword $subjectKeyword
     * @param Predicate $predicate
     * @param Keyword $objectKeyword
     * @throws \Core_Exception_NotFound
     * @throws \Core_Exception_TooMany
     * @return Association
     */
    public function getAssociationBySubjectPredicateObject(Keyword $subjectKeyword, Predicate $predicate, Keyword $objectKeyword);
}
