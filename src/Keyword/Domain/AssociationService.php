<?php

namespace Keyword\Domain;

use Core_Exception_NotFound;
use Core_Exception_User;
use Core_Model_Query;

/**
 * Service Keyword.
 * @author valentin.claras
 */
class AssociationService
{
    /**
     * Ajoute une Association et le renvoie.
     *
     * @param string $subjectKeywordRef
     * @param string $objectKeywordRef
     * @param string $predicateRef
     *
     * @return Association
     */
    public function add($subjectKeywordRef, $objectKeywordRef, $predicateRef)
    {
        $association = new Association();

        $this->checkAdd($subjectKeywordRef, $objectKeywordRef, $predicateRef);

        $association->setSubject(Keyword::loadByRef($subjectKeywordRef));
        $association->setObject(Keyword::loadByRef($objectKeywordRef));
        $association->setPredicate(Predicate::loadByRef($predicateRef));
        $association->save();

        return $association;
    }

    /**
     * Met à jour une Association.
     *
     * @param string $subjectKeywordRef
     * @param string $objectKeywordRef
     * @param string $predicateRef
     * @param string $newPredicateRef
     * @throws Core_Exception_User
     */
    public function updatePredicate($subjectKeywordRef, $objectKeywordRef, $predicateRef, $newPredicateRef)
    {
        try {
            $association = Association::loadByRefs($subjectKeywordRef, $objectKeywordRef, $predicateRef);
        } catch (Core_Exception_NotFound $e) {
            throw new Core_Exception_User('Keyword', 'exceptions', 'AssociationDoesNotExist',
                array('SUBJECT' => $subjectKeywordRef, 'OBJECT' => $objectKeywordRef, 'PREDICATE' => $predicateRef));
        }

        $this->checkUpdatePredicate($subjectKeywordRef, $objectKeywordRef, $predicateRef, $newPredicateRef);

        $association->setPredicate(Predicate::loadByRef($newPredicateRef));
    }

    /**
     * Supprime une Association.
     *
     * @param string $subjectKeywordRef
     * @param string $objectKeywordRef
     * @param string $predicateRef
     * @throws Core_Exception_User
     */
    public function delete($subjectKeywordRef, $objectKeywordRef, $predicateRef)
    {
        try {
            $association = Association::loadByRefs($subjectKeywordRef, $objectKeywordRef, $predicateRef);
        } catch (Core_Exception_NotFound $e) {
            throw new Core_Exception_User('Keyword', 'exceptions', 'AssociationDoesNotExist',
                array('SUBJECT' => $subjectKeywordRef, 'OBJECT' => $objectKeywordRef, 'PREDICATE' => $predicateRef));
        }

        $association->delete();
    }

    /**
     * Renvoie le message d'erreur concernant l'ajout dans un associaiton d'un sujet inexistant.
     *
     * @param string $subjectKeywordRef
     *
     * @return mixed string null
     */
    public function getErrorMessageForAddSubject($subjectKeywordRef)
    {
        $querySubjectKeywordExist = new Core_Model_Query();
        $querySubjectKeywordExist->filter->addCondition(Keyword::QUERY_REF, $subjectKeywordRef);
        if (Keyword::countTotal($querySubjectKeywordExist) != 1) {
            return __('UI', 'formValidation', 'emptyRequiredField');
        }

        return null;
    }

    /**
     * Renvoie le message d'erreur concernant l'ajout dans un associaiton d'un objet inexistant.
     *
     * @param string $objectKeywordRef
     *
     * @return mixed string null
     */
    public function getErrorMessageForAddObject($objectKeywordRef)
    {
        $queryObjectKeywordExist = new Core_Model_Query();
        $queryObjectKeywordExist->filter->addCondition(Keyword::QUERY_REF, $objectKeywordRef);
        if (Keyword::countTotal($queryObjectKeywordExist) != 1) {
            return __('UI', 'formValidation', 'emptyRequiredField');
        }

        return null;
    }

    /**
     * Renvoie le message d'erreur concernant l'ajout dans un associaiton d'un prédicat inexistant.
     *
     * @param string $predicateRef
     *
     * @return mixed string null
     */
    public function getErrorMessageForAddPredicate($predicateRef)
    {
        $queryPredicateExist = new Core_Model_Query();
        $queryPredicateExist->filter->addCondition(Predicate::QUERY_REF, $predicateRef);
        if (Predicate::countTotal($queryPredicateExist) != 1) {
            return __('UI', 'formValidation', 'emptyRequiredField');
        }

        return null;
    }

