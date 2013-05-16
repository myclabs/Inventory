<?php
/**
 * Classe Keyword_Service_Association
 * @author valentin.claras
 * @package Keyword
 */

/**
 * Service Keyword.
 * @package Keyword
 */
class Keyword_Service_Association extends Core_Service
{
    /**
     * Renvoie l'instance Singleton de la classe.
     *
     * @return Keyword_Service_Association
     */
    public static function getInstance()
    {
        return parent::getInstance();
    }

    /**
     * Ajoute une Association et le renvoie.
     *
     * @param string $subjectKeywordRef
     * @param string $objectKeywordRef
     * @param string $predicateRef
     *
     * @return Keyword_Model_Association
     */
    public function addService($subjectKeywordRef, $objectKeywordRef, $predicateRef)
    {
        $association = new Keyword_Model_Association();

        $this->checkAdd($subjectKeywordRef, $objectKeywordRef, $predicateRef);

        $association->setSubject(Keyword_Model_Keyword::loadByRef($subjectKeywordRef));
        $association->setObject(Keyword_Model_Keyword::loadByRef($objectKeywordRef));
        $association->setPredicate(Keyword_Model_Predicate::loadByRef($predicateRef));
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
     */
    public function updatePredicateService($subjectKeywordRef, $objectKeywordRef, $predicateRef, $newPredicateRef)
    {
        try {
            $association = Keyword_Model_Association::loadByRefs($subjectKeywordRef, $objectKeywordRef, $predicateRef);
        } catch (Core_Exception_NotFound $e) {
            throw new Core_Exception_User('Keyword', 'exceptions', 'AssociationDoesNotExist',
                array('SUBJECT' => $subjectKeywordRef, 'OBJECT' => $objectKeywordRef, 'PREDICATE' => $predicateRef));
        }

        $this->checkUpdatePredicate($subjectKeywordRef, $objectKeywordRef, $predicateRef, $newPredicateRef);

        $association->setPredicate(Keyword_Model_Predicate::loadByRef($newPredicateRef));
    }

    /**
     * Supprime une Association.
     *
     * @param string $subjectKeywordRef
     * @param string $objectKeywordRef
     * @param string $predicateRef
     */
    public function deleteService($subjectKeywordRef, $objectKeywordRef, $predicateRef)
    {
        try {
            $association = Keyword_Model_Association::loadByRefs($subjectKeywordRef, $objectKeywordRef, $predicateRef);
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
        $querySubjectKeywordExist->filter->addCondition(Keyword_Model_Keyword::QUERY_REF, $subjectKeywordRef);
        if (Keyword_Model_Keyword::countTotal($querySubjectKeywordExist) != 1) {
            return __('Keyword', 'exceptions', 'AssociationSubjectNotExists', array('REF' => $subjectKeywordRef));
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
        $queryObjectKeywordExist->filter->addCondition(Keyword_Model_Keyword::QUERY_REF, $objectKeywordRef);
        if (Keyword_Model_Keyword::countTotal($queryObjectKeywordExist) != 1) {
            return __('Keyword', 'exceptions', 'AssociationObjectNotExists', array('REF' => $objectKeywordRef));
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
        $queryPredicateExist->filter->addCondition(Keyword_Model_Predicate::QUERY_REF, $predicateRef);
        if (Keyword_Model_Predicate::countTotal($queryPredicateExist) != 1) {
            return __('Keyword', 'exceptions', 'AssociationPredicateNotExists', array('REF' => $predicateRef));
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
            Keyword_Model_Association::loadByRefs($subjectKeywordRef, $objectKeywordRef, $predicateRef);
            return __('Keyword', 'relation', 'associationAlreadyExists');
        } catch (Core_Exception_NotFound $e) {
            // Valide.
        }
        try {
            Keyword_Model_Association::loadByRefs($objectKeywordRef, $subjectKeywordRef, $predicateRef);
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
    protected function checkAdd($subjectKeywordRef, $objectKeywordRef, $predicateRef)
    {
        $querySubjectKeywordExist = new Core_Model_Query();
        $querySubjectKeywordExist->filter->addCondition(Keyword_Model_Keyword::QUERY_REF, $subjectKeywordRef);
        if (Keyword_Model_Keyword::countTotal($querySubjectKeywordExist) != 1) {
            throw new Core_Exception_User('Keyword', 'relation', 'inexistentKeyword',
                                            array('REF' => $subjectKeywordRef));
        }
        $queryObjectKeywordExist = new Core_Model_Query();
        $queryObjectKeywordExist->filter->addCondition(Keyword_Model_Keyword::QUERY_REF, $objectKeywordRef);
        if (Keyword_Model_Keyword::countTotal($queryObjectKeywordExist) != 1) {
            throw new Core_Exception_User('Keyword', 'relation', 'inexistentKeyword',
                                            array('REF' => $objectKeywordRef));
        }
        $queryPredicateExist = new Core_Model_Query();
        $queryPredicateExist->filter->addCondition(Keyword_Model_Predicate::QUERY_REF, $predicateRef);
        if (Keyword_Model_Predicate::countTotal($queryPredicateExist) != 1) {
            throw new Core_Exception_User('Keyword', 'relation', 'inexistentPredicate',
                                            array('REF' => $predicateRef));
        }
        if ($subjectKeywordRef === $objectKeywordRef) {
            throw new Core_Exception_User('Keyword', 'relation', 'subjectSameAsObject',
                                            array('REF' => $subjectKeywordRef));
        }
        try {
            Keyword_Model_Association::loadByRefs($subjectKeywordRef, $objectKeywordRef, $predicateRef);
            throw new Core_Exception_User('Keyword', 'relation', 'associationAlreadyExist');
        } catch (Core_Exception_NotFound $e) {
            // Valide.
        }
        try {
            Keyword_Model_Association::loadByRefs($objectKeywordRef, $subjectKeywordRef, $predicateRef);
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
    protected function checkUpdatePredicate($subjectKeywordRef, $objectKeywordRef, $predicateRef, $newPredicateRef)
    {
        $queryPredicateExist = new Core_Model_Query();
        $queryPredicateExist->filter->addCondition(Keyword_Model_Predicate::QUERY_REF, $newPredicateRef);
        if (Keyword_Model_Predicate::countTotal($queryPredicateExist) != 1) {
            throw new Core_Exception_User('Keyword', 'relation', 'inexistentPredicate',
                                            array('REF' => $newPredicateRef));
        }
        try {
            Keyword_Model_Association::loadByRefs($subjectKeywordRef, $objectKeywordRef, $newPredicateRef);
            throw new Core_Exception_User('Keyword', 'relation', 'associationAlreadyExists');
        } catch (Core_Exception_NotFound $e) {
            // Valide.
        }
        try {
            Keyword_Model_Association::loadByRefs($objectKeywordRef, $subjectKeywordRef, $newPredicateRef);
            throw new Core_Exception_User('Keyword', 'relation', 'associationAlreadyExists');
        } catch (Core_Exception_NotFound $e) {
            // Valide.
        }
    }

}