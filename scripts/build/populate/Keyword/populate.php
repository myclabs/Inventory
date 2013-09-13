<?php
/**
 * @package Keyword
 */

use Keyword\Domain\Association;
use Keyword\Domain\Keyword;
use Keyword\Domain\Predicate;

/**
 * Remplissage de la base de données avec des données de test
 * @package Keyword
 */
class Keyword_Populate extends Core_Script_Action
{
    /**
     * @var \Keyword\Domain\KeywordRepository
     */
    protected $keywordRepository;

    /**
     * @var \Keyword\Domain\PredicateRepository
     */
    protected $predicateRepository;

    /**
     * @var \Keyword\Domain\AssociationRepository
     */
    protected $associationRepository;


    function __construct()
    {
        $entityManagers = Zend_Registry::get('EntityManagers');
        /** @var $entityManager \Doctrine\ORM\EntityManager */
        $entityManager = $entityManagers['default'];
        $this->keywordRepository = $entityManager->getRepository('\Keyword\Domain\Keyword');
        $this->predicateRepository = $entityManager->getRepository('\Keyword\Domain\Predicate');
        $this->associationRepository = $entityManager->getRepository('\Keyword\Domain\Association');
    }

    /**
     * {@inheritdoc}
     */
    public function runEnvironment($environment)
    {
        $entityManagers = Zend_Registry::get('EntityManagers');
        /** @var $entityManager \Doctrine\ORM\EntityManager */
        $entityManager = $entityManagers['default'];


        // Création des prédicats.
        //  + createPredicate : -
        // Params : ref, label, reverseRef, reverseLabel
        // OptionalParams : description=null

        // Création des mot-clefs.
        //  + createKeyword : -
        // Params : ref, label


        $entityManager->flush();


        // Création des associations.
        //  + createAssociation : -
        // Params : Keyword subject, Predicate, Keyword object


        $entityManager->flush();

        echo "\t\tKeyword created".PHP_EOL;
    }

    /**
     * @param string $ref
     * @param string $label
     * @param string $reverseRef
     * @param string $reverseLabel
     * @param string|null $description
     * @return Predicate
     */
    protected function createPredicate($ref, $label, $reverseRef, $reverseLabel, $description=null)
    {
        $predicate = new Predicate($ref, $reverseRef, $label, $reverseLabel);
        $predicate->setDescription($description);
        $this->predicateRepository->add($predicate);
        return $predicate;
    }

    /**
     * @param string $ref
     * @param string $label
     * @return Keyword
     */
    protected function createKeyword($ref, $label)
    {
        $keyword = new Keyword($ref, $label);
        $this->keywordRepository->add($keyword);
        return $keyword;
    }

    /**
     * @param Keyword $object
     * @param Predicate $predicate
     * @param Keyword $subject
     */
    protected function createAssociation(Keyword $subject, Predicate $predicate, Keyword $object)
    {
        $this->associationRepository->add(new Association($subject, $predicate, $object));
    }

}
