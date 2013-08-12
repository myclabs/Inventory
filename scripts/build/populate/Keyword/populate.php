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
        $predicate = new Predicate();
        $predicate->setRef($ref);
        $predicate->setLabel($label);
        $predicate->setReverseRef($reverseRef);
        $predicate->setReverseLabel($reverseLabel);
        $predicate->setDescription($description);
        $predicate->save();
        return $predicate;
    }

    /**
     * @param string $ref
     * @param string $label
     * @return Keyword
     */
    protected function createKeyword($ref, $label)
    {
        $keyword = new Keyword();
        $keyword->setRef($ref);
        $keyword->setLabel($label);
        $keyword->save();
        return $keyword;
    }

    /**
     * @param Keyword $object
     * @param Predicate $predicate
     * @param Keyword $subject
     */
    protected function createAssociation(Keyword $subject, Predicate $predicate, Keyword $object)
    {
        $assocation = new Association();
        $assocation->setSubject($subject);
        $assocation->setPredicate($predicate);
        $assocation->setObject($object);
        $assocation->save();
    }

}
