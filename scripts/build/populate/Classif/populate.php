<?php
/**
 * @package Classif
 */


/**
 * Remplissage de la base de données avec des données de test
 * @package Classif
 */
class Classif_Populate extends Core_Script_Action
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
        //  + createAxis : -
        // Params : ref, label
        // OptionalParams : Axis parent=null

        // Création des membres.
        //  + createMember : -
        // Params : Axis, ref, label
        // OptionalParams : [Member] parents=[]

        // Création des indicateurs.
        //  + createIndicator : -
        // Params : ref, label, unitRef
        // OptionalParams : ratioUnitRef=unitRef

        // Création des contextes.
        //  + createContext : -
        // Params : ref, label


        $entityManager->flush();


        // Création des contexte-indicateurs.
        //  + createContextIndicator : -
        // Params : Context, Indicator
        // OptionalParams : [Axis]=[]


        $entityManager->flush();

        echo "\t\tClassif created".PHP_EOL;
    }

    /**
     * @param string $ref
     * @param string $label
     * @param Classif_Model_Axis|null $narrower
     * @return Classif_Model_Axis
     */
    protected function createAxis($ref, $label, Classif_Model_Axis $narrower=null)
    {
        $axis = new Classif_Model_Axis();
        $axis->setRef($ref);
        $axis->setLabel($label);
        if ($narrower !== null) {
            $axis->setDirectNarrower($narrower);
        }
        $axis->save();
        return $axis;
    }

    /**
     * @param Classif_Model_Axis $axis
     * @param string $ref
     * @param string $label
     * @param array $parents
     * @return Classif_Model_Member
     */
    protected function createMember(Classif_Model_Axis $axis, $ref, $label, array $parents=[])
    {
        $member = new Classif_Model_Member();
        $member->setAxis($axis);
        $member->setRef($ref);
        $member->setLabel($label);
        foreach ($parents as $directParent)
        {
            $member->addDirectParent($directParent);
        }
        $member->save();
        return $member;
    }

    /**
     * @param string $ref
     * @param string $label
     * @param string $unitRef
     * @param string|null $ratioUnitRef
     * @return Classif_Model_Indicator
     */
    protected function createIndicator($ref, $label, $unitRef, $ratioUnitRef=null)
    {
        $indicator = new Classif_Model_Indicator();
        $indicator->setRef($ref);
        $indicator->setLabel($label);
        $indicator->setUnit(new \Unit\UnitAPI($unitRef));
        if ($ratioUnitRef !== null) {
            $indicator->setRatioUnit(new \Unit\UnitAPI($ratioUnitRef));
        } else {
            $indicator->setRatioUnit($indicator->getUnit());
        }
        $indicator->save();
        return $indicator;
    }

    /**
     * @param string $ref
     * @param string $label
     * @return Classif_Model_Context
     */
    protected function createContext($ref, $label)
    {
        $context = new Classif_Model_Context();
        $context->setRef($ref);
        $context->setLabel($label);
        $context->save();
        return $context;
    }

    /**
     * @param Classif_Model_Context $context
     * @param Classif_Model_Indicator $indicator
     * @param Classif_Model_Axis[] $axes
     * @return Classif_Model_ContextIndicator
     */
    protected function createContextIndicator(Classif_Model_Context $context, Classif_Model_Indicator $indicator, array $axes=[])
    {
        $contextIndicator = new Classif_Model_ContextIndicator();
        $contextIndicator->setContext($context);
        $contextIndicator->setIndicator($indicator);
        foreach ($axes as $axis) {
            $contextIndicator->addAxis($axis);
        }
        $contextIndicator->save();
        return $contextIndicator;
    }

}
