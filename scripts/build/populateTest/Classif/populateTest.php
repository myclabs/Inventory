<?php
/**
 * @package Classif
 */

require_once __DIR__ . '/../../populate/Classif/populate.php';



/**
 * Remplissage de la base de données avec des données de test
 * @package Classif
 */
class Classif_PopulateTest extends Classif_Populate
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

        $axis_gaz = $this->createAxis('gaz', 'Gaz');
        $axis_poste_article_75 = $this->createAxis('poste_article_75', 'Poste article 75');
        $axis_scope = $this->createAxis('scope', 'Scope', $axis_poste_article_75);
        $axis_type_deplacement = $this->createAxis('type_deplacement', 'Type de déplacement');
        $axis_axe_vide = $this->createAxis('axe_vide', 'Axe vide');

        // Création des membres.
        // Params : Axis, ref, label
        // OptionalParams : [Member] parents=[]

        $member_gaz_co2 = $this->createMember($axis_gaz, 'co2', 'CO2');
        $member_gaz_ch4 = $this->createMember($axis_gaz, 'ch4', 'CH4');

        $member_scope_1 = $this->createMember($axis_scope, '1', '1');
        $member_scope_2 = $this->createMember($axis_scope, '2', '2');
        $member_scope_3 = $this->createMember($axis_scope, '3', '3');

        $member_poste_article_75_source_fixe_combustion = $this->createMember($axis_poste_article_75, 'source_fixe_combustion', '1 - Sources fixes de combustion', [$member_scope_1]);
        $member_poste_article_75_electricite = $this->createMember($axis_poste_article_75, 'membre_sans_parent', 'Membre sans parent');

        $member_deplacement = $this->createMember($axis_type_deplacement, 'domicile_travail', 'Domicile - travail');
        $member_deplacement = $this->createMember($axis_type_deplacement, 'professionnel', 'Professionnel');

        // Création des indicateurs.
        // Params : ref, label, unitRef
        // OptionalParams : ratioUnitRef=unitRef

        $indicator_ges = $this->createIndicator('ges', 'GES', 't_co2e', 'kg_co2e');
        $indicator_chiffre_affaire = $this->createIndicator('chiffre_affaire', 'Chiffre d\'affaires', 'kiloeuro', 'euro');
        $indicator_no_context_indicator = $this->createIndicator('sans_indicateur_contextualise', 'Sans indicateur contextualisé', 't', 't');
        $indicator_related_axes = $this->createIndicator('axes_relies', 'Axes hiérarchiquement reliés', 't', 't');

        // Création des contextes.
        // Params : ref, label
        $context_general = $this->createContext('general', 'Général');
        $context_deplacement = $this->createContext('deplacement', 'Déplacements');
        $context_no_context_indicator = $this->createContext('sans_indicateur_contextualise', 'Sans indicateur contextualisé');

        $entityManager->flush();

        // Création des contexte-indicateurs.
        // Params : Context, Indicator
        // OptionalParams : [Axis]=[]
        $contextIndicator_ges_general = $this->createContextIndicator($context_general, $indicator_ges, [$axis_gaz, $axis_poste_article_75]);
        $contextIndicator_ges_deplacement = $this->createContextIndicator($context_deplacement, $indicator_ges, [$axis_gaz, $axis_poste_article_75, $axis_type_deplacement]);
        $contextIndicator_chiffre_affaire_general = $this->createContextIndicator($context_general, $indicator_chiffre_affaire);
        $contextIndicator_related_axes = $this->createContextIndicator($context_general, $indicator_related_axes, [$axis_scope, $axis_poste_article_75]);


        $entityManager->flush();

        echo "\t\tClassif created".PHP_EOL;
    }

}
