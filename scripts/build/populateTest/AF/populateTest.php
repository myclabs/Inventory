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
        $category_cont_sous_categorie = $this->createCategory('Catégorie contenant une sous-catégorie');
        $category_sous_categorie = $this->createCategory('Sous-catégorie', $category_cont_sous_categorie);
        $category_cont_formulaire = $this->createCategory('Catégorie contenant un formulaire');
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
        $aF_combustion = $this->createAF($category_cont_formulaire, 'combustion_combustible_unite_masse', 'Combustion de combustible, mesuré en unité de masse');
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
        $aF_d_g = $this->createAF($category_cont_formulaire, 'donnees_generales', 'Données générales');
        // Composants
        $numericInput_chiffre_affaire = $this->createNumericInput($aF_d_g, $aF_d_g->getRootGroup(), 'chiffre_affaire', 'Chiffre d\'affaire', 'kiloeuro');
        // Algos
        $this->createFixedIndexForAlgoNumeric($aF_d_g->getAlgoByRef($numericInput_chiffre_affaire->getRef()), 'general', 'chiffre_affaire', []);
        $aF_d_g->getMainAlgo()->setExpression(':chiffre_affaire;');

        // Formulaire avec sous-formulaires
        $aF_sous_af = $this->createAF($category_cont_formulaire, 'af_avec_sous_af', 'Formulaire avec sous-formulaires');
        // Composants
        $s_f_n_r = $this->createSubAF($aF_sous_af, $aF_sous_af->getRootGroup(), 's_f_n_r', 'Sous-formulaire non rép.', $aF_d_g);
        $s_f_r = $this->createSubAFRepeated($aF_sous_af, $aF_sous_af->getRootGroup(), 's_f_r', 'Sous-formulaire rép.', $aF_combustion);


        // Formulaire de test
        $aF_test = $this->createAF($category_cont_formulaire, 'formulaire_test', 'Formulaire test');

        // Composants
        $g_test_vide = $this->createGroup($aF_test, $aF_test->getRootGroup(), 'g_vide', 'Groupe vide');
        $g_test_cont_champ = $this->createGroup($aF_test, $aF_test->getRootGroup(), 'g_cont_champ', 'Groupe contenant un champ');
        $g_test_cont_sous_g = $this->createGroup($aF_test, $aF_test->getRootGroup(), 'g_cont_sous_g', 'Groupe contenant un sous-groupe');
        $sous_g_test = $this->createGroup($aF_test, $g_test_cont_sous_g, 'sous_g', 'Sous-groupe');

        $s_f_n_r_test = $this->createSubAF($aF_test, $aF_test->getRootGroup(), 's_f_n_r', 'Sous-formulaire non rép.', $aF_d_g);

        $s_f_r_test = $this->createSubAFRepeated($aF_test, $aF_test->getRootGroup(), 's_f_r', 'Sous-formulaire rép.', $aF_combustion);

        $c_n_test = $this->createNumericInput($aF_test, $g_test_cont_champ, 'c_n', 'Champ numérique', 'kg_co2e.m3^-1', '1000.5', '10');
        $c_n_test_cible_activation = $this->createNumericInput($aF_test, $g_test_cont_champ, 'c_n_cible_activation', 'Champ numérique cible activation', 'kg_co2e.m3^-1', '1000.5', '10', false, false, true);
        $c_n_test_cible_setvalue = $this->createNumericInput($aF_test, $g_test_cont_champ, 'c_n_cible_setvalue', 'Champ numérique cible setvalue', 'kg_co2e.m3^-1', '1000.5', '10', false, false, true);

        $c_s_s_test = $this->createSelectInputList($aF_test, $aF_test->getRootGroup(), 'c_s_s', 'Champ sélection simple', ['opt_1' => 'Option 1', 'opt_2' => 'Option 2']);
        $c_s_s_util_cond_el_inter = $this->createSelectInputList($aF_test, $aF_test->getRootGroup(), 'c_s_s_util_cond_el_inter', 'Champ sélection simple utilisé par une condition élémentaire de l\'onglet "Interactions"', ['opt_1' => 'Option 1']);
        $c_s_s_util_cond_el_trait = $this->createSelectInputList($aF_test, $aF_test->getRootGroup(), 'c_s_s_util_cond_el_trait', 'Champ sélection simple utilisé par une condition élémentaire de l\'onglet "Traitement"', ['opt_1' => 'Option 1']);
        $c_s_s_cible_setvalue = $this->createSelectInputList($aF_test, $aF_test->getRootGroup(), 'c_s_s_cible_setvalue', 'Champ sélection simple cible d\'une action "setValue"', ['opt_1' => 'Option 1']);

        $c_s_m_test = $this->createSelectInputBoxes($aF_test, $aF_test->getRootGroup(), 'c_s_m', 'Champ sélection multiple', ['opt_1' => 'Option 1']);

        $c_b_test = $this->createBooleanInput($aF_test, $aF_test->getRootGroup(), 'c_b', 'Champ booléen');
        $c_b_cible_setvalue = $this->createBooleanInput($aF_test, $aF_test->getRootGroup(), 'c_b_cible_setvalue', 'Champ booléen cible d\'une action "setValue"');

        $c_t_c_test = $this->createShortTextInput($aF_test, $aF_test->getRootGroup(), 'c_t_c', 'Champ texte court');
        $c_t_l_test = $this->createLongTextInput($aF_test, $aF_test->getRootGroup(), 'c_t_l', 'Champ texte long');

        // Interactions
        $cond_el_inter = $this->createConditionElementary($aF_test, 'cond_el_inter', $c_s_s_util_cond_el_inter);
        $cond_el_inter_util_act_setstate = $this->createConditionElementary($aF_test, 'cond_el_inter_util_act_setstate', $c_s_s_util_cond_el_inter);
        $cond_el_inter_util_act_setvalue = $this->createConditionElementary($aF_test, 'cond_el_inter_util_act_setvalue', $c_s_s_util_cond_el_inter);

        $cond_comp_inter = $this->createConditionExpression($aF_test, 'cond_comp_inter', 'a&(b|c)&d');

        $this->createActionSetState($c_n_test_cible_activation, AF_Model_Action::TYPE_ENABLE, $cond_el_inter_util_act_setstate);

        $calcValueToBeSet = new Calc_Value(1234.56789, 5.9);
        $this->createActionSetValue($c_n_test_cible_setvalue, AF_Model_Action::TYPE_SETVALUE, $calcValueToBeSet, $cond_el_inter_util_act_setvalue);
        $this->createActionSetValue($c_s_s_cible_setvalue, AF_Model_Action::TYPE_SETVALUE, null);
        $this->createActionSetValue($c_b_cible_setvalue, AF_Model_Action::TYPE_SETVALUE, true, $cond_el_inter_util_act_setvalue);

        // Algorithmes
        $aF_test->getMainAlgo()->setExpression(':c_n;');
        $this->createAlgoNumericExpression($aF_test, 'expression_num', 'Expression numérique', 'c_n*parametre', 't_co2e');
        $this->createAlgoNumericParameter($aF_test, 'parametre', 'Paramètre', 'combustion_combustible_unite_masse');
        $this->createAlgoNumericConstant($aF_test, 'constante', 'Constante', 12345.6789, 5.9, 't_co2e.passager^-1.km^-1');
        $this->createAlgoSelectTextkeyExpression($aF_test, 'expression_sel', 'a:(b:(c:d;e:(f:g;:h)))');
        $this->createAlgoSelectTextkeyExpression($aF_test, 'expression_sel_index_algo', 'a:b');
        $this->createAlgoSelectTextkeyExpression($aF_test, 'expression_sel_coord_param', 'a:b');
        $this->createAlgoConditionExpression($aF_test, 'cond_comp', 'cond_el|condition_inexistante');
        $this->createAlgoConditionElementary($aF_test, $c_s_s_util_cond_el_trait, 'cond_el');
        // Coordonnées des algorithmes numériques de type paramètre
        $this->createFixedCoordinateForAlgoParameter($aF_test->getAlgoByRef('parametre'), ['combustible' => 'charbon']);
        $this->createAlgoCoordinateForAlgoParameter($aF_test->getAlgoByRef('parametre'), ['processus' => $aF_test->getAlgoByRef('expression_sel_coord_param')]);
        // Indexation des algorithmes numériques
        $this->createFixedIndexForAlgoNumeric($aF_test->getAlgoByRef('c_n'), 'general', 'ges', ['gaz' => 'co2']);
        $this->createAlgoIndexForAlgoNumeric($aF_test->getAlgoByRef('c_n'), 'general', 'ges', ['poste_article_75' => $aF_test->getAlgoByRef('expression_sel_index_algo')]);

