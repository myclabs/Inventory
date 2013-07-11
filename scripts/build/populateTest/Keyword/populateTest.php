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
        $predicate_est_plus_general_que = $this->createPredicate('est_plus_general_que', 'est plus général que', 'est_plus_specifique_que', 'est plus spécifique que');
        $predicate_contient = $this->createPredicate('contient', 'contient', 'fait_partie_de', 'fait partie de', 'Blabla');

        // Création des mot-clefs.
        // Params : ref, label
        $keyword_combustible = $this->createKeyword('combustible', 'combustible');
        $keyword_gaz_naturel_ = $this->createKeyword('gaz_naturel', 'gaz naturel');
        $keyword_charbon = $this->createKeyword('charbon', 'charbon');
        $keyword_processus = $this->createKeyword('processus', 'processus');
        $keyword_amont_combustion = $this->createKeyword('amont_combustion', 'amont de la combustion');
        $keyword_combustion = $this->createKeyword('combustion', 'combustion');

        // Création des associations.
        // Params : Keyword subject, Predicate, Keyword object
        $this->createAssociation($keyword_combustible, $predicate_est_plus_general_que, $keyword_gaz_naturel_);
        $this->createAssociation($keyword_combustible, $predicate_est_plus_general_que, $keyword_charbon);


        $entityManager->flush();

        echo "\t\tKeyword created".PHP_EOL;
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
    protected function createAssociation(Keyword_Model_Keyword $subject, Keyword_Model_Predicate $predicate, Keyword_Model_Keyword $object)
    {
        $assocation = new Keyword_Model_Association();
        $assocation->setSubject($subject);
        $assocation->setPredicate($predicate);
        $assocation->setObject($object);
        $assocation->save();
    }

}
