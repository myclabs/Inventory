<?php

namespace Keyword\Domain;

use Core_Exception_User;
use Core_Model_Query;
use Core_Tools;

/**
 * Service Predicate
 * @author valentin.claras
 * @author bertrand.ferry
 */
class PredicateService
{
    /**
     * Ajoute un predicat et le renvoie.
     *
     * @param string $ref
     * @param string $label
     * @param string $reverseRef
     * @param string $reverseLabel
     * @param string $description
     *
     * @throws Core_Exception_User
     *
     * @return Predicate
     */
    public function add($ref, $label, $reverseRef, $reverseLabel, $description = null)
    {
        $predicate = new Predicate();

        if ($ref === $reverseRef) {
            throw new Core_Exception_User('Keyword', 'predicate', 'refIsSameAsRevRef');
        }
        $this->checkPredicateRef($ref);
        $this->checkPredicateReverseRef($reverseRef);

        $predicate->setRef($ref);
        $predicate->setLabel($label);
        $predicate->setReverseRef($reverseRef);
        $predicate->setReverseLabel($reverseLabel);
        $predicate->setDescription($description);
        $predicate->save();

        return $predicate;
    }

    /**
     * Change la reference d'un predicat et le renvoie.
     *
     * @param string $predicateRef
     * @param string $newRef
     *
     * @throws Core_Exception_User
     *
     * @return Predicate
     */
    public function updateRef($predicateRef, $newRef)
    {
        $predicate = Predicate::loadByRef($predicateRef);

        $this->checkPredicateRef($newRef);

        $predicate->setRef($newRef);

        return $predicate;
    }

    /**
     * Change le label d'un predicat et le renvoie.
     *
     * @param string $predicateRef
     * @param string $newLabel
     *
     * @return Predicate
     */
    public function updateLabel($predicateRef, $newLabel)
    {
        $predicate = Predicate::loadByRef($predicateRef);

        $predicate->setLabel($newLabel);

        return $predicate;
    }

    /**
     * Change la reference inverse d'un predicat et le renvoie.
     *
     * @param string $predicateRef Référence du prédicat
     * @param string $newReverseRef
     *
     * @throws Core_Exception_User
     *
     * @return Predicate
     */
    public function updateReverseRef($predicateRef, $newReverseRef)
    {
        $predicate = Predicate::loadByRef($predicateRef);

        $this->checkPredicateReverseRef($newReverseRef);

        $predicate->setReverseRef($newReverseRef);

        return $predicate;
    }

    /**
     * Change le label inverse d'un predicat et le renvoie.
     *
     * @param string $predicateRef Référence du prédicat
     * @param string $newReverseLabel
     *
     * @return Predicate
     */
    public function updateReverseLabel($predicateRef, $newReverseLabel)
    {
        $predicate = Predicate::loadByRef($predicateRef);

        $predicate->setReverseLabel($newReverseLabel);

        return $predicate;
    }

    /**
     * Change la description d'un predicat et le renvoie.
     *
     * @param string $predicateRef Référence du prédicat
     * @param string $newDescription
     *
     * @return Predicate
     */
    public function updateDescription($predicateRef, $newDescription)
    {
        $predicate = Predicate::loadByRef($predicateRef);

        $predicate->setDescription($newDescription);

        return $predicate;
    }

    /**
     * Supprime un predicat.
     *
     * @param string $predicateRef Référence du prédicat
     *
     * @throws Core_Exception_User
     * @return string Label du Service
     */
    public function delete($predicateRef)
    {
        $predicate = Predicate::loadByRef($predicateRef);

        $queryPredicateUsedInAssociation = new Core_Model_Query();
        $queryPredicateUsedInAssociation->filter->addCondition(Association::QUERY_PREDICATE, $predicate);
        if (Association::countTotal($queryPredicateUsedInAssociation) > 0) {
            throw new Core_Exception_User('Keyword', 'predicate', 'predicateUsedInAssociation',
                array('REF' => $predicateRef));
        }

        $predicate->delete();

        return $predicate->getLabel();
    }

