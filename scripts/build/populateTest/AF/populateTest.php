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
        // Params : ref
        // OptionalParams : Category parent=null
        $category_contenant_sous_categorie = $this->createCategory('Catégorie contenant une sous-catégorie');
        $category_sous_categorie = $this->createCategory('Sous-catégorie', $category_contenant_sous_categorie);
        $category_contenant_formulaire = $this->createCategory('Catégorie contenant un formulaire');
        $category_vide = $this->createCategory('Catégorie vide');

        // Création des af.
        // Params : Category, ref, label

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
        $group_combustion = $this->createGroup($aF_combustion, $aF_combustion->getRootGroup(), 'groupe_combustion', 'Groupe combustion');
        $nature_combustible = $this->createSelectInputList($aF_combustion, $group_combustion, 'nature_combustible', 'Nature du combustible', ['charbon' => 'Charbon', 'gaz_naturel' => 'Gaz naturel']);
        $quantite_combustible = $this->createNumericInput($aF_combustion, $group_combustion, 'quantite_combustible', 'Quantité', 't');
        // Algos
        $aF_combustion->getMainAlgo()->setExpression(':emissions_combustion;:emissions_amont;');
        // Paramètres
        $this->createAlgoNumericParameter($aF_combustion, 'fe_combustion', 'Facteur d\'émission pour la combustion', 'combustion_combustible_unite_masse');
        $this->createAlgoNumericParameter($aF_combustion, 'fe_amont', 'Facteur d\'émission pour l\'amont de la combustion', 'combustion_combustible_unite_masse');
        // Expressions et leur indexation
        $this->createAlgoNumericExpression($aF_combustion, 'emissions_combustion', 'Émissions liées à la combustion', 'quantite_combustible * fe_combustion', 't_co2e');
        $this->createAlgoNumericExpression($aF_combustion, 'emissions_amont', 'Émissions liées à la combustion', 'quantite_combustible * fe_amont', 't_co2e');
        $this->createFixedIndexForAlgoNumeric($aF_combustion->getAlgoByRef('emissions_combustion'), 'general', 'ges', ['gaz' => 'co2', 'poste_article_75' => 'source_fixe_combustion']);
        $this->createFixedIndexForAlgoNumeric($aF_combustion->getAlgoByRef('emissions_amont'), 'general', 'ges', ['gaz' => 'co2', 'poste_article_75' => 'source_fixe_combustion']);

        // Données générales
        $aF_d_g = $this->createAF($category_contenant_formulaire, 'donnees_generales', 'Données générales');
        // Composants
        $groupe_d_g = $this->createGroup($aF_d_g, $aF_d_g->getRootGroup(), 'groupe_d_g', 'Groupe données générales');
        $numericInput_chiffre_affaire = $this->createNumericInput($aF_d_g, $groupe_d_g, 'chiffre_affaire', 'Chiffre d\'affaire', 'kiloeuro');
        // Algos
        $this->createFixedIndexForAlgoNumeric($aF_d_g->getAlgoByRef($numericInput_chiffre_affaire->getRef()), 'general', 'chiffre_affaire', []);
        $aF_d_g->getMainAlgo()->setExpression(':chiffre_affaire;');

        // Création des composants.
        // Params : AF, Group, ref, label
        //  + createGroup : -
        //  + createSubAF(Repeated) : AF calledAF
        //  + createNumericInput : refUnit
        //  + createSelectInput List|Radio|Multi|Boxes : [refOtion => labelOption]
        //  + createBooleanInput : -
        // OptionalParams :
        //  + createGroup : foldaway=true
        //  + createSubAF : foldaway=true
        //  + createSubAFRepeated : foldaway=true, minimumRepetition=0, freeLabel=false
        //  + createNumericInput : defaultValue=null, defaultUncertainty=null, defaultReminder=true, required=true, enabled=true
        //  + createSelectInput List|Radio|Multi|Boxes : required=true, enabled=true
        //  + createBooleanInput : defaultValue=true
        //  help=null, visible=true


        // Création des Algos et indexation.
        //  Tip : Pour récupérer un algo à partir de l'AF : $aF->getAlgoByRef();
        //   Donc, Pour récupérer l'algo d'un champs NumericInput : $aF->getAlgoByRef($input->getRef());
        // Param : AF
        //  + createAlgoNumericExpression : ref, label, expression, refUnit
        //  + createAlgoNumericExpression : ref, label, value, uncertainty, refUnit
        //  + createFixedIndexForAlgoNumeric : Numeric numeric, refContext, refIndicator, [refAxis => refMember]
        //  + createAlgoIndexForAlgoNumeric : Numeric numeric, refContext, refIndicator, [refAxis => Selection_TextKey algo]
        //  + createAlgoNumericParameter : ref, label, refFamily
        //  + createFixedIndexForAlgoParameter : Parameter parameter, Family family, [refDimensionKeyword => refMemberKeyword]
        //  + createAlgoIndexForAlgoParameter : Parameter parameter, Family family, [refDimensionKeyword => Selection_TextKey algo]
        //  + createAlgoSelectTextkeyExpression : ref, expression
        //  + createAlgoConditionElementary : Component input, ref
        //  + createAlgoConditionExpression : ref, expression
        // OptionalParams : -

//        $this->createAlgoSelectTextkeyExpression($aF_combustion_combustible_unite_masse, 'refa2', 'expression');
//        $this->createAlgoConditionElementary($aF_combustion_combustible_unite_masse, $booleanInput, 'refa3');
//        $this->createAlgoConditionExpression($aF_combustion_combustible_unite_masse, 'refa4', 'expression');


        $entityManager->flush();

        echo "\t\tAF created".PHP_EOL;
    }

}
