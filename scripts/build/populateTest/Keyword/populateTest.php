<?php
/**
 * @package Keyword
 */

require_once __DIR__ . '/../../populate/Keyword/populate.php';

/**
 * Remplissage de la base de données avec des données de test
 * @package Keyword
 */
class Keyword_PopulateTest extends Keyword_Populate
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
        $predicate_est_plus_general_que = $this->createPredicate('est_plus_general_que', 'est plus général que', 'est_plus_specifique_que', 'est plus spécifique que');
        $predicate_contient = $this->createPredicate('contient', 'contient', 'fait_partie_de', 'fait partie de', 'Blabla');

        // Création des mot-clefs.
        $keyword_combustible = $this->createKeyword('combustible', 'combustible');
        $keyword_gaz_naturel_ = $this->createKeyword('gaz_naturel', 'gaz naturel');
        $keyword_charbon = $this->createKeyword('charbon', 'charbon');
        $keyword_processus = $this->createKeyword('processus', 'processus');
        $keyword_amont_combustion = $this->createKeyword('amont_combustion', 'amont de la combustion');
        $keyword_combustion = $this->createKeyword('combustion', 'combustion');


        $entityManager->flush();


        // Création des associations.
        $this->createAssociation($keyword_combustible, $predicate_est_plus_general_que, $keyword_gaz_naturel_);
        $this->createAssociation($keyword_combustible, $predicate_est_plus_general_que, $keyword_charbon);


        $entityManager->flush();

        echo "\t\tKeyword created".PHP_EOL;
    }

}
