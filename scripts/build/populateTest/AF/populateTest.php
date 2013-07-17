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

        // Combustion de combustible, mesuré en unité de masse
        $aF_combustion_combustible_unite_masse = $this->createAF($category_contenant_formulaire, 'combustion_combustible_unite_masse', 'Combustion de combustible, mesuré en unité de masse');
        $group1 = $this->createGroup($aF_combustion_combustible_unite_masse, $aF_combustion_combustible_unite_masse->getRootGroup(), 'refg1', 'Label Group 1');
        $numericInput = $this->createNumericInput($aF_combustion_combustible_unite_masse, $group1, 'refn1', 'Label Numeric 1', 'm', 25, 10, true);
        $selectInputList = $this->createSelectInputList($aF_combustion_combustible_unite_masse, $group1, 'refs1', 'Label Select 1', ['o1' => 'Option 1', 'o2' => 'Option 2']);
        $booleanInput = $this->createBooleanInput($aF_combustion_combustible_unite_masse, $group1, 'refb1', 'Label Select 1', true);
        $aF_combustion_combustible_unite_masse->getMainAlgo()->setExpression('a:b;');
        $this->createAlgoNumericConstant($aF_combustion_combustible_unite_masse, 'refa1', 'Label 1', 10, 5, 'm');
        $this->createAlgoNumericInput($aF_combustion_combustible_unite_masse, $numericInput, 'general', 'ges');

        // Données générales
        $aF_donnees_generales = $this->createAF($category_contenant_formulaire, 'donnees_generales', 'Données générales');
        $groupe_donnees_generales = $this->createGroup($aF_donnees_generales, $aF_donnees_generales->getRootGroup(), 'groupe_donnees_generales', 'Groupe données générales');
        $numericInput_chiffre_affaire = $this->createNumericInput($aF_donnees_generales, $groupe_donnees_generales, 'chiffre_affaire', 'Chiffre d\'affaire', 'kiloeuro');
        $this->createAlgoNumericInput($aF_donnees_generales, $numericInput_chiffre_affaire, 'general', 'chiffre_affaire');
        $aF_donnees_generales->getMainAlgo()->setExpression(':chiffre_affaire;');

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


        // Création des Algos.
        // Param : AF
        //  + createAlgoNumericExpression : ref, label, expression, refUnit
        //  + createAlgoNumericParameter : ref, label, refFamily
        //  + createAlgoNumericExpression : ref, label, value, uncertainty, refUnit
        //  + createAlgoNumericInput : Component input, refContext, refIndicator
        //  + createAlgoSelectTextkeyExpression : ref, expression
        //  + createAlgoConditionElementary : Component input, ref, expression
        //  + createAlgoConditionExpression : ref, expression
        // OptionalParams : -

//        $this->createAlgoSelectTextkeyExpression($aF_combustion_combustible_unite_masse, 'refa2', 'expression');
//        $this->createAlgoConditionElementary($aF_combustion_combustible_unite_masse, $booleanInput, 'refa3', 'expression');
//        $this->createAlgoConditionExpression($aF_combustion_combustible_unite_masse, 'refa4', 'expression');


        $entityManager->flush();

        echo "\t\tAF created".PHP_EOL;
    }

}