    /**
     * Renoie les messages d'erreur concernant la validation d'une ref.
     *
     * @param string $ref
     *
     * @return mixed string null
     */
    public function getErrorMessageForNewRef($ref)
    {
        try {
            Core_Tools::checkRef($ref);
        } catch (Core_Exception_User $e) {
            return $e->getMessage();
        }
        $queryRefUsedAsRef = new Core_Model_Query();
        $queryRefUsedAsRef->filter->addCondition(Predicate::QUERY_REF, $ref);
        if (Predicate::countTotal($queryRefUsedAsRef) > 0) {
            return __('UI', 'formValidation', 'alreadyUsedIdentifier', array('REF' => $ref));
        }
        $queryRefUsedAsRevRef = new Core_Model_Query();
        $queryRefUsedAsRevRef->filter->addCondition(Predicate::QUERY_REVERSE_REF, $ref);
        if (Predicate::countTotal($queryRefUsedAsRevRef) > 0) {
            return __('UI', 'formValidation', 'alreadyUsedIdentifier', array('REF' => $ref));
        }

        return null;
    }

    /**
     * Renoie les messages d'erreur concernant la validation d'une ref.
     *
     * @param string $reverseRef
     *
     * @return mixed string null
     */
    public function getErrorMessageForNewReverseRef($reverseRef)
    {
        try {
            Core_Tools::checkRef($reverseRef);
        } catch (Core_Exception_User $e) {
            return $e->getMessage();
        }
        $queryRevRefUsedAsRef = new Core_Model_Query();
        $queryRevRefUsedAsRef->filter->addCondition(Predicate::QUERY_REF, $reverseRef);
        if (Predicate::countTotal($queryRevRefUsedAsRef) > 0) {
            return __('UI', 'formValidation', 'alreadyUsedIdentifier', array('REF' => $reverseRef));
        }
        $queryRevRefUsedAsRevRef = new Core_Model_Query();
        $queryRevRefUsedAsRevRef->filter->addCondition(Predicate::QUERY_REF, $reverseRef);
        if (Predicate::countTotal($queryRevRefUsedAsRevRef) > 0) {
            return __('UI', 'formValidation', 'alreadyUsedIdentifier', array('REF' => $reverseRef));
        }

        return null;
    }

    /**
     * Vérifie la disponibilité d'une référence pour un prédicat.
     *
     * @param string $ref
     *
     * @throws Core_Exception_User
     */
    private function checkPredicateRef($ref)
    {
        Core_Tools::checkRef($ref);
        $queryRefUsedAsRef = new Core_Model_Query();
        $queryRefUsedAsRef->filter->addCondition(Predicate::QUERY_REF, $ref);
        if (Predicate::countTotal($queryRefUsedAsRef) > 0) {
            throw new Core_Exception_User('UI', 'formValidation', 'alreadyUsedIdentifier',
                array('REF' => $ref));
        }
        $queryRefUsedAsRevRef = new Core_Model_Query();
        $queryRefUsedAsRevRef->filter->addCondition(Predicate::QUERY_REVERSE_REF, $ref);
        if (Predicate::countTotal($queryRefUsedAsRevRef) > 0) {
            throw new Core_Exception_User('UI', 'formValidation', 'alreadyUsedIdentifier',
                array('REF' => $ref));
        }
    }

    /**
     * Vérifie la disponibilité d'une référence inverse pour un prédicat.
     *
     * @param string $reverseRef
     *
     * @throws Core_Exception_User
     */
    private function checkPredicateReverseRef($reverseRef)
    {
        Core_Tools::checkRef($reverseRef);
        $queryRevRefUsedAsRef = new Core_Model_Query();
        $queryRevRefUsedAsRef->filter->addCondition(Predicate::QUERY_REF, $reverseRef);
        if (Predicate::countTotal($queryRevRefUsedAsRef) > 0) {
            throw new Core_Exception_User('UI', 'formValidation', 'alreadyUsedIdentifier',
                array('REF' => $reverseRef));
        }
        $queryRevRefUsedAsRevRef = new Core_Model_Query();
        $queryRevRefUsedAsRevRef->filter->addCondition(Predicate::QUERY_REF, $reverseRef);
        if (Predicate::countTotal($queryRevRefUsedAsRevRef) > 0) {
            throw new Core_Exception_User('UI', 'formValidation', 'alreadyUsedIdentifier',
                array('REF' => $reverseRef));
        }
    }

}
