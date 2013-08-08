<?php
/**
 * @package Classif
 */

require_once __DIR__ . '/../../populate/Classif/populate.php';


/**
 * Remplissage de la base de données avec des données de test
 * @package Classif
 */
class Classif_PopulateTestDWUpToDate extends Classif_Populate
{

    /**
     * {@inheritdoc}
     */
    public function runEnvironment($environment)
    {
        $entityManagers = Zend_Registry::get('EntityManagers');
        /** @var $entityManager \Doctrine\ORM\EntityManager */
        $entityManager = $entityManagers['default'];


        // Création des axes.
        // Params : ref, label
        // OptionalParams : Axis parent=null

        $axis_poste_article_75 = $this->createAxis('poste_article_75', 'Poste article 75');
        $axis_scope = $this->createAxis('scope', 'Scope', $axis_poste_article_75);

        // Création des membres.
        // Params : Axis, ref, label
        // OptionalParams : [Member] parents=[]

        $member_scope_1 = $this->createMember($axis_scope, '1', '1');
        $member_scope_2 = $this->createMember($axis_scope, '2', '2');

        // Création des indicateurs.
        // Params : ref, label, unitRef
        // OptionalParams : ratioUnitRef=unitRef

        $indicator_ges = $this->createIndicator('ges', 'GES', 't_co2e', 'kg_co2e');

        // Création des contextes.
        // Params : ref, label

        // $context_general = $this->createContext('general', 'Général');

        $entityManager->flush();

        // Création des contexte-indicateurs.
        // Params : Context, Indicator
        // OptionalParams : [Axis]=[]

        // $contextIndicator_ges_general = $this->createContextIndicator($context_general, $indicator_ges, [$axis_gaz, $axis_poste_article_75]);


        $entityManager->flush();

        echo "\t\tClassif created".PHP_EOL;
    }

}
