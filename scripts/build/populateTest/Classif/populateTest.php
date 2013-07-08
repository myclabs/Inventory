<?php
/**
 * @package Classif
 */


/**
 * Remplissage de la base de données avec des données de test
 * @package Classif
 */
class Classif_PopulateTest extends Core_Script_Action
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
        $axis1 = $this->createAxis('ref1', 'Label 1');
        $axis11 = $this->createAxis('ref11', 'Label 11', $axis1);
        $axis2 = $this->createAxis('ref11', 'Label 11', $axis1);

        // Création des membres.
        // Params : Axis, ref, label
        // OptionalParams : [Member] parents=[]
        $member11a = $this->createMember($axis11, 'ref11a', 'Label 11 A');
        $member11b = $this->createMember($axis11, 'ref11b', 'Label 11 B');
        $member1a = $this->createMember($axis1, 'ref1a', 'Label 1 A', [$member11a]);
        $member1b = $this->createMember($axis1, 'ref1b', 'Label 1 B', [$member11b]);
        $member2a = $this->createMember($axis2, 'ref2a', 'Label 2 A');

        // Création des indicateurs.
        // Params : ref, label, unitRef
        // OptionalParams : ratioUnitRef=unitRef
        $indicator1 = $this->createIndicator('ref1', 'Label 1', 'l');
        $indicator2 = $this->createIndicator('ref2', 'Label 2', 'm', 'km');

        // Création des contextes.
        // Params : ref, label
        $context1 = $this->createContext('ref1', 'Label 1');
        $context2 = $this->createContext('ref2', 'Label 2');

        // Création des contexte-indicateurs.
        // Params : Context, Indicator
        // OptionalParams : [Axis]=[]
        $contextIndicator1 = $this->createContextIndicator($context1, $indicator1);
        $contextIndicator2 = $this->createContextIndicator($context2, $indicator2, [$axis1, $axis2]);


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
        $indicator->setUnit(new Unit_API($unitRef));
        if ($ratioUnitRef !== null) {
            $indicator->setRatioUnit(new Unit_API($ratioUnitRef));
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
