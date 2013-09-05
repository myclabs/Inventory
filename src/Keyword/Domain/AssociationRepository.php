<?php

namespace Keyword\Domain;

use Core\Domain\EntityRepository;

/**
 * Gère les Association.
 * @author valentin.claras
 */
interface AssociationRepository extends EntityRepository
{
    const QUERY_KEYWORD_SUBJECT = 'subject';
    const QUERY_PREDICATE = 'predicate';
    const QUERY_KEYWORD_OBJECT = 'object';

    /**
     * Renoie les messages d'erreur concernant la validation d'une Association.
     *
     * @param Association $association
     *
     * @return mixed string null
     */
    public function getErrorMessageForAssociation(Association $association);

    /**
     * Vérifie la disponibilité d'une Association.
     *
     * @param Association $association
     *
     * @throws \Core_Exception_User
     */
    public function checkAssociation(Association $association);

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
    public function getOneBySubjectPredicateObject(Keyword $subjectKeyword, Predicate $predicate, Keyword $objectKeyword);

}
