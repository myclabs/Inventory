<?php

namespace Inventory\Command\PopulateDB\TestDataSet;

use AF\Domain\Action\Action;
use AF\Domain\AFLibrary;
use Calc_Value;
use Core\Translation\TranslatedString;
use Doctrine\ORM\EntityManager;
use Inventory\Command\PopulateDB\Base\AbstractPopulateAF;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Remplissage de la base de données avec des données de test
 *
 * Ce service est lazy car on veut injecter "account.myc-sense" après que ça ait été créé.
 *
 * @Injectable(lazy=true)
 */
class PopulateAF extends AbstractPopulateAF
{
    /**
     * @Inject
     * @var EntityManager
     */
    private $entityManager;

    public function run(OutputInterface $output)
    {
        $output->writeln('  <info>Populating AF</info>');

        $library = new AFLibrary($this->publicAccount, new TranslatedString('Formulaires My C-Sense', 'fr'), true);
        $library->save();

        // Création des catégories.
        $category_cont_sous_categorie = $this->createCategory($library, 'Catégorie contenant une sous-catégorie');
        $category_sous_categorie = $this->createCategory($library, 'Sous-catégorie', $category_cont_sous_categorie);
        $category_cont_formulaire = $this->createCategory($library, 'Catégorie contenant un formulaire');
        $category_vide = $this->createCategory($library, 'Catégorie vide');

        // Combustion de combustible, mesuré en unité de masse
        $af_combustion = $this->createAF($library, $category_cont_formulaire, 'Combustion de combustible, mesuré en unité de masse');
        // Composants
        $nature_combustible = $this->createSelectInputList($af_combustion, $af_combustion->getRootGroup(), 'nature_combustible', 'Nature du combustible', ['charbon' => 'Charbon', 'gaz_naturel' => 'Gaz naturel']);
        $quantite_combustible = $this->createNumericInput($af_combustion, $af_combustion->getRootGroup(), 'quantite_combustible', 'Quantité', 't');
        // Algos
        $af_combustion->getMainAlgo()->setExpression(':emissions_combustion;:emissions_amont;');
        // Paramètres
        $this->createAlgoNumericParameter($af_combustion, 'fe_combustion', 'Facteur d\'émission pour la combustion', 'combustion_combustible_unite_masse');
        $this->createAlgoNumericParameter($af_combustion, 'fe_amont', 'Facteur d\'émission pour l\'amont de la combustion', 'combustion_combustible_unite_masse');
        $this->createFixedCoordinateForAlgoParameter($af_combustion->getAlgoByRef('fe_amont'), ['processus' => 'amont_combustion']);
        $this->createFixedCoordinateForAlgoParameter($af_combustion->getAlgoByRef('fe_combustion'), ['processus' => 'combustion']);
        $this->createAlgoCoordinateForAlgoParameter($af_combustion->getAlgoByRef('fe_amont'), ['combustible' => $af_combustion->getAlgoByRef('nature_combustible')]);
        $this->createAlgoCoordinateForAlgoParameter($af_combustion->getAlgoByRef('fe_combustion'), ['combustible' => $af_combustion->getAlgoByRef('nature_combustible')]);
        // Expressions et leur indexation
        $this->createAlgoNumericExpression($af_combustion, 'emissions_combustion', 'Émissions liées à la combustion', 'quantite_combustible * fe_combustion', 't_co2e');
        $this->createAlgoNumericExpression($af_combustion, 'emissions_amont', 'Émissions liées aux processus amont de la combustion', 'quantite_combustible * fe_amont', 't_co2e');
        $this->createFixedIndexForAlgoNumeric($af_combustion->getAlgoByRef('emissions_combustion'), 'general', 'ges', ['gaz' => 'co2', 'poste_article_75' => 'source_fixe_combustion']);
        $this->createFixedIndexForAlgoNumeric($af_combustion->getAlgoByRef('emissions_amont'), 'general', 'ges', ['gaz' => 'co2', 'poste_article_75' => 'source_fixe_combustion']);

        // Données générales
        $af_d_g = $this->createAF($library, $category_cont_formulaire, 'Données générales');
        // Composants
        $numericInput_chiffre_affaire = $this->createNumericInput($af_d_g, $af_d_g->getRootGroup(), 'chiffre_affaire', 'Chiffre d\'affaire', 'kiloeuro');
        // Algos
        $this->createFixedIndexForAlgoNumeric($af_d_g->getAlgoByRef($numericInput_chiffre_affaire->getRef()), 'general', 'chiffre_affaire', []);
        $af_d_g->getMainAlgo()->setExpression(':chiffre_affaire;');

        // Formulaire avec sous-formulaires
        $aF_sous_af = $this->createAF($library, $category_cont_formulaire, 'Formulaire avec sous-formulaires');
        // Composants
        $s_f_n_r = $this->createSubAF($aF_sous_af, $aF_sous_af->getRootGroup(), 's_f_n_r', 'Sous-formulaire non répété', $af_d_g);
        $s_f_r = $this->createSubAFRepeated($aF_sous_af, $aF_sous_af->getRootGroup(), 's_f_r', 'Sous-formulaire répété', $af_combustion);


        // Formulaire de test
        $aF_test = $this->createAF($library, $category_cont_formulaire, 'Formulaire test');

        // Composants
        $g_test_vide = $this->createGroup($aF_test, $aF_test->getRootGroup(), 'g_vide', 'Groupe vide');
        $g_test_cont_champ = $this->createGroup($aF_test, $aF_test->getRootGroup(), 'g_cont_champ', 'Groupe contenant un champ');
        $g_test_cont_sous_g = $this->createGroup($aF_test, $aF_test->getRootGroup(), 'g_cont_sous_g', 'Groupe contenant un sous-groupe');
        $sous_g_test = $this->createGroup($aF_test, $g_test_cont_sous_g, 'sous_g', 'Sous-groupe');

        $s_f_n_r_test = $this->createSubAF($aF_test, $aF_test->getRootGroup(), 's_f_n_r', 'Sous-formulaire non répété', $af_d_g);

        $s_f_r_test = $this->createSubAFRepeated($aF_test, $aF_test->getRootGroup(), 's_f_r', 'Sous-formulaire répété', $af_combustion);

        $c_n_test = $this->createNumericInput($aF_test, $g_test_cont_champ, 'c_n', 'Champ numérique', 'kg_co2e.m3^-1', '1000.5', '10');
        $c_n_test_cible_activation = $this->createNumericInput($aF_test, $g_test_cont_champ, 'c_n_cible_activation', 'Champ numérique cible activation', 'kg_co2e.m3^-1', '1000.5', '10', false, false, true);
        $c_n_test_cible_setvalue = $this->createNumericInput($aF_test, $g_test_cont_champ, 'c_n_cible_setvalue', 'Champ numérique cible setvalue', 'kg_co2e.m3^-1', '1000.5', '10', false, false, true);

        $c_s_s_test = $this->createSelectInputList($aF_test, $aF_test->getRootGroup(), 'c_s_s', 'Champ sélection simple', ['opt_1' => 'Option 1', 'opt_2' => 'Option 2']);
        $c_s_s_util_cond_el_inter = $this->createSelectInputList($aF_test, $aF_test->getRootGroup(), 'c_s_s_util_cond_el_inter', 'Champ sélection simple utilisé par une condition élémentaire de l\'onglet "Interactions"', ['opt_1' => 'Option 1']);
        $c_s_s_util_cond_el_trait = $this->createSelectInputList($aF_test, $aF_test->getRootGroup(), 'c_s_s_util_cond_el_trait', 'Champ sélection simple utilisé par une condition élémentaire de l\'onglet "Traitement"', ['opt_1' => 'Option 1']);
        $c_s_s_cible_setvalue = $this->createSelectInputList($aF_test, $aF_test->getRootGroup(), 'c_s_s_cible_setvalue', 'Champ sélection simple cible d\'une action "setValue"', ['opt_1' => 'Option 1']);

        $c_s_m_test = $this->createSelectInputBoxes($aF_test, $aF_test->getRootGroup(), 'c_s_m', 'Champ sélection multiple', ['opt_1' => 'Option 1', 'opt_2' => 'Option 2']);

        $c_b_test = $this->createBooleanInput($aF_test, $aF_test->getRootGroup(), 'c_b', 'Champ booléen');
        $c_b_cible_setvalue = $this->createBooleanInput($aF_test, $aF_test->getRootGroup(), 'c_b_cible_setvalue', 'Champ booléen cible d\'une action "setValue"');

        $c_t_c_test = $this->createShortTextInput($aF_test, $aF_test->getRootGroup(), 'c_t_c', 'Champ texte court');
        $c_t_l_test = $this->createLongTextInput($aF_test, $aF_test->getRootGroup(), 'c_t_l', 'Champ texte long');

        // Interactions
        $cond_el_inter = $this->createConditionElementary($aF_test, 'cond_el_inter', $c_s_s_util_cond_el_inter);
        $cond_el_inter_util_act_setstate = $this->createConditionElementary($aF_test, 'cond_el_inter_util_act_setstate', $c_s_s_util_cond_el_inter);
        $cond_el_inter_util_act_setvalue = $this->createConditionElementary($aF_test, 'cond_el_inter_util_act_setvalue', $c_s_s_util_cond_el_inter);

        $cond_comp_inter = $this->createConditionExpression($aF_test, 'cond_comp_inter', 'a&(b|c)&d');

        $this->createActionSetState($c_n_test_cible_activation, Action::TYPE_ENABLE, $cond_el_inter_util_act_setstate);

        $calcValueToBeSet = new Calc_Value(1234.56789, 5.9);
        $this->createActionSetValue($c_n_test_cible_setvalue, Action::TYPE_SETVALUE, $calcValueToBeSet, $cond_el_inter_util_act_setvalue);
        $this->createActionSetValue($c_s_s_cible_setvalue, Action::TYPE_SETVALUE, null);
        $this->createActionSetValue($c_b_cible_setvalue, Action::TYPE_SETVALUE, true, $cond_el_inter_util_act_setvalue);

        // Algorithmes
        $aF_test->getMainAlgo()->setExpression(':c_n;');
        $this->createAlgoNumericExpression($aF_test, 'expression_num', 'Expression numérique', 'c_n*parametre', 't_co2e');
        $this->createAlgoNumericParameter($aF_test, 'parametre', 'Paramètre', 'combustion_combustible_unite_masse');
        $this->createAlgoNumericParameter($aF_test, 'parametre_2', 'Paramètre 2', 'masse_volumique_combustible');
        $this->createAlgoNumericConstant($aF_test, 'constante', 'Constante', 12345.6789, 5.9, 't_co2e.passager^-1.km^-1');
        $this->createAlgoSelectTextkeyExpression($aF_test, 'expression_sel', 'a:(b:(c:d;e:(f:g;:h)))');
        $this->createAlgoSelectTextkeyExpression($aF_test, 'expression_sel_index_algo', 'a:b');
        $this->createAlgoSelectTextkeyExpression($aF_test, 'expression_sel_coord_param', 'a:b');
        $this->createAlgoSelectTextkeyContextValue($aF_test, 'orga_coordinate', 'axis_ref_1', 'dafault_value_1');
        $this->createAlgoSelectTextkeyContextValue($aF_test, 'orga_coordinate_coord_param', 'axis_ref_2', 'dafault_value_2');
        $this->createAlgoSelectTextkeyContextValue($aF_test, 'orga_coordinate_index_algo', 'axis_ref_3', 'dafault_value_3');
        $this->createAlgoConditionExpression($aF_test, 'cond_comp', 'cond_el|condition_inexistante');
        $this->createAlgoConditionElementary($aF_test, $c_s_s_util_cond_el_trait, 'cond_el');
        // Coordonnées des algorithmes numériques de type paramètre
        $this->createFixedCoordinateForAlgoParameter($aF_test->getAlgoByRef('parametre'), ['combustible' => 'charbon']);
        $this->createAlgoCoordinateForAlgoParameter($aF_test->getAlgoByRef('parametre'), ['processus' => $aF_test->getAlgoByRef('expression_sel_coord_param')]);
        $this->createAlgoCoordinateForAlgoParameter($aF_test->getAlgoByRef('parametre_2'), ['combustible' => $aF_test->getAlgoByRef('orga_coordinate_coord_param')]);
        // Indexation des algorithmes numériques
        $this->createFixedIndexForAlgoNumeric($aF_test->getAlgoByRef('c_n'), 'general', 'ges', ['gaz' => 'co2']);
        $this->createAlgoIndexForAlgoNumeric($aF_test->getAlgoByRef('c_n'), 'general', 'ges', ['poste_article_75' => $aF_test->getAlgoByRef('expression_sel_index_algo')]);
        $this->createAlgoIndexForAlgoNumeric($aF_test->getAlgoByRef('c_n'), 'general', 'ges', ['gaz' => $aF_test->getAlgoByRef('orga_coordinate_index_algo')]);


        // Formulaire avec tous types de champs
        $af_tous_types_champs = $this->createAF($library, $category_cont_formulaire, 'Formulaire avec tout type de champ');

        // Composants
        $c_n = $this->createNumericInput($af_tous_types_champs, $af_tous_types_champs->getRootGroup(), 'c_n', 'Champ numérique', 'kg_co2e.m3^-1', null, null, true, true, true, null, true);
        $c_s_s_liste = $this->createSelectInputList($af_tous_types_champs, $af_tous_types_champs->getRootGroup(), 'c_s_s_liste', 'Champ sélection simple (liste déroulante)', ['opt_1' => 'Option 1', 'opt_2' => 'Option 2', 'opt_3' => 'Option 3', 'opt_4' => 'Option 4', 'opt_5' => 'Option 5']);
        $c_s_s_bouton = $this->createSelectInputRadio($af_tous_types_champs, $af_tous_types_champs->getRootGroup(), 'c_s_s_bouton', 'Champ sélection simple (boutons radio)', ['opt_1' => 'Option 1', 'opt_2' => 'Option 2', 'opt_3' => 'Option 3', 'opt_4' => 'Option 4', 'opt_5' => 'Option 5']);
        $c_s_m_checkbox = $this->createSelectInputBoxes($af_tous_types_champs, $af_tous_types_champs->getRootGroup(), 'c_s_m_checkbox', 'Champ sélection multiple (checkboxes)', ['opt_1' => 'Option 1', 'opt_2' => 'Option 2', 'opt_3' => 'Option 3', 'opt_4' => 'Option 4', 'opt_5' => 'Option 5']);
        $c_s_m_liste = $this->createSelectInputMulti($af_tous_types_champs, $af_tous_types_champs->getRootGroup(), 'c_s_m_liste', 'Champ sélection multiple (liste)', ['opt_1' => 'Option 1', 'opt_2' => 'Option 2', 'opt_3' => 'Option 3', 'opt_4' => 'Option 4', 'opt_5' => 'Option 5']);
        $c_b_test = $this->createBooleanInput($af_tous_types_champs, $af_tous_types_champs->getRootGroup(), 'c_b', 'Champ booléen', false, true, null, true);
        $c_t_c = $this->createShortTextInput($af_tous_types_champs, $af_tous_types_champs->getRootGroup(), 'c_t_c', 'Champ texte court', true, true, null, true);
        $c_t_l = $this->createLongTextInput($af_tous_types_champs, $af_tous_types_champs->getRootGroup(), 'c_t_l', 'Champ texte long', true, true, null, true);

        // Formulaire avec sous-formulaire répété contenant tous types de champs
        $aF_sous_AF_tous_types_champs = $this->createAF($library, $category_cont_formulaire, 'Formulaire avec sous-formulaire répété contenant tout type de champ');

        // Composants
        $s_f_r_t_t_c = $this->createSubAFRepeated($aF_sous_AF_tous_types_champs, $aF_sous_AF_tous_types_champs->getRootGroup(), 's_f_r_t_t_c', 'Sous-formulaire répété tout type de champ', $af_tous_types_champs);

        // Formulaire vide
        $this->createAF($library, $category_cont_formulaire, 'Formulaire vide');

        // Forfait émissions en fonction de la marque
        $af_forfait_marque = $this->createAF($library, $category_cont_formulaire, 'Forfait émissions en fonction de la marque');
        // Composants
        $numericInput_sans_effet = $this->createNumericInput($af_forfait_marque, $af_forfait_marque->getRootGroup(), 'sans_effet', 'Champ sans effet', 'kiloeuro', null, null, true, false);
        // Algos
        $af_forfait_marque->getMainAlgo()->setExpression(':algo_numerique_forfait_marque;');
        $this->createAlgoNumericParameter($af_forfait_marque, 'algo_numerique_forfait_marque', 'Algo forfait émissions fonction marque', 'forfait_emissions_fonction_marque');
        $this->createAlgoSelectTextkeyContextValue($af_forfait_marque, 'algo_determination_marque', 'marque', 'marque_a');
        // Coordonnées des algorithmes numériques de type paramètre
        $this->createAlgoCoordinateForAlgoParameter($af_forfait_marque->getAlgoByRef('algo_numerique_forfait_marque'), ['marque' => $af_forfait_marque->getAlgoByRef('algo_determination_marque')]);
        // Indexation
        $this->createFixedIndexForAlgoNumeric($af_forfait_marque->getAlgoByRef('algo_numerique_forfait_marque'), 'general', 'ges', ['gaz' => 'co2', 'poste_article_75' => 'source_fixe_combustion']);

        $this->entityManager->flush();
    }
}
