<?php

namespace Keyword\Domain;

/**
 * Service Predicate
 * @author valentin.claras
 * @author bertrand.ferry
 */
class PredicateService
{
    /**
     * @var PredicateRepository
     */
    protected $predicateRepository;


    /**
     * Constructeur du Service Predicate.
     *
     * @param PredicateRepository $repository
     * @return \Keyword\Domain\PredicateService
     */
    public function __construct(PredicateRepository $repository)
    {
        $this->predicateRepository = $repository;
    }

    /**
     * Renoie les messages d'erreur concernant la validation d'une ref.
     *
     * @param string $ref
     *
     * @return mixed string null
     */
    public function getErrorMessageRef($ref)
    {
        try {
            \Core_Tools::checkRef($ref);
        } catch (\Core_Exception_User $e) {
            return $e->getMessage();
        }
        try {
            $existingPredicateWithRef = $this->predicateRepository->getOneByRef($ref);
            $existingPredicateWithReverseRef = $this->predicateRepository->getOneByReverseRef($ref);
            return __('UI', 'formValidation', 'alreadyUsedIdentifier', array('REF' => $ref));
        } catch (\Core_Exception_NotFound $e) {
            // Pas de Keyword trouvé.
        }
        return null;
    }

    /**
     * Vérifie la disponibilité d'une référence pour un prédicat.
     *
     * @param string $ref
     *
     * @throws \Core_Exception_User
     */
    private function checkRef($ref)
    {
        \Core_Tools::checkRef($ref);
        try {
            $existingPredicateWithRef = $this->predicateRepository->getOneByRef($ref);
            $existingPredicateWithReverseRef = $this->predicateRepository->getOneByReverseRef($ref);
            throw new \Core_Exception_User('UI', 'formValidation', 'alreadyUsedIdentifier', array('REF' => $ref));
        } catch (\Core_Exception_NotFound $e) {
            // Pas de Keyword trouvé.
        }
    }

    /**
     * Retourne le Predicate correspondant à la ref donnée.
     *
     * @param string $ref
     * @return Predicate
     */
    public function get($ref)
    {
        return $this->predicateRepository->get($ref);
    }

    /**
     * Ajoute un Predicate.
     *
     * @param Predicate $predicate
     */
    public function add(Predicate $predicate)
    {
        $this->checkRef($predicate->getRef());
        $this->checkRef($predicate->getReverseRef());
        $this->predicateRepository->add($predicate);
    }

    /**
     * Supprime un Predicate.
     *
     * @param Predicate $predicate
     *
     * @return string Le label du Predicate.
     */
    public function remove(Predicate $predicate)
    {
        $this->predicateRepository->remove($predicate);
    }

    /**
     * Supprime un predicat.
     *
     * @param string $predicateRef Référence du prédicat
     *
     * @throws \Core_Exception_User
     * @return string Label du Service
     */
    public function delete($predicateRef)
    {
        $predicate = Predicate::loadByRef($predicateRef);

        $queryPredicateUsedInAssociation = new Core_Model_Query();
        $queryPredicateUsedInAssociation->filter->addCondition(Association::QUERY_PREDICATE, $predicate);
        if (Association::countTotal($queryPredicateUsedInAssociation) > 0) {
            throw new \Core_Exception_User('Keyword', 'predicate', 'predicateUsedInAssociation',
                array('REF' => $predicateRef));
        }

        $predicate->delete();

        return $predicate->getLabel();
    }

}
