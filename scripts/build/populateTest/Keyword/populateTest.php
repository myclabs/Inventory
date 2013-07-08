<?php
/**
 * @package Keyword
 */


/**
 * Remplissage de la base de données avec des données de test
 * @package Keyword
 */
class Keyword_PopulateTest extends Core_Script_Action
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
        // Params : ref, label, reverseRef, reverseLabel
        // OptionalParams : description=null
        $predicate1 = $this->createPredicate('ref1', 'Label 1', 'reverseRef1', 'Reverse Label 1');
        $predicate2 = $this->createPredicate('ref2', 'Label 2', 'reverseRef2', 'Reverse Label 2', 'description');

        // Création des mot-clefs.
        // Params : ref, label
        $keyword1 = $this->createKeyword('ref1', 'Label 1');
        $keyword2 = $this->createKeyword('ref2', 'Label 2');
        $keyword3 = $this->createKeyword('ref3', 'Label 3');
        $keyword4 = $this->createKeyword('ref4', 'Label 4');

        // Création des associations.
        // Params : Keyword object, Predicate, Keyword subject
        $this->createAssociation($keyword1, $predicate1, $keyword2);
        $this->createAssociation($keyword3, $predicate2, $keyword4);


        $entityManager->flush();

        echo "\t\tKeywordnzation created".PHP_EOL;
    }

    /**
     * @param string $ref
     * @param string $label
     * @param string $reverseRef
     * @param string $reverseLabel
     * @param string|null $description
     * @return Keyword_Model_Predicate
     */
    protected function createPredicate($ref, $label, $reverseRef, $reverseLabel, $description=null)
    {
        $predicate = new Keyword_Model_Predicate();
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
     * @return Keyword_Model_Keyword
     */
    protected function createKeyword($ref, $label)
    {
        $keyword = new Keyword_Model_Keyword();
        $keyword->setRef($ref);
        $keyword->setLabel($label);
        $keyword->save();
        return $keyword;
    }

    /**
     * @param Keyword_Model_Keyword $object
     * @param Keyword_Model_Predicate $predicate
     * @param Keyword_Model_Keyword $subject
     */
    protected function createAssociation(Keyword_Model_Keyword $object, Keyword_Model_Predicate $predicate, Keyword_Model_Keyword $subject)
    {
        $assocation = new Keyword_Model_Association();
        $assocation->setObject($object);
        $assocation->setPredicate($predicate);
        $assocation->setSubject($subject);
        $assocation->save();
    }

}
