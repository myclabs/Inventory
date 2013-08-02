<?php
/**
 * @package AF
 */

require_once __DIR__ . '/../../populate/AF/populate.php';

/**
 * Remplissage de la base de données avec des données de test
 */
class AF_PopulateTest extends AF_Populate
{

    /**
     * {@inheritdoc}
     */
    public function runEnvironment($environment)
    {
        $entityManagers = Zend_Registry::get('EntityManagers');
        /** @var $entityManager \Doctrine\ORM\EntityManager */
        $entityManager = $entityManagers['default'];


        // Création des catégories.
        $category_contenant_sous_categorie = $this->createCategory('Catégorie contenant une sous-catégorie');
        $category_sous_categorie = $this->createCategory('Sous-catégorie', $category_contenant_sous_categorie);
        $category_contenant_formulaire = $this->createCategory('Catégorie contenant un formulaire');
        $category_vide = $this->createCategory('Catégorie vide');

        // Formulaire génériques, paramétrage commenté pour exemple
        // Composants
        // $numericInput = $this->createNumericInput($aF_combustion, $group_combustion, 'refn1', 'Label Numeric 1', 'm', 25, 10, true);
        // $selectInputList = $this->createSelectInputList($aF_combustion, $group_combustion, 'refs1', 'Label Select 1', ['o1' => 'Option 1', 'o2' => 'Option 2']);
        // $booleanInput = $this->createBooleanInput($aF_combustion, $group_combustion, 'refb1', 'Label Select 1', true);
        // Algos
        // $this->createFixedIndexForAlgoNumeric($aF_combustion->getAlgoByRef($numericInput->getRef()), 'general', 'ges', []);
        // $this->createAlgoNumericConstant($aF_combustion, 'refa1', 'Label 1', 10, 5, 'm');

        // Combustion de combustible, mesuré en unité de masse
        $aF_combustion = $this->createAF($category_contenant_formulaire, 'combustion_combustible_unite_masse', 'Combustion de combustible, mesuré en unité de masse');
        // Composants
        $nature_combustible = $this->createSelectInputList($aF_combustion, $aF_combustion->getRootGroup(), 'nature_combustible', 'Nature du combustible', ['charbon' => 'Charbon', 'gaz_naturel' => 'Gaz naturel']);
        $quantite_combustible = $this->createNumericInput($aF_combustion, $aF_combustion->getRootGroup(), 'quantite_combustible', 'Quantité', 't');
        // Algos
        $aF_combustion->getMainAlgo()->setExpression(':emissions_combustion;:emissions_amont;');
        // Paramètres
        $this->createAlgoNumericParameter($aF_combustion, 'fe_combustion', 'Facteur d\'émission pour la combustion', 'combustion_combustible_unite_masse');
        $this->createAlgoNumericParameter($aF_combustion, 'fe_amont', 'Facteur d\'émission pour l\'amont de la combustion', 'combustion_combustible_unite_masse');
        $this->createFixedCoordinateForAlgoParameter($aF_combustion->getAlgoByRef('fe_amont'), ['processus' => 'amont_combustion']);
        $this->createFixedCoordinateForAlgoParameter($aF_combustion->getAlgoByRef('fe_combustion'), ['processus' => 'combustion']);
        $this->createAlgoCoordinateForAlgoParameter($aF_combustion->getAlgoByRef('fe_amont'), ['combustible' => $aF_combustion->getAlgoByRef('nature_combustible')]);
        $this->createAlgoCoordinateForAlgoParameter($aF_combustion->getAlgoByRef('fe_combustion'), ['combustible' => $aF_combustion->getAlgoByRef('nature_combustible')]);
        // Expressions et leur indexation
        $this->createAlgoNumericExpression($aF_combustion, 'emissions_combustion', 'Émissions liées à la combustion', 'quantite_combustible * fe_combustion', 't_co2e');
        $this->createAlgoNumericExpression($aF_combustion, 'emissions_amont', 'Émissions liées aux processus amont de la combustion', 'quantite_combustible * fe_amont', 't_co2e');
        $this->createFixedIndexForAlgoNumeric($aF_combustion->getAlgoByRef('emissions_combustion'), 'general', 'ges', ['gaz' => 'co2', 'poste_article_75' => 'source_fixe_combustion']);
        $this->createFixedIndexForAlgoNumeric($aF_combustion->getAlgoByRef('emissions_amont'), 'general', 'ges', ['gaz' => 'co2', 'poste_article_75' => 'source_fixe_combustion']);

        // Données générales
        $aF_d_g = $this->createAF($category_contenant_formulaire, 'donnees_generales', 'Données générales');
        // Composants
        $numericInput_chiffre_affaire = $this->createNumericInput($aF_d_g, $aF_d_g->getRootGroup(), 'chiffre_affaire', 'Chiffre d\'affaire', 'kiloeuro');
        // Algos
        $this->createFixedIndexForAlgoNumeric($aF_d_g->getAlgoByRef($numericInput_chiffre_affaire->getRef()), 'general', 'chiffre_affaire', []);
        $aF_d_g->getMainAlgo()->setExpression(':chiffre_affaire;');

        // Formulaire avec sous-formulaires
        $aF_sous_af = $this->createAF($category_contenant_formulaire, 'af_avec_sous_af', 'Formulaire avec sous-formulaires');
        // Composants
        $sous_formulaire_non_repete = $this->createSubAF($aF_sous_af, $aF_sous_af->getRootGroup(), 'sous_formulaire_non_repete', 'Sous-formulaire non répété', $aF_d_g);
        $sous_formulaire_repete = $this->createSubAFRepeated($aF_sous_af, $aF_sous_af->getRootGroup(), 'sous_formulaire_repete', 'Sous-formulaire répété', $aF_combustion);


        // Formulaire de test
        $aF_test = $this->createAF($category_contenant_formulaire, 'formulaire_test', 'Formulaire test');

        // Composants
        $groupe_test_vide = $this->createGroup($aF_test, $aF_test->getRootGroup(), 'groupe_vide', 'Groupe vide');
        $groupe_test_contenant_champ = $this->createGroup($aF_test, $aF_test->getRootGroup(), 'groupe_contenant_champ', 'Groupe contenant un champ');
        $groupe_test_contenant_sous_groupe = $this->createGroup($aF_test, $aF_test->getRootGroup(), 'groupe_contenant_sous_groupe', 'Groupe contenant un sous-groupe');
        $sous_groupe_test = $this->createGroup($aF_test, $groupe_test_contenant_sous_groupe, 'sous_groupe', 'Sous-groupe');

        $sous_formulaire_non_repete_test = $this->createSubAF($aF_test, $aF_test->getRootGroup(), 'sous_formulaire_non_repete', 'Sous-formulaire non répété', $aF_d_g);

        $sous_formulaire_repete_test = $this->createSubAFRepeated($aF_test, $aF_test->getRootGroup(), 'sous_formulaire_repete', 'Sous-formulaire répété', $aF_combustion);

        $champ_numerique_test = $this->createNumericInput($aF_test, $groupe_test_contenant_champ, 'champ_numerique', 'Champ numérique', 'kg_co2e.m3^-1', '1000.5', '10');
        $champ_numerique_test_cible_activation = $this->createNumericInput($aF_test, $groupe_test_contenant_champ, 'champ_numerique_cible_activation', 'Champ numérique cible activation', 'kg_co2e.m3^-1', '1000.5', '10', false, false, true);
        $champ_numerique_test_cible_setvalue = $this->createNumericInput($aF_test, $groupe_test_contenant_champ, 'champ_numerique_cible_setvalue', 'Champ numérique cible setvalue', 'kg_co2e.m3^-1', '1000.5', '10', false, false, true);

        $champ_selection_simple_test = $this->createSelectInputList($aF_test, $aF_test->getRootGroup(), 'champ_selection_simple', 'Champ sélection simple', ['option_1' => 'Option 1', 'option_2' => 'Option 2']);
        $champ_selection_simple_utilise_condition_elementaire_interaction = $this->createSelectInputList($aF_test, $aF_test->getRootGroup(), 'champ_selection_simple_utilise_condition_elementaire_interaction', 'Champ sélection simple utilisé par une condition élémentaire de l\'onglet "Interactions"', ['option_1' => 'Option 1']);
        $champ_selection_simple_utilise_condition_elementaire_traitement = $this->createSelectInputList($aF_test, $aF_test->getRootGroup(), 'champ_selection_simple_utilise_condition_elementaire_traitement', 'Champ sélection simple utilisé par une condition élémentaire de l\'onglet "Traitement"', ['option_1' => 'Option 1']);
        $champ_selection_simple_cible_setvalue = $this->createSelectInputList($aF_test, $aF_test->getRootGroup(), 'champ_selection_simple_cible_setvalue', 'Champ sélection simple cible d\'une action "setValue"', ['option_1' => 'Option 1']);

        $champ_selection_multiple_test = $this->createSelectInputBoxes($aF_test, $aF_test->getRootGroup(), 'champ_selection_multiple', 'Champ sélection multiple', ['option_1' => 'Option 1']);

        $champ_booleen_test = $this->createBooleanInput($aF_test, $aF_test->getRootGroup(), 'champ_booleen', 'Champ booléen');
        $champ_booleen_cible_setvalue = $this->createBooleanInput($aF_test, $aF_test->getRootGroup(), 'champ_booleen_cible_setvalue', 'Champ booléen cible d\'une action "setValue"');

        $champ_texte_court_test = $this->createShortTextInput($aF_test, $aF_test->getRootGroup(), 'champ_texte_court', 'Champ texte court');
        $champ_texte_long_test = $this->createLongTextInput($aF_test, $aF_test->getRootGroup(), 'champ_texte_long', 'Champ texte long');

        // Interactions
        $condition_elementaire_interactions = $this->createConditionElementary($aF_test, 'condition_elementaire_interactions', $champ_selection_simple_utilise_condition_elementaire_interaction);
        $condition_elementaire_interactions_utilisee_action_setstate = $this->createConditionElementary($aF_test, 'condition_elementaire_interactions_utilisee_action_setstate', $champ_selection_simple_utilise_condition_elementaire_interaction);
        $condition_elementaire_interactions_utilisee_action_setvalue = $this->createConditionElementary($aF_test, 'condition_elementaire_interactions_utilisee_action_setvalue', $champ_selection_simple_utilise_condition_elementaire_interaction);

        $condition_composee_interactions = $this->createConditionExpression($aF_test, 'condition_composee_interactions', 'a&(b|c)&d');

        $this->createActionSetState($champ_numerique_test_cible_activation, AF_Model_Action::TYPE_ENABLE, $condition_elementaire_interactions_utilisee_action_setstate);

        $calcValueToBeSet = new Calc_Value(1234.56789, 5.9);
        $this->createActionSetValue($champ_numerique_test_cible_setvalue, AF_Model_Action::TYPE_SETVALUE, $calcValueToBeSet, $condition_elementaire_interactions_utilisee_action_setvalue);
        $this->createActionSetValue($champ_selection_simple_cible_setvalue, AF_Model_Action::TYPE_SETVALUE, null);
        $this->createActionSetValue($champ_booleen_cible_setvalue, AF_Model_Action::TYPE_SETVALUE, true, $condition_elementaire_interactions_utilisee_action_setvalue);

        // Algorithmes
        $aF_test->getMainAlgo()->setExpression(':champ_numerique;');
        $this->createAlgoNumericExpression($aF_test, 'expression_numerique', 'Expression numérique', 'champ_numerique*parametre', 't_co2e');
        $this->createAlgoNumericParameter($aF_test, 'parametre', 'Paramètre', 'combustion_combustible_unite_masse');
        $this->createAlgoNumericConstant($aF_test, 'constante', 'Constante', 12345.6789, 5.9, 't_co2e.passager^-1.km^-1');
        $this->createAlgoSelectTextkeyExpression($aF_test, 'expression_selection', 'a:(b:(c:d;e:(f:g;:h)))');
        $this->createAlgoSelectTextkeyExpression($aF_test, 'expression_selection_indexation_algorithme', 'a:b');
        $this->createAlgoSelectTextkeyExpression($aF_test, 'expression_selection_coordonnee_parametre', 'a:b');
        $this->createAlgoConditionExpression($aF_test, 'condition_composee', 'condition_elementaire|condition_inexistante');
        $this->createAlgoConditionElementary($aF_test, $champ_selection_simple_utilise_condition_elementaire_traitement, 'condition_elementaire');
        // Coordonnées des algorithmes numériques de type paramètre
        $this->createFixedCoordinateForAlgoParameter($aF_test->getAlgoByRef('parametre'), ['combustible' => 'charbon']);
        $this->createAlgoCoordinateForAlgoParameter($aF_test->getAlgoByRef('parametre'), ['processus' => $aF_test->getAlgoByRef('expression_selection_coordonnee_parametre')]);
        // Indexation des algorithmes numériques
        $this->createFixedIndexForAlgoNumeric($aF_test->getAlgoByRef('champ_numerique'), 'general', 'ges', ['gaz' => 'co2']);
        $this->createAlgoIndexForAlgoNumeric($aF_test->getAlgoByRef('champ_numerique'), 'general', 'ges', ['poste_article_75' => $aF_test->getAlgoByRef('expression_selection_indexation_algorithme')]);

//        $this->createAlgoSelectTextkeyExpression($aF_combustion_combustible_unite_masse, 'refa2', 'expression');
//        $this->createAlgoConditionElementary($aF_combustion_combustible_unite_masse, $booleanInput, 'refa3');
//        $this->createAlgoConditionExpression($aF_combustion_combustible_unite_masse, 'refa4', 'expression');

        // Formulaire avec tous types de champs
        $aF_tous_types_champs = $this->createAF($category_contenant_formulaire, 'formulaire_tous_types_champ', 'Formulaire avec tout type de champ');

        // Composants
        $champ_numerique = $this->createNumericInput($aF_tous_types_champs, $aF_tous_types_champs->getRootGroup(), 'champ_numerique', 'Champ numérique', 'kg_co2e.m3^-1', null, null, true, true, true, null, true);
        $champ_selection_simple_liste = $this->createSelectInputList($aF_tous_types_champs, $aF_tous_types_champs->getRootGroup(), 'champ_selection_simple_liste', 'Champ sélection simple (liste déroulante)', ['option_1' => 'Option 1', 'option_2' => 'Option 2']);
        $champ_selection_simple_bouton = $this->createSelectInputRadio($aF_tous_types_champs, $aF_tous_types_champs->getRootGroup(), 'champ_selection_simple_bouton', 'Champ sélection simple (boutons radio)', ['option_1' => 'Option 1', 'option_2' => 'Option 2']);
        $champ_selection_multi_checkbox = $this->createSelectInputBoxes($aF_tous_types_champs, $aF_tous_types_champs->getRootGroup(), 'champ_selection_multi_checkbox', 'Champ sélection multiple (checkboxes)', ['option_1' => 'Option 1', 'option_2' => 'Option 2']);
        $champ_selection_multi_list = $this->createSelectInputMulti($aF_tous_types_champs, $aF_tous_types_champs->getRootGroup(), 'champ_selection_multi_list', 'Champ sélection multiple (liste)', ['option_1' => 'Option 1', 'option_2' => 'Option 2']);
        $champ_booleen_test = $this->createBooleanInput($aF_tous_types_champs, $aF_tous_types_champs->getRootGroup(), 'champ_booleen', 'Champ booléen', false, true, null, true);
        $champ_texte_court = $this->createShortTextInput($aF_tous_types_champs, $aF_tous_types_champs->getRootGroup(), 'champ_texte_court', 'Champ texte court', true, true, null, true);
        $champ_texte_long = $this->createLongTextInput($aF_tous_types_champs, $aF_tous_types_champs->getRootGroup(), 'champ_texte_long', 'Champ texte long', true, true, null, true);

        // Formulaire vide
        $aF_vide = $this->createAF($category_contenant_formulaire, 'formulaire_vide', 'Formulaire vide');

        $entityManager->flush();

        echo "\t\tAF created".PHP_EOL;
    }

}