    /**
     * Renoie les messages d'erreur concernant la validation d'une ref.
     *
     * @param string $subjectKeywordRef
     * @param string $objectKeywordRef
     * @param string $predicateRef
     *
     * @return mixed string null
     */
    public function getErrorMessageForAdd($subjectKeywordRef, $objectKeywordRef, $predicateRef)
    {
        if ($subjectKeywordRef === $objectKeywordRef) {
            return __('Keyword', 'relation', 'subjectSameAsObject', array('REF' => $subjectKeywordRef));
        }
        try {
            Association::loadByRefs($subjectKeywordRef, $objectKeywordRef, $predicateRef);
            return __('Keyword', 'relation', 'associationAlreadyExists');
        } catch (Core_Exception_NotFound $e) {
            // Valide.
        }
        try {
            Association::loadByRefs($objectKeywordRef, $subjectKeywordRef, $predicateRef);
            return __('Keyword', 'relation', 'associationAlreadyExists');
        } catch (Core_Exception_NotFound $e) {
            // Valide.
        }

        return null;
    }

    /**
     * Vérifie la disponibilité d'une référence pour un prédicat.
     *
     * @param string $subjectKeywordRef
     * @param string $objectKeywordRef
     * @param string $predicateRef
     *
     * @throws Core_Exception_User
     */
    private function checkAdd($subjectKeywordRef, $objectKeywordRef, $predicateRef)
    {
        $querySubjectKeywordExist = new Core_Model_Query();
        $querySubjectKeywordExist->filter->addCondition(Keyword::QUERY_REF, $subjectKeywordRef);
        if (Keyword::countTotal($querySubjectKeywordExist) != 1) {
            throw new Core_Exception_User('Keyword', 'relation', 'inexistentKeyword',
                array('REF' => $subjectKeywordRef));
        }
        $queryObjectKeywordExist = new Core_Model_Query();
        $queryObjectKeywordExist->filter->addCondition(Keyword::QUERY_REF, $objectKeywordRef);
        if (Keyword::countTotal($queryObjectKeywordExist) != 1) {
            throw new Core_Exception_User('Keyword', 'relation', 'inexistentKeyword',
                array('REF' => $objectKeywordRef));
        }
        $queryPredicateExist = new Core_Model_Query();
        $queryPredicateExist->filter->addCondition(Predicate::QUERY_REF, $predicateRef);
        if (Predicate::countTotal($queryPredicateExist) != 1) {
            throw new Core_Exception_User('Keyword', 'relation', 'inexistentPredicate',
                array('REF' => $predicateRef));
        }
        if ($subjectKeywordRef === $objectKeywordRef) {
            throw new Core_Exception_User('Keyword', 'relation', 'subjectSameAsObject',
                array('REF' => $subjectKeywordRef));
        }
        try {
            Association::loadByRefs($subjectKeywordRef, $objectKeywordRef, $predicateRef);
            throw new Core_Exception_User('Keyword', 'relation', 'associationAlreadyExist');
        } catch (Core_Exception_NotFound $e) {
            // Valide.
        }
        try {
            Association::loadByRefs($objectKeywordRef, $subjectKeywordRef, $predicateRef);
            throw new Core_Exception_User('Keyword', 'relation', 'associationAlreadyExist');
        } catch (Core_Exception_NotFound $e) {
            // Valide.
        }
    }

    /**
     * Vérifie la disponibilité d'une référence pour un prédicat.
     *
     * @param string $subjectKeywordRef
     * @param string $objectKeywordRef
     * @param string $predicateRef
     * @param string $newPredicateRef
     *
     * @throws Core_Exception_User
     */
    private function checkUpdatePredicate($subjectKeywordRef, $objectKeywordRef, $predicateRef, $newPredicateRef)
    {
        $queryPredicateExist = new Core_Model_Query();
        $queryPredicateExist->filter->addCondition(Predicate::QUERY_REF, $newPredicateRef);
        if (Predicate::countTotal($queryPredicateExist) != 1) {
            throw new Core_Exception_User('Keyword', 'relation', 'inexistentPredicate',
                array('REF' => $newPredicateRef));
        }
        try {
            Association::loadByRefs($subjectKeywordRef, $objectKeywordRef, $newPredicateRef);
            throw new Core_Exception_User('Keyword', 'relation', 'associationAlreadyExists');
        } catch (Core_Exception_NotFound $e) {
            // Valide.
        }
        try {
            Association::loadByRefs($objectKeywordRef, $subjectKeywordRef, $newPredicateRef);
            throw new Core_Exception_User('Keyword', 'relation', 'associationAlreadyExists');
        } catch (Core_Exception_NotFound $e) {
            // Valide.
        }
    }

}
