<?php

namespace Inventory\Command\PopulateDB\Base;

use Classification\Domain\Axis;
use Classification\Domain\Context;
use Classification\Domain\ContextIndicator;
use Classification\Domain\Indicator;
use Classification\Domain\AxisMember;
use Classification\Domain\ClassificationLibrary;
use Symfony\Component\Console\Output\OutputInterface;
use Unit\UnitAPI;

/**
 * Remplissage de la base de données avec des données de test
 */
abstract class AbstractPopulateClassification
{
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

    // Création des contexte-indicateurs.
    //  + createContextIndicator : -
    // Params : Context, Indicator
    // OptionalParams : [Axis]=[]

    abstract public function run(OutputInterface $output);

    protected function createAxis(ClassificationLibrary $library, $ref, $label, Axis $narrower = null)
    {
        $axis = new Axis($library);
        $axis->setRef($ref);
        $axis->setLabel($label);
        if ($narrower !== null) {
            $axis->setDirectNarrower($narrower);
        }
        $axis->save();
        return $axis;
    }

    protected function createMember(Axis $axis, $ref, $label, array $parents = [])
    {
        $member = new AxisMember();
        $member->setAxis($axis);
        $member->setRef($ref);
        $member->setLabel($label);
        foreach ($parents as $directParent) {
            $member->addDirectParent($directParent);
        }
        $member->save();
        return $member;
    }

    /**
     * @param ClassificationLibrary $library
     * @param string           $ref
     * @param string           $label
     * @param string           $unitRef
     * @param string|null      $ratioUnitRef
     * @return Indicator
     */
    protected function createIndicator(ClassificationLibrary $library, $ref, $label, $unitRef, $ratioUnitRef = null)
    {
        $ratioUnit = $ratioUnitRef ? new UnitAPI($ratioUnitRef) : null;

        $indicator = new Indicator($library, $ref, $label, new UnitAPI($unitRef), $ratioUnit);
        $indicator->save();

        $library->addIndicator($indicator);

        return $indicator;
    }

    protected function createContext(ClassificationLibrary $library, $ref, $label)
    {
        $context = new Context($library);
        $context->setRef($ref);
        $context->setLabel($label);
        $context->save();
        return $context;
    }

    protected function createContextIndicator(
        ClassificationLibrary $library,
        Context $context,
        Indicator $indicator,
        array $axes = []
    ) {
        $contextIndicator = new ContextIndicator($library, $context, $indicator);
        foreach ($axes as $axis) {
            $contextIndicator->addAxis($axis);
        }
        $contextIndicator->save();
        return $contextIndicator;
    }
}