//        $this->createAlgoSelectTextkeyExpression($aF_combustion_combustible_unite_masse, 'refa2', 'expression');
//        $this->createAlgoConditionElementary($aF_combustion_combustible_unite_masse, $booleanInput, 'refa3');
//        $this->createAlgoConditionExpression($aF_combustion_combustible_unite_masse, 'refa4', 'expression');

        // Formulaire avec tous types de champs
        $aF_tous_types_champs = $this->createAF($category_cont_formulaire, 'formulaire_tous_types_champ', 'Formulaire avec tout type de champ');

        // Composants
        $c_n = $this->createNumericInput($aF_tous_types_champs, $aF_tous_types_champs->getRootGroup(), 'c_n', 'Champ numérique', 'kg_co2e.m3^-1', null, null, true, true, true, null, true);
        $c_s_s_liste = $this->createSelectInputList($aF_tous_types_champs, $aF_tous_types_champs->getRootGroup(), 'c_s_s_liste', 'Champ sélection simple (liste déroulante)', ['opt_1' => 'Option 1', 'opt_2' => 'Option 2']);
        $c_s_s_bouton = $this->createSelectInputRadio($aF_tous_types_champs, $aF_tous_types_champs->getRootGroup(), 'c_s_s_bouton', 'Champ sélection simple (boutons radio)', ['opt_1' => 'Option 1', 'opt_2' => 'Option 2']);
        $c_s_m_checkbox = $this->createSelectInputBoxes($aF_tous_types_champs, $aF_tous_types_champs->getRootGroup(), 'c_s_m_checkbox', 'Champ sélection multiple (checkboxes)', ['opt_1' => 'Option 1', 'opt_2' => 'Option 2']);
        $c_s_m_list = $this->createSelectInputMulti($aF_tous_types_champs, $aF_tous_types_champs->getRootGroup(), 'c_s_m_list', 'Champ sélection multiple (liste)', ['opt_1' => 'Option 1', 'opt_2' => 'Option 2']);
        $c_b_test = $this->createBooleanInput($aF_tous_types_champs, $aF_tous_types_champs->getRootGroup(), 'c_b', 'Champ booléen', false, true, null, true);
        $c_t_c = $this->createShortTextInput($aF_tous_types_champs, $aF_tous_types_champs->getRootGroup(), 'c_t_c', 'Champ texte court', true, true, null, true);
        $c_t_l = $this->createLongTextInput($aF_tous_types_champs, $aF_tous_types_champs->getRootGroup(), 'c_t_l', 'Champ texte long', true, true, null, true);

        // Formulaire vide
        $aF_vide = $this->createAF($category_cont_formulaire, 'formulaire_vide', 'Formulaire vide');

        $entityManager->flush();

        echo "\t\tAF created".PHP_EOL;
    }

}
